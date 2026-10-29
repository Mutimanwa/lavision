import { Body, Controller, Get, HttpCode, HttpStatus, Param, Patch, Post, Query, Req, UseGuards } from "@nestjs/common";
import {
  PERMISSIONS,
  ruleCatalogQuerySchema,
  ruleOrganizationQuerySchema,
  ruleResolveInputSchema,
  ruleRevisionsQuerySchema,
  ruleSetCreateInputSchema,
  ruleSetListQuerySchema,
  ruleSimulationInputSchema,
  ruleSnapshotInputSchema,
  ruleValidationInputSchema,
  ruleVersionActionInputSchema,
  ruleVersionCreateInputSchema,
  ruleVersionDuplicateInputSchema,
  ruleVersionUpdateInputSchema,
} from "@lavision/contracts";
import type { FastifyRequest } from "fastify";
import { parseInput } from "../../common/validation.js";
import { CurrentAuth, authRequestContext } from "../auth/auth.decorators.js";
import type { SessionAuthContext } from "../auth/auth.types.js";
import { RequirePermission } from "../access/access.decorators.js";
import { PermissionGuard } from "../access/permission.guard.js";
import { RulesService } from "./rules.service.js";

const queryOrganization = { in: "query", key: "organizationId" } as const;
const bodyOrganization = { in: "body", key: "organizationId" } as const;
const readGuard = {
  permission: PERMISSIONS.rulesCatalogRead,
  organization: queryOrganization,
  resource: queryOrganization,
  resourceType: "organization",
} as const;
const writeGuard = {
  permission: PERMISSIONS.rulesCatalogValidate,
  organization: bodyOrganization,
  resource: bodyOrganization,
  resourceType: "organization",
} as const;

@Controller("rules")
@UseGuards(PermissionGuard)
export class RulesController {
  constructor(private readonly rules: RulesService) {}

  @Get("catalog")
  @RequirePermission(readGuard)
  catalog(@CurrentAuth() actor: SessionAuthContext, @Query() query: unknown) {
    return this.rules.catalog(actor, parseInput(ruleCatalogQuerySchema, query));
  }

  @Post("validate")
  @HttpCode(HttpStatus.OK)
  @RequirePermission(writeGuard)
  validate(@CurrentAuth() actor: SessionAuthContext, @Body() body: unknown) {
    return this.rules.validate(actor, parseInput(ruleValidationInputSchema, body));
  }

  @Get("sets")
  @RequirePermission(readGuard)
  listSets(@CurrentAuth() actor: SessionAuthContext, @Query() query: unknown) {
    return this.rules.listSets(actor, parseInput(ruleSetListQuerySchema, query));
  }

  @Post("sets")
  @HttpCode(HttpStatus.CREATED)
  @RequirePermission(writeGuard)
  createSet(@CurrentAuth() actor: SessionAuthContext, @Body() body: unknown, @Req() request: FastifyRequest) {
    return this.rules.createSet(actor, parseInput(ruleSetCreateInputSchema, body), authRequestContext(request));
  }

  @Get("sets/:id")
  @RequirePermission(readGuard)
  getSet(@CurrentAuth() actor: SessionAuthContext, @Param("id") id: string, @Query() query: unknown) {
    return this.rules.getSet(actor, parseInput(ruleOrganizationQuerySchema, query).organizationId, id);
  }

  @Post("sets/:id/versions")
  @HttpCode(HttpStatus.CREATED)
  @RequirePermission(writeGuard)
  createVersion(@CurrentAuth() actor: SessionAuthContext, @Param("id") id: string, @Body() body: unknown, @Req() request: FastifyRequest) {
    return this.rules.createVersion(actor, id, parseInput(ruleVersionCreateInputSchema, body), authRequestContext(request));
  }

  @Get("versions/:id")
  @RequirePermission(readGuard)
  getVersion(@CurrentAuth() actor: SessionAuthContext, @Param("id") id: string, @Query() query: unknown) {
    return this.rules.getVersion(actor, parseInput(ruleOrganizationQuerySchema, query).organizationId, id);
  }

  @Patch("versions/:id")
  @RequirePermission(writeGuard)
  updateVersion(@CurrentAuth() actor: SessionAuthContext, @Param("id") id: string, @Body() body: unknown, @Req() request: FastifyRequest) {
    return this.rules.updateVersion(actor, id, parseInput(ruleVersionUpdateInputSchema, body), authRequestContext(request));
  }

