-- Référentiels d’établissement et calendrier #8.
-- Les nouvelles ressources sont enregistrées par les commandes serveur après validation.
ALTER TYPE "AccessResourceType" ADD VALUE 'level';
ALTER TYPE "AccessResourceType" ADD VALUE 'room';
ALTER TYPE "AccessResourceType" ADD VALUE 'school_year';
ALTER TYPE "AccessResourceType" ADD VALUE 'period';
ALTER TYPE "AccessResourceType" ADD VALUE 'non_working_day';

CREATE TYPE "SchoolYearStatus" AS ENUM ('DRAFT', 'ACTIVE', 'CLOSED');

CREATE TABLE "academic_levels" (
  "id" UUID NOT NULL,
  "organizationId" UUID NOT NULL,
  "code" VARCHAR(32) NOT NULL,
  "name" VARCHAR(160) NOT NULL,
  "rank" INTEGER NOT NULL,
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updatedAt" TIMESTAMPTZ(6) NOT NULL,
  "archivedAt" TIMESTAMPTZ(6),
  CONSTRAINT "academic_levels_pkey" PRIMARY KEY ("id"),
  CONSTRAINT "academic_levels_rank_check" CHECK ("rank" > 0)
);
CREATE UNIQUE INDEX "academic_levels_organizationId_code_key" ON "academic_levels"("organizationId", "code");
CREATE INDEX "academic_levels_organizationId_archivedAt_idx" ON "academic_levels"("organizationId", "archivedAt");
ALTER TABLE "academic_levels"
  ADD CONSTRAINT "academic_levels_organizationId_fkey"
  FOREIGN KEY ("organizationId") REFERENCES "organizations"("id")
  ON DELETE RESTRICT ON UPDATE CASCADE;

CREATE TABLE "rooms" (
  "id" UUID NOT NULL,
  "organizationId" UUID NOT NULL,
  "siteId" UUID NOT NULL,
  "code" VARCHAR(32) NOT NULL,
  "name" VARCHAR(160) NOT NULL,
  "capacity" INTEGER,
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updatedAt" TIMESTAMPTZ(6) NOT NULL,
  "archivedAt" TIMESTAMPTZ(6),
  CONSTRAINT "rooms_pkey" PRIMARY KEY ("id"),
  CONSTRAINT "rooms_capacity_check" CHECK ("capacity" IS NULL OR "capacity" > 0)
);
CREATE UNIQUE INDEX "rooms_siteId_code_key" ON "rooms"("siteId", "code");
CREATE INDEX "rooms_organizationId_siteId_archivedAt_idx" ON "rooms"("organizationId", "siteId", "archivedAt");
ALTER TABLE "rooms"
  ADD CONSTRAINT "rooms_organizationId_fkey"
  FOREIGN KEY ("organizationId") REFERENCES "organizations"("id")
  ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "rooms"
  ADD CONSTRAINT "rooms_siteId_fkey"
  FOREIGN KEY ("siteId") REFERENCES "sites"("id")
  ON DELETE RESTRICT ON UPDATE CASCADE;

CREATE TABLE "school_years" (
  "id" UUID NOT NULL,
  "organizationId" UUID NOT NULL,
  "code" VARCHAR(32) NOT NULL,
  "name" VARCHAR(160) NOT NULL,
  "startDate" DATE NOT NULL,
  "endDate" DATE NOT NULL,
  "status" "SchoolYearStatus" NOT NULL DEFAULT 'DRAFT',
  "closedAt" TIMESTAMPTZ(6),
  "closedByUserId" UUID,
  "closureReason" VARCHAR(500),
  "reopenedAt" TIMESTAMPTZ(6),
  "reopenedByUserId" UUID,
  "reopenReason" VARCHAR(500),
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updatedAt" TIMESTAMPTZ(6) NOT NULL,
  "archivedAt" TIMESTAMPTZ(6),
  CONSTRAINT "school_years_pkey" PRIMARY KEY ("id"),
  CONSTRAINT "school_years_dates_check" CHECK ("startDate" < "endDate")
);
CREATE UNIQUE INDEX "school_years_organizationId_code_key" ON "school_years"("organizationId", "code");
CREATE INDEX "school_years_organizationId_status_archivedAt_idx" ON "school_years"("organizationId", "status", "archivedAt");
CREATE UNIQUE INDEX "school_years_one_active_per_organization"
  ON "school_years"("organizationId")
  WHERE "status" = 'ACTIVE' AND "archivedAt" IS NULL;
