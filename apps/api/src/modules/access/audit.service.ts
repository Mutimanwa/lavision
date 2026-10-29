import { Injectable, Logger } from "@nestjs/common";
import type { AuditQuery } from "@lavision/contracts";
import {
  AuditOutcome,
  Prisma,
  type AuditEvent,
} from "../../database/client.js";
import { PrismaService } from "../infrastructure/prisma.service.js";

export interface AuditRecordInput {
  organizationId: string;
  actorUserId?: string;
  action: string;
  resourceType: string;
  resourceId?: string;
  outcome: AuditOutcome;
  requestId: string;
  reason?: string;
  context?: Record<string, unknown>;
}

type Db = Prisma.TransactionClient | PrismaService;
type AuditContext = Record<string, string | number | boolean>;

const CONTEXT_KEYS = new Set([
  "permission",
  "relation",
  "scope",
  "correlationId",
  "version",
  "from",
  "to",
]);

export function sanitizeAuditContext(value: unknown): AuditContext | null {
  if (!value || typeof value !== "object" || Array.isArray(value)) return null;
  const context: AuditContext = {};
  for (const [key, raw] of Object.entries(value)) {
    if (!CONTEXT_KEYS.has(key)) continue;
    if (typeof raw === "string") context[key] = raw.slice(0, 160);
    if (typeof raw === "number" && Number.isFinite(raw)) context[key] = raw;
    if (typeof raw === "boolean") context[key] = raw;
  }
  return Object.keys(context).length > 0 ? context : null;
}

@Injectable()
export class AuditService {
  constructor(private readonly prisma: PrismaService) {}

  async record(input: AuditRecordInput, db: Db = this.prisma): Promise<AuditEvent> {
    this.assertLabel(input.action, 96);
    this.assertLabel(input.resourceType, 64);
    const context = sanitizeAuditContext(input.context);
    return db.auditEvent.create({
      data: {
        organizationId: input.organizationId,
        actorUserId: input.actorUserId,
        action: input.action,
        resourceType: input.resourceType,
        resourceId: input.resourceId,
        outcome: input.outcome,
        requestId: input.requestId,
        reason: input.reason?.slice(0, 96),
        context: context ?? undefined,
      },
    });
  }

  async recordSafe(input: AuditRecordInput): Promise<void> {
    try {
      await this.record(input);
    } catch {
      Logger.error("Échec d’écriture d’un événement d’audit.", "Audit");
    }
  }

  async list(query: AuditQuery): Promise<AuditEvent[]> {
    return this.prisma.auditEvent.findMany({
      where: {
        organizationId: query.organizationId,
        action: query.action,
        resourceType: query.resourceType,
        createdAt: query.before ? { lt: new Date(query.before) } : undefined,
      },
      orderBy: { createdAt: "desc" },
      take: query.limit,
    });
  }

  private assertLabel(value: string, maxLength: number): void {
    if (!/^[a-z][a-z0-9._-]*$/.test(value) || value.length > maxLength) {
      throw new Error("Libellé d’audit invalide.");
    }
  }
}

export { AuditOutcome };
