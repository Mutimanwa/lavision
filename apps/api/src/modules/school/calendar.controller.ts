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
  nonWorkingDayCreateInputSchema,
  nonWorkingDayUpdateInputSchema,
  periodCreateInputSchema,
  periodUpdateInputSchema,
  PERMISSIONS,
  schoolArchiveInputSchema,
  schoolListQuerySchema,
  schoolYearActivationInputSchema,
  schoolYearCloseInputSchema,
  schoolYearCreateInputSchema,
  schoolYearReopenInputSchema,
  schoolYearUpdateInputSchema,
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
import { CalendarService } from "./calendar.service.js";
import {
  presentNonWorkingDay,
  presentPeriod,
  presentSchoolYear,
} from "./school.presenters.js";

const bodyOrganization = { in: "body", key: "organizationId" } as const;
const queryOrganization = { in: "query", key: "organizationId" } as const;
const bodyYear = { in: "body", key: "schoolYearId" } as const;
const parameterId = { in: "params", key: "id" } as const;
const parameterYear = { in: "params", key: "yearId" } as const;
const readCalendar = {
  permission: PERMISSIONS.calendarRead,
  organization: queryOrganization,
  resource: queryOrganization,
  resourceType: "organization",
} as const;
const readYear = {
  permission: PERMISSIONS.calendarRead,
  organization: queryOrganization,
  resource: parameterYear,
  resourceType: "school_year",
} as const;
const manageCalendarOrganization = {
  permission: PERMISSIONS.calendarManage,
  organization: bodyOrganization,
  resource: bodyOrganization,
  resourceType: "organization",
} as const;
const manageYear = {
  permission: PERMISSIONS.calendarManage,
  organization: bodyOrganization,
  resource: parameterId,
  resourceType: "school_year",
} as const;
const manageYearFromBody = {
  permission: PERMISSIONS.calendarManage,
  organization: bodyOrganization,
  resource: bodyYear,
  resourceType: "school_year",
} as const;
const activateYear = {
  permission: PERMISSIONS.calendarActivate,
  organization: bodyOrganization,
  resource: parameterId,
  resourceType: "school_year",
} as const;
const closeYear = {
  permission: PERMISSIONS.calendarClose,
  organization: bodyOrganization,
  resource: parameterId,
  resourceType: "school_year",
} as const;
const reopenYear = {
  permission: PERMISSIONS.calendarReopen,
  organization: bodyOrganization,
  resource: parameterId,
  resourceType: "school_year",
} as const;
const managePeriod = {
  permission: PERMISSIONS.calendarManage,
  organization: bodyOrganization,
  resource: parameterId,
  resourceType: "period",
} as const;
const manageDay = {
  permission: PERMISSIONS.calendarManage,
  organization: bodyOrganization,
  resource: parameterId,
  resourceType: "non_working_day",
} as const;

@Controller("school")
@UseGuards(PermissionGuard)
export class CalendarController {
  constructor(private readonly calendar: CalendarService) {}

  @Get("years")
  @RequirePermission(readCalendar)
  async years(@Query() query: unknown) {
    const years = await this.calendar.listYears(parseInput(schoolListQuerySchema, query));
    return { years: years.map(presentSchoolYear) };
  }

  @Post("years")
  @HttpCode(HttpStatus.CREATED)
  @RequirePermission(manageCalendarOrganization)
  createYear(
    @CurrentAuth() actor: SessionAuthContext,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.calendar.createYear(
      actor,
      parseInput(schoolYearCreateInputSchema, body),
      authRequestContext(request),
    );
  }

  @Patch("years/:id")
  @RequirePermission(manageYear)
  updateYear(
    @CurrentAuth() actor: SessionAuthContext,
    @Param("id") id: string,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.calendar.updateYear(
      actor,
      id,
      parseInput(schoolYearUpdateInputSchema, body),
      authRequestContext(request),
    );
  }

