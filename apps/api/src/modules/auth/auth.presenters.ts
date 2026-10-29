import type {
  AuthSessionResponse,
  AuthUserResponse,
  AuthenticatedResponse,
} from "@lavision/contracts";
import type {
  AuthSession,
  Organization,
  OrganizationMembership,
  RoleAssignment,
  User,
} from "../../database/client.js";
import { ROLE_ID_BY_PRISMA_ROLE } from "./auth.policy.js";
import type { SessionWithUser } from "./auth.types.js";

export function presentUser(user: User): AuthUserResponse {
  return {
    id: user.id,
    email: user.email,
    displayName: user.displayName,
    status: user.status,
  };
}

export function presentSession(
  session: AuthSession,
  currentSessionId = session.id,
): AuthSessionResponse {
  return {
    id: session.id,
    createdAt: session.createdAt.toISOString(),
    lastSeenAt: session.lastSeenAt.toISOString(),
    expiresAt: session.expiresAt.toISOString(),
    absoluteExpiresAt: session.absoluteExpiresAt.toISOString(),
    mfaVerifiedAt: session.mfaVerifiedAt?.toISOString() ?? null,
    userAgent: session.userAgent,
    current: session.id === currentSessionId,
  };
}

export function presentAuthenticated(
  session: SessionWithUser,
  recoveryCodes?: string[],
): AuthenticatedResponse & { recoveryCodes?: string[] } {
  return {
    status: "authenticated",
    user: presentUser(session.user),
    session: presentSession(session),
    ...(recoveryCodes ? { recoveryCodes } : {}),
  };
}

export function presentMembership(
  membership: OrganizationMembership & {
    organization: Organization;
    roleAssignments: RoleAssignment[];
  },
) {
  return {
    organizationId: membership.organizationId,
    organizationCode: membership.organization.code,
    organizationName: membership.organization.name,
    roles: membership.roleAssignments
      .filter((assignment) => !assignment.archivedAt)
      .map((assignment) => ROLE_ID_BY_PRISMA_ROLE[assignment.role]),
  };
}
