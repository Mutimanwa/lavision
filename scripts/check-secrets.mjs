import { execFileSync } from "node:child_process";

const files = execFileSync("git", ["ls-files"], { encoding: "utf8" }).trim().split("\n").filter(Boolean);
const forbiddenFiles = files.filter((file) => /(^|\/)\.env(\..+)?$/.test(file) && !/(^|\/)\.env\.example$/.test(file));
if (forbiddenFiles.length > 0) throw new Error(`Fichiers d’environnement secrets suivis : ${forbiddenFiles.join(", ")}`);

const highConfidencePatterns = [
  /-----BEGIN (?:RSA |OPENSSH |EC |PGP )?PRIVATE KEY-----/,
  /\bAKIA[0-9A-Z]{16}\b/,
  /\bghp_[A-Za-z0-9]{36}\b/,
  /\bgithub_pat_[A-Za-z0-9_]{40,}\b/,
  /\bxox[baprs]-[A-Za-z0-9-]{20,}\b/,
];
const violations = [];
for (const file of files) {
  let content;
  try {
    content = execFileSync("git", ["show", `HEAD:${file}`], { encoding: "utf8", maxBuffer: 4 * 1024 * 1024 });
  } catch {
    continue; // Les fichiers binaires ou trop volumineux sont ignorés par ce contrôle textuel.
  }
  if (highConfidencePatterns.some((pattern) => pattern.test(content))) violations.push(file);
}
if (violations.length > 0) throw new Error(`Motifs de secrets à révoquer et retirer de Git : ${violations.join(", ")}`);
process.stdout.write(`Contrôle secrets : ${files.length} fichiers suivis, aucun motif à haute confiance détecté.\n`);
