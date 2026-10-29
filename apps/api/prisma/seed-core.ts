import { coreSeedSchema } from "@lavision/contracts";
import { coreSeedFixture } from "@lavision/testing";
import {
  MembershipStatus,
  type PrismaClient,
  UserStatus,
} from "../src/database/client.js";

export async function seedCore(prisma: PrismaClient) {
  const fixture = coreSeedSchema.parse(coreSeedFixture);

  return prisma.$transaction(async (transaction) => {
    const organization = await transaction.organization.upsert({
      where: { code: fixture.organization.code },
      update: {
        name: fixture.organization.name,
        archivedAt: null,
      },
      create: fixture.organization,
    });

    const site = await transaction.site.upsert({
      where: {
        organizationId_code: {
          organizationId: organization.id,
          code: fixture.site.code,
        },
      },
      update: {
        name: fixture.site.name,
        archivedAt: null,
      },
      create: {
        organizationId: organization.id,
        code: fixture.site.code,
        name: fixture.site.name,
      },
    });

    const user = await transaction.user.upsert({
      where: { email: fixture.user.email },
      update: {
        displayName: fixture.user.displayName,
        status: UserStatus.ACTIVE,
        archivedAt: null,
      },
      create: {
        email: fixture.user.email,
        displayName: fixture.user.displayName,
        status: UserStatus.ACTIVE,
      },
    });

    const membership = await transaction.organizationMembership.upsert({
      where: {
        organizationId_userId: {
          organizationId: organization.id,
          userId: user.id,
        },
      },
      update: {
        status: MembershipStatus.ACTIVE,
        archivedAt: null,
      },
      create: {
        organizationId: organization.id,
        userId: user.id,
        status: MembershipStatus.ACTIVE,
      },
    });

    return {
      organizationId: organization.id,
      siteId: site.id,
      userId: user.id,
      membershipId: membership.id,
    };
  });
}
