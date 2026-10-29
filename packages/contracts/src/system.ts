import { z } from "zod";

export const API_PREFIX = "/api/v1";
export const API_VERSION = "v1";
export const SERVICE_NAME = "lavision-api";

export const livenessResponseSchema = z
  .object({
    status: z.literal("ok"),
    service: z.literal(SERVICE_NAME),
  })
  .strict();

export const readinessResponseSchema = z
  .object({
    status: z.literal("ready"),
    service: z.literal(SERVICE_NAME),
    checks: z
      .object({
        database: z.literal("up"),
      })
      .strict(),
  })
  .strict();

export const versionResponseSchema = z
  .object({
    name: z.literal(SERVICE_NAME),
    version: z.string().min(1).max(64),
    apiVersion: z.literal(API_VERSION),
  })
  .strict();

export const ERROR_CODES = {
  NOT_FOUND: "NOT_FOUND",
  VALIDATION_ERROR: "VALIDATION_ERROR",
  UNAUTHORIZED: "UNAUTHORIZED",
  FORBIDDEN: "FORBIDDEN",
  CSRF_INVALID: "CSRF_INVALID",
  SESSION_EXPIRED: "SESSION_EXPIRED",
  MFA_REQUIRED: "MFA_REQUIRED",
  MFA_INVALID: "MFA_INVALID",
  RATE_LIMITED: "RATE_LIMITED",
  TOKEN_INVALID: "TOKEN_INVALID",
  USER_CONFLICT: "USER_CONFLICT",
  CONFLICT: "CONFLICT",
  MAIL_UNAVAILABLE: "MAIL_UNAVAILABLE",
  DATABASE_UNAVAILABLE: "DATABASE_UNAVAILABLE",
  INTERNAL_ERROR: "INTERNAL_ERROR",
} as const;

export const errorCodeSchema = z.enum([
  "NOT_FOUND",
  "VALIDATION_ERROR",
  "UNAUTHORIZED",
  "FORBIDDEN",
  "CSRF_INVALID",
  "SESSION_EXPIRED",
  "MFA_REQUIRED",
  "MFA_INVALID",
  "RATE_LIMITED",
  "TOKEN_INVALID",
  "USER_CONFLICT",
  "CONFLICT",
  "MAIL_UNAVAILABLE",
  "DATABASE_UNAVAILABLE",
  "INTERNAL_ERROR",
]);

export const problemDetailsSchema = z
  .object({
    type: z.string().min(1),
    title: z.string().min(1),
    status: z.number().int().min(400).max(599),
    detail: z.string().min(1),
    code: errorCodeSchema,
    instance: z.string().startsWith("/"),
    requestId: z.uuid(),
  })
  .strict();

export type LivenessResponse = z.infer<typeof livenessResponseSchema>;
export type ReadinessResponse = z.infer<typeof readinessResponseSchema>;
export type VersionResponse = z.infer<typeof versionResponseSchema>;
export type ProblemDetails = z.infer<typeof problemDetailsSchema>;
