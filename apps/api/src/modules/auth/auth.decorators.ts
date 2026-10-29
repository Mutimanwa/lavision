import {
  createParamDecorator,
  SetMetadata,
  type ExecutionContext,
} from "@nestjs/common";
import type { FastifyRequest } from "fastify";
import type {
  AuthenticatedRequest,
  AuthRequestContext,
  SessionAuthContext,
} from "./auth.types.js";

export const IS_PUBLIC_ROUTE = "auth:is-public-route";
export const Public = () => SetMetadata(IS_PUBLIC_ROUTE, true);

export const CurrentAuth = createParamDecorator(
  (_data: unknown, context: ExecutionContext): SessionAuthContext => {
    return context.switchToHttp().getRequest<AuthenticatedRequest>().auth;
  },
);

export function authRequestContext(
  request: FastifyRequest,
  subject?: string,
): AuthRequestContext {
  const userAgent = request.headers["user-agent"];
  return {
    requestId: request.id,
    ipAddress: request.ip,
    userAgent: Array.isArray(userAgent) ? userAgent[0] : userAgent,
    subject,
  };
}
