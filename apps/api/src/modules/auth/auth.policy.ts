import {
  MFA_REQUIRED_ROLE_IDS,
  type RoleId,
} from "@lavision/contracts";
import { Role } from "../../database/client.js";

export const ROLE_ID_BY_PRISMA_ROLE: Record<Role, RoleId> = {
  TECHNICAL_ADMIN: "technical_admin",
  DIRECTION: "direction",
  ACADEMIC_MANAGER: "academic_manager",
  SCHOOL_SECRETARY: "school_secretary",
  STUDENT_AFFAIRS: "student_affairs",
  TEACHER: "teacher",
  ACCOUNTANT: "accountant",
  CASHIER: "cashier",
  HR_MANAGER: "hr_manager",
  EMPLOYEE: "employee",
  GUARDIAN: "guardian",
  STUDENT: "student",
  AUDITOR: "auditor",
};

export const PRISMA_ROLE_BY_ROLE_ID = Object.fromEntries(
  Object.entries(ROLE_ID_BY_PRISMA_ROLE).map(([role, roleId]) => [roleId, role]),
) as Record<RoleId, Role>;

export function requiresMfa(roles: Role[]): boolean {
  return roles.some((role) => {
    const roleId = ROLE_ID_BY_PRISMA_ROLE[role];
    return MFA_REQUIRED_ROLE_IDS.some((requiredRole) => requiredRole === roleId);
  });
}
