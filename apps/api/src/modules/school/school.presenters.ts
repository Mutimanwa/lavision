import type {
  AcademicLevel,
  NonWorkingDay,
  Organization,
  ReferentialRevision,
  Room,
  SchoolPeriod,
  SchoolYear,
  Site,
} from "../../database/client.js";

const timestamp = (value: Date | null): string | null =>
  value?.toISOString() ?? null;
const date = (value: Date): string => value.toISOString().slice(0, 10);

export function presentOrganization(organization: Organization) {
  return {
    id: organization.id,
    code: organization.code,
    name: organization.name,
    createdAt: organization.createdAt.toISOString(),
    updatedAt: organization.updatedAt.toISOString(),
    archivedAt: timestamp(organization.archivedAt),
  };
}

export function presentSite(site: Site) {
  return {
    id: site.id,
    organizationId: site.organizationId,
    code: site.code,
    name: site.name,
    createdAt: site.createdAt.toISOString(),
    updatedAt: site.updatedAt.toISOString(),
    archivedAt: timestamp(site.archivedAt),
  };
}

export function presentLevel(level: AcademicLevel) {
  return {
    id: level.id,
    organizationId: level.organizationId,
    code: level.code,
    name: level.name,
    rank: level.rank,
    createdAt: level.createdAt.toISOString(),
    updatedAt: level.updatedAt.toISOString(),
    archivedAt: timestamp(level.archivedAt),
  };
}

export function presentRoom(room: Room) {
  return {
    id: room.id,
    organizationId: room.organizationId,
    siteId: room.siteId,
    code: room.code,
    name: room.name,
    capacity: room.capacity,
    createdAt: room.createdAt.toISOString(),
    updatedAt: room.updatedAt.toISOString(),
    archivedAt: timestamp(room.archivedAt),
  };
}

export function presentSchoolYear(year: SchoolYear) {
  return {
    id: year.id,
    organizationId: year.organizationId,
    code: year.code,
    name: year.name,
    startDate: date(year.startDate),
    endDate: date(year.endDate),
    status: year.status,
    closedAt: timestamp(year.closedAt),
    closureReason: year.closureReason,
    reopenedAt: timestamp(year.reopenedAt),
    reopenReason: year.reopenReason,
    createdAt: year.createdAt.toISOString(),
    updatedAt: year.updatedAt.toISOString(),
    archivedAt: timestamp(year.archivedAt),
  };
}

export function presentPeriod(period: SchoolPeriod) {
  return {
    id: period.id,
    schoolYearId: period.schoolYearId,
    code: period.code,
    name: period.name,
    sequence: period.sequence,
    startDate: date(period.startDate),
    endDate: date(period.endDate),
    createdAt: period.createdAt.toISOString(),
    updatedAt: period.updatedAt.toISOString(),
    archivedAt: timestamp(period.archivedAt),
  };
}

export function presentNonWorkingDay(day: NonWorkingDay) {
  return {
    id: day.id,
    schoolYearId: day.schoolYearId,
    siteId: day.siteId,
    day: date(day.day),
    reason: day.reason,
    createdAt: day.createdAt.toISOString(),
    updatedAt: day.updatedAt.toISOString(),
    archivedAt: timestamp(day.archivedAt),
  };
}

export function presentReferentialRevision(revision: ReferentialRevision) {
  return {
    id: revision.id,
    organizationId: revision.organizationId,
    entityType: revision.entityType,
    entityId: revision.entityId,
    action: revision.action,
    reason: revision.reason,
    actorUserId: revision.actorUserId,
    requestId: revision.requestId,
    before: revision.before,
    after: revision.after,
    createdAt: revision.createdAt.toISOString(),
  };
}
