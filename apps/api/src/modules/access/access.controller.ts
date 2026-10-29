import {
  Body,
  Controller,
  Get,
  HttpCode,
  HttpStatus,
  Post,
  Query,
  Req,
  UseGuards,
} from "@nestjs/common";
import {
  auditQuerySchema,
  PERMISSIONS,
  permissionGrantInputSchema,
  permissionRevokeInputSchema,
  roleAssignmentInputSchema,
  roleRevokeInputSchema,
} from "@lavision/contracts";
import type { FastifyRequest } from "fastify";
import { parseInput } from "../../common/validation.js";
import {
  authRequestContext,
  CurrentAuth,
} from "../auth/auth.decorators.js";
import type { SessionAuthContext } from "../auth/auth.types.js";
import { RequirePermission } from "./access.decorators.js";
import { presentAuditEvent } from "./access.presenters.js";
import { AccessService } from "./access.service.js";
import { AuditService } from "./audit.service.js";
import { PermissionGuard } from "./permission.guard.js";

const bodyOrganization = { in: "body", key: "organizationId" } as const;
const queryOrganization = { in: "query", key: "organizationId" } as const;
const manageRequirement = {
  permission: PERMISSIONS.securityPermissionsManage,
  organization: bodyOrganization,
  resource: bodyOrganization,
  resourceType: "organization",
} as const;
const auditRequirement = {
  permission: PERMISSIONS.auditRead,
  organization: queryOrganization,
  resource: queryOrganization,
  resourceType: "organization",
} as const;

@Controller("security")
@UseGuards(PermissionGuard)
export class AccessController {
  constructor(
    private readonly access: AccessService,
    private readonly audit: AuditService,
  ) {}

  @Post("permissions/grant")
  @HttpCode(HttpStatus.CREATED)
  @RequirePermission(manageRequirement)
  grantPermission(
    @CurrentAuth() actor: SessionAuthContext,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.access.grantPermission(
      actor,
      parseInput(permissionGrantInputSchema, body),
      authRequestContext(request),
    );
  }

  @Post("permissions/revoke")
  @HttpCode(HttpStatus.OK)
  @RequirePermission(manageRequirement)
  revokePermission(
    @CurrentAuth() actor: SessionAuthContext,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.access.revokePermission(
      actor,
      parseInput(permissionRevokeInputSchema, body),
      authRequestContext(request),
    );
  }

  @Post("roles/assign")
  @HttpCode(HttpStatus.CREATED)
  @RequirePermission(manageRequirement)
  assignRole(
    @CurrentAuth() actor: SessionAuthContext,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.access.assignRole(
      actor,
      parseInput(roleAssignmentInputSchema, body),
      authRequestContext(request),
    );
  }

  @Post("roles/revoke")
  @HttpCode(HttpStatus.OK)
  @RequirePermission(manageRequirement)
  revokeRole(
    @CurrentAuth() actor: SessionAuthContext,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.access.revokeRole(
      actor,
      parseInput(roleRevokeInputSchema, body),
      authRequestContext(request),
    );
  }

  @Get("audit")
  @RequirePermission(auditRequirement)
  async listAudit(
    @Query() query: unknown,
  ) {
    const events = await this.audit.list(parseInput(auditQuerySchema, query));
    return { events: events.map(presentAuditEvent) };
  }
}
