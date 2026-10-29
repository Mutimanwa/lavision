import { z } from "zod";
import { roleSchema } from "./auth.js";
import { PERMISSIONS, type Permission } from "./permissions.js";

export const ACCESS_RESOURCE_TYPES = [
  "organization",
  "site",
  "level",
  "room",
  "school_year",
  "period",
  "non_working_day",
  "class",
  "subject",
  "section",
  "option",
  "program",
  "student",
  "employee",
  "note",
  "payment",
  "payroll",
  "proclamation",
  "permission",
  "other",
] as const;

export const ACCESS_RELATION_TYPES = [
  "teacher_class",
  "teacher_subject",
  "principal_class",
  "guardian_student",
  "student_self",
  "employee_self",
] as const;

export const accessResourceTypeSchema = z.enum(ACCESS_RESOURCE_TYPES);
export const accessRelationTypeSchema = z.enum(ACCESS_RELATION_TYPES);
export const permissionSchema = z.enum(
  Object.values(PERMISSIONS) as [Permission, ...Permission[]],
);

export const accessScopeSchema = z
  .object({ type: accessResourceTypeSchema, id: z.uuid() })
  .strict();

export const permissionGrantInputSchema = z
  .object({
    organizationId: z.uuid(),
    userId: z.uuid(),
    permission: permissionSchema,
    scope: accessScopeSchema,
  })
  .strict();

export const permissionRevokeInputSchema = z
  .object({ organizationId: z.uuid(), grantId: z.uuid() })
  .strict();

export const roleAssignmentInputSchema = z
  .object({
    organizationId: z.uuid(),
    userId: z.uuid(),
    role: roleSchema,
    scope: accessScopeSchema,
  })
  .strict();

export const roleRevokeInputSchema = z
  .object({ organizationId: z.uuid(), assignmentId: z.uuid() })
  .strict();

export const auditQuerySchema = z
  .object({
    organizationId: z.uuid(),
    action: z
      .string()
      .regex(/^[a-z][a-z0-9._-]{0,95}$/)
      .optional(),
    resourceType: z.string().trim().min(1).max(64).optional(),
    before: z.iso.datetime().optional(),
    limit: z.coerce.number().int().min(1).max(100).default(50),
  })
  .strict();

export const auditContextSchema = z.record(
  z.string(),
  z.union([z.string(), z.number(), z.boolean()]),
);

export const auditEventSchema = z
  .object({
    id: z.uuid(),
    organizationId: z.uuid(),
    actorUserId: z.uuid().nullable(),
    action: z.string(),
    resourceType: z.string(),
    resourceId: z.uuid().nullable(),
    outcome: z.enum(["SUCCESS", "DENIED", "FAILURE"]),
    requestId: z.uuid(),
    reason: z.string().nullable(),
    context: auditContextSchema.nullable(),
    createdAt: z.iso.datetime(),
  })
  .strict();

export const auditEventsResponseSchema = z
  .object({ events: z.array(auditEventSchema) })
  .strict();

export const permissionGrantResponseSchema = z
  .object({
    id: z.uuid(),
    userId: z.uuid(),
    permission: permissionSchema,
    scope: accessScopeSchema,
    createdAt: z.iso.datetime(),
    archivedAt: z.iso.datetime().nullable(),
  })
  .strict();

export const roleAssignmentResponseSchema = z
  .object({
    id: z.uuid(),
    userId: z.uuid(),
    role: roleSchema,
    scope: accessScopeSchema,
    createdAt: z.iso.datetime(),
    archivedAt: z.iso.datetime().nullable(),
  })
  .strict();

export type AccessResourceType = z.infer<typeof accessResourceTypeSchema>;
export type AccessRelationType = z.infer<typeof accessRelationTypeSchema>;
export type AccessScope = z.infer<typeof accessScopeSchema>;
export type PermissionGrantInput = z.infer<typeof permissionGrantInputSchema>;
export type PermissionRevokeInput = z.infer<typeof permissionRevokeInputSchema>;
export type RoleAssignmentInput = z.infer<typeof roleAssignmentInputSchema>;
export type RoleRevokeInput = z.infer<typeof roleRevokeInputSchema>;
export type AuditQuery = z.infer<typeof auditQuerySchema>;
export type AuditEventResponse = z.infer<typeof auditEventSchema>;
export type AuditEventsResponse = z.infer<typeof auditEventsResponseSchema>;
export type PermissionGrantResponse = z.infer<typeof permissionGrantResponseSchema>;
export type RoleAssignmentResponse = z.infer<typeof roleAssignmentResponseSchema>;
