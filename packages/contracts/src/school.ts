import { z } from "zod";

const codeSchema = z
  .string()
  .trim()
  .min(2)
  .max(32)
  .regex(/^[A-Z0-9][A-Z0-9_-]*$/, "Code invalide.");
const labelSchema = z.string().trim().min(1).max(160);
const reasonSchema = z.string().trim().min(3).max(500);
const dateSchema = z.iso.date();
const timestampSchema = z.iso.datetime();
const positiveIntegerSchema = z.number().int().min(1).max(100000);
const optionalArchiveQuery = z.enum(["true", "false"]).optional();

export const SCHOOL_ENTITY_TYPES = [
  "organization",
  "site",
  "level",
  "room",
  "school_year",
  "period",
  "non_working_day",
] as const;
export const schoolEntityTypeSchema = z.enum(SCHOOL_ENTITY_TYPES);
export const schoolYearStatusSchema = z.enum(["DRAFT", "ACTIVE", "CLOSED"]);

export const schoolListQuerySchema = z
  .object({
    organizationId: z.uuid(),
    siteId: z.uuid().optional(),
    includeArchived: optionalArchiveQuery,
  })
  .strict();

export const schoolHistoryQuerySchema = z
  .object({
    organizationId: z.uuid(),
    entityType: schoolEntityTypeSchema.optional(),
    entityId: z.uuid().optional(),
    limit: z.coerce.number().int().min(1).max(100).default(50),
  })
  .strict();

export const organizationUpdateInputSchema = z
  .object({
    organizationId: z.uuid(),
    code: codeSchema.optional(),
    name: labelSchema.optional(),
  })
  .strict()
  .refine((value) => value.code !== undefined || value.name !== undefined, {
    message: "Au moins un champ à modifier est requis.",
  });

export const siteCreateInputSchema = z
  .object({ organizationId: z.uuid(), code: codeSchema, name: labelSchema })
  .strict();
export const siteUpdateInputSchema = z
  .object({
    organizationId: z.uuid(),
    code: codeSchema.optional(),
    name: labelSchema.optional(),
  })
  .strict()
  .refine((value) => value.code !== undefined || value.name !== undefined, {
    message: "Au moins un champ à modifier est requis.",
  });

export const levelCreateInputSchema = z
  .object({
    organizationId: z.uuid(),
    code: codeSchema,
    name: labelSchema,
    rank: positiveIntegerSchema,
  })
  .strict();
export const levelUpdateInputSchema = z
  .object({
    organizationId: z.uuid(),
    code: codeSchema.optional(),
    name: labelSchema.optional(),
    rank: positiveIntegerSchema.optional(),
  })
  .strict()
  .refine(
    (value) =>
      value.code !== undefined ||
      value.name !== undefined ||
      value.rank !== undefined,
    { message: "Au moins un champ à modifier est requis." },
  );

export const roomCreateInputSchema = z
  .object({
    organizationId: z.uuid(),
    siteId: z.uuid(),
    code: codeSchema,
    name: labelSchema,
    capacity: positiveIntegerSchema.optional(),
  })
  .strict();
export const roomUpdateInputSchema = z
  .object({
    organizationId: z.uuid(),
    code: codeSchema.optional(),
    name: labelSchema.optional(),
    capacity: positiveIntegerSchema.nullable().optional(),
  })
  .strict()
  .refine(
    (value) =>
      value.code !== undefined ||
      value.name !== undefined ||
      value.capacity !== undefined,
    { message: "Au moins un champ à modifier est requis." },
  );

export const schoolYearCreateInputSchema = z
  .object({
    organizationId: z.uuid(),
    code: codeSchema,
    name: labelSchema,
    startDate: dateSchema,
    endDate: dateSchema,
  })
  .strict();
