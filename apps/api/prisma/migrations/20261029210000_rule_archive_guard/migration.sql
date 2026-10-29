-- Complément #39 : l’archivage reste une transition explicite, pas une réécriture SQL libre.
CREATE FUNCTION "lavision_rule_archive_guard"() RETURNS trigger
LANGUAGE plpgsql AS $$
BEGIN
  IF OLD."status" IN ('PUBLISHED', 'SCHEDULED') THEN
    IF NEW."status" = 'ARCHIVED' THEN
      IF OLD."archivedAt" IS NOT NULL OR NEW."archivedAt" IS NULL THEN
        RAISE EXCEPTION 'rule_archive_requires_transition';
      END IF;
    ELSIF NEW."archivedAt" IS DISTINCT FROM OLD."archivedAt" THEN
      RAISE EXCEPTION 'rule_published_archive_immutable';
    END IF;
  END IF;
  RETURN NEW;
END;
$$;

CREATE TRIGGER "rule_set_versions_archive_guard"
BEFORE UPDATE ON "rule_set_versions"
FOR EACH ROW EXECUTE FUNCTION "lavision_rule_archive_guard"();
