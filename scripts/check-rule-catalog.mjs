import { readFile } from "node:fs/promises";

const documentation = await readFile(new URL("../docs/cadrage/02_CATALOGUE_DES_REGLES.md", import.meta.url), "utf8");
const source = await readFile(new URL("../packages/contracts/src/rules.ts", import.meta.url), "utf8");

// Ces clés sont des invariants ou des référentiels identifiés, pas des RuleValue.
// Leur raccordement fonctionnel peut rester ouvert ; les sortir de cette liste exige
// une décision d’architecture, pas une valeur cachée.
const nonRuleKeys = new Set([
  "rules.scope.organization",
  "rules.catalog.coverage",
  "rules.unknown_policy",
  "rules.lifecycle",
  "rules.publication.immutable",
  "rules.publication.simulation_required",
  "rules.documents.snapshot",
  "rules.security.domain_permissions",
  "rules.security.no_arbitrary_code",
  "rules.audit",
  "calendar.year.period_structure",
  "calendar.year.closure_policy",
  "calendar.non_working_days",
  "academic.section.catalog",
  "academic.option.catalog",
  "academic.program.course_catalog",
  "finance.payment.cancellation_policy",
  "document.template.versioning",
  "document.rectification_policy",
]);

const documentedKeys = new Set(
  [...documentation.matchAll(/`([a-z][a-z0-9]+(?:\.[a-z0-9_]+)+)`/g)].map((match) => match[1]),
);
const implementedKeys = new Set(
  [...source.matchAll(/key: "([a-z][a-z0-9]+(?:\.[a-z0-9_]+)+)"/g)].map((match) => match[1]),
);

const missing = [...documentedKeys].filter((key) => !nonRuleKeys.has(key) && !implementedKeys.has(key));
const undocumented = [...implementedKeys].filter((key) => !documentedKeys.has(key));
const reclassified = [...nonRuleKeys].filter((key) => implementedKeys.has(key));
const duplicateKeys = [...source.matchAll(/key: "([a-z][a-z0-9]+(?:\.[a-z0-9_]+)+)"/g)]
  .map((match) => match[1])
  .filter((key, index, keys) => keys.indexOf(key) !== index);

if (missing.length || undocumented.length || reclassified.length || duplicateKeys.length || /defaultValue\s*:/.test(source)) {
  if (missing.length) console.error(`Règles documentées absentes du catalogue API : ${missing.join(", ")}`);
  if (undocumented.length) console.error(`Règles API absentes du catalogue documenté : ${undocumented.join(", ")}`);
  if (reclassified.length) console.error(`Clés à reclasser explicitement : ${reclassified.join(", ")}`);
  if (duplicateKeys.length) console.error(`Clés dupliquées : ${[...new Set(duplicateKeys)].join(", ")}`);
  if (/defaultValue\s*:/.test(source)) console.error("Une valeur par défaut a été détectée dans le catalogue.");
  process.exitCode = 1;
} else {
  console.log(`Catalogue de règles cohérent : ${implementedKeys.size} paramètres API, ${nonRuleKeys.size} invariants/référentiels séparés, aucune valeur par défaut.`);
}
