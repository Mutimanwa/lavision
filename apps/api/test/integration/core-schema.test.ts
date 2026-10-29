import { afterAll, beforeEach, describe, expect, it } from "vitest";
import { createTestPrismaClient, resetCoreDatabase } from "./helpers.js";

const prisma = createTestPrismaClient();

async function createOrganization(code: string) {
  return prisma.organization.create({
    data: { code, name: `Établissement fictif ${code}` },
  });
}

describe("contraintes PostgreSQL du socle", () => {
  beforeEach(async () => {
    await resetCoreDatabase(prisma);
  });

  afterAll(async () => {
    await resetCoreDatabase(prisma);
    await prisma.$disconnect();
  });

  it("refuse les codes d’établissement dupliqués", async () => {
    await createOrganization("DUP");
    const duplicate = createOrganization("DUP");

    await expect(duplicate).rejects.toMatchObject({ code: "P2002" });
  });

  it("autorise un même code de site dans deux établissements, pas deux fois dans le même", async () => {
    const first = await createOrganization("SITE_A");
    const second = await createOrganization("SITE_B");

    await prisma.site.create({
      data: { organizationId: first.id, code: "MAIN", name: "Site A" },
    });
    await prisma.site.create({
      data: { organizationId: second.id, code: "MAIN", name: "Site B" },
    });

    const duplicate = prisma.site.create({
      data: { organizationId: first.id, code: "MAIN", name: "Doublon" },
    });
    await expect(duplicate).rejects.toMatchObject({ code: "P2002" });
  });

  it("rend l’unicité de l’e-mail insensible à la casse", async () => {
    await prisma.user.create({
      data: { email: "Case@Example.invalid", displayName: "Fictif A" },
    });

    const duplicate = prisma.user.create({
      data: { email: "case@example.invalid", displayName: "Fictif B" },
    });
    await expect(duplicate).rejects.toMatchObject({ code: "P2002" });
  });

  it("permet à un utilisateur d’appartenir à deux établissements sans doublon", async () => {
    const first = await createOrganization("MEMBER_A");
    const second = await createOrganization("MEMBER_B");
    const user = await prisma.user.create({
      data: { email: "member@example.invalid", displayName: "Membre fictif" },
    });

    await prisma.organizationMembership.create({
      data: { organizationId: first.id, userId: user.id },
    });
    await prisma.organizationMembership.create({
      data: { organizationId: second.id, userId: user.id },
    });

    await expect(
      prisma.organizationMembership.count({ where: { userId: user.id } }),
    ).resolves.toBe(2);

    const duplicate = prisma.organizationMembership.create({
      data: { organizationId: first.id, userId: user.id },
    });
    await expect(duplicate).rejects.toMatchObject({ code: "P2002" });
  });
});
