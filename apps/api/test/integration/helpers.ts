import {
  createPrismaClient,
  type PrismaClient,
} from "../../src/database/client.js";

export function createTestPrismaClient(): PrismaClient {
  return createPrismaClient();
}

export async function resetCoreDatabase(prisma: PrismaClient): Promise<void> {
  await prisma.authEvent.deleteMany();
  await prisma.authRateBucket.deleteMany();
  await prisma.authSession.deleteMany();
  await prisma.authOneTimeToken.deleteMany();
  await prisma.mfaRecoveryCode.deleteMany();
  await prisma.mfaCredential.deleteMany();
  await prisma.organizationMembership.deleteMany();
  await prisma.site.deleteMany();
  await prisma.user.deleteMany();
  await prisma.organization.deleteMany();
}
