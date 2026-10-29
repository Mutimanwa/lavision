import {
  Body,
  Controller,
  Get,
  HttpCode,
  HttpStatus,
  Param,
  Patch,
  Post,
  Query,
  Req,
  UseGuards,
} from "@nestjs/common";
import {
  levelCreateInputSchema,
  levelUpdateInputSchema,
  organizationUpdateInputSchema,
  PERMISSIONS,
  roomCreateInputSchema,
  roomUpdateInputSchema,
  schoolArchiveInputSchema,
  schoolHistoryQuerySchema,
  schoolListQuerySchema,
  siteCreateInputSchema,
  siteUpdateInputSchema,
} from "@lavision/contracts";
import type { FastifyRequest } from "fastify";
import { parseInput } from "../../common/validation.js";
import {
  authRequestContext,
  CurrentAuth,
} from "../auth/auth.decorators.js";
import type { SessionAuthContext } from "../auth/auth.types.js";
import { RequirePermission } from "../access/access.decorators.js";
import { PermissionGuard } from "../access/permission.guard.js";
import {
  presentLevel,
  presentOrganization,
  presentReferentialRevision,
  presentRoom,
  presentSite,
} from "./school.presenters.js";
import { SchoolHistoryService } from "./school-history.service.js";
import { SchoolService } from "./school.service.js";

const bodyOrganization = { in: "body", key: "organizationId" } as const;
const queryOrganization = { in: "query", key: "organizationId" } as const;
const bodySite = { in: "body", key: "siteId" } as const;
const parameterId = { in: "params", key: "id" } as const;
const readSchool = {
  permission: PERMISSIONS.referentialsSchoolRead,
  organization: queryOrganization,
  resource: queryOrganization,
  resourceType: "organization",
} as const;
const writeSchoolOrganization = {
  permission: PERMISSIONS.referentialsSchoolDraft,
  organization: bodyOrganization,
  resource: bodyOrganization,
  resourceType: "organization",
} as const;
const writeOrganization = {
  permission: PERMISSIONS.referentialsSchoolDraft,
  organization: bodyOrganization,
  resource: parameterId,
  resourceType: "organization",
} as const;
const writeSite = {
  permission: PERMISSIONS.referentialsSchoolDraft,
  organization: bodyOrganization,
  resource: parameterId,
  resourceType: "site",
} as const;
const writeLevel = {
  permission: PERMISSIONS.referentialsSchoolDraft,
  organization: bodyOrganization,
  resource: parameterId,
  resourceType: "level",
} as const;
const writeRoom = {
  permission: PERMISSIONS.referentialsSchoolDraft,
  organization: bodyOrganization,
  resource: parameterId,
  resourceType: "room",
} as const;
const writeRoomForSite = {
  permission: PERMISSIONS.referentialsSchoolDraft,
  organization: bodyOrganization,
  resource: bodySite,
  resourceType: "site",
} as const;

@Controller("school")
@UseGuards(PermissionGuard)
export class SchoolController {
  constructor(
    private readonly school: SchoolService,
    private readonly history: SchoolHistoryService,
  ) {}

  @Get("organization")
  @RequirePermission(readSchool)
  async organization(@Query() query: unknown) {
    const input = parseInput(schoolListQuerySchema, query);
    return presentOrganization(await this.school.organization(input.organizationId));
  }

  @Patch("organization/:id")
  @RequirePermission(writeOrganization)
  updateOrganization(
    @CurrentAuth() actor: SessionAuthContext,
    @Param("id") id: string,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.school.updateOrganization(
      actor,
      id,
      parseInput(organizationUpdateInputSchema, body),
      authRequestContext(request),
    );
  }

  @Get("sites")
  @RequirePermission(readSchool)
  async sites(@Query() query: unknown) {
    const sites = await this.school.listSites(parseInput(schoolListQuerySchema, query));
    return { sites: sites.map(presentSite) };
  }

