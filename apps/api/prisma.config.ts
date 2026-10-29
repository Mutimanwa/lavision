import "dotenv/config";
import { defineConfig } from "prisma/config";

// Valeur jetable uniquement pour générer le client hors ligne. L’API exige
// explicitement DATABASE_URL au démarrage et les migrations réelles aussi.
const databaseUrl =
  process.env.DATABASE_URL ??
  "postgresql://lavision:lavision_local@localhost:5432/lavision_local?schema=public";

export default defineConfig({
  schema: "prisma/schema.prisma",
  migrations: {
    path: "prisma/migrations",
    seed: "tsx prisma/seed.ts",
  },
  datasource: {
    url: databaseUrl,
  },
});