  @Post("years/:id/activate")
  @HttpCode(HttpStatus.OK)
  @RequirePermission(activateYear)
  activateYear(
    @CurrentAuth() actor: SessionAuthContext,
    @Param("id") id: string,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.calendar.activateYear(
      actor,
      id,
      parseInput(schoolYearActivationInputSchema, body),
      authRequestContext(request),
    );
  }

  @Post("years/:id/close")
  @HttpCode(HttpStatus.OK)
  @RequirePermission(closeYear)
  closeYear(
    @CurrentAuth() actor: SessionAuthContext,
    @Param("id") id: string,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.calendar.closeYear(
      actor,
      id,
      parseInput(schoolYearCloseInputSchema, body),
      authRequestContext(request),
    );
  }

  @Post("years/:id/reopen")
  @HttpCode(HttpStatus.OK)
  @RequirePermission(reopenYear)
  reopenYear(
    @CurrentAuth() actor: SessionAuthContext,
    @Param("id") id: string,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.calendar.reopenYear(
      actor,
      id,
      parseInput(schoolYearReopenInputSchema, body),
      authRequestContext(request),
    );
  }

  @Get("years/:yearId/periods")
  @RequirePermission(readYear)
  async periods(@Param("yearId") yearId: string, @Query() query: unknown) {
    const periods = await this.calendar.listPeriods(
      yearId,
      parseInput(schoolListQuerySchema, query),
    );
    return { periods: periods.map(presentPeriod) };
  }

  @Post("periods")
  @HttpCode(HttpStatus.CREATED)
  @RequirePermission(manageYearFromBody)
  createPeriod(
    @CurrentAuth() actor: SessionAuthContext,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.calendar.createPeriod(
      actor,
      parseInput(periodCreateInputSchema, body),
      authRequestContext(request),
    );
  }

  @Patch("periods/:id")
  @RequirePermission(managePeriod)
  updatePeriod(
    @CurrentAuth() actor: SessionAuthContext,
    @Param("id") id: string,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.calendar.updatePeriod(
      actor,
      id,
      parseInput(periodUpdateInputSchema, body),
      authRequestContext(request),
    );
  }

  @Post("periods/:id/archive")
  @HttpCode(HttpStatus.OK)
  @RequirePermission(managePeriod)
  archivePeriod(
    @CurrentAuth() actor: SessionAuthContext,
    @Param("id") id: string,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.calendar.archivePeriod(
      actor,
      id,
      parseInput(schoolArchiveInputSchema, body),
      authRequestContext(request),
    );
  }

  @Get("years/:yearId/non-working-days")
  @RequirePermission(readYear)
  async nonWorkingDays(@Param("yearId") yearId: string, @Query() query: unknown) {
    const days = await this.calendar.listNonWorkingDays(
      yearId,
      parseInput(schoolListQuerySchema, query),
    );
    return { days: days.map(presentNonWorkingDay) };
  }

  @Post("non-working-days")
  @HttpCode(HttpStatus.CREATED)
  @RequirePermission(manageYearFromBody)
  createNonWorkingDay(
    @CurrentAuth() actor: SessionAuthContext,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.calendar.createNonWorkingDay(
      actor,
      parseInput(nonWorkingDayCreateInputSchema, body),
      authRequestContext(request),
    );
  }

  @Patch("non-working-days/:id")
  @RequirePermission(manageDay)
  updateNonWorkingDay(
    @CurrentAuth() actor: SessionAuthContext,
    @Param("id") id: string,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.calendar.updateNonWorkingDay(
      actor,
      id,
      parseInput(nonWorkingDayUpdateInputSchema, body),
      authRequestContext(request),
    );
  }

  @Post("non-working-days/:id/archive")
  @HttpCode(HttpStatus.OK)
  @RequirePermission(manageDay)
  archiveNonWorkingDay(
    @CurrentAuth() actor: SessionAuthContext,
    @Param("id") id: string,
    @Body() body: unknown,
    @Req() request: FastifyRequest,
  ) {
    return this.calendar.archiveNonWorkingDay(
      actor,
      id,
      parseInput(schoolArchiveInputSchema, body),
      authRequestContext(request),
    );
  }
}
