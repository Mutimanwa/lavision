import { createHash } from "node:crypto";
import { mkdir, readFile, readdir, rename, writeFile } from "node:fs/promises";
import { dirname, join } from "node:path";

export const DEPLOYMENT_STATE_VERSION = 1;

export function emptyDeploymentState() {
  return { version: DEPLOYMENT_STATE_VERSION, environment: "staging", current: null, previous: [] };
}

export function validateImageTag(value) {
  const tag = String(value ?? "").trim();
  if (!/^[A-Za-z0-9][A-Za-z0-9_.-]{0,127}$/.test(tag) || ["latest", "local"].includes(tag.toLowerCase())) {
    throw new Error("IMAGE_TAG doit être un identifiant immutable (par exemple le SHA du commit), sans latest ni local.");
  }
  return tag;
}

export async function migrationHash(migrationsDirectory) {
  const entries = await readdir(migrationsDirectory, { withFileTypes: true });
  const hash = createHash("sha256");
  for (const entry of entries.filter((item) => item.isDirectory()).map((item) => item.name).sort()) {
    const content = await readFile(join(migrationsDirectory, entry, "migration.sql"), "utf8");
    hash.update(entry);
    hash.update("\0");
    hash.update(content);
    hash.update("\0");
  }
  return hash.digest("hex");
}

function assertDeployment(deployment) {
  if (!deployment || typeof deployment !== "object") throw new Error("État de déploiement invalide.");
  validateImageTag(deployment.imageTag);
  if (!/^[a-f0-9]{64}$/.test(deployment.migrationHash ?? "")) throw new Error("Empreinte de migration invalide.");
  if (typeof deployment.deployedAt !== "string" || Number.isNaN(Date.parse(deployment.deployedAt))) throw new Error("Date de déploiement invalide.");
}

export function makeDeployment(imageTag, hash, now = new Date()) {
  return { imageTag: validateImageTag(imageTag), migrationHash: hash, deployedAt: now.toISOString() };
}

export function recordDeployment(state, deployment) {
  assertDeployment(deployment);
  if (state.current?.imageTag === deployment.imageTag) throw new Error("Cette image est déjà l’état courant ; utilisez verify ou une nouvelle image.");
  const previous = state.current ? [...state.previous, state.current].slice(-10) : state.previous;
  return { ...state, current: deployment, previous };
}

export function rollbackTarget(state) {
  if (!state.current || state.previous.length === 0) throw new Error("Aucune image précédente connue pour le rollback.");
  const target = state.previous.at(-1);
  assertDeployment(target);
  if (target.migrationHash !== state.current.migrationHash) {
    throw new Error("ROLLBACK_BASE_REQUIRED : les empreintes de migrations diffèrent ; une restauration contrôlée est requise.");
  }
  return target;
}

export function recordRollback(state, now = new Date()) {
  const target = rollbackTarget(state);
  return {
    ...state,
    current: target,
    previous: state.previous.slice(0, -1),
    lastRollback: { from: state.current.imageTag, to: target.imageTag, at: now.toISOString() },
  };
}

export async function readDeploymentState(file) {
  try {
    const state = JSON.parse(await readFile(file, "utf8"));
    if (state.version !== DEPLOYMENT_STATE_VERSION || state.environment !== "staging" || !Array.isArray(state.previous)) throw new Error("État de déploiement non reconnu.");
    if (state.current) assertDeployment(state.current);
    state.previous.forEach(assertDeployment);
    return state;
  } catch (error) {
    if (error?.code === "ENOENT") return emptyDeploymentState();
    throw error;
  }
}

export async function writeDeploymentState(file, state) {
  await mkdir(dirname(file), { recursive: true, mode: 0o700 });
  const temporary = `${file}.${process.pid}.tmp`;
  await writeFile(temporary, `${JSON.stringify(state, null, 2)}\n`, { mode: 0o600 });
  await rename(temporary, file);
}