export const schoolYearUpdateInputSchema = z
  .object({
    organizationId: z.uuid(),
    code: codeSchema.optional(),
    name: labelSchema.optional(),
    startDate: dateSchema.optional(),
    endDate: dateSchema.optional(),
  })
  .strict()
  .refine(
    (value) =>
      value.code !== undefined ||
      value.name !== undefined ||
      value.startDate !== undefined ||
      value.endDate !== undefined,
    { message: "Au moins un champ à modifier est requis." },
  );
export const schoolYearActivationInputSchema = z
  .object({ organizationId: z.uuid() })
  .strict();
export const schoolYearCloseInputSchema = z
  .object({ organizationId: z.uuid(), reason: reasonSchema })
  .strict();
export const schoolYearReopenInputSchema = z
  .object({ organizationId: z.uuid(), reason: reasonSchema })
  .strict();

export const periodCreateInputSchema = z
  .object({
    organizationId: z.uuid(),
    schoolYearId: z.uuid(),
    code: codeSchema,
    name: labelSchema,
    sequence: positiveIntegerSchema,
    startDate: dateSchema,
    endDate: dateSchema,
  })
  .strict();
export const periodUpdateInputSchema = z
  .object({
    organizationId: z.uuid(),
    code: codeSchema.optional(),
    name: labelSchema.optional(),
    sequence: positiveIntegerSchema.optional(),
    startDate: dateSchema.optional(),
    endDate: dateSchema.optional(),
  })
  .strict()
  .refine(
    (value) =>
      value.code !== undefined ||
      value.name !== undefined ||
      value.sequence !== undefined ||
      value.startDate !== undefined ||
      value.endDate !== undefined,
    { message: "Au moins un champ à modifier est requis." },
  );

export const nonWorkingDayCreateInputSchema = z
  .object({
    organizationId: z.uuid(),
    schoolYearId: z.uuid(),
    siteId: z.uuid().optional(),
    day: dateSchema,
    reason: labelSchema,
  })
  .strict();
export const nonWorkingDayUpdateInputSchema = z
  .object({
    organizationId: z.uuid(),
    day: dateSchema.optional(),
    reason: labelSchema.optional(),
  })
  .strict()
  .refine((value) => value.day !== undefined || value.reason !== undefined, {
    message: "Au moins un champ à modifier est requis.",
  });

export const schoolArchiveInputSchema = z
  .object({ organizationId: z.uuid(), reason: reasonSchema.optional() })
  .strict();

export const organizationResponseSchema = z
  .object({
    id: z.uuid(),
    code: z.string(),
    name: z.string(),
    createdAt: timestampSchema,
    updatedAt: timestampSchema,
    archivedAt: timestampSchema.nullable(),
  })
  .strict();
export const siteResponseSchema = z
  .object({
    id: z.uuid(),
    organizationId: z.uuid(),
    code: z.string(),
    name: z.string(),
    createdAt: timestampSchema,
    updatedAt: timestampSchema,
    archivedAt: timestampSchema.nullable(),
  })
  .strict();
export const levelResponseSchema = z
  .object({
    id: z.uuid(),
    organizationId: z.uuid(),
    code: z.string(),
    name: z.string(),
    rank: z.number().int(),
    createdAt: timestampSchema,
    updatedAt: timestampSchema,
    archivedAt: timestampSchema.nullable(),
  })
  .strict();
export const roomResponseSchema = z
  .object({
    id: z.uuid(),
    organizationId: z.uuid(),
    siteId: z.uuid(),
    code: z.string(),
    name: z.string(),
    capacity: z.number().int().nullable(),
    createdAt: timestampSchema,
    updatedAt: timestampSchema,
    archivedAt: timestampSchema.nullable(),
  })
  .strict();
export const schoolYearResponseSchema = z
  .object({
    id: z.uuid(),
    organizationId: z.uuid(),
    code: z.string(),
    name: z.string(),
    startDate: dateSchema,
    endDate: dateSchema,
    status: schoolYearStatusSchema,
    closedAt: timestampSchema.nullable(),
    closureReason: z.string().nullable(),
    reopenedAt: timestampSchema.nullable(),
    reopenReason: z.string().nullable(),
    createdAt: timestampSchema,
    updatedAt: timestampSchema,
    archivedAt: timestampSchema.nullable(),
  })
  .strict();
