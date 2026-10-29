import { spawn } from "node:child_process";
import { fileURLToPath } from "node:url";
import { join } from "node:path";
import {
  makeDeployment,
  migrationHash,
  readDeploymentState,
  recordDeployment,
  recordRollback,
  rollbackTarget,
  validateImageTag,
  writeDeploymentState,
} from "./deployment-state.mjs";

const root = fileURLToPath(new URL("../", import.meta.url));
const composeFile = join(root, "deploy/compose.staging.yml");
const migrationsDirectory = join(root, "apps/api/prisma/migrations");
const stateFile = process.env.LAVISION_DEPLOY_STATE_FILE ?? join(root, ".lavision/staging-state.json");
const compose = ["compose", "-f", composeFile];

function run(command, args, environment = process.env) {
  return new Promise((resolve, reject) => {
    const child = spawn(command, args, { cwd: root, env: environment, stdio: "inherit" });
    child.once("error", reject);
    child.once("exit", (code, signal) => {
      if (code === 0) resolve();
      else reject(new Error(`Commande d’exploitation interrompue (${signal ?? code}).`));
    });
  });
}

function docker(args, environment = process.env) {
  return run("docker", args, environment);
}

function requireSecretEnvironment({ image = true } = {}) {
  const names = [
    "DATABASE_URL",
    "POSTGRES_PASSWORD",
    "AUTH_ENCRYPTION_KEY",
    "AUTH_WEB_BASE_URL",
    "SMTP_URL",
    "AUTH_MAIL_FROM",
    "METRICS_TOKEN",
  ];
  for (const name of names) {
    if (!process.env[name]?.trim()) throw new Error(`${name} doit être fourni par le gestionnaire de secrets.`);
  }
  if (image) validateImageTag(process.env.IMAGE_TAG);
  let webUrl;
  try {
    webUrl = new URL(process.env.AUTH_WEB_BASE_URL);
  } catch {
    throw new Error("AUTH_WEB_BASE_URL doit être une URL HTTPS valide sur staging.");
  }
  if (webUrl.protocol !== "https:") throw new Error("AUTH_WEB_BASE_URL doit utiliser HTTPS sur staging.");
  const encodedKey = process.env.AUTH_ENCRYPTION_KEY;
  const key = Buffer.from(encodedKey, "base64");
  if (key.length !== 32 || key.toString("base64") !== encodedKey) throw new Error("AUTH_ENCRYPTION_KEY doit contenir 32 octets en base64.");
}

async function buildImages(tag) {
  for (const target of ["api", "worker", "migrate", "web"]) {
    await docker(["build", "--target", target, "--tag", `lavision-${target}:${tag}`, "."]);
  }
}

async function verify(environment = process.env) {
  const port = environment.WEB_PORT ?? "8080";
  const base = `http://127.0.0.1:${port}`;
  const deadline = Date.now() + 120_000;
  let lastError = "service indisponible";
  while (Date.now() < deadline) {
    try {
      const [web, api] = await Promise.all([
        fetch(`${base}/`, { signal: AbortSignal.timeout(5_000) }),
        fetch(`${base}/api/v1/health/ready`, { signal: AbortSignal.timeout(5_000) }),
      ]);
      if (!web.ok || !api.ok) throw new Error("healthcheck HTTP non prêt");
      await docker([...compose, "exec", "-T", "worker", "node", "-e", "fetch('http://127.0.0.1:3002/health/ready').then(r=>process.exit(r.ok?0:1)).catch(()=>process.exit(1))"], environment);
      return;
    } catch (error) {
      lastError = error instanceof Error ? error.message : lastError;
      await new Promise((resolve) => setTimeout(resolve, 2_000));
    }
  }
  throw new Error(`Vérification staging échouée : ${lastError}`);
}

async function deploy() {
  requireSecretEnvironment();
  const imageTag = validateImageTag(process.env.IMAGE_TAG);
  const state = await readDeploymentState(stateFile);
  const hash = await migrationHash(migrationsDirectory);
  const deployment = makeDeployment(imageTag, hash);
  const nextState = recordDeployment(state, deployment);
  await buildImages(imageTag);
  await docker([...compose, "up", "-d", "postgres", "redis"]);
  await docker([...compose, "run", "--rm", "--no-deps", "migrate"]);
  await docker([...compose, "up", "-d", "--no-build", "--no-deps", "api", "worker", "web"]);
  await verify();
  await writeDeploymentState(stateFile, nextState);
  process.stdout.write(`Déploiement staging vérifié : ${imageTag} (empreinte migrations ${hash.slice(0, 12)}).\n`);
}

async function rollback() {
  requireSecretEnvironment({ image: false });
  if (process.env.ROLLBACK_CONFIRM !== "YES") throw new Error("Rollback refusé : définir ROLLBACK_CONFIRM=YES après avoir vérifié le plan.");
  const state = await readDeploymentState(stateFile);
  const target = rollbackTarget(state);
  const environment = { ...process.env, IMAGE_TAG: target.imageTag };
  for (const service of ["api", "worker", "web"]) {
    await docker(["image", "inspect", `lavision-${service}:${target.imageTag}`], environment);
  }
  await docker([...compose, "up", "-d", "--no-build", "--no-deps", "api", "worker", "web"], environment);
  await verify(environment);
  await writeDeploymentState(stateFile, recordRollback(state));
  process.stdout.write(`Rollback applicatif vérifié vers ${target.imageTag}. Aucune migration n’a été exécutée.\n`);
}

async function plan() {
  const imageTag = validateImageTag(process.env.IMAGE_TAG);
  const state = await readDeploymentState(stateFile);
  const hash = await migrationHash(migrationsDirectory);
  let previous = null;
  try {
    previous = rollbackTarget(state).imageTag;
  } catch {
    previous = null;
  }
  process.stdout.write(`${JSON.stringify({ environment: "staging", imageTag, migrationHash: hash, current: state.current, rollbackCandidate: previous }, null, 2)}\n`);
}

async function state() {
  process.stdout.write(`${JSON.stringify(await readDeploymentState(stateFile), null, 2)}\n`);
}

const command = process.argv[2] ?? "plan";
try {
  if (command === "deploy") await deploy();
  else if (command === "rollback") await rollback();
  else if (command === "verify") { requireSecretEnvironment({ image: false }); await verify(); }
  else if (command === "state") await state();
  else if (command === "plan") await plan();
  else throw new Error("Commande inconnue : plan, deploy, verify, rollback ou state.");
} catch (error) {
  process.stderr.write(`${error instanceof Error ? error.message : "Échec d’exploitation"}\n`);
  process.exitCode = 1;
}
