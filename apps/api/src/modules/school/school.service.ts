import { Injectable } from "@nestjs/common";
import {
  PERMISSIONS,
  type AccessResourceType,
  type LevelCreateInput,
  type LevelUpdateInput,
  type OrganizationUpdateInput,
  type Permission,
  type RoomCreateInput,
  type RoomUpdateInput,
  type SchoolArchiveInput,
  type SchoolListQuery,
  type SiteCreateInput,
  type SiteUpdateInput,
} from "@lavision/contracts";
import {
  AccessResourceType as PrismaResourceType,
  type AcademicLevel,
  type Organization,
  type Room,
  type Site,
} from "../../database/client.js";
import type {
  AuthRequestContext,
  SessionAuthContext,
} from "../auth/auth.types.js";
import { AuthorizationService } from "../access/authorization.service.js";
import { PrismaService } from "../infrastructure/prisma.service.js";
import {
  presentLevel,
  presentOrganization,
  presentRoom,
  presentSite,
} from "./school.presenters.js";
import { SchoolHistoryService, type SchoolDb } from "./school-history.service.js";
import {
  conflict,
  includeArchived,
  notFound,
} from "./school.utils.js";

@Injectable()
export class SchoolService {
  constructor(
    private readonly prisma: PrismaService,
    private readonly authorization: AuthorizationService,
    private readonly history: SchoolHistoryService,
  ) {}

  async organization(organizationId: string): Promise<Organization> {
    const organization = await this.prisma.organization.findUnique({
      where: { id: organizationId },
    });
    if (!organization) notFound();
    return organization;
  }

