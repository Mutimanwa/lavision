import type {
  DocumentRuleSnapshot,
  RuleBinding,
  RuleRevision,
  RuleSet,
  RuleSetVersion,
  RuleSimulation,
  RuleValue,
} from "../../database/client.js";
import type { RuleScope } from "@lavision/contracts";

const isoDate = (value: Date | null) => value ? value.toISOString().slice(0, 10) : null;
const isoDateTime = (value: Date | null) => value ? value.toISOString() : null;

export type RuleVersionWithChildren = RuleSetVersion & {
  values: RuleValue[];
  bindings: RuleBinding[];
};

export function presentRuleSet(set: RuleSet) {
  return {
    id: set.id,
    organizationId: set.organizationId,
    domain: set.domain,
    code: set.code,
    name: set.name,
    purpose: set.purpose,
    scopeOrder: set.scopeOrder as RuleScope[],
    definitionKeys: set.definitionKeys as string[],
    createdById: set.createdById,
    createdAt: set.createdAt.toISOString(),
    updatedAt: set.updatedAt.toISOString(),
    archivedAt: isoDateTime(set.archivedAt),
  };
}

export function presentRuleVersion(version: RuleVersionWithChildren) {
  return {
    id: version.id,
    ruleSetId: version.ruleSetId,
    version: version.version,
    status: version.status,
    effectiveFrom: isoDate(version.effectiveFrom)!,
    effectiveTo: isoDate(version.effectiveTo),
    effectiveTimezone: version.effectiveTimezone,
    source: version.source,
    reason: version.reason,
    contentHash: version.contentHash?.trim() ?? null,
    createdById: version.createdById,
    reviewedById: version.reviewedById,
    approvedById: version.approvedById,
    publishedById: version.publishedById,
    publicationSimulationId: version.publicationSimulationId,
    submittedAt: isoDateTime(version.submittedAt),
    reviewedAt: isoDateTime(version.reviewedAt),
    approvedAt: isoDateTime(version.approvedAt),
    publishedAt: isoDateTime(version.publishedAt),
    createdAt: version.createdAt.toISOString(),
    updatedAt: version.updatedAt.toISOString(),
    archivedAt: isoDateTime(version.archivedAt),
    values: [...version.values]
      .sort((left, right) => left.definitionKey.localeCompare(right.definitionKey))
      .map((entry) => ({
        key: entry.definitionKey,
        value: entry.value,
        notApplicable: entry.notApplicable,
        reason: entry.reason,
      })),
    bindings: [...version.bindings]
      .sort((left, right) => `${left.scopeType}:${left.scopeId}`.localeCompare(`${right.scopeType}:${right.scopeId}`))
      .map((entry) => ({ scopeType: entry.scopeType as RuleScope, scopeId: entry.scopeId })),
  };
}

export function presentRuleSimulation(simulation: RuleSimulation) {
  return {
    id: simulation.id,
    ruleSetVersionId: simulation.ruleSetVersionId,
    status: simulation.status,
    inputHash: simulation.inputHash.trim(),
    contentHash: simulation.contentHash.trim(),
    report: simulation.report,
    createdById: simulation.createdById,
    createdAt: simulation.createdAt.toISOString(),
  };
}

export function presentRuleSnapshot(snapshot: DocumentRuleSnapshot) {
  return {
    id: snapshot.id,
    organizationId: snapshot.organizationId,
    documentType: snapshot.documentType,
    documentId: snapshot.documentId,
    ruleSetVersionId: snapshot.ruleSetVersionId,
    resolved: snapshot.resolved,
    contentHash: snapshot.contentHash.trim(),
    createdById: snapshot.createdById,
    createdAt: snapshot.createdAt.toISOString(),
  };
}

export function presentRuleRevision(revision: RuleRevision) {
  return {
    id: revision.id,
    action: revision.action,
    reason: revision.reason,
    actorUserId: revision.actorUserId,
    requestId: revision.requestId,
    before: revision.before,
    after: revision.after,
    createdAt: revision.createdAt.toISOString(),
  };
}
