-- Authentification M1 : évolution additive, sans réécriture du socle initial.
CREATE TYPE "Role" AS ENUM (
  'technical_admin', 'direction', 'academic_manager', 'school_secretary',
  'student_affairs', 'teacher', 'accountant', 'cashier', 'hr_manager',
  'employee', 'guardian', 'student', 'auditor'
);
CREATE TYPE "AuthTokenKind" AS ENUM ('INVITATION', 'PASSWORD_RESET', 'MFA_LOGIN', 'MFA_SETUP');
CREATE TYPE "AuthEventOutcome" AS ENUM ('SUCCESS', 'FAILURE', 'RATE_LIMITED');
CREATE TYPE "AuthRateAction" AS ENUM ('LOGIN', 'MFA', 'PASSWORD_RESET');

ALTER TABLE "users"
  ADD COLUMN "passwordHash" TEXT,
  ADD COLUMN "authVersion" INTEGER NOT NULL DEFAULT 0,
  ADD COLUMN "lastLoginAt" TIMESTAMPTZ(6);

CREATE TABLE "role_assignments" (
  "id" UUID NOT NULL,
  "membershipId" UUID NOT NULL,
  "role" "Role" NOT NULL,
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "archivedAt" TIMESTAMPTZ(6),
  CONSTRAINT "role_assignments_pkey" PRIMARY KEY ("id")
);

CREATE TABLE "auth_sessions" (
  "id" UUID NOT NULL,
  "userId" UUID NOT NULL,
  "tokenHash" CHAR(64) NOT NULL,
  "csrfTokenHash" CHAR(64) NOT NULL,
  "authVersion" INTEGER NOT NULL,
  "mfaVerifiedAt" TIMESTAMPTZ(6),
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "lastSeenAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "expiresAt" TIMESTAMPTZ(6) NOT NULL,
  "absoluteExpiresAt" TIMESTAMPTZ(6) NOT NULL,
  "revokedAt" TIMESTAMPTZ(6),
  "revocationReason" VARCHAR(64),
  "userAgent" VARCHAR(512),
  CONSTRAINT "auth_sessions_pkey" PRIMARY KEY ("id")
);

CREATE TABLE "auth_one_time_tokens" (
  "id" UUID NOT NULL,
  "userId" UUID NOT NULL,
  "kind" "AuthTokenKind" NOT NULL,
  "tokenHash" CHAR(64) NOT NULL,
  "attemptCount" INTEGER NOT NULL DEFAULT 0,
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "expiresAt" TIMESTAMPTZ(6) NOT NULL,
  "consumedAt" TIMESTAMPTZ(6),
  CONSTRAINT "auth_one_time_tokens_pkey" PRIMARY KEY ("id")
);

CREATE TABLE "mfa_credentials" (
  "id" UUID NOT NULL,
  "userId" UUID NOT NULL,
  "secretCiphertext" TEXT NOT NULL,
  "confirmedAt" TIMESTAMPTZ(6),
  "lastAcceptedTimeStep" INTEGER,
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updatedAt" TIMESTAMPTZ(6) NOT NULL,
  CONSTRAINT "mfa_credentials_pkey" PRIMARY KEY ("id")
);

CREATE TABLE "mfa_recovery_codes" (
  "id" UUID NOT NULL,
  "userId" UUID NOT NULL,
  "codeHash" CHAR(64) NOT NULL,
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "usedAt" TIMESTAMPTZ(6),
  CONSTRAINT "mfa_recovery_codes_pkey" PRIMARY KEY ("id")
);

CREATE TABLE "auth_events" (
  "id" UUID NOT NULL,
  "userId" UUID,
  "action" VARCHAR(64) NOT NULL,
  "outcome" "AuthEventOutcome" NOT NULL,
  "reason" VARCHAR(64),
  "requestId" UUID NOT NULL,
  "subjectHash" CHAR(64),
  "ipHash" CHAR(64),
  "userAgent" VARCHAR(512),
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT "auth_events_pkey" PRIMARY KEY ("id")
);

CREATE TABLE "auth_rate_buckets" (
  "id" UUID NOT NULL,
  "action" "AuthRateAction" NOT NULL,
  "keyHash" CHAR(64) NOT NULL,
  "windowStartedAt" TIMESTAMPTZ(6) NOT NULL,
  "failureCount" INTEGER NOT NULL DEFAULT 0,
  "blockedUntil" TIMESTAMPTZ(6),
  "updatedAt" TIMESTAMPTZ(6) NOT NULL,
  CONSTRAINT "auth_rate_buckets_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "role_assignments_membershipId_role_key" ON "role_assignments"("membershipId", "role");
CREATE INDEX "role_assignments_role_idx" ON "role_assignments"("role");
CREATE UNIQUE INDEX "auth_sessions_tokenHash_key" ON "auth_sessions"("tokenHash");
CREATE INDEX "auth_sessions_userId_revokedAt_idx" ON "auth_sessions"("userId", "revokedAt");
CREATE INDEX "auth_sessions_expiresAt_idx" ON "auth_sessions"("expiresAt");
CREATE UNIQUE INDEX "auth_one_time_tokens_tokenHash_key" ON "auth_one_time_tokens"("tokenHash");
CREATE INDEX "auth_one_time_tokens_userId_kind_consumedAt_idx" ON "auth_one_time_tokens"("userId", "kind", "consumedAt");
CREATE UNIQUE INDEX "mfa_credentials_userId_key" ON "mfa_credentials"("userId");
CREATE UNIQUE INDEX "mfa_recovery_codes_userId_codeHash_key" ON "mfa_recovery_codes"("userId", "codeHash");
CREATE INDEX "mfa_recovery_codes_userId_usedAt_idx" ON "mfa_recovery_codes"("userId", "usedAt");
CREATE INDEX "auth_events_userId_createdAt_idx" ON "auth_events"("userId", "createdAt");
CREATE INDEX "auth_events_action_createdAt_idx" ON "auth_events"("action", "createdAt");
CREATE UNIQUE INDEX "auth_rate_buckets_action_keyHash_key" ON "auth_rate_buckets"("action", "keyHash");

ALTER TABLE "role_assignments"
  ADD CONSTRAINT "role_assignments_membershipId_fkey"
  FOREIGN KEY ("membershipId") REFERENCES "organization_memberships"("id")
  ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "auth_sessions"
  ADD CONSTRAINT "auth_sessions_userId_fkey"
  FOREIGN KEY ("userId") REFERENCES "users"("id")
  ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "auth_one_time_tokens"
  ADD CONSTRAINT "auth_one_time_tokens_userId_fkey"
  FOREIGN KEY ("userId") REFERENCES "users"("id")
  ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "mfa_credentials"
  ADD CONSTRAINT "mfa_credentials_userId_fkey"
  FOREIGN KEY ("userId") REFERENCES "users"("id")
  ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "mfa_recovery_codes"
  ADD CONSTRAINT "mfa_recovery_codes_userId_fkey"
  FOREIGN KEY ("userId") REFERENCES "users"("id")
  ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "auth_events"
  ADD CONSTRAINT "auth_events_userId_fkey"
  FOREIGN KEY ("userId") REFERENCES "users"("id")
  ON DELETE SET NULL ON UPDATE CASCADE;