  @Post("versions/:id/duplicate")
  @HttpCode(HttpStatus.CREATED)
  @RequirePermission(writeGuard)
  duplicateVersion(@CurrentAuth() actor: SessionAuthContext, @Param("id") id: string, @Body() body: unknown, @Req() request: FastifyRequest) {
    return this.rules.duplicateVersion(actor, id, parseInput(ruleVersionDuplicateInputSchema, body), authRequestContext(request));
  }

  @Post("versions/:id/submit")
  @HttpCode(HttpStatus.OK)
  @RequirePermission(writeGuard)
  submit(@CurrentAuth() actor: SessionAuthContext, @Param("id") id: string, @Body() body: unknown, @Req() request: FastifyRequest) {
    return this.rules.transitionVersion(actor, id, "submit", parseInput(ruleVersionActionInputSchema, body), authRequestContext(request));
  }

  @Post("versions/:id/review")
  @HttpCode(HttpStatus.OK)
  @RequirePermission(writeGuard)
  review(@CurrentAuth() actor: SessionAuthContext, @Param("id") id: string, @Body() body: unknown, @Req() request: FastifyRequest) {
    return this.rules.transitionVersion(actor, id, "review", parseInput(ruleVersionActionInputSchema, body), authRequestContext(request));
  }

  @Post("versions/:id/approve")
  @HttpCode(HttpStatus.OK)
  @RequirePermission(writeGuard)
  approve(@CurrentAuth() actor: SessionAuthContext, @Param("id") id: string, @Body() body: unknown, @Req() request: FastifyRequest) {
    return this.rules.transitionVersion(actor, id, "approve", parseInput(ruleVersionActionInputSchema, body), authRequestContext(request));
  }

  @Post("versions/:id/publish")
  @HttpCode(HttpStatus.OK)
  @RequirePermission(writeGuard)
  publish(@CurrentAuth() actor: SessionAuthContext, @Param("id") id: string, @Body() body: unknown, @Req() request: FastifyRequest) {
    return this.rules.transitionVersion(actor, id, "publish", parseInput(ruleVersionActionInputSchema, body), authRequestContext(request));
  }

  @Post("versions/:id/archive")
  @HttpCode(HttpStatus.OK)
  @RequirePermission(writeGuard)
  archive(@CurrentAuth() actor: SessionAuthContext, @Param("id") id: string, @Body() body: unknown, @Req() request: FastifyRequest) {
    return this.rules.transitionVersion(actor, id, "archive", parseInput(ruleVersionActionInputSchema, body), authRequestContext(request));
  }

  @Post("resolve")
  @HttpCode(HttpStatus.OK)
  @RequirePermission({
    ...readGuard,
    organization: bodyOrganization,
    resource: bodyOrganization,
  })
  resolve(@CurrentAuth() actor: SessionAuthContext, @Body() body: unknown) {
    return this.rules.resolve(actor, parseInput(ruleResolveInputSchema, body));
  }

  @Post("versions/:id/simulate")
  @HttpCode(HttpStatus.CREATED)
  @RequirePermission(writeGuard)
  simulate(@CurrentAuth() actor: SessionAuthContext, @Param("id") id: string, @Body() body: unknown, @Req() request: FastifyRequest) {
    return this.rules.simulate(actor, id, parseInput(ruleSimulationInputSchema, body), authRequestContext(request));
  }

  @Post("snapshots")
  @HttpCode(HttpStatus.CREATED)
  @RequirePermission(writeGuard)
  snapshot(@CurrentAuth() actor: SessionAuthContext, @Body() body: unknown, @Req() request: FastifyRequest) {
    return this.rules.createSnapshot(actor, parseInput(ruleSnapshotInputSchema, body), authRequestContext(request));
  }

  @Get("snapshots/:id")
  @RequirePermission(readGuard)
  getSnapshot(@CurrentAuth() actor: SessionAuthContext, @Param("id") id: string, @Query() query: unknown) {
    return this.rules.getSnapshot(actor, parseInput(ruleOrganizationQuerySchema, query).organizationId, id);
  }

  @Get("versions/:id/diff/:againstId")
  @RequirePermission(readGuard)
  diff(@CurrentAuth() actor: SessionAuthContext, @Param("id") id: string, @Param("againstId") againstId: string, @Query() query: unknown) {
    return this.rules.diffVersions(actor, parseInput(ruleOrganizationQuerySchema, query).organizationId, id, againstId);
  }

  @Get("versions/:id/revisions")
  @RequirePermission(readGuard)
  revisions(@CurrentAuth() actor: SessionAuthContext, @Param("id") id: string, @Query() query: unknown) {
    const input = parseInput(ruleRevisionsQuerySchema, query);
    return this.rules.revisions(actor, input.organizationId, id, input.limit);
  }
}