  @Post("sites")
  @HttpCode(HttpStatus.CREATED)
  @RequirePermission(writeSchoolOrganization)
  createSite(
    @CurrentAuth() actor: SessionAuthContext,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.school.createSite(
      actor,
      parseInput(siteCreateInputSchema, body),
      authRequestContext(request),
    );
  }

  @Patch("sites/:id")
  @RequirePermission(writeSite)
  updateSite(
    @CurrentAuth() actor: SessionAuthContext,
    @Param("id") id: string,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.school.updateSite(
      actor,
      id,
      parseInput(siteUpdateInputSchema, body),
      authRequestContext(request),
    );
  }

  @Post("sites/:id/archive")
  @HttpCode(HttpStatus.OK)
  @RequirePermission(writeSite)
  archiveSite(
    @CurrentAuth() actor: SessionAuthContext,
    @Param("id") id: string,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.school.archiveSite(
      actor,
      id,
      parseInput(schoolArchiveInputSchema, body),
      authRequestContext(request),
    );
  }

  @Get("levels")
  @RequirePermission(readSchool)
  async levels(@Query() query: unknown) {
    const levels = await this.school.listLevels(parseInput(schoolListQuerySchema, query));
    return { levels: levels.map(presentLevel) };
  }

  @Post("levels")
  @HttpCode(HttpStatus.CREATED)
  @RequirePermission(writeSchoolOrganization)
  createLevel(
    @CurrentAuth() actor: SessionAuthContext,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.school.createLevel(
      actor,
      parseInput(levelCreateInputSchema, body),
      authRequestContext(request),
    );
  }

  @Patch("levels/:id")
  @RequirePermission(writeLevel)
  updateLevel(
    @CurrentAuth() actor: SessionAuthContext,
    @Param("id") id: string,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.school.updateLevel(
      actor,
      id,
      parseInput(levelUpdateInputSchema, body),
      authRequestContext(request),
    );
  }

  @Post("levels/:id/archive")
  @HttpCode(HttpStatus.OK)
  @RequirePermission(writeLevel)
  archiveLevel(
    @CurrentAuth() actor: SessionAuthContext,
    @Param("id") id: string,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.school.archiveLevel(
      actor,
      id,
      parseInput(schoolArchiveInputSchema, body),
      authRequestContext(request),
    );
  }

  @Get("rooms")
  @RequirePermission(readSchool)
  async rooms(@Query() query: unknown) {
    const rooms = await this.school.listRooms(parseInput(schoolListQuerySchema, query));
    return { rooms: rooms.map(presentRoom) };
  }

  @Post("rooms")
  @HttpCode(HttpStatus.CREATED)
  @RequirePermission(writeRoomForSite)
  createRoom(
    @CurrentAuth() actor: SessionAuthContext,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.school.createRoom(
      actor,
      parseInput(roomCreateInputSchema, body),
      authRequestContext(request),
    );
  }

  @Patch("rooms/:id")
  @RequirePermission(writeRoom)
  updateRoom(
    @CurrentAuth() actor: SessionAuthContext,
    @Param("id") id: string,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.school.updateRoom(
      actor,
      id,
      parseInput(roomUpdateInputSchema, body),
      authRequestContext(request),
    );
  }

  @Post("rooms/:id/archive")
  @HttpCode(HttpStatus.OK)
  @RequirePermission(writeRoom)
  archiveRoom(
    @CurrentAuth() actor: SessionAuthContext,
    @Param("id") id: string,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.school.archiveRoom(
      actor,
      id,
      parseInput(schoolArchiveInputSchema, body),
      authRequestContext(request),
    );
  }

  @Get("history")
  @RequirePermission(readSchool)
  async revisionHistory(@Query() query: unknown) {
    const revisions = await this.history.list(parseInput(schoolHistoryQuerySchema, query));
    return { revisions: revisions.map(presentReferentialRevision) };
  }
}
