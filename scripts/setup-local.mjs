import { spawn } from "node:child_process";
import { constants } from "node:fs";
import { copyFile } from "node:fs/promises";
import { fileURLToPath } from "node:url";

const root = fileURLToPath(new URL("../", import.meta.url));
const npm = process.platform === "win32" ? "npm.cmd" : "npm";

function run(command, args) {
  return new Promise((resolve, reject) => {
    const child = spawn(command, args, {
      cwd: root,
      env: process.env,
      stdio: "inherit",
    });

    child.once("error", reject);
    child.once("exit", (code, signal) => {
      if (code === 0) {
        resolve();
        return;
      }

      reject(
        new Error(
          `Commande échouée : ${command} ${args.join(" ")} (${signal ?? code}).`,
        ),
      );
    });
  });
}

await copyFile(
  `${root}apps/api/.env.example`,
  `${root}apps/api/.env`,
  constants.COPYFILE_EXCL,
).catch((error) => {
  if (error?.code !== "EEXIST") throw error;
});

if (process.env.LAVISION_SKIP_DOCKER !== "true") {
  await run("docker", ["compose", "up", "-d", "--wait", "postgres"]);
}

for (const script of [
  "build:packages",
  "prisma:generate",
  "prisma:migrate",
  "prisma:seed",
]) {
  await run(npm, ["run", script]);
}

console.log("Socle local prêt : PostgreSQL migré et seed fictif appliqué.");