ALTER TABLE "school_years"
  ADD CONSTRAINT "school_years_organizationId_fkey"
  FOREIGN KEY ("organizationId") REFERENCES "organizations"("id")
  ON DELETE RESTRICT ON UPDATE CASCADE;

CREATE TABLE "school_periods" (
  "id" UUID NOT NULL,
  "schoolYearId" UUID NOT NULL,
  "code" VARCHAR(32) NOT NULL,
  "name" VARCHAR(160) NOT NULL,
  "sequence" INTEGER NOT NULL,
  "startDate" DATE NOT NULL,
  "endDate" DATE NOT NULL,
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updatedAt" TIMESTAMPTZ(6) NOT NULL,
  "archivedAt" TIMESTAMPTZ(6),
  CONSTRAINT "school_periods_pkey" PRIMARY KEY ("id"),
  CONSTRAINT "school_periods_dates_check" CHECK ("startDate" <= "endDate"),
  CONSTRAINT "school_periods_sequence_check" CHECK ("sequence" > 0)
);
CREATE UNIQUE INDEX "school_periods_schoolYearId_code_key" ON "school_periods"("schoolYearId", "code");
CREATE UNIQUE INDEX "school_periods_schoolYearId_sequence_key" ON "school_periods"("schoolYearId", "sequence");
CREATE INDEX "school_periods_schoolYearId_startDate_endDate_idx" ON "school_periods"("schoolYearId", "startDate", "endDate");
ALTER TABLE "school_periods"
  ADD CONSTRAINT "school_periods_schoolYearId_fkey"
  FOREIGN KEY ("schoolYearId") REFERENCES "school_years"("id")
  ON DELETE RESTRICT ON UPDATE CASCADE;

-- La contrainte d’exclusion protège aussi contre deux requêtes concurrentes.
CREATE EXTENSION IF NOT EXISTS btree_gist;
ALTER TABLE "school_periods"
  ADD CONSTRAINT "school_periods_no_overlap"
  EXCLUDE USING gist (
    "schoolYearId" WITH =,
    daterange("startDate", "endDate", '[]') WITH &&
  );

CREATE TABLE "non_working_days" (
  "id" UUID NOT NULL,
  "schoolYearId" UUID NOT NULL,
  "siteId" UUID,
  "day" DATE NOT NULL,
  "reason" VARCHAR(160) NOT NULL,
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updatedAt" TIMESTAMPTZ(6) NOT NULL,
  "archivedAt" TIMESTAMPTZ(6),
  CONSTRAINT "non_working_days_pkey" PRIMARY KEY ("id")
);
CREATE INDEX "non_working_days_schoolYearId_siteId_day_idx" ON "non_working_days"("schoolYearId", "siteId", "day");
CREATE UNIQUE INDEX "non_working_days_year_site_day_key"
  ON "non_working_days"(
    "schoolYearId",
    COALESCE("siteId", '00000000-0000-0000-0000-000000000000'::uuid),
    "day"
  );
ALTER TABLE "non_working_days"
  ADD CONSTRAINT "non_working_days_schoolYearId_fkey"
  FOREIGN KEY ("schoolYearId") REFERENCES "school_years"("id")
  ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "non_working_days"
  ADD CONSTRAINT "non_working_days_siteId_fkey"
  FOREIGN KEY ("siteId") REFERENCES "sites"("id")
  ON DELETE RESTRICT ON UPDATE CASCADE;

CREATE TABLE "referential_revisions" (
  "id" UUID NOT NULL,
  "organizationId" UUID NOT NULL,
  "entityType" VARCHAR(32) NOT NULL,
  "entityId" UUID NOT NULL,
  "action" VARCHAR(64) NOT NULL,
  "reason" VARCHAR(500),
  "actorUserId" UUID,
  "requestId" UUID NOT NULL,
  "before" JSONB,
  "after" JSONB,
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT "referential_revisions_pkey" PRIMARY KEY ("id")
);
CREATE INDEX "referential_revisions_organizationId_entityType_entityId_createdAt_idx"
  ON "referential_revisions"("organizationId", "entityType", "entityId", "createdAt");
