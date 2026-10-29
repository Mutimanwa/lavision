import type {
  AuditEventResponse,
  PermissionGrantResponse,
  RoleAssignmentResponse,
} from "@lavision/contracts";
import type {
  AccessResource,
  AuditEvent,
  OrganizationMembership,
  PermissionGrant,
  RoleAssignment,
} from "../../database/client.js";
import { ROLE_ID_BY_PRISMA_ROLE } from "../auth/auth.policy.js";
import { CONTRACT_RESOURCE_TYPE } from "./access.types.js";
import { sanitizeAuditContext } from "./audit.service.js";

export function presentScope(resource: AccessResource) {
  return {
    type: CONTRACT_RESOURCE_TYPE[resource.type],
    id: resource.id,
  };
}

export function presentPermissionGrant(
  grant: PermissionGrant & {
    scope: AccessResource;
    membership: OrganizationMembership;
  },
): PermissionGrantResponse {
  return {
    id: grant.id,
    userId: grant.membership.userId,
    permission: grant.permission as PermissionGrantResponse["permission"],
    scope: presentScope(grant.scope),
    createdAt: grant.createdAt.toISOString(),
    archivedAt: grant.archivedAt?.toISOString() ?? null,
  };
}

export function presentRoleAssignment(
  assignment: RoleAssignment & {
    scope: AccessResource;
    membership: OrganizationMembership;
  },
): RoleAssignmentResponse {
  return {
    id: assignment.id,
    userId: assignment.membership.userId,
    role: ROLE_ID_BY_PRISMA_ROLE[assignment.role],
    scope: presentScope(assignment.scope),
    createdAt: assignment.createdAt.toISOString(),
    archivedAt: assignment.archivedAt?.toISOString() ?? null,
  };
}

export function presentAuditEvent(event: AuditEvent): AuditEventResponse {
  return {
    id: event.id,
    organizationId: event.organizationId,
    actorUserId: event.actorUserId,
    action: event.action,
    resourceType: event.resourceType,
    resourceId: event.resourceId,
    outcome: event.outcome,
    requestId: event.requestId,
    reason: event.reason,
    context: sanitizeAuditContext(event.context),
    createdAt: event.createdAt.toISOString(),
  };
}
