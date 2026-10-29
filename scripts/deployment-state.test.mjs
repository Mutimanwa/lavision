import assert from "node:assert/strict";
import { mkdtemp, mkdir, writeFile } from "node:fs/promises";
import { tmpdir } from "node:os";
import { join } from "node:path";
import test from "node:test";
import {
  emptyDeploymentState,
  makeDeployment,
  migrationHash,
  recordDeployment,
  recordRollback,
  rollbackTarget,
} from "./deployment-state.mjs";

const hashA = "a".repeat(64);
const hashB = "b".repeat(64);

test("le rollback choisit l’image précédente sans réécrire l’historique", () => {
  const first = makeDeployment("sha-a", hashA, new Date("2026-01-01T00:00:00Z"));
  const second = makeDeployment("sha-b", hashA, new Date("2026-01-02T00:00:00Z"));
  const deployed = recordDeployment(recordDeployment(emptyDeploymentState(), first), second);
  assert.equal(rollbackTarget(deployed).imageTag, "sha-a");
  const rolledBack = recordRollback(deployed, new Date("2026-01-03T00:00:00Z"));
  assert.equal(rolledBack.current.imageTag, "sha-a");
  assert.equal(rolledBack.previous.length, 0);
  assert.equal(rolledBack.lastRollback.from, "sha-b");
});

test("le rollback refuse une différence de schéma et les tags mutables", () => {
  const first = makeDeployment("sha-a", hashA);
  const second = makeDeployment("sha-b", hashB);
  const deployed = recordDeployment(recordDeployment(emptyDeploymentState(), first), second);
  assert.throws(() => rollbackTarget(deployed), /ROLLBACK_BASE_REQUIRED/);
  assert.throws(() => makeDeployment("latest", hashA), /immutable/);
  assert.throws(() => recordDeployment(deployed, makeDeployment("sha-b", hashB)), /déjà l’état courant/);
});

test("l’empreinte des migrations est stable et détecte un changement de contenu", async () => {
  const directory = await mkdtemp(join(tmpdir(), "lavision-migrations-"));
  await mkdir(join(directory, "002_second"), { recursive: true });
  await mkdir(join(directory, "001_first"), { recursive: true });
  await writeFile(join(directory, "002_second", "migration.sql"), "SELECT 2;\n");
  await writeFile(join(directory, "001_first", "migration.sql"), "SELECT 1;\n");
  const initial = await migrationHash(directory);
  assert.equal(initial, await migrationHash(directory));
  await writeFile(join(directory, "002_second", "migration.sql"), "SELECT 3;\n");
  assert.notEqual(initial, await migrationHash(directory));
});
