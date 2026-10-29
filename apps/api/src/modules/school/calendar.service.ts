import { Injectable } from "@nestjs/common";
import {
  PERMISSIONS,
  type AccessResourceType,
  type NonWorkingDayCreateInput,
  type NonWorkingDayUpdateInput,
  type PeriodCreateInput,
  type PeriodUpdateInput,
  type Permission,
  type SchoolArchiveInput,
  type SchoolListQuery,
  type SchoolYearActivationInput,
  type SchoolYearCloseInput,
  type SchoolYearCreateInput,
  type SchoolYearReopenInput,
  type SchoolYearUpdateInput,
} from "@lavision/contracts";
import {
  AccessResourceType as PrismaResourceType,
  type NonWorkingDay,
  type SchoolPeriod,
  type SchoolYear,
} from "../../database/client.js";
import type {
  AuthRequestContext,
  SessionAuthContext,
} from "../auth/auth.types.js";
import { AuthorizationService } from "../access/authorization.service.js";
import { PrismaService } from "../infrastructure/prisma.service.js";
import {
  presentNonWorkingDay,
  presentPeriod,
  presentSchoolYear,
} from "./school.presenters.js";
import { SchoolHistoryService, type SchoolDb } from "./school-history.service.js";
import {
  assertDateRange,
  assertWithinYear,
  assertYearMutable,
  conflict,
  dateOnly,
  includeArchived,
  isoDate,
  notFound,
} from "./school.utils.js";

@Injectable()
export class CalendarService {
  constructor(
    private readonly prisma: PrismaService,
    private readonly authorization: AuthorizationService,
    private readonly history: SchoolHistoryService,
  ) {}

