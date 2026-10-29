import "@fastify/cookie";
import { Injectable } from "@nestjs/common";
import type { FastifyReply, FastifyRequest } from "fastify";
import { AuthConfigService } from "./auth.config.js";
import type { IssuedSession } from "./auth.types.js";

export const SESSION_COOKIE = "lavision_session";
export const CSRF_COOKIE = "lavision_csrf";

@Injectable()
export class AuthCookieService {
  constructor(private readonly config: AuthConfigService) {}

  set(reply: FastifyReply, issued: IssuedSession): void {
    const options = {
      path: "/",
      secure: this.config.cookieSecure,
      sameSite: "strict" as const,
      expires: issued.session.expiresAt,
      priority: "high" as const,
    };
    reply.setCookie(SESSION_COOKIE, issued.token, {
      ...options,
      httpOnly: true,
    });
    reply.setCookie(CSRF_COOKIE, issued.csrfToken, {
      ...options,
      httpOnly: false,
    });
  }

  clear(reply: FastifyReply): void {
    const options = {
      path: "/",
      secure: this.config.cookieSecure,
      sameSite: "strict" as const,
    };
    reply.clearCookie(SESSION_COOKIE, options);
    reply.clearCookie(CSRF_COOKIE, options);
  }

  sessionToken(request: FastifyRequest): string | undefined {
    return request.cookies[SESSION_COOKIE];
  }

  csrfToken(request: FastifyRequest): string | undefined {
    const header = request.headers["x-csrf-token"];
    return Array.isArray(header) ? header[0] : header;
  }

  csrfCookie(request: FastifyRequest): string | undefined {
    return request.cookies[CSRF_COOKIE];
  }
}
