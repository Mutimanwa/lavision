import {
  Body,
  Controller,
  Delete,
  Get,
  HttpCode,
  HttpStatus,
  Param,
  Post,
  Req,
  Res,
} from "@nestjs/common";
import {
  acceptInvitationInputSchema,
  authSessionIdSchema,
  invitationInputSchema,
  loginInputSchema,
  mfaRecoveryInputSchema,
  mfaSetupInputSchema,
  mfaVerifyInputSchema,
  organizationActionInputSchema,
  passwordResetConfirmInputSchema,
  passwordResetRequestInputSchema,
} from "@lavision/contracts";
import type { FastifyReply, FastifyRequest } from "fastify";
import { parseInput } from "../../common/validation.js";
import { AuthCookieService } from "./auth.cookies.js";
import {
  authRequestContext,
  CurrentAuth,
  Public,
} from "./auth.decorators.js";
import { AuthService } from "./auth.service.js";
import type { SessionAuthContext } from "./auth.types.js";

@Controller("auth")
export class AuthController {
  constructor(
    private readonly auth: AuthService,
    private readonly cookies: AuthCookieService,
  ) {}

  @Public()
  @Post("login")
  @HttpCode(HttpStatus.OK)
  async login(
    @Body() body: unknown,
    @Req() request: FastifyRequest,
    @Res({ passthrough: true }) reply: FastifyReply,
  ) {
    const result = await this.auth.login(
      parseInput(loginInputSchema, body),
      authRequestContext(request),
    );
    if (result.status === "mfa_required") {
      reply.code(HttpStatus.ACCEPTED);
      return result.response;
    }
    this.cookies.set(reply, result.authentication.issued);
    return result.authentication.response;
  }

  @Post("admin/invitations")
  @HttpCode(HttpStatus.CREATED)
  invite(
    @CurrentAuth() actor: SessionAuthContext,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.auth.invite(
      actor,
      parseInput(invitationInputSchema, body),
      authRequestContext(request),
    );
  }

  @Public()
  @Post("invitations/accept")
  @HttpCode(HttpStatus.OK)
  async acceptInvitation(
    @Body() body: unknown,
    @Req() request: FastifyRequest,
    @Res({ passthrough: true }) reply: FastifyReply,
  ) {
    const result = await this.auth.acceptInvitation(
      parseInput(acceptInvitationInputSchema, body),
      authRequestContext(request),
    );
    if (result.status === "mfa_required") {
      reply.code(HttpStatus.ACCEPTED);
      return result.response;
    }
    this.cookies.set(reply, result.authentication.issued);
    return result.authentication.response;
  }

  @Public()
  @Post("password-reset/request")
  @HttpCode(HttpStatus.ACCEPTED)
  async requestPasswordReset(
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    await this.auth.requestPasswordReset(
      parseInput(passwordResetRequestInputSchema, body),
      authRequestContext(request),
    );
    return { status: "accepted" };
  }

  @Public()
  @Post("password-reset/confirm")
  @HttpCode(HttpStatus.NO_CONTENT)
  async confirmPasswordReset(
    @Body() body: unknown,
    @Req() request: FastifyRequest,
    @Res({ passthrough: true }) reply: FastifyReply,
  ) {
    await this.auth.confirmPasswordReset(
      parseInput(passwordResetConfirmInputSchema, body),
      authRequestContext(request),
    );
    this.cookies.clear(reply);
  }

  @Public()
  @Post("mfa/setup")
  @HttpCode(HttpStatus.OK)
  setupMfa(@Body() body: unknown, @Req() request: FastifyRequest) {
    return this.auth.setupMfa(
      parseInput(mfaSetupInputSchema, body),
      authRequestContext(request),
    );
  }

  @Public()
  @Post("mfa/setup/confirm")
  @HttpCode(HttpStatus.OK)
  async confirmMfaSetup(
    @Body() body: unknown,
    @Req() request: FastifyRequest,
    @Res({ passthrough: true }) reply: FastifyReply,
  ) {
    const result = await this.auth.confirmMfaSetup(
      parseInput(mfaVerifyInputSchema, body),
      authRequestContext(request),
    );
    this.cookies.set(reply, result.issued);
    return result.response;
  }

  @Public()
  @Post("mfa/verify")
  @HttpCode(HttpStatus.OK)
  async verifyMfa(
    @Body() body: unknown,
    @Req() request: FastifyRequest,
    @Res({ passthrough: true }) reply: FastifyReply,
  ) {
    const result = await this.auth.verifyMfa(
      parseInput(mfaVerifyInputSchema, body),
      authRequestContext(request),
    );
    this.cookies.set(reply, result.issued);
    return result.response;
  }

  @Public()
  @Post("mfa/recovery")
  @HttpCode(HttpStatus.OK)
  async recoverMfa(
    @Body() body: unknown,
    @Req() request: FastifyRequest,
    @Res({ passthrough: true }) reply: FastifyReply,
  ) {
    const result = await this.auth.recoverMfa(
      parseInput(mfaRecoveryInputSchema, body),
      authRequestContext(request),
    );
    this.cookies.set(reply, result.issued);
    return result.response;
  }

  @Get("me")
  me(@CurrentAuth() auth: SessionAuthContext) {
    return this.auth.me(auth);
  }

  @Post("refresh")
  @HttpCode(HttpStatus.OK)
  async refresh(
    @CurrentAuth() auth: SessionAuthContext,
    @Req() request: FastifyRequest,
    @Res({ passthrough: true }) reply: FastifyReply,
  ) {
    const result = await this.auth.refresh(
      auth,
      authRequestContext(request),
    );
    this.cookies.set(reply, result.issued);
    return result.response;
  }

  @Post("logout")
  @HttpCode(HttpStatus.NO_CONTENT)
  async logout(
    @CurrentAuth() auth: SessionAuthContext,
    @Req() request: FastifyRequest,
    @Res({ passthrough: true }) reply: FastifyReply,
  ) {
    await this.auth.logout(auth, authRequestContext(request));
    this.cookies.clear(reply);
  }

  @Get("sessions")
  listSessions(@CurrentAuth() auth: SessionAuthContext) {
    return this.auth.listSessions(auth);
  }

  @Delete("sessions/:id")
  @HttpCode(HttpStatus.NO_CONTENT)
  async revokeSession(
    @CurrentAuth() auth: SessionAuthContext,
    @Param("id") id: string,
    @Req() request: FastifyRequest,
    @Res({ passthrough: true }) reply: FastifyReply,
  ) {
    const current = await this.auth.revokeSession(
      auth,
      parseInput(authSessionIdSchema, id),
      authRequestContext(request),
    );
    if (current) this.cookies.clear(reply);
  }

  @Post("admin/users/:id/disable")
  @HttpCode(HttpStatus.NO_CONTENT)
  async disableUser(
    @CurrentAuth() actor: SessionAuthContext,
    @Param("id") id: string,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    await this.auth.disableUser(
      actor,
      parseInput(authSessionIdSchema, id),
      parseInput(organizationActionInputSchema, body),
      authRequestContext(request),
    );
  }

  @Post("admin/users/:id/sessions/revoke")
  @HttpCode(HttpStatus.NO_CONTENT)
  async revokeUserSessions(
    @CurrentAuth() actor: SessionAuthContext,
    @Param("id") id: string,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    await this.auth.revokeUserSessions(
      actor,
      parseInput(authSessionIdSchema, id),
      parseInput(organizationActionInputSchema, body),
      authRequestContext(request),
    );
  }
}
