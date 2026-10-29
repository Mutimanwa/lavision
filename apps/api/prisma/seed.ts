import { createPrismaClient } from "../src/database/client.js";
import { seedCore } from "./seed-core.js";

const prisma = createPrismaClient();

try {
  await seedCore(prisma);
  console.log(
    "Seed initial anonymisé appliqué : 1 établissement, 1 site, 1 utilisateur.",
  );
} finally {
  await prisma.$disconnect();
}
