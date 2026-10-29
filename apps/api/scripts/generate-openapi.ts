import { mkdir, readFile, writeFile } from "node:fs/promises";
import path from "node:path";
import { createOpenApiDocument } from "@lavision/contracts";

const output = path.resolve("openapi/openapi.json");
const generated = `${JSON.stringify(createOpenApiDocument(), null, 2)}\n`;

if (process.argv.includes("--check")) {
  let current = "";
  try {
    current = await readFile(output, "utf8");
  } catch {
    // Le contrôle ci-dessous produit un message unique et sûr.
  }

  if (current !== generated) {
    throw new Error(
      "openapi/openapi.json n’est pas à jour. Exécutez npm run openapi.",
    );
  }
} else {
  await mkdir(path.dirname(output), { recursive: true });
  await writeFile(output, generated, "utf8");
  console.log(`Document écrit dans ${output}`);
}
