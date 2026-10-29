import { ForbiddenException, Injectable } from "@nestjs/common";
import {
  ERROR_CODES,
  PERMISSIONS,
  ROLE_PERMISSIONS,
  type AccessScope,
  type Permission,
} from "@lavision/contracts";
import {
  AccessRelationType,
  AccessResourceType as PrismaResourceType,
  MembershipStatus,
  Prisma,
  Role,
  type AccessResource,
} from "../../database/client.js";
import { ROLE_ID_BY_PRISMA_ROLE } from "../auth/auth.policy.js";
import { PrismaService } from "../infrastructure/prisma.service.js";
import {
  PRISMA_RELATION_TYPE,
  PRISMA_RESOURCE_TYPE,
  type AuthorizationDecision,
  type AuthorizationInput,
} from "./access.types.js";

const membershipAccessInclude = {
  roleAssignments: {
    where: { archivedAt: null },
    include: { scope: true },
  },
  permissionGrants: {
    where: { archivedAt: null },
    include: { scope: true },
  },
} satisfies Prisma.OrganizationMembershipInclude;

type MembershipWithAccess = Prisma.OrganizationMembershipGetPayload<{
  include: typeof membershipAccessInclude;
}>;

@Injectable()
export class AuthorizationService {
  constructor(private readonly prisma: PrismaService) {}

  async authorize(input: AuthorizationInput): Promise<AuthorizationDecision> {
    if (
      input.resourceType === "organization" &&
      input.resourceId === input.organizationId
    ) {
      return this.authorizeOrganization(input);
    }

    const resource = await this.prisma.accessResource.findUnique({
      where: { id: input.resourceId },
    });
    if (
      !resource ||
      resource.archivedAt ||
      resource.organizationId !== input.organizationId ||
      resource.type !== PRISMA_RESOURCE_TYPE[input.resourceType]
    ) {
      return { allowed: false, reason: "RESOURCE_MISMATCH" };
    }

    const memberships = await this.prisma.organizationMembership.findMany({
      where: {
        userId: input.userId,
        organizationId: input.organizationId,
        status: MembershipStatus.ACTIVE,
        archivedAt: null,
      },
      include: membershipAccessInclude,
    });
    if (memberships.length === 0) {
      return { allowed: false, reason: "NO_MEMBERSHIP" };
    }

    let sawPermission = false;
    let sawScope = false;
    let sawRelation = false;
    for (const membership of memberships) {
      for (const assignment of membership.roleAssignments) {
        const roleId = ROLE_ID_BY_PRISMA_ROLE[assignment.role];
        if (!ROLE_PERMISSIONS[roleId].includes(input.permission)) continue;
        sawPermission = true;
        if (!this.scopeMatches(assignment.scope, resource)) continue;
        sawScope = true;
        const relations = this.requiredRelations(
          assignment.role,
          input.permission,
          resource,
          input.relation,
        );
        if (await this.hasRelation(membership, relations, resource)) {
          return { allowed: true, reason: "ALLOWED" };
        }
        sawRelation = true;
      }

      for (const grant of membership.permissionGrants) {
        if (grant.permission !== input.permission) continue;
        sawPermission = true;
        if (!this.scopeMatches(grant.scope, resource)) continue;
        sawScope = true;
        const relations = this.relationsForGrant(
          membership,
          input.permission,
          resource,
          input.relation,
        );
        if (await this.hasRelation(membership, relations, resource)) {
          return { allowed: true, reason: "ALLOWED" };
        }
        sawRelation = true;
      }
    }

    if (sawRelation) return { allowed: false, reason: "RELATION_REQUIRED" };
    if (sawScope) return { allowed: false, reason: "NO_PERMISSION" };
    if (sawPermission) return { allowed: false, reason: "SCOPE_MISMATCH" };
    return { allowed: false, reason: "NO_PERMISSION" };
  }

  private async authorizeOrganization(
    input: AuthorizationInput,
  ): Promise<AuthorizationDecision> {
    const memberships = await this.prisma.organizationMembership.findMany({
      where: {
        userId: input.userId,
        organizationId: input.organizationId,
        status: MembershipStatus.ACTIVE,
        archivedAt: null,
      },
      include: membershipAccessInclude,
    });
    if (memberships.length === 0) {
      return { allowed: false, reason: "NO_MEMBERSHIP" };
    }

    let sawPermission = false;
    for (const membership of memberships) {
      for (const assignment of membership.roleAssignments) {
        const roleId = ROLE_ID_BY_PRISMA_ROLE[assignment.role];
        if (!ROLE_PERMISSIONS[roleId].includes(input.permission)) continue;
        sawPermission = true;
        if (
          assignment.scope.type === PrismaResourceType.ORGANIZATION &&
          assignment.scope.id === input.organizationId &&
          !assignment.scope.archivedAt
        ) {
          return { allowed: true, reason: "ALLOWED" };
        }
      }
      for (const grant of membership.permissionGrants) {
        if (grant.permission !== input.permission) continue;
        sawPermission = true;
        if (
          grant.scope.type === PrismaResourceType.ORGANIZATION &&
          grant.scope.id === input.organizationId &&
          !grant.scope.archivedAt
        ) {
          return { allowed: true, reason: "ALLOWED" };
        }
      }
    }
    return sawPermission
      ? { allowed: false, reason: "SCOPE_MISMATCH" }
      : { allowed: false, reason: "NO_PERMISSION" };
  }