  async updateOrganization(
    actor: SessionAuthContext,
    id: string,
    input: OrganizationUpdateInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.referentialsSchoolDraft,
      input.organizationId,
      id,
      "organization",
    );
    if (id !== input.organizationId) notFound();
    return this.prisma.$transaction(async (transaction) => {
      const existing = await transaction.organization.findUnique({ where: { id } });
      if (!existing || existing.archivedAt) notFound();
      if (input.code) {
        const duplicate = await transaction.organization.findFirst({
          where: { code: input.code, id: { not: id } },
        });
        if (duplicate) conflict();
      }
      const updated = await transaction.organization.update({
        where: { id },
        data: { code: input.code, name: input.name },
      });
      await this.history.record(transaction, {
        organizationId: id,
        entityType: "organization",
        entityId: id,
        action: "school.organization.update",
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        before: presentOrganization(existing),
        after: presentOrganization(updated),
      });
      return presentOrganization(updated);
    });
  }

  async createSite(
    actor: SessionAuthContext,
    input: SiteCreateInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.referentialsSchoolDraft,
      input.organizationId,
      input.organizationId,
      "organization",
    );
    return this.prisma.$transaction(async (transaction) => {
      const duplicate = await transaction.site.findFirst({
        where: { organizationId: input.organizationId, code: input.code },
      });
      if (duplicate) conflict();
      const site = await transaction.site.create({
        data: {
          organizationId: input.organizationId,
          code: input.code,
          name: input.name,
        },
      });
      await this.history.record(transaction, {
        organizationId: input.organizationId,
        entityType: "site",
        entityId: site.id,
        action: "school.site.create",
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        after: presentSite(site),
      });
      return presentSite(site);
    });
  }

  async updateSite(
    actor: SessionAuthContext,
    id: string,
    input: SiteUpdateInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.referentialsSchoolDraft,
      input.organizationId,
      id,
      "site",
    );
    return this.prisma.$transaction(async (transaction) => {
      const existing = await transaction.site.findFirst({
        where: { id, organizationId: input.organizationId, archivedAt: null },
      });
      if (!existing) notFound();
      if (input.code) {
        const duplicate = await transaction.site.findFirst({
          where: {
            organizationId: input.organizationId,
            code: input.code,
            id: { not: id },
          },
        });
        if (duplicate) conflict();
      }
      const updated = await transaction.site.update({
        where: { id },
        data: { code: input.code, name: input.name },
      });
      await this.history.record(transaction, {
        organizationId: input.organizationId,
        entityType: "site",
        entityId: id,
        action: "school.site.update",
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        before: presentSite(existing),
        after: presentSite(updated),
      });
      return presentSite(updated);
    });
  }

  async archiveSite(
    actor: SessionAuthContext,
    id: string,
    input: SchoolArchiveInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.referentialsSchoolDraft,
      input.organizationId,
      id,
      "site",
    );
    return this.prisma.$transaction(async (transaction) => {
      const existing = await transaction.site.findFirst({
        where: { id, organizationId: input.organizationId, archivedAt: null },
      });
      if (!existing) notFound();
      const updated = await transaction.site.update({
        where: { id },
        data: { archivedAt: new Date() },
      });
      await transaction.accessResource.updateMany({
        where: { id },
        data: { archivedAt: updated.archivedAt },
      });
      await this.history.record(transaction, {
        organizationId: input.organizationId,
        entityType: "site",
        entityId: id,
        action: "school.site.archive",
        reason: input.reason,
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        before: presentSite(existing),
        after: presentSite(updated),
      });
      return presentSite(updated);
    });
  }

  async listSites(query: SchoolListQuery): Promise<Site[]> {
    return this.prisma.site.findMany({
      where: {
        organizationId: query.organizationId,
        archivedAt: includeArchived(query) ? undefined : null,
      },
      orderBy: { code: "asc" },
    });
  }

  async createLevel(
    actor: SessionAuthContext,
    input: LevelCreateInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.referentialsSchoolDraft,
      input.organizationId,
      input.organizationId,
      "organization",
    );
    return this.prisma.$transaction(async (transaction) => {
      const duplicate = await transaction.academicLevel.findFirst({
        where: { organizationId: input.organizationId, code: input.code },
      });
      if (duplicate) conflict();
      const level = await transaction.academicLevel.create({
        data: {
          organizationId: input.organizationId,
          code: input.code,
          name: input.name,
          rank: input.rank,
        },
      });
      await this.registerResource(transaction, {
        id: level.id,
        organizationId: level.organizationId,
        parentId: level.organizationId,
        type: PrismaResourceType.LEVEL,
      });
      await this.history.record(transaction, {
        organizationId: input.organizationId,
        entityType: "level",
        entityId: level.id,
        action: "school.level.create",
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        after: presentLevel(level),
      });
      return presentLevel(level);
    });
  }

  async updateLevel(
    actor: SessionAuthContext,
    id: string,
    input: LevelUpdateInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.referentialsSchoolDraft,
      input.organizationId,
      id,
      "level",
    );
    return this.prisma.$transaction(async (transaction) => {
      const existing = await transaction.academicLevel.findFirst({
        where: { id, organizationId: input.organizationId, archivedAt: null },
      });
      if (!existing) notFound();
      if (input.code) {
        const duplicate = await transaction.academicLevel.findFirst({
          where: {
            organizationId: input.organizationId,
            code: input.code,
            id: { not: id },
          },
        });
        if (duplicate) conflict();
      }
      const updated = await transaction.academicLevel.update({
        where: { id },
        data: { code: input.code, name: input.name, rank: input.rank },
      });
      await this.history.record(transaction, {
        organizationId: input.organizationId,
        entityType: "level",
        entityId: id,
        action: "school.level.update",
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        before: presentLevel(existing),
        after: presentLevel(updated),
      });
      return presentLevel(updated);
    });
  }

  async archiveLevel(
    actor: SessionAuthContext,
    id: string,
    input: SchoolArchiveInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.referentialsSchoolDraft,
      input.organizationId,
      id,
      "level",
    );
    return this.prisma.$transaction(async (transaction) => {
      const existing = await transaction.academicLevel.findFirst({
        where: { id, organizationId: input.organizationId, archivedAt: null },
      });
      if (!existing) notFound();
      const updated = await transaction.academicLevel.update({
        where: { id },
        data: { archivedAt: new Date() },
      });
      await transaction.accessResource.updateMany({
        where: { id },
        data: { archivedAt: updated.archivedAt },
      });
      await this.history.record(transaction, {
        organizationId: input.organizationId,
        entityType: "level",
        entityId: id,
        action: "school.level.archive",
        reason: input.reason,
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        before: presentLevel(existing),
        after: presentLevel(updated),
      });
      return presentLevel(updated);
    });
  }

  async listLevels(query: SchoolListQuery): Promise<AcademicLevel[]> {
    return this.prisma.academicLevel.findMany({
      where: {
        organizationId: query.organizationId,
        archivedAt: includeArchived(query) ? undefined : null,
      },
      orderBy: [{ rank: "asc" }, { code: "asc" }],
    });
  }

  async createRoom(
    actor: SessionAuthContext,
    input: RoomCreateInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.referentialsSchoolDraft,
      input.organizationId,
      input.siteId,
      "site",
    );
    return this.prisma.$transaction(async (transaction) => {
      const site = await transaction.site.findFirst({
        where: { id: input.siteId, organizationId: input.organizationId, archivedAt: null },
      });
      if (!site) notFound();
      const duplicate = await transaction.room.findFirst({
        where: { siteId: input.siteId, code: input.code },
      });
      if (duplicate) conflict();
      const room = await transaction.room.create({
        data: {
          organizationId: input.organizationId,
          siteId: input.siteId,
          code: input.code,
          name: input.name,
          capacity: input.capacity,
        },
      });
      await this.registerResource(transaction, {
        id: room.id,
        organizationId: room.organizationId,
        siteId: room.siteId,
        parentId: room.siteId,
        type: PrismaResourceType.ROOM,
      });
      await this.history.record(transaction, {
        organizationId: input.organizationId,
        entityType: "room",
        entityId: room.id,
        action: "school.room.create",
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        after: presentRoom(room),
      });
      return presentRoom(room);
    });
  }

  async updateRoom(
    actor: SessionAuthContext,
    id: string,
    input: RoomUpdateInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.referentialsSchoolDraft,
      input.organizationId,
      id,
      "room",
    );
    return this.prisma.$transaction(async (transaction) => {
      const existing = await transaction.room.findFirst({
        where: { id, organizationId: input.organizationId, archivedAt: null },
      });
      if (!existing) notFound();
      if (input.code) {
        const duplicate = await transaction.room.findFirst({
          where: { siteId: existing.siteId, code: input.code, id: { not: id } },
        });
        if (duplicate) conflict();
      }
      const updated = await transaction.room.update({
        where: { id },
        data: { code: input.code, name: input.name, capacity: input.capacity },
      });
      await this.history.record(transaction, {
        organizationId: input.organizationId,
        entityType: "room",
        entityId: id,
        action: "school.room.update",
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        before: presentRoom(existing),
        after: presentRoom(updated),
      });
      return presentRoom(updated);
    });
  }

  async archiveRoom(
    actor: SessionAuthContext,
    id: string,
    input: SchoolArchiveInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.referentialsSchoolDraft,
      input.organizationId,
      id,
      "room",
    );
    return this.prisma.$transaction(async (transaction) => {
      const existing = await transaction.room.findFirst({
        where: { id, organizationId: input.organizationId, archivedAt: null },
      });
      if (!existing) notFound();
      const updated = await transaction.room.update({
        where: { id },
        data: { archivedAt: new Date() },
      });
      await transaction.accessResource.updateMany({
        where: { id },
        data: { archivedAt: updated.archivedAt },
      });
      await this.history.record(transaction, {
        organizationId: input.organizationId,
        entityType: "room",
        entityId: id,
        action: "school.room.archive",
        reason: input.reason,
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        before: presentRoom(existing),
        after: presentRoom(updated),
      });
      return presentRoom(updated);
    });
  }

  async listRooms(query: SchoolListQuery): Promise<Room[]> {
    return this.prisma.room.findMany({
      where: {
        organizationId: query.organizationId,
        siteId: query.siteId,
        archivedAt: includeArchived(query) ? undefined : null,
      },
      orderBy: [{ siteId: "asc" }, { code: "asc" }],
    });
  }

  private async registerResource(
    db: SchoolDb,
    input: {
      id: string;
      organizationId: string;
      siteId?: string;
      parentId?: string;
      type: PrismaResourceType;
    },
  ): Promise<void> {
    await db.accessResource.create({ data: input });
  }

  private async assert(
    actor: SessionAuthContext,
    permission: Permission,
    organizationId: string,
    resourceId: string,
    resourceType: AccessResourceType,
  ): Promise<void> {
    await this.authorization.assertAuthorized({
      userId: actor.session.userId,
      organizationId,
      permission,
      resourceId,
      resourceType,
    });
  }
}
