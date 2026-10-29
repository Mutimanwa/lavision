import type {
  AccessRelationType,
  AccessResourceType,
  Permission,
} from "@lavision/contracts";
import {
  AccessRelationType as PrismaRelationType,
  AccessResourceType as PrismaResourceType,
} from "../../database/client.js";

export const PRISMA_RESOURCE_TYPE: Record<
  AccessResourceType,
  PrismaResourceType
> = {
  organization: PrismaResourceType.ORGANIZATION,
  site: PrismaResourceType.SITE,
  level: PrismaResourceType.LEVEL,
  room: PrismaResourceType.ROOM,
  school_year: PrismaResourceType.SCHOOL_YEAR,
  period: PrismaResourceType.PERIOD,
  non_working_day: PrismaResourceType.NON_WORKING_DAY,
  class: PrismaResourceType.CLASS,
  subject: PrismaResourceType.SUBJECT,
  student: PrismaResourceType.STUDENT,
  employee: PrismaResourceType.EMPLOYEE,
  note: PrismaResourceType.NOTE,
  payment: PrismaResourceType.PAYMENT,
  payroll: PrismaResourceType.PAYROLL,
  proclamation: PrismaResourceType.PROCLAMATION,
  permission: PrismaResourceType.PERMISSION,
  other: PrismaResourceType.OTHER,
};

export const CONTRACT_RESOURCE_TYPE = Object.fromEntries(
  Object.entries(PRISMA_RESOURCE_TYPE).map(([contract, prisma]) => [
    prisma,
    contract,
  ]),
) as Record<PrismaResourceType, AccessResourceType>;

export const PRISMA_RELATION_TYPE: Record<
  AccessRelationType,
  PrismaRelationType
> = {
  teacher_class: PrismaRelationType.TEACHER_CLASS,
  teacher_subject: PrismaRelationType.TEACHER_SUBJECT,
  principal_class: PrismaRelationType.PRINCIPAL_CLASS,
  guardian_student: PrismaRelationType.GUARDIAN_STUDENT,
  student_self: PrismaRelationType.STUDENT_SELF,
  employee_self: PrismaRelationType.EMPLOYEE_SELF,
};

export interface ValueSource {
  in: "body" | "query" | "params";
  key: string;
}

export interface PermissionRequirement {
  permission: Permission;
  organization: ValueSource;
  resource: ValueSource;
  resourceType: AccessResourceType;
  relation?: AccessRelationType;
}

export interface AuthorizationInput {
  userId: string;
  organizationId: string;
  permission: Permission;
  resourceId: string;
  resourceType: AccessResourceType;
  relation?: AccessRelationType;
}

export type AuthorizationDenialReason =
  | "RESOURCE_MISMATCH"
  | "NO_MEMBERSHIP"
  | "NO_PERMISSION"
  | "SCOPE_MISMATCH"
  | "RELATION_REQUIRED";

export type AuthorizationDecision =
  | { allowed: true; reason: "ALLOWED" }
  | { allowed: false; reason: AuthorizationDenialReason };
