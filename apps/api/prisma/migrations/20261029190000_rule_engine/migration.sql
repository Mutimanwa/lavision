-- Moteur de règles #39 : portées explicites, versions et snapshots immuables.
ALTER TYPE "AccessResourceType" ADD VALUE 'section';
ALTER TYPE "AccessResourceType" ADD VALUE 'option';
ALTER TYPE "AccessResourceType" ADD VALUE 'program';

CREATE TYPE "RuleVersionStatus" AS ENUM ('DRAFT', 'IN_REVIEW', 'VALIDATED', 'APPROVED', 'PUBLISHED', 'SCHEDULED', 'ARCHIVED');
CREATE TYPE "RuleSimulationStatus" AS ENUM ('PASSED', 'FAILED');

CREATE TABLE "rule_sets" (
  "id" UUID NOT NULL,
  "organizationId" UUID NOT NULL,
  "domain" VARCHAR(32) NOT NULL,
  "code" VARCHAR(64) NOT NULL,
  "name" VARCHAR(160) NOT NULL,
  "purpose" VARCHAR(500) NOT NULL,
  "scopeOrder" JSONB NOT NULL,
  "definitionKeys" JSONB NOT NULL,
  "nextVersion" INTEGER NOT NULL DEFAULT 1,
  "createdById" UUID NOT NULL,
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updatedAt" TIMESTAMPTZ(6) NOT NULL,
  "archivedAt" TIMESTAMPTZ(6),
  CONSTRAINT "rule_sets_pkey" PRIMARY KEY ("id"),
  CONSTRAINT "rule_sets_domain_check" CHECK ("domain" IN ('academic', 'attendance', 'finance', 'hr', 'payroll', 'documents', 'calendar')),
  CONSTRAINT "rule_sets_next_version_check" CHECK ("nextVersion" > 0),
  CONSTRAINT "rule_sets_scope_order_check" CHECK (jsonb_typeof("scopeOrder") = 'array'),
  CONSTRAINT "rule_sets_definition_keys_check" CHECK (jsonb_typeof("definitionKeys") = 'array')
);
CREATE UNIQUE INDEX "rule_sets_organizationId_domain_code_key" ON "rule_sets"("organizationId", "domain", "code");
CREATE INDEX "rule_sets_organizationId_domain_archivedAt_idx" ON "rule_sets"("organizationId", "domain", "archivedAt");
ALTER TABLE "rule_sets" ADD CONSTRAINT "rule_sets_organizationId_fkey" FOREIGN KEY ("organizationId") REFERENCES "organizations"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

CREATE TABLE "rule_set_versions" (
  "id" UUID NOT NULL,
  "ruleSetId" UUID NOT NULL,
  "version" INTEGER NOT NULL,
  "status" "RuleVersionStatus" NOT NULL DEFAULT 'DRAFT',
  "effectiveFrom" DATE NOT NULL,
  "effectiveTo" DATE,
  "effectiveTimezone" VARCHAR(64) NOT NULL,
  "source" VARCHAR(500) NOT NULL,
  "reason" VARCHAR(500) NOT NULL,
  "contentHash" CHAR(64),
  "createdById" UUID NOT NULL,
  "reviewedById" UUID,
  "approvedById" UUID,
  "publishedById" UUID,
  "publicationSimulationId" UUID,
  "submittedAt" TIMESTAMPTZ(6),
  "reviewedAt" TIMESTAMPTZ(6),
  "approvedAt" TIMESTAMPTZ(6),
  "publishedAt" TIMESTAMPTZ(6),
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "updatedAt" TIMESTAMPTZ(6) NOT NULL,
  "archivedAt" TIMESTAMPTZ(6),
  CONSTRAINT "rule_set_versions_pkey" PRIMARY KEY ("id"),
  CONSTRAINT "rule_set_versions_version_check" CHECK ("version" > 0),
  CONSTRAINT "rule_set_versions_dates_check" CHECK ("effectiveTo" IS NULL OR "effectiveFrom" < "effectiveTo")
);
CREATE UNIQUE INDEX "rule_set_versions_ruleSetId_version_key" ON "rule_set_versions"("ruleSetId", "version");
CREATE INDEX "rule_set_versions_ruleSetId_status_effective_idx" ON "rule_set_versions"("ruleSetId", "status", "effectiveFrom", "effectiveTo");
ALTER TABLE "rule_set_versions" ADD CONSTRAINT "rule_set_versions_ruleSetId_fkey" FOREIGN KEY ("ruleSetId") REFERENCES "rule_sets"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

