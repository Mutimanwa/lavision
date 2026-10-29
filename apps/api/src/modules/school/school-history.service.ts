import { Injectable } from "@nestjs/common";
import type { SchoolEntityType, SchoolHistoryQuery } from "@lavision/contracts";
import {
  AuditOutcome,
  Prisma,
  type ReferentialRevision,
} from "../../database/client.js";
import { AuditService } from "../access/audit.service.js";
import { PrismaService } from "../infrastructure/prisma.service.js";

export type SchoolDb = Prisma.TransactionClient | PrismaService;

export interface ReferentialRevisionInput {
  organizationId: string;
  entityType: SchoolEntityType;
  entityId: string;
  action: string;
  reason?: string;
  actorUserId: string;
  requestId: string;
  before?: Prisma.InputJsonValue;
  after?: Prisma.InputJsonValue;
}

@Injectable()
export class SchoolHistoryService {
  constructor(
    private readonly prisma: PrismaService,
    private readonly audit: AuditService,
  ) {}

  async record(
    db: SchoolDb,
    input: ReferentialRevisionInput,
  ): Promise<ReferentialRevision> {
    const revision = await db.referentialRevision.create({
      data: {
        organizationId: input.organizationId,
        entityType: input.entityType,
        entityId: input.entityId,
        action: input.action,
        reason: input.reason,
        actorUserId: input.actorUserId,
        requestId: input.requestId,
        before: input.before,
        after: input.after,
      },
    });
    await this.audit.record(
      {
        organizationId: input.organizationId,
        actorUserId: input.actorUserId,
        action: input.action,
        resourceType: input.entityType,
        resourceId: input.entityId,
        outcome: AuditOutcome.SUCCESS,
        requestId: input.requestId,
        reason: input.reason,
        context: { scope: "school" },
      },
      db,
    );
    return revision;
  }

  list(query: SchoolHistoryQuery): Promise<ReferentialRevision[]> {
    return this.prisma.referentialRevision.findMany({
      where: {
        organizationId: query.organizationId,
        entityType: query.entityType,
        entityId: query.entityId,
      },
      orderBy: { createdAt: "desc" },
      take: query.limit,
    });
  }
}
