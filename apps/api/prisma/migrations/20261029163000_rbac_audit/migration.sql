-- RBAC et audit #7 : backfill des attributions #6, sans perte de données.
CREATE TYPE "AccessResourceType" AS ENUM (
  'organization', 'site', 'class', 'subject', 'student', 'employee',
  'note', 'payment', 'payroll', 'proclamation', 'permission', 'other'
);
CREATE TYPE "AccessRelationType" AS ENUM (
  'teacher_class', 'teacher_subject', 'principal_class',
  'guardian_student', 'student_self', 'employee_self'
);
CREATE TYPE "AuditOutcome" AS ENUM ('SUCCESS', 'DENIED', 'FAILURE');

CREATE TABLE "access_resources" (
  "id" UUID NOT NULL,
  "organizationId" UUID NOT NULL,
  "siteId" UUID,
  "parentId" UUID,
  "type" "AccessResourceType" NOT NULL,
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "archivedAt" TIMESTAMPTZ(6),
  CONSTRAINT "access_resources_pkey" PRIMARY KEY ("id")
);

CREATE INDEX "access_resources_organizationId_type_idx" ON "access_resources"("organizationId", "type");
CREATE INDEX "access_resources_siteId_type_idx" ON "access_resources"("siteId", "type");
CREATE INDEX "access_resources_parentId_idx" ON "access_resources"("parentId");

ALTER TABLE "access_resources"
  ADD CONSTRAINT "access_resources_organizationId_fkey"
  FOREIGN KEY ("organizationId") REFERENCES "organizations"("id")
  ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "access_resources"
  ADD CONSTRAINT "access_resources_siteId_fkey"
  FOREIGN KEY ("siteId") REFERENCES "sites"("id")
  ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "access_resources"
  ADD CONSTRAINT "access_resources_parentId_fkey"
  FOREIGN KEY ("parentId") REFERENCES "access_resources"("id")
  ON DELETE SET NULL ON UPDATE CASCADE;

-- Toute organisation/site existant devient une ressource de portée vérifiable.
INSERT INTO "access_resources" ("id", "organizationId", "type", "createdAt")
SELECT "id", "id", 'organization', "createdAt" FROM "organizations";
INSERT INTO "access_resources" ("id", "organizationId", "siteId", "type", "createdAt")
SELECT "id", "organizationId", "id", 'site', "createdAt" FROM "sites";

ALTER TABLE "role_assignments" ADD COLUMN "scopeId" UUID;
UPDATE "role_assignments" AS assignment
SET "scopeId" = membership."organizationId"
FROM "organization_memberships" AS membership
WHERE assignment."membershipId" = membership."id";
ALTER TABLE "role_assignments" ALTER COLUMN "scopeId" SET NOT NULL;
DROP INDEX "role_assignments_membershipId_role_key";
ALTER TABLE "role_assignments"
  ADD CONSTRAINT "role_assignments_scopeId_fkey"
  FOREIGN KEY ("scopeId") REFERENCES "access_resources"("id")
  ON DELETE RESTRICT ON UPDATE CASCADE;
CREATE UNIQUE INDEX "role_assignments_membershipId_role_scopeId_key"
  ON "role_assignments"("membershipId", "role", "scopeId");
CREATE INDEX "role_assignments_scopeId_idx" ON "role_assignments"("scopeId");

CREATE TABLE "permission_grants" (
  "id" UUID NOT NULL,
  "membershipId" UUID NOT NULL,
  "permission" VARCHAR(96) NOT NULL,
  "scopeId" UUID NOT NULL,
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "archivedAt" TIMESTAMPTZ(6),
  CONSTRAINT "permission_grants_pkey" PRIMARY KEY ("id")
);
CREATE UNIQUE INDEX "permission_grants_membershipId_permission_scopeId_key"
  ON "permission_grants"("membershipId", "permission", "scopeId");
CREATE INDEX "permission_grants_permission_scopeId_idx"
  ON "permission_grants"("permission", "scopeId");
ALTER TABLE "permission_grants"
  ADD CONSTRAINT "permission_grants_membershipId_fkey"
  FOREIGN KEY ("membershipId") REFERENCES "organization_memberships"("id")
  ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "permission_grants"
  ADD CONSTRAINT "permission_grants_scopeId_fkey"
  FOREIGN KEY ("scopeId") REFERENCES "access_resources"("id")
  ON DELETE RESTRICT ON UPDATE CASCADE;

