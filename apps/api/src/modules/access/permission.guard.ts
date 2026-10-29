import {
  BadRequestException,
  CanActivate,
  ExecutionContext,
  ForbiddenException,
  Injectable,
  UnauthorizedException,
} from "@nestjs/common";
import { Reflector } from "@nestjs/core";
import { ERROR_CODES } from "@lavision/contracts";
import { z } from "zod";
import { AuditOutcome } from "../../database/client.js";
import type { AuthenticatedRequest } from "../auth/auth.types.js";
import { PERMISSION_REQUIREMENT } from "./access.decorators.js";
import type { PermissionRequirement, ValueSource } from "./access.types.js";
import { AuditService } from "./audit.service.js";
import { AuthorizationService } from "./authorization.service.js";

@Injectable()
export class PermissionGuard implements CanActivate {
  constructor(
    private readonly reflector: Reflector,
    private readonly authorization: AuthorizationService,
    private readonly audit: AuditService,
  ) {}

  async canActivate(context: ExecutionContext): Promise<boolean> {
    const requirement = this.reflector.getAllAndOverride<PermissionRequirement>(
      PERMISSION_REQUIREMENT,
      [context.getHandler(), context.getClass()],
    );
    if (!requirement) return true;

    const request = context
      .switchToHttp()
      .getRequest<AuthenticatedRequest>();
    if (!request.auth) {
      throw new UnauthorizedException({ code: ERROR_CODES.UNAUTHORIZED });
    }
    const organizationId = this.read(request, requirement.organization);
    const resourceId = this.read(request, requirement.resource);
    if (
      !organizationId ||
      !resourceId ||
      !z.uuid().safeParse(organizationId).success ||
      !z.uuid().safeParse(resourceId).success
    ) {
      throw new BadRequestException({ code: ERROR_CODES.VALIDATION_ERROR });
    }

    const decision = await this.authorization.authorize({
      userId: request.auth.session.userId,
      organizationId,
      permission: requirement.permission,
      resourceId,
      resourceType: requirement.resourceType,
      relation: requirement.relation,
    });
    if (!decision.allowed) {
      await this.audit.recordSafe({
        organizationId,
        actorUserId: request.auth.session.userId,
        action: "authorization.denied",
        resourceType: requirement.resourceType,
        resourceId,
        outcome: AuditOutcome.DENIED,
        requestId: request.id,
        reason: decision.reason,
        context: {
          permission: requirement.permission,
          relation: requirement.relation,
        },
      });
      throw new ForbiddenException({ code: ERROR_CODES.FORBIDDEN });
    }
    return true;
  }

  private read(request: AuthenticatedRequest, source: ValueSource) {
    const container = request[source.in] as unknown;
    if (!container || typeof container !== "object") return undefined;
    const value = (container as Record<string, unknown>)[source.key];
    return typeof value === "string" ? value : undefined;
  }
}
