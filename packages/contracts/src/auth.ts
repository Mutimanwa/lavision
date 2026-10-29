import { z } from "zod";

export const ROLE_IDS = [
  "technical_admin",
  "direction",
  "academic_manager",
  "school_secretary",
  "student_affairs",
  "teacher",
  "accountant",
  "cashier",
  "hr_manager",
  "employee",
  "guardian",
  "student",
  "auditor",
] as const;

export const MFA_REQUIRED_ROLE_IDS = [
  "technical_admin",
  "direction",
  "accountant",
  "cashier",
  "hr_manager",
] as const;

export const roleSchema = z.enum(ROLE_IDS);
export type RoleId = z.infer<typeof roleSchema>;

const emailSchema = z
  .string()
  .trim()
  .toLowerCase()
  .pipe(z.email().max(320));
const oneTimeTokenSchema = z.string().min(32).max(512);
export const authSessionIdSchema = z.uuid();

export const newPasswordSchema = z.string().min(12).max(128);

export const loginInputSchema = z
  .object({
    email: emailSchema,
    password: z.string().min(1).max(128),
  })
  .strict();

export const invitationInputSchema = z
  .object({
    organizationId: z.uuid(),
    email: emailSchema,
    displayName: z.string().trim().min(1).max(160),
    roles: z
      .array(roleSchema)
      .min(1)
      .max(ROLE_IDS.length)
      .refine((roles) => new Set(roles).size === roles.length, {
        message: "Les rôles ne doivent pas contenir de doublon.",
      }),
  })
  .strict();

export const organizationActionInputSchema = z
  .object({ organizationId: z.uuid() })
  .strict();

export const acceptInvitationInputSchema = z
  .object({
    token: oneTimeTokenSchema,
    password: newPasswordSchema,
  })
  .strict();

export const passwordResetRequestInputSchema = z
  .object({ email: emailSchema })
  .strict();

export const passwordResetConfirmInputSchema = z
  .object({
    token: oneTimeTokenSchema,
    password: newPasswordSchema,
  })
  .strict();

export const mfaSetupInputSchema = z
  .object({ challengeToken: oneTimeTokenSchema })
  .strict();

export const mfaVerifyInputSchema = z
  .object({
    challengeToken: oneTimeTokenSchema,
    code: z.string().regex(/^\d{6}$/),
  })
  .strict();

export const mfaRecoveryInputSchema = z
  .object({
    challengeToken: oneTimeTokenSchema,
    recoveryCode: z.string().min(8).max(32),
  })
  .strict();

export const authUserSchema = z
  .object({
    id: z.uuid(),
    email: z.email(),
    displayName: z.string(),
    status: z.enum(["INVITED", "ACTIVE", "SUSPENDED", "DISABLED"]),
  })
  .strict();

export const authSessionSchema = z
  .object({
    id: z.uuid(),
    createdAt: z.iso.datetime(),
    lastSeenAt: z.iso.datetime(),
    expiresAt: z.iso.datetime(),
    absoluteExpiresAt: z.iso.datetime(),
    mfaVerifiedAt: z.iso.datetime().nullable(),
    userAgent: z.string().nullable(),
    current: z.boolean(),
  })
  .strict();

export const authenticatedResponseSchema = z
  .object({
    status: z.literal("authenticated"),
    user: authUserSchema,
    session: authSessionSchema,
  })
  .strict();

export const mfaRequiredResponseSchema = z
  .object({
    status: z.literal("mfa_required"),
    challengeToken: oneTimeTokenSchema,
    enrollmentRequired: z.boolean(),
  })
  .strict();

export const mfaSetupResponseSchema = z
  .object({
    status: z.literal("mfa_setup"),
    secret: z.string().min(16),
    otpauthUrl: z.string().startsWith("otpauth://totp/"),
  })
  .strict();

export const mfaSetupConfirmResponseSchema = authenticatedResponseSchema
  .extend({ recoveryCodes: z.array(z.string()).length(10) })
  .strict();

export const membershipResponseSchema = z
  .object({
    organizationId: z.uuid(),
    organizationCode: z.string(),
    organizationName: z.string(),
    roles: z.array(roleSchema),
  })
  .strict();

export const authMeResponseSchema = z
  .object({
    user: authUserSchema,
    memberships: z.array(membershipResponseSchema),
    session: authSessionSchema,
  })
  .strict();

export const sessionsResponseSchema = z
  .object({ sessions: z.array(authSessionSchema) })
  .strict();

export const acceptedResponseSchema = z
  .object({ status: z.literal("accepted") })
  .strict();

export const invitationResponseSchema = z
  .object({ status: z.literal("invited"), userId: z.uuid() })
  .strict();

export type LoginInput = z.infer<typeof loginInputSchema>;
export type InvitationInput = z.infer<typeof invitationInputSchema>;
export type OrganizationActionInput = z.infer<typeof organizationActionInputSchema>;
export type AcceptInvitationInput = z.infer<typeof acceptInvitationInputSchema>;
export type PasswordResetRequestInput = z.infer<typeof passwordResetRequestInputSchema>;
export type PasswordResetConfirmInput = z.infer<typeof passwordResetConfirmInputSchema>;
export type MfaSetupInput = z.infer<typeof mfaSetupInputSchema>;
export type MfaVerifyInput = z.infer<typeof mfaVerifyInputSchema>;
export type MfaRecoveryInput = z.infer<typeof mfaRecoveryInputSchema>;
export type AuthenticatedResponse = z.infer<typeof authenticatedResponseSchema>;
export type MfaRequiredResponse = z.infer<typeof mfaRequiredResponseSchema>;
export type MfaSetupResponse = z.infer<typeof mfaSetupResponseSchema>;
export type AuthMeResponse = z.infer<typeof authMeResponseSchema>;
export type SessionsResponse = z.infer<typeof sessionsResponseSchema>;
export type InvitationResponse = z.infer<typeof invitationResponseSchema>;
export type AuthSessionResponse = z.infer<typeof authSessionSchema>;
export type AuthUserResponse = z.infer<typeof authUserSchema>;
