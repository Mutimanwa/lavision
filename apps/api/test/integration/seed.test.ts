import { afterAll, beforeEach, describe, expect, it } from "vitest";
import { seedCore } from "../../prisma/seed-core.js";
import { createTestPrismaClient, resetCoreDatabase } from "./helpers.js";

const prisma = createTestPrismaClient();

describe.sequential("seed anonymisé", () => {
  beforeEach(async () => {
    await resetCoreDatabase(prisma);
  });

  afterAll(async () => {
    await resetCoreDatabase(prisma);
    await prisma.$disconnect();
  });

  it("est idempotent et conserve une seule copie des données fictives", async () => {
    const first = await seedCore(prisma);
    const second = await seedCore(prisma);

    expect(second).toEqual(first);
    await expect(prisma.organization.count()).resolves.toBe(1);
    await expect(prisma.site.count()).resolves.toBe(1);
    await expect(prisma.user.count()).resolves.toBe(1);
    await expect(prisma.organizationMembership.count()).resolves.toBe(1);
  });
});