export const periodResponseSchema = z
  .object({
    id: z.uuid(),
    schoolYearId: z.uuid(),
    code: z.string(),
    name: z.string(),
    sequence: z.number().int(),
    startDate: dateSchema,
    endDate: dateSchema,
    createdAt: timestampSchema,
    updatedAt: timestampSchema,
    archivedAt: timestampSchema.nullable(),
  })
  .strict();
export const nonWorkingDayResponseSchema = z
  .object({
    id: z.uuid(),
    schoolYearId: z.uuid(),
    siteId: z.uuid().nullable(),
    day: dateSchema,
    reason: z.string(),
    createdAt: timestampSchema,
    updatedAt: timestampSchema,
    archivedAt: timestampSchema.nullable(),
  })
  .strict();
export const referentialRevisionResponseSchema = z
  .object({
    id: z.uuid(),
    organizationId: z.uuid(),
    entityType: schoolEntityTypeSchema,
    entityId: z.uuid(),
    action: z.string(),
    reason: z.string().nullable(),
    actorUserId: z.uuid().nullable(),
    requestId: z.uuid(),
    before: z.unknown().nullable(),
    after: z.unknown().nullable(),
    createdAt: timestampSchema,
  })
  .strict();

export const sitesResponseSchema = z.object({ sites: z.array(siteResponseSchema) }).strict();
export const levelsResponseSchema = z.object({ levels: z.array(levelResponseSchema) }).strict();
export const roomsResponseSchema = z.object({ rooms: z.array(roomResponseSchema) }).strict();
export const schoolYearsResponseSchema = z
  .object({ years: z.array(schoolYearResponseSchema) })
  .strict();
export const periodsResponseSchema = z
  .object({ periods: z.array(periodResponseSchema) })
  .strict();
export const nonWorkingDaysResponseSchema = z
  .object({ days: z.array(nonWorkingDayResponseSchema) })
  .strict();
export const referentialRevisionsResponseSchema = z
  .object({ revisions: z.array(referentialRevisionResponseSchema) })
  .strict();

export type SchoolEntityType = z.infer<typeof schoolEntityTypeSchema>;
export type SchoolListQuery = z.infer<typeof schoolListQuerySchema>;
export type SchoolHistoryQuery = z.infer<typeof schoolHistoryQuerySchema>;
export type OrganizationUpdateInput = z.infer<typeof organizationUpdateInputSchema>;
export type SiteCreateInput = z.infer<typeof siteCreateInputSchema>;
export type SiteUpdateInput = z.infer<typeof siteUpdateInputSchema>;
export type LevelCreateInput = z.infer<typeof levelCreateInputSchema>;
export type LevelUpdateInput = z.infer<typeof levelUpdateInputSchema>;
export type RoomCreateInput = z.infer<typeof roomCreateInputSchema>;
export type RoomUpdateInput = z.infer<typeof roomUpdateInputSchema>;
export type SchoolYearCreateInput = z.infer<typeof schoolYearCreateInputSchema>;
export type SchoolYearUpdateInput = z.infer<typeof schoolYearUpdateInputSchema>;
export type SchoolYearActivationInput = z.infer<typeof schoolYearActivationInputSchema>;
export type SchoolYearCloseInput = z.infer<typeof schoolYearCloseInputSchema>;
export type SchoolYearReopenInput = z.infer<typeof schoolYearReopenInputSchema>;
export type PeriodCreateInput = z.infer<typeof periodCreateInputSchema>;
export type PeriodUpdateInput = z.infer<typeof periodUpdateInputSchema>;
export type NonWorkingDayCreateInput = z.infer<typeof nonWorkingDayCreateInputSchema>;
export type NonWorkingDayUpdateInput = z.infer<typeof nonWorkingDayUpdateInputSchema>;
export type SchoolArchiveInput = z.infer<typeof schoolArchiveInputSchema>;