CREATE TABLE "rule_values" (
  "id" UUID NOT NULL,
  "ruleSetVersionId" UUID NOT NULL,
  "definitionKey" VARCHAR(160) NOT NULL,
  "value" JSONB,
  "notApplicable" BOOLEAN NOT NULL DEFAULT FALSE,
  "reason" VARCHAR(500),
  CONSTRAINT "rule_values_pkey" PRIMARY KEY ("id"),
  CONSTRAINT "rule_values_value_check" CHECK (
    ("notApplicable" = TRUE AND "value" IS NULL AND "reason" IS NOT NULL)
    OR ("notApplicable" = FALSE AND "value" IS NOT NULL)
  )
);
CREATE UNIQUE INDEX "rule_values_ruleSetVersionId_definitionKey_key" ON "rule_values"("ruleSetVersionId", "definitionKey");
CREATE INDEX "rule_values_definitionKey_idx" ON "rule_values"("definitionKey");
ALTER TABLE "rule_values" ADD CONSTRAINT "rule_values_ruleSetVersionId_fkey" FOREIGN KEY ("ruleSetVersionId") REFERENCES "rule_set_versions"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

CREATE TABLE "rule_bindings" (
  "id" UUID NOT NULL,
  "ruleSetVersionId" UUID NOT NULL,
  "scopeType" VARCHAR(32) NOT NULL,
  "scopeId" UUID NOT NULL,
  CONSTRAINT "rule_bindings_pkey" PRIMARY KEY ("id"),
  CONSTRAINT "rule_bindings_scope_type_check" CHECK ("scopeType" IN ('organization', 'site', 'school_year', 'level', 'section', 'option', 'program', 'class', 'subject'))
);
CREATE UNIQUE INDEX "rule_bindings_ruleSetVersionId_scope_key" ON "rule_bindings"("ruleSetVersionId", "scopeType", "scopeId");
CREATE INDEX "rule_bindings_scopeType_scopeId_idx" ON "rule_bindings"("scopeType", "scopeId");
ALTER TABLE "rule_bindings" ADD CONSTRAINT "rule_bindings_ruleSetVersionId_fkey" FOREIGN KEY ("ruleSetVersionId") REFERENCES "rule_set_versions"("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "rule_bindings" ADD CONSTRAINT "rule_bindings_scopeId_fkey" FOREIGN KEY ("scopeId") REFERENCES "access_resources"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

CREATE TABLE "rule_simulations" (
  "id" UUID NOT NULL,
  "organizationId" UUID NOT NULL,
  "ruleSetVersionId" UUID NOT NULL,
  "status" "RuleSimulationStatus" NOT NULL,
  "input" JSONB NOT NULL,
  "report" JSONB NOT NULL,
  "inputHash" CHAR(64) NOT NULL,
  "contentHash" CHAR(64) NOT NULL,
  "createdById" UUID NOT NULL,
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT "rule_simulations_pkey" PRIMARY KEY ("id")
);
CREATE INDEX "rule_simulations_ruleSetVersionId_createdAt_idx" ON "rule_simulations"("ruleSetVersionId", "createdAt");
CREATE INDEX "rule_simulations_organizationId_createdAt_idx" ON "rule_simulations"("organizationId", "createdAt");
ALTER TABLE "rule_simulations" ADD CONSTRAINT "rule_simulations_ruleSetVersionId_fkey" FOREIGN KEY ("ruleSetVersionId") REFERENCES "rule_set_versions"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

CREATE TABLE "document_rule_snapshots" (
  "id" UUID NOT NULL,
  "organizationId" UUID NOT NULL,
  "documentType" VARCHAR(32) NOT NULL,
  "documentId" UUID NOT NULL,
  "ruleSetVersionId" UUID NOT NULL,
  "resolved" JSONB NOT NULL,
  "contentHash" CHAR(64) NOT NULL,
  "createdById" UUID NOT NULL,
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT "document_rule_snapshots_pkey" PRIMARY KEY ("id")
);
CREATE UNIQUE INDEX "document_rule_snapshots_document_version_key" ON "document_rule_snapshots"("documentType", "documentId", "ruleSetVersionId");
CREATE INDEX "document_rule_snapshots_organization_document_idx" ON "document_rule_snapshots"("organizationId", "documentType", "documentId");
ALTER TABLE "document_rule_snapshots" ADD CONSTRAINT "document_rule_snapshots_ruleSetVersionId_fkey" FOREIGN KEY ("ruleSetVersionId") REFERENCES "rule_set_versions"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

CREATE TABLE "rule_revisions" (
  "id" UUID NOT NULL,
  "organizationId" UUID NOT NULL,
  "ruleSetVersionId" UUID NOT NULL,
  "action" VARCHAR(64) NOT NULL,
  "reason" VARCHAR(500),
  "actorUserId" UUID NOT NULL,
  "requestId" UUID NOT NULL,
  "before" JSONB,
  "after" JSONB,
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT "rule_revisions_pkey" PRIMARY KEY ("id")
);
CREATE INDEX "rule_revisions_organization_version_created_idx" ON "rule_revisions"("organizationId", "ruleSetVersionId", "createdAt");
ALTER TABLE "rule_revisions" ADD CONSTRAINT "rule_revisions_ruleSetVersionId_fkey" FOREIGN KEY ("ruleSetVersionId") REFERENCES "rule_set_versions"("id") ON DELETE RESTRICT ON UPDATE CASCADE;

-- Une version publiée/planifiée ne peut changer que de statut vers ARCHIVED.
CREATE FUNCTION "lavision_rule_version_immutable"() RETURNS trigger
LANGUAGE plpgsql AS $$
BEGIN
  IF TG_OP = 'DELETE' THEN
    IF OLD."status" <> 'DRAFT' THEN
      RAISE EXCEPTION 'rule_version_immutable';
    END IF;
    RETURN OLD;
  END IF;
  IF OLD."status" = 'ARCHIVED' THEN
    RAISE EXCEPTION 'rule_version_immutable';
  END IF;
  IF OLD."status" IN ('PUBLISHED', 'SCHEDULED') THEN
    IF NEW."status" IS DISTINCT FROM OLD."status" AND NEW."status" <> 'ARCHIVED' THEN
      RAISE EXCEPTION 'rule_version_immutable';
    END IF;
    IF NEW."ruleSetId" IS DISTINCT FROM OLD."ruleSetId"
      OR NEW."version" IS DISTINCT FROM OLD."version"
      OR NEW."effectiveFrom" IS DISTINCT FROM OLD."effectiveFrom"
      OR NEW."effectiveTo" IS DISTINCT FROM OLD."effectiveTo"
      OR NEW."effectiveTimezone" IS DISTINCT FROM OLD."effectiveTimezone"
      OR NEW."source" IS DISTINCT FROM OLD."source"
      OR NEW."reason" IS DISTINCT FROM OLD."reason"
      OR NEW."contentHash" IS DISTINCT FROM OLD."contentHash"
      OR NEW."createdById" IS DISTINCT FROM OLD."createdById"
      OR NEW."reviewedById" IS DISTINCT FROM OLD."reviewedById"
      OR NEW."approvedById" IS DISTINCT FROM OLD."approvedById"
      OR NEW."publishedById" IS DISTINCT FROM OLD."publishedById"
      OR NEW."publicationSimulationId" IS DISTINCT FROM OLD."publicationSimulationId"
      OR NEW."submittedAt" IS DISTINCT FROM OLD."submittedAt"
      OR NEW."reviewedAt" IS DISTINCT FROM OLD."reviewedAt"
      OR NEW."approvedAt" IS DISTINCT FROM OLD."approvedAt"
      OR NEW."publishedAt" IS DISTINCT FROM OLD."publishedAt"
      OR NEW."createdAt" IS DISTINCT FROM OLD."createdAt" THEN
      RAISE EXCEPTION 'rule_version_immutable';
    END IF;
  END IF;
  RETURN NEW;
END;
$$;
CREATE TRIGGER "rule_set_versions_immutable"
BEFORE UPDATE OR DELETE ON "rule_set_versions"
FOR EACH ROW EXECUTE FUNCTION "lavision_rule_version_immutable"();

CREATE FUNCTION "lavision_rule_children_mutable"() RETURNS trigger
LANGUAGE plpgsql AS $$
DECLARE
  parent_status "RuleVersionStatus";
BEGIN
  IF TG_OP = 'DELETE' THEN
    SELECT "status" INTO parent_status FROM "rule_set_versions" WHERE "id" = OLD."ruleSetVersionId";
  ELSE
    SELECT "status" INTO parent_status FROM "rule_set_versions" WHERE "id" = NEW."ruleSetVersionId";
  END IF;
  IF parent_status IS DISTINCT FROM 'DRAFT' THEN
    RAISE EXCEPTION 'rule_children_immutable_unless_draft';
  END IF;
  IF TG_OP = 'UPDATE' AND NEW."ruleSetVersionId" IS DISTINCT FROM OLD."ruleSetVersionId" THEN
    RAISE EXCEPTION 'rule_child_parent_immutable';
  END IF;
  IF TG_OP = 'DELETE' THEN RETURN OLD; END IF;
  RETURN NEW;
END;
$$;
CREATE TRIGGER "rule_values_draft_only"
BEFORE INSERT OR UPDATE OR DELETE ON "rule_values"
FOR EACH ROW EXECUTE FUNCTION "lavision_rule_children_mutable"();
CREATE TRIGGER "rule_bindings_draft_only"
BEFORE INSERT OR UPDATE OR DELETE ON "rule_bindings"
FOR EACH ROW EXECUTE FUNCTION "lavision_rule_children_mutable"();

CREATE FUNCTION "lavision_append_only"() RETURNS trigger
LANGUAGE plpgsql AS $$
BEGIN
  RAISE EXCEPTION 'append_only_resource';
END;
$$;
CREATE TRIGGER "rule_simulations_append_only"
BEFORE UPDATE OR DELETE ON "rule_simulations"
FOR EACH ROW EXECUTE FUNCTION "lavision_append_only"();
CREATE TRIGGER "document_rule_snapshots_append_only"
BEFORE UPDATE OR DELETE ON "document_rule_snapshots"
FOR EACH ROW EXECUTE FUNCTION "lavision_append_only"();
CREATE TRIGGER "rule_revisions_append_only"
BEFORE UPDATE OR DELETE ON "rule_revisions"
FOR EACH ROW EXECUTE FUNCTION "lavision_append_only"();

-- Le verrou et la vérification SQL empêchent deux publications concurrentes incompatibles.
CREATE FUNCTION "lavision_rule_publication_guard"() RETURNS trigger
LANGUAGE plpgsql AS $$
BEGIN
  IF NEW."status" IN ('PUBLISHED', 'SCHEDULED') AND OLD."status" NOT IN ('PUBLISHED', 'SCHEDULED') THEN
    PERFORM pg_advisory_xact_lock(hashtext(NEW."ruleSetId"::text)::bigint);
    IF NEW."contentHash" IS NULL OR NEW."publishedById" IS NULL OR NEW."publishedAt" IS NULL OR NEW."publicationSimulationId" IS NULL THEN
      RAISE EXCEPTION 'rule_publication_requires_simulation';
    END IF;
    IF NOT EXISTS (
      SELECT 1 FROM "rule_simulations" simulation
      WHERE simulation."id" = NEW."publicationSimulationId"
        AND simulation."ruleSetVersionId" = NEW."id"
        AND simulation."status" = 'PASSED'
        AND simulation."contentHash" = NEW."contentHash"
    ) THEN
      RAISE EXCEPTION 'rule_publication_requires_passed_simulation';
    END IF;
    IF EXISTS (
      SELECT 1
      FROM "rule_set_versions" other
      JOIN "rule_bindings" other_binding ON other_binding."ruleSetVersionId" = other."id"
      JOIN "rule_bindings" new_binding ON new_binding."ruleSetVersionId" = NEW."id"
        AND new_binding."scopeType" = other_binding."scopeType"
        AND new_binding."scopeId" = other_binding."scopeId"
      JOIN "rule_values" other_value ON other_value."ruleSetVersionId" = other."id"
      JOIN "rule_values" new_value ON new_value."ruleSetVersionId" = NEW."id"
        AND new_value."definitionKey" = other_value."definitionKey"
      WHERE other."ruleSetId" = NEW."ruleSetId"
        AND other."id" <> NEW."id"
        AND other."status" IN ('PUBLISHED', 'SCHEDULED')
        AND other."archivedAt" IS NULL
        AND other."effectiveFrom" = NEW."effectiveFrom"
        AND daterange(other."effectiveFrom", other."effectiveTo", '[)') && daterange(NEW."effectiveFrom", NEW."effectiveTo", '[)')
      LIMIT 1
    ) THEN
      RAISE EXCEPTION 'rule_version_overlapping_scope';
    END IF;
  END IF;
  RETURN NEW;
END;
$$;
CREATE TRIGGER "rule_set_versions_publication_guard"
BEFORE UPDATE OF "status" ON "rule_set_versions"
FOR EACH ROW EXECUTE FUNCTION "lavision_rule_publication_guard"();