  async assertAuthorized(input: AuthorizationInput): Promise<void> {
    const decision = await this.authorize(input);
    if (!decision.allowed) {
      throw new ForbiddenException({ code: ERROR_CODES.FORBIDDEN });
    }
  }

  async findScope(
    scope: AccessScope,
    organizationId: string,
  ): Promise<AccessResource | null> {
    const resource = await this.prisma.accessResource.findUnique({
      where: { id: scope.id },
    });
    if (
      !resource ||
      resource.archivedAt ||
      resource.organizationId !== organizationId ||
      resource.type !== PRISMA_RESOURCE_TYPE[scope.type]
    ) {
      return null;
    }
    return resource;
  }

  private scopeMatches(scope: AccessResource, target: AccessResource): boolean {
    if (scope.archivedAt || scope.organizationId !== target.organizationId) {
      return false;
    }
    if (scope.id === target.id || scope.id === target.parentId) return true;
    if (scope.type === PrismaResourceType.ORGANIZATION) {
      return scope.id === target.organizationId;
    }
    if (scope.type === PrismaResourceType.SITE) {
      return target.siteId === scope.id;
    }
    return false;
  }

  private relationsForGrant(
    membership: MembershipWithAccess,
    permission: Permission,
    resource: AccessResource,
    explicitRelation?: AuthorizationInput["relation"],
  ): AccessRelationType[] {
    if (explicitRelation) return [PRISMA_RELATION_TYPE[explicitRelation]];
    const relevantAssignments = membership.roleAssignments.filter((assignment) =>
      ROLE_PERMISSIONS[ROLE_ID_BY_PRISMA_ROLE[assignment.role]].includes(permission),
    );
    if (
      relevantAssignments.some(
        (assignment) =>
          this.requiredRelations(assignment.role, permission, resource).length === 0,
      )
    ) {
      return [];
    }
    return [
      ...new Set(
        relevantAssignments.flatMap((assignment) =>
          this.requiredRelations(assignment.role, permission, resource),
        ),
      ),
    ];
  }

  private requiredRelations(
    role: Role,
    permission: Permission,
    resource: AccessResource,
    explicitRelation?: AuthorizationInput["relation"],
  ): AccessRelationType[] {
    if (explicitRelation) return [PRISMA_RELATION_TYPE[explicitRelation]];

    if (role === Role.TEACHER) {
      if (
        permission === PERMISSIONS.notesWrite ||
        permission === PERMISSIONS.attendanceWrite
      ) {
        return resource.type === PrismaResourceType.SUBJECT
          ? [AccessRelationType.TEACHER_SUBJECT]
          : [AccessRelationType.TEACHER_CLASS];
      }
      if (
        permission === PERMISSIONS.classesRead ||
        permission === PERMISSIONS.notesRead ||
        permission === PERMISSIONS.attendanceRead
      ) {
        return resource.type === PrismaResourceType.SUBJECT
          ? [AccessRelationType.TEACHER_SUBJECT]
          : [AccessRelationType.TEACHER_CLASS, AccessRelationType.PRINCIPAL_CLASS];
      }
    }
    if (
      role === Role.GUARDIAN &&
      (permission === PERMISSIONS.studentsRead ||
        permission === PERMISSIONS.financeFamilyRead)
    ) {
      return [AccessRelationType.GUARDIAN_STUDENT];
    }
    if (role === Role.STUDENT && permission === PERMISSIONS.studentsRead) {
      return [AccessRelationType.STUDENT_SELF];
    }
    return [];
  }

  private async hasRelation(
    membership: MembershipWithAccess,
    relations: AccessRelationType[],
    resource: AccessResource,
  ): Promise<boolean> {
    if (relations.length === 0) return true;
    const resourceIds = [resource.id, resource.parentId].filter(
      (id): id is string => Boolean(id),
    );
    const relation = await this.prisma.accessRelation.findFirst({
      where: {
        membershipId: membership.id,
        relation: { in: relations },
        resourceId: { in: resourceIds },
        archivedAt: null,
        resource: {
          organizationId: resource.organizationId,
          archivedAt: null,
        },
      },
      select: { id: true },
    });
    return Boolean(relation);
  }
}