  async createYear(
    actor: SessionAuthContext,
    input: SchoolYearCreateInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.calendarManage,
      input.organizationId,
      input.organizationId,
      "organization",
    );
    assertDateRange(input.startDate, input.endDate);
    return this.prisma.$transaction(async (transaction) => {
      const duplicate = await transaction.schoolYear.findFirst({
        where: { organizationId: input.organizationId, code: input.code },
      });
      if (duplicate) conflict();
      const year = await transaction.schoolYear.create({
        data: {
          organizationId: input.organizationId,
          code: input.code,
          name: input.name,
          startDate: dateOnly(input.startDate),
          endDate: dateOnly(input.endDate),
        },
      });
      await this.registerResource(transaction, {
        id: year.id,
        organizationId: year.organizationId,
        parentId: year.organizationId,
        type: PrismaResourceType.SCHOOL_YEAR,
      });
      await this.history.record(transaction, {
        organizationId: input.organizationId,
        entityType: "school_year",
        entityId: year.id,
        action: "school.year.create",
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        after: presentSchoolYear(year),
      });
      return presentSchoolYear(year);
    });
  }

  async updateYear(
    actor: SessionAuthContext,
    id: string,
    input: SchoolYearUpdateInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.calendarManage,
      input.organizationId,
      id,
      "school_year",
    );
    return this.prisma.$transaction(async (transaction) => {
      const existing = await transaction.schoolYear.findFirst({
        where: { id, organizationId: input.organizationId, archivedAt: null },
      });
      if (!existing) notFound();
      assertYearMutable(existing);
      const startDate = input.startDate ?? isoDate(existing.startDate);
      const endDate = input.endDate ?? isoDate(existing.endDate);
      assertDateRange(startDate, endDate);
      const periods = await transaction.schoolPeriod.findMany({
        where: { schoolYearId: id, archivedAt: null },
      });
      for (const period of periods) {
        if (isoDate(period.startDate) < startDate || isoDate(period.endDate) > endDate) {
          conflict();
        }
      }
      if (input.code) {
        const duplicate = await transaction.schoolYear.findFirst({
          where: {
            organizationId: input.organizationId,
            code: input.code,
            id: { not: id },
          },
        });
        if (duplicate) conflict();
      }
      const updated = await transaction.schoolYear.update({
        where: { id },
        data: {
          code: input.code,
          name: input.name,
          startDate: dateOnly(startDate),
          endDate: dateOnly(endDate),
        },
      });
      await this.history.record(transaction, {
        organizationId: input.organizationId,
        entityType: "school_year",
        entityId: id,
        action: "school.year.update",
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        before: presentSchoolYear(existing),
        after: presentSchoolYear(updated),
      });
      return presentSchoolYear(updated);
    });
  }

  async activateYear(
    actor: SessionAuthContext,
    id: string,
    input: SchoolYearActivationInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.calendarActivate,
      input.organizationId,
      id,
      "school_year",
    );
    return this.prisma.$transaction(async (transaction) => {
      const existing = await transaction.schoolYear.findFirst({
        where: { id, organizationId: input.organizationId, archivedAt: null },
      });
      if (!existing) notFound();
      if (existing.status === "CLOSED") conflict();
      if (existing.status === "ACTIVE") return presentSchoolYear(existing);
      const periodCount = await transaction.schoolPeriod.count({
        where: { schoolYearId: id, archivedAt: null },
      });
      if (periodCount === 0) conflict();
      const otherActive = await transaction.schoolYear.findFirst({
        where: {
          organizationId: input.organizationId,
          status: "ACTIVE",
          archivedAt: null,
          id: { not: id },
        },
      });
      if (otherActive) conflict();
      const updated = await transaction.schoolYear.update({
        where: { id },
        data: {
          status: "ACTIVE",
          closedAt: null,
          closedByUserId: null,
          closureReason: null,
          reopenedAt: null,
          reopenedByUserId: null,
          reopenReason: null,
        },
      });
      await this.history.record(transaction, {
        organizationId: input.organizationId,
        entityType: "school_year",
        entityId: id,
        action: "school.year.activate",
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        before: presentSchoolYear(existing),
        after: presentSchoolYear(updated),
      });
      return presentSchoolYear(updated);
    });
  }

  async closeYear(
    actor: SessionAuthContext,
    id: string,
    input: SchoolYearCloseInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.calendarClose,
      input.organizationId,
      id,
      "school_year",
    );
    return this.prisma.$transaction(async (transaction) => {
      const existing = await transaction.schoolYear.findFirst({
        where: { id, organizationId: input.organizationId, archivedAt: null },
      });
      if (!existing || existing.status !== "ACTIVE") conflict();
      const updated = await transaction.schoolYear.update({
        where: { id },
        data: {
          status: "CLOSED",
          closedAt: new Date(),
          closedByUserId: actor.session.userId,
          closureReason: input.reason,
          reopenedAt: null,
          reopenedByUserId: null,
          reopenReason: null,
        },
      });
      await this.history.record(transaction, {
        organizationId: input.organizationId,
        entityType: "school_year",
        entityId: id,
        action: "school.year.close",
        reason: input.reason,
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        before: presentSchoolYear(existing),
        after: presentSchoolYear(updated),
      });
      return presentSchoolYear(updated);
    });
  }

  async reopenYear(
    actor: SessionAuthContext,
    id: string,
    input: SchoolYearReopenInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.calendarReopen,
      input.organizationId,
      id,
      "school_year",
    );
    return this.prisma.$transaction(async (transaction) => {
      const existing = await transaction.schoolYear.findFirst({
        where: { id, organizationId: input.organizationId, archivedAt: null },
      });
      if (!existing || existing.status !== "CLOSED") conflict();
      const updated = await transaction.schoolYear.update({
        where: { id },
        data: {
          status: "DRAFT",
          closedAt: null,
          closedByUserId: null,
          closureReason: null,
          reopenedAt: new Date(),
          reopenedByUserId: actor.session.userId,
          reopenReason: input.reason,
        },
      });
      await this.history.record(transaction, {
        organizationId: input.organizationId,
        entityType: "school_year",
        entityId: id,
        action: "school.year.reopen",
        reason: input.reason,
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        before: presentSchoolYear(existing),
        after: presentSchoolYear(updated),
      });
      return presentSchoolYear(updated);
    });
  }

  async listYears(query: SchoolListQuery): Promise<SchoolYear[]> {
    return this.prisma.schoolYear.findMany({
      where: {
        organizationId: query.organizationId,
        archivedAt: includeArchived(query) ? undefined : null,
      },
      orderBy: [{ startDate: "desc" }, { code: "desc" }],
    });
  }

  async createPeriod(
    actor: SessionAuthContext,
    input: PeriodCreateInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.calendarManage,
      input.organizationId,
      input.schoolYearId,
      "school_year",
    );
    assertDateRange(input.startDate, input.endDate, true);
    return this.prisma.$transaction(async (transaction) => {
      const year = await this.mutableYear(
        transaction,
        input.schoolYearId,
        input.organizationId,
      );
      assertWithinYear(year, input.startDate, input.endDate);
      await this.assertPeriodAvailable(transaction, input.schoolYearId, {
        code: input.code,
        sequence: input.sequence,
        startDate: input.startDate,
        endDate: input.endDate,
      });
      const period = await transaction.schoolPeriod.create({
        data: {
          schoolYearId: input.schoolYearId,
          code: input.code,
          name: input.name,
          sequence: input.sequence,
          startDate: dateOnly(input.startDate),
          endDate: dateOnly(input.endDate),
        },
      });
      await this.registerResource(transaction, {
        id: period.id,
        organizationId: input.organizationId,
        parentId: input.schoolYearId,
        type: PrismaResourceType.PERIOD,
      });
      await this.history.record(transaction, {
        organizationId: input.organizationId,
        entityType: "period",
        entityId: period.id,
        action: "school.period.create",
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        after: presentPeriod(period),
      });
      return presentPeriod(period);
    });
  }

  async updatePeriod(
    actor: SessionAuthContext,
    id: string,
    input: PeriodUpdateInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.calendarManage,
      input.organizationId,
      id,
      "period",
    );
    return this.prisma.$transaction(async (transaction) => {
      const existing = await transaction.schoolPeriod.findUnique({
        where: { id },
        include: { schoolYear: true },
      });
      if (!existing || existing.archivedAt || existing.schoolYear.organizationId !== input.organizationId) {
        notFound();
      }
      assertYearMutable(existing.schoolYear);
      const startDate = input.startDate ?? isoDate(existing.startDate);
      const endDate = input.endDate ?? isoDate(existing.endDate);
      assertDateRange(startDate, endDate, true);
      assertWithinYear(existing.schoolYear, startDate, endDate);
      await this.assertPeriodAvailable(transaction, existing.schoolYearId, {
        id,
        code: input.code ?? existing.code,
        sequence: input.sequence ?? existing.sequence,
        startDate,
        endDate,
      });
      const updated = await transaction.schoolPeriod.update({
        where: { id },
        data: {
          code: input.code,
          name: input.name,
          sequence: input.sequence,
          startDate: dateOnly(startDate),
          endDate: dateOnly(endDate),
        },
      });
      await this.history.record(transaction, {
        organizationId: input.organizationId,
        entityType: "period",
        entityId: id,
        action: "school.period.update",
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        before: presentPeriod(existing),
        after: presentPeriod(updated),
      });
      return presentPeriod(updated);
    });
  }

  async archivePeriod(
    actor: SessionAuthContext,
    id: string,
    input: SchoolArchiveInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.calendarManage,
      input.organizationId,
      id,
      "period",
    );
    return this.prisma.$transaction(async (transaction) => {
      const existing = await transaction.schoolPeriod.findUnique({
        where: { id },
        include: { schoolYear: true },
      });
      if (!existing || existing.archivedAt || existing.schoolYear.organizationId !== input.organizationId) {
        notFound();
      }
      assertYearMutable(existing.schoolYear);
      const updated = await transaction.schoolPeriod.update({
        where: { id },
        data: { archivedAt: new Date() },
      });
      await transaction.accessResource.updateMany({
        where: { id },
        data: { archivedAt: updated.archivedAt },
      });
      await this.history.record(transaction, {
        organizationId: input.organizationId,
        entityType: "period",
        entityId: id,
        action: "school.period.archive",
        reason: input.reason,
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        before: presentPeriod(existing),
        after: presentPeriod(updated),
      });
      return presentPeriod(updated);
    });
  }

  async listPeriods(yearId: string, query: SchoolListQuery): Promise<SchoolPeriod[]> {
    const year = await this.prisma.schoolYear.findFirst({
      where: { id: yearId, organizationId: query.organizationId, archivedAt: null },
    });
    if (!year) notFound();
    return this.prisma.schoolPeriod.findMany({
      where: {
        schoolYearId: yearId,
        archivedAt: includeArchived(query) ? undefined : null,
      },
      orderBy: { sequence: "asc" },
    });
  }

  async createNonWorkingDay(
    actor: SessionAuthContext,
    input: NonWorkingDayCreateInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.calendarManage,
      input.organizationId,
      input.schoolYearId,
      "school_year",
    );
    return this.prisma.$transaction(async (transaction) => {
      const year = await this.mutableYear(
        transaction,
        input.schoolYearId,
        input.organizationId,
      );
      assertWithinYear(year, input.day, input.day);
      if (input.siteId) {
        const site = await transaction.site.findFirst({
          where: { id: input.siteId, organizationId: input.organizationId, archivedAt: null },
        });
        if (!site) notFound();
      }
      await this.assertDayAvailable(transaction, input.schoolYearId, input.siteId, input.day);
      const day = await transaction.nonWorkingDay.create({
        data: {
          schoolYearId: input.schoolYearId,
          siteId: input.siteId,
          day: dateOnly(input.day),
          reason: input.reason,
        },
      });
      await this.registerResource(transaction, {
        id: day.id,
        organizationId: input.organizationId,
        siteId: input.siteId,
        parentId: input.schoolYearId,
        type: PrismaResourceType.NON_WORKING_DAY,
      });
      await this.history.record(transaction, {
        organizationId: input.organizationId,
        entityType: "non_working_day",
        entityId: day.id,
        action: "school.non_working_day.create",
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        after: presentNonWorkingDay(day),
      });
      return presentNonWorkingDay(day);
    });
  }

  async updateNonWorkingDay(
    actor: SessionAuthContext,
    id: string,
    input: NonWorkingDayUpdateInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.calendarManage,
      input.organizationId,
      id,
      "non_working_day",
    );
    return this.prisma.$transaction(async (transaction) => {
      const existing = await transaction.nonWorkingDay.findUnique({
        where: { id },
        include: { schoolYear: true },
      });
      if (!existing || existing.archivedAt || existing.schoolYear.organizationId !== input.organizationId) {
        notFound();
      }
      assertYearMutable(existing.schoolYear);
      const day = input.day ?? isoDate(existing.day);
      assertWithinYear(existing.schoolYear, day, day);
      await this.assertDayAvailable(
        transaction,
        existing.schoolYearId,
        existing.siteId,
        day,
        id,
      );
      const updated = await transaction.nonWorkingDay.update({
        where: { id },
        data: { day: dateOnly(day), reason: input.reason },
      });
      await this.history.record(transaction, {
        organizationId: input.organizationId,
        entityType: "non_working_day",
        entityId: id,
        action: "school.non_working_day.update",
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        before: presentNonWorkingDay(existing),
        after: presentNonWorkingDay(updated),
      });
      return presentNonWorkingDay(updated);
    });
  }

  async archiveNonWorkingDay(
    actor: SessionAuthContext,
    id: string,
    input: SchoolArchiveInput,
    context: AuthRequestContext,
  ) {
    await this.assert(
      actor,
      PERMISSIONS.calendarManage,
      input.organizationId,
      id,
      "non_working_day",
    );
    return this.prisma.$transaction(async (transaction) => {
      const existing = await transaction.nonWorkingDay.findUnique({
        where: { id },
        include: { schoolYear: true },
      });
      if (!existing || existing.archivedAt || existing.schoolYear.organizationId !== input.organizationId) {
        notFound();
      }
      assertYearMutable(existing.schoolYear);
      const updated = await transaction.nonWorkingDay.update({
        where: { id },
        data: { archivedAt: new Date() },
      });
      await transaction.accessResource.updateMany({
        where: { id },
        data: { archivedAt: updated.archivedAt },
      });
      await this.history.record(transaction, {
        organizationId: input.organizationId,
        entityType: "non_working_day",
        entityId: id,
        action: "school.non_working_day.archive",
        reason: input.reason,
        actorUserId: actor.session.userId,
        requestId: context.requestId,
        before: presentNonWorkingDay(existing),
        after: presentNonWorkingDay(updated),
      });
      return presentNonWorkingDay(updated);
    });
  }

  async listNonWorkingDays(
    yearId: string,
    query: SchoolListQuery,
  ): Promise<NonWorkingDay[]> {
    const year = await this.prisma.schoolYear.findFirst({
      where: { id: yearId, organizationId: query.organizationId, archivedAt: null },
    });
    if (!year) notFound();
    return this.prisma.nonWorkingDay.findMany({
      where: {
        schoolYearId: yearId,
        siteId: query.siteId,
        archivedAt: includeArchived(query) ? undefined : null,
      },
      orderBy: { day: "asc" },
    });
  }

  private async mutableYear(
    db: SchoolDb,
    id: string,
    organizationId: string,
  ): Promise<SchoolYear> {
    const year = await db.schoolYear.findFirst({
      where: { id, organizationId, archivedAt: null },
    });
    if (!year) notFound();
    assertYearMutable(year);
    return year;
  }

  private async assertPeriodAvailable(
    db: SchoolDb,
    schoolYearId: string,
    input: {
      id?: string;
      code: string;
      sequence: number;
      startDate: string;
      endDate: string;
    },
  ): Promise<void> {
    const duplicate = await db.schoolPeriod.findFirst({
      where: {
        schoolYearId,
        id: input.id ? { not: input.id } : undefined,
        OR: [{ code: input.code }, { sequence: input.sequence }],
      },
    });
    if (duplicate) conflict();
    const overlap = await db.schoolPeriod.findFirst({
      where: {
        schoolYearId,
        id: input.id ? { not: input.id } : undefined,
        startDate: { lte: dateOnly(input.endDate) },
        endDate: { gte: dateOnly(input.startDate) },
      },
    });
    if (overlap) conflict();
  }

  private async assertDayAvailable(
    db: SchoolDb,
    schoolYearId: string,
    siteId: string | null | undefined,
    day: string,
    excludeId?: string,
  ): Promise<void> {
    const duplicate = await db.nonWorkingDay.findFirst({
      where: {
        schoolYearId,
        siteId: siteId ?? null,
        day: dateOnly(day),
        id: excludeId ? { not: excludeId } : undefined,
      },
    });
    if (duplicate) conflict();
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
