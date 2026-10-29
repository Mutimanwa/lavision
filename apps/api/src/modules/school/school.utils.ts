import {
  ConflictException,
  NotFoundException,
} from "@nestjs/common";
import { ERROR_CODES, type SchoolListQuery } from "@lavision/contracts";
import type { SchoolYear } from "../../database/client.js";

export function conflict(): never {
  throw new ConflictException({ code: ERROR_CODES.CONFLICT });
}

export function notFound(): never {
  throw new NotFoundException({ code: ERROR_CODES.NOT_FOUND });
}

export function dateOnly(value: string): Date {
  return new Date(`${value}T00:00:00.000Z`);
}

export function isoDate(value: Date): string {
  return value.toISOString().slice(0, 10);
}

export function assertDateRange(
  startDate: string,
  endDate: string,
  allowSameDay = false,
): void {
  if (allowSameDay ? startDate > endDate : startDate >= endDate) conflict();
}

export function assertWithinYear(
  year: SchoolYear,
  startDate: string,
  endDate: string,
): void {
  if (startDate < isoDate(year.startDate) || endDate > isoDate(year.endDate)) {
    conflict();
  }
}

export function assertYearMutable(year: SchoolYear): void {
  if (year.archivedAt || year.status === "CLOSED") conflict();
}

export function includeArchived(query: SchoolListQuery): boolean {
  return query.includeArchived === "true";
}
