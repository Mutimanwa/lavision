import { ForbiddenException, Injectable } from "@nestjs/common";
import {
  ERROR_CODES,
  PERMISSIONS,
  type PermissionGrantInput,
  type PermissionRevokeInput,
  type RoleAssignmentInput,
  type RoleRevokeInput,
} from "@lavision/contracts";
import {
  AuditOutcome,
  MembershipStatus,
  type OrganizationMembership,
} from "../../database/client.js";
import { PrismaService } from "../infrastructure/prisma.service.js";
import {
  PRISMA_ROLE_BY_ROLE_ID,
  ROLE_ID_BY_PRISMA_ROLE,
} from "../auth/auth.policy.js";
import type { AuthRequestContext, SessionAuthContext } from "../auth/auth.types.js";
import {
  presentPermissionGrant,
  presentRoleAssignment,
} from "./access.presenters.js";
import { AuditService } from "./audit.service.js";
import { AuthorizationService } from "./authorization.service.js";

@Injectable()
export class AccessService {
  constructor(
    private readonly prisma: PrismaService,
    private readonly authorization: AuthorizationService,
    private readonly audit: AuditService,
  ) {}

  async grantPermission(
    actor: SessionAuthContext,
    input: PermissionGrantInput,
    context: AuthRequestContext,
  ) {
    await this.assertManage(actor, input.organizationId);
    this.rejectSelfElevation(actor, input.userId);
    const membership = await this.targetMembership(input.userId, input.organizationId);
    const scope = await this.requiredScope(input.scope, input.organizationId);

    const grant = await this.prisma.$transaction(async (transaction) => {
      const created = await transaction.permissionGrant.upsert({
        where: {
          membershipId_permission_scopeId: {
            membershipId: membership.id,
            permission: input.permission,
            scopeId: scope.id,
          },
        },
        create: {
          membershipId: membership.id,
          permission: input.permission,
          scopeId: scope.id,
        },
        update: { archivedAt: null },
        include: { membership: true, scope: true },
      });
      await this.audit.record(
        {
          organizationId: input.organizationId,
          actorUserId: actor.session.userId,
          action: "permissions.grant",
          resourceType: "permission",
          resourceId: created.id,
          outcome: AuditOutcome.SUCCESS,
          requestId: context.requestId,
          context: { permission: input.permission, scope: input.scope.type },
        },
        transaction,
      );
      return created;
    });
    return presentPermissionGrant(grant);
  }

  async revokePermission(
    actor: SessionAuthContext,
    input: PermissionRevokeInput,
    context: AuthRequestContext,
  ) {
    await this.assertManage(actor, input.organizationId);
    const existing = await this.prisma.permissionGrant.findUnique({
      where: { id: input.grantId },
      include: { membership: true, scope: true },
    });
    if (!existing || existing.membership.organizationId !== input.organizationId) {
      throw new ForbiddenException({ code: ERROR_CODES.FORBIDDEN });
    }

    const grant = await this.prisma.$transaction(async (transaction) => {
      const updated = await transaction.permissionGrant.update({
        where: { id: existing.id },
        data: { archivedAt: existing.archivedAt ?? new Date() },
        include: { membership: true, scope: true },
      });
      await this.audit.record(
        {
          organizationId: input.organizationId,
          actorUserId: actor.session.userId,
          action: "permissions.revoke",
          resourceType: "permission",
          resourceId: updated.id,
          outcome: AuditOutcome.SUCCESS,
          requestId: context.requestId,
          context: { permission: updated.permission },
        },
        transaction,
      );
      return updated;
    });
    return presentPermissionGrant(grant);
  }

  async assignRole(
    actor: SessionAuthContext,
    input: RoleAssignmentInput,
    context: AuthRequestContext,
  ) {
    await this.assertManage(actor, input.organizationId);
    this.rejectSelfElevation(actor, input.userId);
    const membership = await this.targetMembership(input.userId, input.organizationId);
    const scope = await this.requiredScope(input.scope, input.organizationId);

    const assignment = await this.prisma.$transaction(async (transaction) => {
      const created = await transaction.roleAssignment.upsert({
        where: {
          membershipId_role_scopeId: {
            membershipId: membership.id,
            role: PRISMA_ROLE_BY_ROLE_ID[input.role],
            scopeId: scope.id,
          },
        },
        create: {
          membershipId: membership.id,
          role: PRISMA_ROLE_BY_ROLE_ID[input.role],
          scopeId: scope.id,
        },
        update: { archivedAt: null },
        include: { membership: true, scope: true },
      });
      await this.audit.record(
        {
          organizationId: input.organizationId,
          actorUserId: actor.session.userId,
          action: "permissions.role.assign",
          resourceType: "permission",
          resourceId: created.id,
          outcome: AuditOutcome.SUCCESS,
          requestId: context.requestId,
          context: { permission: input.role, scope: input.scope.type },
        },
        transaction,
      );
      return created;
    });
    return presentRoleAssignment(assignment);
  }

  async revokeRole(
    actor: SessionAuthContext,
    input: RoleRevokeInput,
    context: AuthRequestContext,
  ) {
    await this.assertManage(actor, input.organizationId);
    const existing = await this.prisma.roleAssignment.findUnique({
      where: { id: input.assignmentId },
      include: { membership: true, scope: true },
    });
    if (!existing || existing.membership.organizationId !== input.organizationId) {
      throw new ForbiddenException({ code: ERROR_CODES.FORBIDDEN });
    }

    const assignment = await this.prisma.$transaction(async (transaction) => {
      const updated = await transaction.roleAssignment.update({
        where: { id: existing.id },
        data: { archivedAt: existing.archivedAt ?? new Date() },
        include: { membership: true, scope: true },
      });
      await this.audit.record(
        {
          organizationId: input.organizationId,
          actorUserId: actor.session.userId,
          action: "permissions.role.revoke",
          resourceType: "permission",
          resourceId: updated.id,
          outcome: AuditOutcome.SUCCESS,
          requestId: context.requestId,
          context: { permission: ROLE_ID_BY_PRISMA_ROLE[updated.role] },
        },
        transaction,
      );
      return updated;
    });
    return presentRoleAssignment(assignment);
  }

  private async assertManage(
    actor: SessionAuthContext,
    organizationId: string,
  ): Promise<void> {
    await this.authorization.assertAuthorized({
      userId: actor.session.userId,
      organizationId,
      permission: PERMISSIONS.securityPermissionsManage,
      resourceId: organizationId,
      resourceType: "organization",
    });
  }

  private async targetMembership(
    userId: string,
    organizationId: string,
  ): Promise<OrganizationMembership> {
    const membership = await this.prisma.organizationMembership.findFirst({
      where: {
        userId,
        organizationId,
        status: MembershipStatus.ACTIVE,
        archivedAt: null,
      },
    });
    if (!membership) {
      throw new ForbiddenException({ code: ERROR_CODES.FORBIDDEN });
    }
    return membership;
  }

  private async requiredScope(
    scope: RoleAssignmentInput["scope"],
    organizationId: string,
  ) {
    const resource = await this.authorization.findScope(scope, organizationId);
    if (!resource) {
      throw new ForbiddenException({ code: ERROR_CODES.FORBIDDEN });
    }
    return resource;
  }

  private rejectSelfElevation(actor: SessionAuthContext, userId: string): void {
    if (actor.session.userId === userId) {
      throw new ForbiddenException({ code: ERROR_CODES.FORBIDDEN });
    }
  }
}
