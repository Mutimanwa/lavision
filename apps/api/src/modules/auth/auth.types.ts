import type { FastifyRequest } from "fastify";
import type { Prisma } from "../../database/client.js";

export interface AuthRequestContext {
  requestId: string;
  ipAddress?: string;
  userAgent?: string;
  subject?: string;
}

export const authSessionInclude = {
  user: {
    include: {
      mfaCredential: true,
      memberships: {
        include: {
          organization: true,
          roleAssignments: true,
        },
      },
    },
  },
} satisfies Prisma.AuthSessionInclude;

export type SessionWithUser = Prisma.AuthSessionGetPayload<{
  include: typeof authSessionInclude;
}>;

export interface SessionAuthContext {
  session: SessionWithUser;
  sessionToken: string;
}

export type AuthenticatedRequest = FastifyRequest & {
  auth: SessionAuthContext;
};

export interface IssuedSession {
  token: string;
  csrfToken: string;
  session: SessionWithUser;
}