CREATE TABLE "access_relations" (
  "id" UUID NOT NULL,
  "membershipId" UUID NOT NULL,
  "relation" "AccessRelationType" NOT NULL,
  "resourceId" UUID NOT NULL,
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "archivedAt" TIMESTAMPTZ(6),
  CONSTRAINT "access_relations_pkey" PRIMARY KEY ("id")
);
CREATE UNIQUE INDEX "access_relations_membershipId_relation_resourceId_key"
  ON "access_relations"("membershipId", "relation", "resourceId");
CREATE INDEX "access_relations_resourceId_relation_idx"
  ON "access_relations"("resourceId", "relation");
ALTER TABLE "access_relations"
  ADD CONSTRAINT "access_relations_membershipId_fkey"
  FOREIGN KEY ("membershipId") REFERENCES "organization_memberships"("id")
  ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "access_relations"
  ADD CONSTRAINT "access_relations_resourceId_fkey"
  FOREIGN KEY ("resourceId") REFERENCES "access_resources"("id")
  ON DELETE RESTRICT ON UPDATE CASCADE;

CREATE TABLE "audit_events" (
  "id" UUID NOT NULL,
  "organizationId" UUID NOT NULL,
  "actorUserId" UUID,
  "action" VARCHAR(96) NOT NULL,
  "resourceType" VARCHAR(64) NOT NULL,
  "resourceId" UUID,
  "outcome" "AuditOutcome" NOT NULL,
  "requestId" UUID NOT NULL,
  "reason" VARCHAR(96),
  "context" JSONB,
  "createdAt" TIMESTAMPTZ(6) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT "audit_events_pkey" PRIMARY KEY ("id")
);
CREATE INDEX "audit_events_organizationId_createdAt_idx" ON "audit_events"("organizationId", "createdAt");
CREATE INDEX "audit_events_actorUserId_createdAt_idx" ON "audit_events"("actorUserId", "createdAt");
CREATE INDEX "audit_events_action_createdAt_idx" ON "audit_events"("action", "createdAt");
CREATE INDEX "audit_events_resourceType_resourceId_createdAt_idx" ON "audit_events"("resourceType", "resourceId", "createdAt");

-- Les nouvelles organisations/sites sont enregistrés dans la même transaction.
CREATE FUNCTION "lavision_register_organization_scope"() RETURNS trigger
LANGUAGE plpgsql AS $$
BEGIN
  INSERT INTO "access_resources" ("id", "organizationId", "type", "createdAt")
  VALUES (NEW."id", NEW."id", 'organization', NEW."createdAt")
  ON CONFLICT ("id") DO NOTHING;
  RETURN NEW;
END;
$$;
CREATE TRIGGER "organizations_access_resource"
AFTER INSERT ON "organizations"
FOR EACH ROW EXECUTE FUNCTION "lavision_register_organization_scope"();

CREATE FUNCTION "lavision_register_site_scope"() RETURNS trigger
LANGUAGE plpgsql AS $$
BEGIN
  INSERT INTO "access_resources" ("id", "organizationId", "siteId", "type", "createdAt")
  VALUES (NEW."id", NEW."organizationId", NEW."id", 'site', NEW."createdAt")
  ON CONFLICT ("id") DO NOTHING;
  RETURN NEW;
END;
$$;
CREATE TRIGGER "sites_access_resource"
AFTER INSERT ON "sites"
FOR EACH ROW EXECUTE FUNCTION "lavision_register_site_scope"();

-- L’application ne peut ni modifier ni supprimer une ligne d’audit.
-- La purge éventuelle relève d’une migration/maintenance après validation de la rétention.
CREATE FUNCTION "lavision_audit_append_only"() RETURNS trigger
LANGUAGE plpgsql AS $$
BEGIN
  RAISE EXCEPTION 'audit_events_append_only';
END;
$$;
CREATE TRIGGER "audit_events_no_update"
BEFORE UPDATE ON "audit_events"
FOR EACH ROW EXECUTE FUNCTION "lavision_audit_append_only"();
CREATE TRIGGER "audit_events_no_delete"
BEFORE DELETE ON "audit_events"
FOR EACH ROW EXECUTE FUNCTION "lavision_audit_append_only"();
