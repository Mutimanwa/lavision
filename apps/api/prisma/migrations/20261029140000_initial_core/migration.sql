-- Socle initial M1. Les évolutions ultérieures sont additives et versionnées.
CREATE EXTENSION IF NOT EXISTS citext;

CREATE TYPE "UserStatus" AS ENUM ('INVITED', 'ACTIVE', 'SUSPENDED', 'DISABLED');
CREATE TYPE "MembershipStatus" AS ENUM ('ACTIVE', 'SUSPENDED');

CREATE TABLE "organizations" (
  "id" UUID NOT NULL,
  "code" VARCHAR(32) NOT NULL,
  "name" VARCHAR(160) NOT NULL,
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updatedAt" TIMESTAMPTZ(6) NOT NULL,
  "archivedAt" TIMESTAMPTZ(6),
  CONSTRAINT "organizations_pkey" PRIMARY KEY ("id")
);

CREATE TABLE "sites" (
  "id" UUID NOT NULL,
  "organizationId" UUID NOT NULL,
  "code" VARCHAR(32) NOT NULL,
  "name" VARCHAR(160) NOT NULL,
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updatedAt" TIMESTAMPTZ(6) NOT NULL,
  "archivedAt" TIMESTAMPTZ(6),
  CONSTRAINT "sites_pkey" PRIMARY KEY ("id")
);

CREATE TABLE "users" (
  "id" UUID NOT NULL,
  "email" CITEXT NOT NULL,
  "displayName" VARCHAR(160) NOT NULL,
  "status" "UserStatus" NOT NULL DEFAULT 'INVITED',
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updatedAt" TIMESTAMPTZ(6) NOT NULL,
  "archivedAt" TIMESTAMPTZ(6),
  CONSTRAINT "users_pkey" PRIMARY KEY ("id")
);

CREATE TABLE "organization_memberships" (
  "id" UUID NOT NULL,
  "organizationId" UUID NOT NULL,
  "userId" UUID NOT NULL,
  "status" "MembershipStatus" NOT NULL DEFAULT 'ACTIVE',
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updatedAt" TIMESTAMPTZ(6) NOT NULL,
  "archivedAt" TIMESTAMPTZ(6),
  CONSTRAINT "organization_memberships_pkey" PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX "organizations_code_key" ON "organizations"("code");
CREATE UNIQUE INDEX "sites_organizationId_code_key" ON "sites"("organizationId", "code");
CREATE INDEX "sites_organizationId_idx" ON "sites"("organizationId");
CREATE UNIQUE INDEX "users_email_key" ON "users"("email");
CREATE UNIQUE INDEX "organization_memberships_organizationId_userId_key" ON "organization_memberships"("organizationId", "userId");
CREATE INDEX "organization_memberships_userId_idx" ON "organization_memberships"("userId");

ALTER TABLE "sites"
  ADD CONSTRAINT "sites_organizationId_fkey"
  FOREIGN KEY ("organizationId") REFERENCES "organizations"("id")
  ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "organization_memberships"
  ADD CONSTRAINT "organization_memberships_organizationId_fkey"
  FOREIGN KEY ("organizationId") REFERENCES "organizations"("id")
  ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "organization_memberships"
  ADD CONSTRAINT "organization_memberships_userId_fkey"
  FOREIGN KEY ("userId") REFERENCES "users"("id")
  ON DELETE RESTRICT ON UPDATE CASCADE;
