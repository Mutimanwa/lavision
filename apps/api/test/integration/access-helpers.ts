import { randomUUID } from "node:crypto";
import {
  AccessRelationType,
  AccessResourceType,
  PrismaClient,
} from "../../src/database/client.js";

export async function createResource(
  prisma: PrismaClient,
  input: {
    organizationId: string;
    siteId?: string;
    parentId?: string;
    type: AccessResourceType;
  },
) {
  return prisma.accessResource.create({
    data: {
      id: randomUUID(),
      organizationId: input.organizationId,
      siteId: input.siteId,
      parentId: input.parentId,
      type: input.type,
    },
  });
}

export async function createRelation(
  prisma: PrismaClient,
  input: {
    membershipId: string;
    relation: AccessRelationType;
    resourceId: string;
  },
) {
  return prisma.accessRelation.create({
    data: {
      membershipId: input.membershipId,
      relation: input.relation,
      resourceId: input.resourceId,
    },
  });
}

export async function membershipFor(
  prisma: PrismaClient,
  userId: string,
  organizationId: string,
) {
  return prisma.organizationMembership.findUniqueOrThrow({
    where: { organizationId_userId: { organizationId, userId } },
  });
}
