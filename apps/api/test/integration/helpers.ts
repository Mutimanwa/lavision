import {
  createPrismaClient,
  type PrismaClient,
} from "../../src/database/client.js";

export function createTestPrismaClient(): PrismaClient {
  return createPrismaClient();
}

export async function resetCoreDatabase(prisma: PrismaClient): Promise<void> {
  await prisma.$executeRaw`TRUNCATE TABLE "audit_events"`;
  await prisma.authEvent.deleteMany();
  await prisma.authRateBucket.deleteMany();
  await prisma.authSession.deleteMany();
  await prisma.authOneTimeToken.deleteMany();
  await prisma.mfaRecoveryCode.deleteMany();
  await prisma.mfaCredential.deleteMany();
  await prisma.referentialRevision.deleteMany();
  await prisma.nonWorkingDay.deleteMany();
  await prisma.schoolPeriod.deleteMany();
  await prisma.schoolYear.deleteMany();
  await prisma.room.deleteMany();
  await prisma.academicLevel.deleteMany();
  await prisma.accessRelation.deleteMany();
  await prisma.permissionGrant.deleteMany();
  await prisma.organizationMembership.deleteMany();
  await prisma.accessResource.deleteMany();
  await prisma.site.deleteMany();
  await prisma.user.deleteMany();
  await prisma.organization.deleteMany();
}
