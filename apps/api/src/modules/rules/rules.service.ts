import {
  BadRequestException,
  ConflictException,
  ForbiddenException,
  Injectable,
  NotFoundException,
} from "@nestjs/common";
import {
  ERROR_CODES,
  PERMISSIONS,
  RULE_CATALOG_VERSION,
  RULE_DEFINITIONS,
  ruleScopeOrderSchema,
  type Permission,
  type RuleBindingInput,
  type RuleCatalogQuery,
  type RuleDefinition,
  type RuleDomain,
  type RuleResolutionScope,
  type RuleResolveInput,
  type RuleSetCreateInput,
  type RuleSetListQuery,
  type RuleSimulationInput,
  type RuleSnapshotInput,
  type RuleValidationInput,
  type RuleValueInput,
  type RuleVersionActionInput,
  type RuleVersionCreateInput,
  type RuleVersionDuplicateInput,
  type RuleVersionUpdateInput,
} from "@lavision/contracts";
import {
  AuditOutcome,
  Prisma,
  RuleSimulationStatus,
  RuleVersionStatus,
  type RuleSet,
  type RuleSetVersion,
} from "../../database/client.js";
import { AuthorizationService } from "../access/authorization.service.js";
import { AuditService } from "../access/audit.service.js";
import type { AuthRequestContext, SessionAuthContext } from "../auth/auth.types.js";
import { PrismaService } from "../infrastructure/prisma.service.js";
import { findRuleDefinition, validateRuleValue } from "./rule-catalog.js";
import {
  presentRuleRevision,
  presentRuleSet,
  presentRuleSimulation,
  presentRuleSnapshot,
  presentRuleVersion,
  type RuleVersionWithChildren,
} from "./rule-presenters.js";
import { compareExactDecimals, resolveRules } from "./rule-resolver.js";
import {
  asJson,
  dateInTimezone,
  dateOnly,
  isoDate,
  isValidTimezone,
  sha256,
  toResolverVersion,
  versionContentHash,
  type NormalizedRuleValue,
} from "./rule.utils.js";

type Db = Prisma.TransactionClient | PrismaService;
type RuleAction = "read" | "draft" | "simulate" | "submit" | "review" | "approve" | "publish" | "archive" | "snapshot";
type VersionWithSet = RuleVersionWithChildren & { ruleSet: RuleSet };

const domainPermissions: Record<RuleDomain, Record<RuleAction, Permission>> = {
  academic: {
    read: PERMISSIONS.rulesAcademicRead,
    draft: PERMISSIONS.rulesAcademicDraft,
    simulate: PERMISSIONS.rulesAcademicSimulate,
    submit: PERMISSIONS.rulesAcademicSubmit,
    review: PERMISSIONS.rulesAcademicReview,
    approve: PERMISSIONS.rulesAcademicApprove,
    publish: PERMISSIONS.rulesAcademicPublish,
    archive: PERMISSIONS.rulesAcademicArchive,
    snapshot: PERMISSIONS.rulesAcademicSnapshot,
  },
  attendance: {
    read: PERMISSIONS.rulesAttendanceRead,
    draft: PERMISSIONS.rulesAttendanceDraft,
    simulate: PERMISSIONS.rulesAttendanceSimulate,
    submit: PERMISSIONS.rulesAttendanceSubmit,
    review: PERMISSIONS.rulesAttendanceReview,
    approve: PERMISSIONS.rulesAttendanceApprove,
    publish: PERMISSIONS.rulesAttendancePublish,
    archive: PERMISSIONS.rulesAttendanceArchive,
    snapshot: PERMISSIONS.rulesAttendanceSnapshot,
  },
  finance: {
    read: PERMISSIONS.rulesFinanceRead,
    draft: PERMISSIONS.rulesFinanceDraft,
    simulate: PERMISSIONS.rulesFinanceSimulate,
    submit: PERMISSIONS.rulesFinanceSubmit,
    review: PERMISSIONS.rulesFinanceReview,
    approve: PERMISSIONS.rulesFinanceApprove,
    publish: PERMISSIONS.rulesFinancePublish,
    archive: PERMISSIONS.rulesFinanceArchive,
    snapshot: PERMISSIONS.rulesFinanceSnapshot,
  },
  hr: {
    read: PERMISSIONS.rulesHrRead,
    draft: PERMISSIONS.rulesHrDraft,
    simulate: PERMISSIONS.rulesHrSimulate,
    submit: PERMISSIONS.rulesHrSubmit,
    review: PERMISSIONS.rulesHrReview,
    approve: PERMISSIONS.rulesHrApprove,
    publish: PERMISSIONS.rulesHrPublish,
    archive: PERMISSIONS.rulesHrArchive,
    snapshot: PERMISSIONS.rulesHrSnapshot,
  },
  payroll: {
    read: PERMISSIONS.rulesPayrollRead,
    draft: PERMISSIONS.rulesPayrollDraft,
    simulate: PERMISSIONS.rulesPayrollSimulate,
    submit: PERMISSIONS.rulesPayrollSubmit,
    review: PERMISSIONS.rulesPayrollReview,
    approve: PERMISSIONS.rulesPayrollApprove,
    publish: PERMISSIONS.rulesPayrollPublish,
    archive: PERMISSIONS.rulesPayrollArchive,
    snapshot: PERMISSIONS.rulesPayrollSnapshot,
  },
  documents: {
    read: PERMISSIONS.rulesDocumentsRead,
    draft: PERMISSIONS.rulesDocumentsDraft,
    simulate: PERMISSIONS.rulesDocumentsSimulate,
    submit: PERMISSIONS.rulesDocumentsSubmit,
    review: PERMISSIONS.rulesDocumentsReview,
    approve: PERMISSIONS.rulesDocumentsApprove,
    publish: PERMISSIONS.rulesDocumentsPublish,
    archive: PERMISSIONS.rulesDocumentsArchive,
    snapshot: PERMISSIONS.rulesDocumentsSnapshot,
  },
  calendar: {
    read: PERMISSIONS.rulesCalendarRead,
    draft: PERMISSIONS.rulesCalendarDraft,
    simulate: PERMISSIONS.rulesCalendarSimulate,
    submit: PERMISSIONS.rulesCalendarSubmit,
    review: PERMISSIONS.rulesCalendarReview,
    approve: PERMISSIONS.rulesCalendarApprove,
    publish: PERMISSIONS.rulesCalendarPublish,
    archive: PERMISSIONS.rulesCalendarArchive,
    snapshot: PERMISSIONS.rulesCalendarSnapshot,
  },
};

const versionChildren = { values: true, bindings: true } as const;
const versionWithSet = { ...versionChildren, ruleSet: true } as const;

@Injectable()
export class RulesService {
  constructor(
    private readonly prisma: PrismaService,
    private readonly authorization: AuthorizationService,
    private readonly audit: AuditService,
  ) {}

  async catalog(actor: SessionAuthContext, input: RuleCatalogQuery) {
    if (input.domain) await this.assertDomain(actor, input.organizationId, input.domain, "read");
    const search = input.search?.toLocaleLowerCase("fr");
    const definitions = (RULE_DEFINITIONS as readonly RuleDefinition[]).filter((definition) => {
      if (input.domain && definition.domain !== input.domain) return false;
      if (!search) return true;
      return `${definition.key} ${definition.label} ${definition.description}`
        .toLocaleLowerCase("fr")
        .includes(search);
    }).map((definition) => ({
      ...definition,
      scopes: [...definition.scopes],
      constraints: definition.constraints
        ? { ...definition.constraints, values: definition.constraints.values ? [...definition.constraints.values] : undefined }
        : null,
    }));
    return { catalogVersion: RULE_CATALOG_VERSION, definitions };
  }

  async validate(actor: SessionAuthContext, input: RuleValidationInput) {
    const definition = findRuleDefinition(input.key);
    if (!definition) {
      return {
        valid: false,
        key: input.key,
        issues: [{ path: "key", code: "UNKNOWN_RULE", message: "Cette définition n’appartient pas au catalogue fermé." }],
      };
    }
    await this.assertDomain(actor, input.organizationId, definition.domain, "read");
    const result = validateRuleValue(definition, input.value);
    return { key: input.key, ...result };
  }

  async listSets(actor: SessionAuthContext, input: RuleSetListQuery) {
    await this.assertDomain(actor, input.organizationId, input.domain, "read");
    const sets = await this.prisma.ruleSet.findMany({
      where: {
        organizationId: input.organizationId,
        domain: input.domain,
        archivedAt: input.includeArchived === "true" ? undefined : null,
      },
      orderBy: { code: "asc" },
    });
    return { sets: sets.map(presentRuleSet) };
  }

  async getSet(actor: SessionAuthContext, organizationId: string, id: string) {
    const set = await this.prisma.ruleSet.findFirst({
      where: { id, organizationId },
      include: { versions: { include: versionChildren, orderBy: { version: "desc" } } },
    });
    if (!set) this.notFound();
    await this.assertDomain(actor, organizationId, set.domain as RuleDomain, "read");
    return { set: presentRuleSet(set), versions: set.versions.map(presentRuleVersion) };
  }

  async getVersion(actor: SessionAuthContext, organizationId: string, id: string) {
    const version = await this.findVersion(organizationId, id);
    await this.assertDomain(actor, organizationId, version.ruleSet.domain as RuleDomain, "read");
    return presentRuleVersion(version);
  }

  async createSet(actor: SessionAuthContext, input: RuleSetCreateInput, context: AuthRequestContext) {
    await this.assertDomain(actor, input.organizationId, input.domain, "draft");
    this.validateSetDefinitions(input.domain, input.definitionKeys, input.scopeOrder);
    try {
      return await this.prisma.$transaction(async (transaction) => {
        const set = await transaction.ruleSet.create({
          data: {
            organizationId: input.organizationId,
            domain: input.domain,
            code: input.code,
            name: input.name,
            purpose: input.purpose,
            scopeOrder: asJson(input.scopeOrder),
            definitionKeys: asJson(input.definitionKeys),
            createdById: actor.session.userId,
          },
        });
        await this.audit.record({
          organizationId: input.organizationId,
          actorUserId: actor.session.userId,
          action: "rules.set.create",
          resourceType: "rule_set",
          resourceId: set.id,
          outcome: AuditOutcome.SUCCESS,
          requestId: context.requestId,
          context: { scope: "rules" },
        }, transaction);
        return presentRuleSet(set);
      });
    } catch (error) {
      this.translateDatabaseError(error);
    }
  }

  async createVersion(actor: SessionAuthContext, setId: string, input: RuleVersionCreateInput, context: AuthRequestContext) {
    const set = await this.findSet(input.organizationId, setId);
    await this.assertDomain(actor, input.organizationId, set.domain as RuleDomain, "draft");
    return this.createVersionInternal(actor, set, input, context, "rules.version.create");
  }

  async duplicateVersion(actor: SessionAuthContext, versionId: string, input: RuleVersionDuplicateInput, context: AuthRequestContext) {
    const source = await this.findVersion(input.organizationId, versionId);
    await this.assertDomain(actor, input.organizationId, source.ruleSet.domain as RuleDomain, "read");
    const target = await this.findSet(input.organizationId, input.targetSetId ?? source.ruleSetId);
    await this.assertDomain(actor, input.organizationId, target.domain as RuleDomain, "draft");
    if (target.domain !== source.ruleSet.domain) this.conflict("TARGET_DOMAIN_MISMATCH", "La duplication doit conserver le domaine.");
    const values: RuleValueInput[] = source.values.map((entry) => ({
      key: entry.definitionKey,
      value: entry.notApplicable ? undefined : entry.value,
      notApplicable: entry.notApplicable,
      reason: entry.reason ?? undefined,
    }));
    return this.createVersionInternal(actor, target, {
      organizationId: input.organizationId,
      effectiveFrom: input.effectiveFrom,
      effectiveTo: input.effectiveTo,
      timezone: input.timezone,
      source: input.source,
      reason: input.reason,
      values,
      bindings: source.bindings.map((entry) => ({ scopeType: entry.scopeType as RuleBindingInput["scopeType"], scopeId: entry.scopeId })),
    }, context, "rules.version.duplicate", source);
  }

  async updateVersion(actor: SessionAuthContext, versionId: string, input: RuleVersionUpdateInput, context: AuthRequestContext) {
    const existing = await this.findVersion(input.organizationId, versionId);
    const domain = existing.ruleSet.domain as RuleDomain;
    await this.assertDomain(actor, input.organizationId, domain, "draft");
    this.assertDraft(existing);
    const currentValues: RuleValueInput[] = existing.values.map((entry) => ({
      key: entry.definitionKey,
      value: entry.notApplicable ? undefined : entry.value,
      notApplicable: entry.notApplicable,
      reason: entry.reason ?? undefined,
    }));
    const data = await this.validateVersionData(existing.ruleSet, input.organizationId, {
      organizationId: input.organizationId,
      effectiveFrom: input.effectiveFrom ?? isoDate(existing.effectiveFrom),
      effectiveTo: input.effectiveTo === undefined ? (existing.effectiveTo ? isoDate(existing.effectiveTo) : undefined) : input.effectiveTo ?? undefined,
      timezone: input.timezone ?? existing.effectiveTimezone,
      source: input.source ?? existing.source,
      reason: input.reason ?? existing.reason,
      values: input.values ?? currentValues,
      bindings: input.bindings ?? existing.bindings.map((entry) => ({ scopeType: entry.scopeType as RuleBindingInput["scopeType"], scopeId: entry.scopeId })),
    }, false);
    try {
      return await this.prisma.$transaction(async (transaction) => {
        const current = await transaction.ruleSetVersion.findUnique({ where: { id: versionId } });
        if (!current) this.notFound();
        this.assertDraft(current);
        await transaction.ruleSetVersion.update({
          where: { id: versionId },
          data: {
            effectiveFrom: dateOnly(data.effectiveFrom),
            effectiveTo: data.effectiveTo ? dateOnly(data.effectiveTo) : null,
            effectiveTimezone: data.timezone,
            source: data.source,
            reason: data.reason,
            contentHash: data.contentHash,
          },
        });
        await transaction.ruleValue.deleteMany({ where: { ruleSetVersionId: versionId } });
        await transaction.ruleBinding.deleteMany({ where: { ruleSetVersionId: versionId } });
        await this.insertChildren(transaction, versionId, data.values, data.bindings);
        const after = await transaction.ruleSetVersion.findUniqueOrThrow({ where: { id: versionId }, include: versionChildren });
        await this.recordRevision(transaction, {
          organizationId: input.organizationId,
          versionId,
          action: "rules.version.update",
          actor,
          context,
          reason: data.reason,
          before: presentRuleVersion(existing),
          after: presentRuleVersion(after),
        });
        return presentRuleVersion(after);
      });
    } catch (error) {
      this.translateDatabaseError(error);
    }
  }

  async transitionVersion(actor: SessionAuthContext, versionId: string, action: "submit" | "review" | "approve" | "publish" | "archive", input: RuleVersionActionInput, context: AuthRequestContext) {
    const existing = await this.findVersion(input.organizationId, versionId);
    const domain = existing.ruleSet.domain as RuleDomain;
    await this.assertDomain(actor, input.organizationId, domain, action);
    if (["review", "approve", "publish"].includes(action) && existing.createdById === actor.session.userId) {
      throw new ForbiddenException({ code: ERROR_CODES.FORBIDDEN });
    }
    if (action === "submit") {
      await this.validateVersionData(existing.ruleSet, input.organizationId, this.versionInputFromDb(existing), false);
      await this.assertResolvedComplete(existing);
    }
    let simulationId: string | undefined;
    if (action === "publish") {
      await this.assertResolvedComplete(existing);
      const simulation = await this.prisma.ruleSimulation.findFirst({
        where: { ruleSetVersionId: versionId, status: RuleSimulationStatus.PASSED, contentHash: existing.contentHash ?? undefined },
        orderBy: { createdAt: "desc" },
      });
      if (!simulation || !this.simulationCoversCandidate(simulation.report)) {
        this.conflict("SIMULATION_REQUIRED", "Une simulation réussie du contenu exact, avec un cas où la version est applicable, est requise.");
      }
      simulationId = simulation.id;
    }
    try {
      return await this.prisma.$transaction(async (transaction) => {
        const current = await transaction.ruleSetVersion.findUnique({ where: { id: versionId }, include: versionChildren });
        if (!current) this.notFound();
        this.assertTransition(current, action);
        const now = new Date();
        const data: Prisma.RuleSetVersionUpdateInput = {};
        if (action === "submit") Object.assign(data, { status: RuleVersionStatus.IN_REVIEW, submittedAt: now });
        if (action === "review") Object.assign(data, { status: RuleVersionStatus.VALIDATED, reviewedById: actor.session.userId, reviewedAt: now });
        if (action === "approve") Object.assign(data, { status: RuleVersionStatus.APPROVED, approvedById: actor.session.userId, approvedAt: now });
        if (action === "publish") {
          const status = current.effectiveFrom > dateOnly(dateInTimezone(now, current.effectiveTimezone)) ? RuleVersionStatus.SCHEDULED : RuleVersionStatus.PUBLISHED;
          Object.assign(data, { status, publishedById: actor.session.userId, publishedAt: now, publicationSimulationId: simulationId });
        }
        if (action === "archive") Object.assign(data, { status: RuleVersionStatus.ARCHIVED, archivedAt: now });
        const updated = await transaction.ruleSetVersion.update({ where: { id: versionId }, data, include: versionChildren });
        await this.recordRevision(transaction, {
          organizationId: input.organizationId,
          versionId,
          action: `rules.version.${action}`,
          actor,
          context,
          reason: input.reason,
          before: presentRuleVersion(existing),
          after: presentRuleVersion(updated),
        });
        return presentRuleVersion(updated);
      });
    } catch (error) {
      this.translateDatabaseError(error);
    }
  }

  async resolve(actor: SessionAuthContext, input: RuleResolveInput) {
    const set = await this.findSet(input.organizationId, input.ruleSetId);
    await this.assertDomain(actor, input.organizationId, set.domain as RuleDomain, "read");
    await this.validateContext(input.organizationId, input.scope);
    return this.resolveSet(set, input.asOf, input.scope);
  }

  async simulate(actor: SessionAuthContext, versionId: string, input: RuleSimulationInput, context: AuthRequestContext) {
    const candidate = await this.findVersion(input.organizationId, versionId);
    const domain = candidate.ruleSet.domain as RuleDomain;
    await this.assertDomain(actor, input.organizationId, domain, "simulate");
    if (candidate.status === RuleVersionStatus.ARCHIVED || candidate.ruleSet.archivedAt) this.conflict("VERSION_ARCHIVED", "Une version archivée ne peut pas être simulée comme candidate.");
    for (const entry of input.cases) await this.validateContext(input.organizationId, entry.scope);
    let comparison: VersionWithSet | undefined;
    if (input.comparisonVersionId) {
      comparison = await this.findVersion(input.organizationId, input.comparisonVersionId);
      if (comparison.ruleSetId !== candidate.ruleSetId) this.conflict("COMPARISON_SET_MISMATCH", "La comparaison doit appartenir au même ensemble.");
    }
    const baseVersions = await this.publishedVersions(candidate.ruleSetId);
    const candidateResolver = toResolverVersion(candidate);
    const comparisonResolver = comparison ? toResolverVersion(comparison) : undefined;
    const cases = input.cases.map((entry) => {
      const candidateResult = resolveRules({
        ruleSetId: candidate.ruleSetId,
        asOf: entry.asOf,
        scopeOrder: this.scopeOrder(candidate.ruleSet),
        definitions: this.definitions(candidate.ruleSet),
        context: { organizationId: input.organizationId, ...entry.scope },
        versions: baseVersions.map(toResolverVersion),
        candidate: candidateResolver,
      });
      const comparisonResult = resolveRules({
        ruleSetId: candidate.ruleSetId,
        asOf: entry.asOf,
        scopeOrder: this.scopeOrder(candidate.ruleSet),
        definitions: this.definitions(candidate.ruleSet),
        context: { organizationId: input.organizationId, ...entry.scope },
        versions: baseVersions.map(toResolverVersion),
        candidate: comparisonResolver,
      });
      const candidateApplies = candidate.effectiveFrom <= dateOnly(entry.asOf) && (!candidate.effectiveTo || entry.asOf < isoDate(candidate.effectiveTo));
      return {
        name: entry.name,
        kind: entry.kind ?? null,
        input: entry.input ?? null,
        candidateApplies,
        status: candidateResult.status,
        resolved: candidateResult,
        comparison: comparisonResult,
      };
    });
    const report = { contentHash: candidate.contentHash?.trim() ?? "", cases };
    const status = cases.every((entry) => entry.status === "RESOLVED") ? RuleSimulationStatus.PASSED : RuleSimulationStatus.FAILED;
    const inputHash = sha256({ versionId, contentHash: report.contentHash, comparisonVersionId: input.comparisonVersionId ?? null, cases: input.cases });
    const simulation = await this.prisma.$transaction(async (transaction) => {
      const created = await transaction.ruleSimulation.create({
        data: {
          organizationId: input.organizationId,
          ruleSetVersionId: versionId,
          status,
          input: asJson(input),
          report: asJson(report),
          inputHash,
          contentHash: report.contentHash,
          createdById: actor.session.userId,
        },
      });
      await this.audit.record({
        organizationId: input.organizationId,
        actorUserId: actor.session.userId,
        action: "rules.simulation.create",
        resourceType: "rule_simulation",
        resourceId: created.id,
        outcome: AuditOutcome.SUCCESS,
        requestId: context.requestId,
        context: { scope: "rules", version: candidate.version },
      }, transaction);
      return created;
    });
    return presentRuleSimulation(simulation);
  }

  async createSnapshot(actor: SessionAuthContext, input: RuleSnapshotInput, context: AuthRequestContext) {
    const version = await this.findVersion(input.organizationId, input.ruleSetVersionId);
    const domain = version.ruleSet.domain as RuleDomain;
    await this.assertDomain(actor, input.organizationId, domain, "snapshot");
    if ((version.status !== RuleVersionStatus.PUBLISHED && version.status !== RuleVersionStatus.SCHEDULED) || version.archivedAt) {
      this.conflict("PUBLISHED_VERSION_REQUIRED", "Un snapshot doit viser une version publiée ou planifiée active.");
    }
    await this.validateContext(input.organizationId, input.scope);
    if (version.effectiveFrom > dateOnly(input.asOf) || (version.effectiveTo && input.asOf >= isoDate(version.effectiveTo))) {
      this.conflict("VERSION_NOT_APPLICABLE", "La version n’est pas applicable à la date du snapshot.");
    }
    const resolution = await this.resolveSet(version.ruleSet, input.asOf, input.scope, version);
    if (resolution.status !== "RESOLVED") this.conflict("RESOLUTION_INCOMPLETE", "La configuration résolue est incomplète ou en conflit.");
    const envelope = {
      catalogVersion: RULE_CATALOG_VERSION,
      ruleSetId: version.ruleSetId,
      ruleSetVersionId: version.id,
      ruleContentHash: version.contentHash?.trim() ?? "",
      asOf: input.asOf,
      scope: input.scope,
      resolution,
    };
    const contentHash = sha256(envelope);
    const snapshot = await this.prisma.$transaction(async (transaction) => {
      const lockKey = `${input.documentType}:${input.documentId}:${version.id}`;
      await transaction.$executeRaw`SELECT pg_advisory_xact_lock(hashtext(${lockKey})::bigint)`;
      const existing = await transaction.documentRuleSnapshot.findFirst({
        where: { documentType: input.documentType, documentId: input.documentId, ruleSetVersionId: version.id },
      });
      if (existing) {
        if (existing.contentHash.trim() !== contentHash) this.conflict("SNAPSHOT_CONTEXT_MISMATCH", "Le même document et la même version ne peuvent pas produire deux snapshots différents.");
        return existing;
      }
      const created = await transaction.documentRuleSnapshot.create({
        data: {
          organizationId: input.organizationId,
          documentType: input.documentType,
          documentId: input.documentId,
          ruleSetVersionId: version.id,
          resolved: asJson(envelope),
          contentHash,
          createdById: actor.session.userId,
        },
      });
      await this.audit.record({
        organizationId: input.organizationId,
        actorUserId: actor.session.userId,
        action: "rules.snapshot.create",
        resourceType: "document_rule_snapshot",
        resourceId: created.id,
        outcome: AuditOutcome.SUCCESS,
        requestId: context.requestId,
        context: { scope: "rules", version: version.version },
      }, transaction);
      return created;
    });
    return presentRuleSnapshot(snapshot);
  }

  async getSnapshot(actor: SessionAuthContext, organizationId: string, id: string) {
    const snapshot = await this.prisma.documentRuleSnapshot.findFirst({
      where: { id, organizationId },
      include: { ruleSetVersion: { include: { ruleSet: true } } },
    });
    if (!snapshot) this.notFound();
    await this.assertDomain(actor, organizationId, snapshot.ruleSetVersion.ruleSet.domain as RuleDomain, "read");
    return presentRuleSnapshot(snapshot);
  }

  async diffVersions(actor: SessionAuthContext, organizationId: string, fromId: string, toId: string) {
    const from = await this.findVersion(organizationId, fromId);
    const to = await this.findVersion(organizationId, toId);
    if (from.ruleSetId !== to.ruleSetId) this.conflict("DIFF_SET_MISMATCH", "Les versions comparées doivent appartenir au même ensemble.");
    await this.assertDomain(actor, organizationId, from.ruleSet.domain as RuleDomain, "read");
    const keys = [...new Set([...from.values.map((entry) => entry.definitionKey), ...to.values.map((entry) => entry.definitionKey)])].sort();
    const valueView = (version: VersionWithSet, key: string) => {
      const entry = version.values.find((candidate) => candidate.definitionKey === key);
      return entry ? { value: entry.value, notApplicable: entry.notApplicable, reason: entry.reason } : null;
    };
    const changes = keys.map((key) => {
      const before = valueView(from, key);
      const after = valueView(to, key);
      const change = !before ? "added" : !after ? "removed" : sha256(before) === sha256(after) ? "unchanged" : "changed";
      return { key, change, before, after };
    });
    const bindings = (version: VersionWithSet) => version.bindings.map((entry) => `${entry.scopeType}:${entry.scopeId}`).sort();
    return {
      fromVersionId: from.id,
      toVersionId: to.id,
      changes,
      bindingsChanged: sha256(bindings(from)) !== sha256(bindings(to)),
      effectChanged: from.effectiveFrom.getTime() !== to.effectiveFrom.getTime() || from.effectiveTo?.getTime() !== to.effectiveTo?.getTime() || from.effectiveTimezone !== to.effectiveTimezone,
    };
  }

  async revisions(actor: SessionAuthContext, organizationId: string, versionId: string, limit = 50) {
    const version = await this.findVersion(organizationId, versionId);
    await this.assertDomain(actor, organizationId, version.ruleSet.domain as RuleDomain, "read");
    const revisions = await this.prisma.ruleRevision.findMany({
      where: { organizationId, ruleSetVersionId: versionId },
      orderBy: { createdAt: "desc" },
      take: limit,
    });
    return { revisions: revisions.map(presentRuleRevision) };
  }

  private async createVersionInternal(actor: SessionAuthContext, set: RuleSet, input: RuleVersionCreateInput, context: AuthRequestContext, action: string, source?: VersionWithSet) {
    const data = await this.validateVersionData(set, input.organizationId, input, false);
    try {
      return await this.prisma.$transaction(async (transaction) => {
        const currentSet = await transaction.ruleSet.findUnique({ where: { id: set.id } });
        if (!currentSet || currentSet.archivedAt) this.conflict("SET_ARCHIVED", "L’ensemble de règles est archivé.");
        const next = await transaction.ruleSet.update({ where: { id: set.id }, data: { nextVersion: { increment: 1 } }, select: { nextVersion: true } });
        const created = await transaction.ruleSetVersion.create({
          data: {
            ruleSetId: set.id,
            version: next.nextVersion - 1,
            effectiveFrom: dateOnly(data.effectiveFrom),
            effectiveTo: data.effectiveTo ? dateOnly(data.effectiveTo) : null,
            effectiveTimezone: data.timezone,
            source: data.source,
            reason: data.reason,
            contentHash: data.contentHash,
            createdById: actor.session.userId,
          },
        });
        await this.insertChildren(transaction, created.id, data.values, data.bindings);
        const after = await transaction.ruleSetVersion.findUniqueOrThrow({ where: { id: created.id }, include: versionChildren });
        await this.recordRevision(transaction, {
          organizationId: input.organizationId,
          versionId: created.id,
          action,
          actor,
          context,
          reason: input.reason,
          before: source ? presentRuleVersion(source) : undefined,
          after: presentRuleVersion(after),
        });
        return presentRuleVersion(after);
      });
    } catch (error) {
      this.translateDatabaseError(error);
    }
  }

  private async validateVersionData(set: RuleSet, organizationId: string, input: RuleVersionCreateInput, requireComplete: boolean) {
    if (!isValidTimezone(input.timezone)) this.invalid("timezone", "INVALID_TIMEZONE", "Le fuseau horaire IANA est invalide.");
    if (input.effectiveTo && input.effectiveFrom >= input.effectiveTo) this.invalid("effectiveTo", "INVALID_EFFECTIVE_RANGE", "La fin d’effet doit être postérieure au début.");
    const values = this.normalizeValues(set, input.values, requireComplete);
    const bindings = await this.normalizeBindings(set, organizationId, input.bindings);
    return {
      effectiveFrom: input.effectiveFrom,
      effectiveTo: input.effectiveTo ?? null,
      timezone: input.timezone,
      source: input.source,
      reason: input.reason,
      values,
      bindings,
      contentHash: versionContentHash({
        scopeOrder: this.scopeOrder(set),
        definitionKeys: this.definitionKeys(set),
        effectiveFrom: input.effectiveFrom,
        effectiveTo: input.effectiveTo ?? null,
        timezone: input.timezone,
        values,
        bindings,
      }),
    };
  }

  private normalizeValues(set: RuleSet, inputs: readonly RuleValueInput[], requireComplete: boolean): NormalizedRuleValue[] {
    const definitions = this.definitions(set);
    const byKey = new Map(definitions.map((definition) => [definition.key, definition]));
    const seen = new Set<string>();
    const issues: Array<{ path: string; code: string; message: string }> = [];
    const values: NormalizedRuleValue[] = [];
    inputs.forEach((input, index) => {
      const definition = byKey.get(input.key);
      if (!definition) {
        issues.push({ path: `values.${index}.key`, code: "KEY_NOT_IN_SET", message: "Cette définition n’appartient pas à l’ensemble." });
        return;
      }
      if (seen.has(input.key)) {
        issues.push({ path: `values.${index}.key`, code: "DUPLICATE_KEY", message: "Une définition ne peut apparaître qu’une fois." });
        return;
      }
      seen.add(input.key);
      if (input.notApplicable) {
        if (definition.required) {
          issues.push({ path: `values.${index}.notApplicable`, code: "REQUIRED_NOT_APPLICABLE", message: "Une règle obligatoire ne peut pas être déclarée non applicable." });
        }
        values.push({ key: input.key, value: null, notApplicable: true, reason: input.reason ?? null });
        return;
      }
      const result = validateRuleValue({ ...definition, required: true }, input.value);
      if (!result.valid) {
        issues.push(...result.issues.map((issue) => ({ ...issue, path: `values.${index}.value` })));
        return;
      }
      values.push({ key: input.key, value: result.normalizedValue, notApplicable: false, reason: input.reason ?? null });
    });
    if (requireComplete) {
      for (const definition of definitions) {
        if (definition.required && !seen.has(definition.key)) {
          issues.push({ path: "values", code: "MISSING_REQUIRED", message: `La définition obligatoire ${definition.key} est absente.` });
        }
      }
    }
    const normalizedByKey = new Map(values.map((entry) => [entry.key, entry]));
    const min = normalizedByKey.get("academic.grading.scale_min");
    const max = normalizedByKey.get("academic.grading.scale_max");
    if (min && max && !min.notApplicable && !max.notApplicable && (compareExactDecimals(max.value, min.value) ?? -1) <= 0) {
      issues.push({ path: "values", code: "INCOHERENT_SCALE", message: "Le maximum doit être strictement supérieur au minimum." });
    }
    const pass = normalizedByKey.get("academic.pass.mark");
    if (pass && min && max && !pass.notApplicable && !min.notApplicable && !max.notApplicable) {
      const lower = compareExactDecimals(pass.value, min.value);
      const upper = compareExactDecimals(pass.value, max.value);
      if (lower === null || upper === null || lower < 0 || upper > 0) {
        issues.push({ path: "values", code: "PASS_MARK_OUTSIDE_SCALE", message: "Le seuil doit être compris dans le barème." });
      }
    }
    if (issues.length > 0) throw new BadRequestException({ code: ERROR_CODES.VALIDATION_ERROR, issues });
    return values;
  }

  private async normalizeBindings(set: RuleSet, organizationId: string, bindings: readonly RuleBindingInput[]) {
    const order = this.scopeOrder(set);
    const seen = new Set<string>();
    for (const [index, binding] of bindings.entries()) {
      if (!order.includes(binding.scopeType)) this.invalid(`bindings.${index}.scopeType`, "SCOPE_NOT_IN_ORDER", "Cette portée n’appartient pas à l’ordre déclaré par l’ensemble.");
      const key = `${binding.scopeType}:${binding.scopeId}`;
      if (seen.has(key)) this.invalid(`bindings.${index}`, "DUPLICATE_BINDING", "Une portée ne peut être liée qu’une fois.");
      seen.add(key);
      const resource = await this.authorization.findScope({ type: binding.scopeType, id: binding.scopeId }, organizationId);
      if (!resource) this.invalid(`bindings.${index}.scopeId`, "SCOPE_NOT_FOUND", "La ressource de portée est inconnue, archivée ou appartient à une autre organisation.");
    }
    return bindings.map((binding) => ({ ...binding }));
  }

  private async insertChildren(db: Db, versionId: string, values: readonly NormalizedRuleValue[], bindings: readonly RuleBindingInput[]) {
    for (const entry of values) {
      await db.ruleValue.create({
        data: {
          ruleSetVersionId: versionId,
          definitionKey: entry.key,
          value: entry.notApplicable ? Prisma.DbNull : asJson(entry.value),
          notApplicable: entry.notApplicable,
          reason: entry.reason,
        },
      });
    }
    for (const binding of bindings) {
      await db.ruleBinding.create({ data: { ruleSetVersionId: versionId, scopeType: binding.scopeType, scopeId: binding.scopeId } });
    }
  }

  private async assertResolvedComplete(version: VersionWithSet) {
    const baseVersions = await this.publishedVersions(version.ruleSetId);
    const asOf = isoDate(version.effectiveFrom);
    const issues: Array<{ path: string; code: string; message: string }> = [];
    for (const binding of version.bindings) {
      const scope = this.scopeForBinding({ scopeType: binding.scopeType as RuleBindingInput["scopeType"], scopeId: binding.scopeId });
      const result = resolveRules({
        ruleSetId: version.ruleSetId,
        asOf,
        scopeOrder: this.scopeOrder(version.ruleSet),
        definitions: this.definitions(version.ruleSet),
        context: { organizationId: version.ruleSet.organizationId, ...scope },
        versions: baseVersions.map(toResolverVersion),
        candidate: toResolverVersion(version),
      });
      issues.push(...result.missing.map((issue) => ({ path: "values", code: issue.code, message: issue.message })));
      issues.push(...result.conflicts.map((issue) => ({ path: "values", code: issue.code, message: issue.message })));
    }
    if (issues.length > 0) throw new BadRequestException({ code: ERROR_CODES.VALIDATION_ERROR, issues });
  }

  private scopeForBinding(binding: RuleBindingInput): RuleResolutionScope {
    const keyByScope: Record<RuleBindingInput["scopeType"], keyof RuleResolutionScope | null> = {
      organization: null,
      site: "siteId",
      school_year: "schoolYearId",
      level: "levelId",
      section: "sectionId",
      option: "optionId",
      program: "programId",
      class: "classId",
      subject: "subjectId",
    };
    const key = keyByScope[binding.scopeType];
    return key ? { [key]: binding.scopeId } : {};
  }

  private async resolveSet(set: RuleSet, asOf: string, scope: RuleResolutionScope, candidate?: VersionWithSet) {
    const versions = await this.publishedVersions(set.id);
    return resolveRules({
      ruleSetId: set.id,
      asOf,
      scopeOrder: this.scopeOrder(set),
      definitions: this.definitions(set),
      context: { organizationId: set.organizationId, ...scope },
      versions: versions.map(toResolverVersion),
      candidate: candidate ? toResolverVersion(candidate) : undefined,
    });
  }

  private async publishedVersions(ruleSetId: string) {
    return this.prisma.ruleSetVersion.findMany({
      where: { ruleSetId, archivedAt: null, status: { in: [RuleVersionStatus.PUBLISHED, RuleVersionStatus.SCHEDULED] } },
      include: versionChildren,
      orderBy: { version: "asc" },
    });
  }

  private async validateContext(organizationId: string, scope: RuleResolutionScope) {
    const organization = await this.authorization.findScope({ type: "organization", id: organizationId }, organizationId);
    if (!organization) this.notFound();
    const entries: Array<[keyof RuleResolutionScope, RuleBindingInput["scopeType"]]> = [
      ["siteId", "site"], ["schoolYearId", "school_year"], ["levelId", "level"], ["sectionId", "section"],
      ["optionId", "option"], ["programId", "program"], ["classId", "class"], ["subjectId", "subject"],
    ];
    for (const [key, type] of entries) {
      const id = scope[key];
      if (id && !(await this.authorization.findScope({ type, id }, organizationId))) {
        this.invalid(`scope.${key}`, "SCOPE_NOT_FOUND", "La ressource de contexte est inconnue, archivée ou appartient à une autre organisation.");
      }
    }
  }

  private versionInputFromDb(version: VersionWithSet): RuleVersionCreateInput {
    return {
      organizationId: version.ruleSet.organizationId,
      effectiveFrom: isoDate(version.effectiveFrom),
      effectiveTo: version.effectiveTo ? isoDate(version.effectiveTo) : undefined,
      timezone: version.effectiveTimezone,
      source: version.source,
      reason: version.reason,
      values: version.values.map((entry) => ({ key: entry.definitionKey, value: entry.notApplicable ? undefined : entry.value, notApplicable: entry.notApplicable, reason: entry.reason ?? undefined })),
      bindings: version.bindings.map((entry) => ({ scopeType: entry.scopeType as RuleBindingInput["scopeType"], scopeId: entry.scopeId })),
    };
  }

  private assertTransition(version: RuleSetVersion, action: "submit" | "review" | "approve" | "publish" | "archive") {
    if (action === "archive") {
      if (version.status === RuleVersionStatus.ARCHIVED) this.conflict("VERSION_ARCHIVED", "La version est déjà archivée.");
      return;
    }
    const expected = {
      submit: RuleVersionStatus.DRAFT,
      review: RuleVersionStatus.IN_REVIEW,
      approve: RuleVersionStatus.VALIDATED,
      publish: RuleVersionStatus.APPROVED,
    } as const;
    if (version.status !== expected[action]) this.conflict("INVALID_VERSION_TRANSITION", `La transition ${action} n’est pas possible depuis ${version.status}.`);
  }

  private assertDraft(version: RuleSetVersion) {
    if (version.status !== RuleVersionStatus.DRAFT || version.archivedAt) this.conflict("VERSION_NOT_DRAFT", "Seul un brouillon peut être modifié.");
  }

  private simulationCoversCandidate(report: unknown): boolean {
    if (!report || typeof report !== "object" || !("cases" in report) || !Array.isArray(report.cases)) return false;
    return report.cases.some((entry) => typeof entry === "object" && entry !== null && "candidateApplies" in entry && entry.candidateApplies === true);
  }

  private scopeOrder(set: RuleSet) {
    const result = ruleScopeOrderSchema.safeParse(set.scopeOrder);
    if (!result.success) throw new Error("Ordre de portées stocké invalide.");
    return result.data;
  }

  private definitionKeys(set: RuleSet): string[] {
    if (!Array.isArray(set.definitionKeys) || set.definitionKeys.some((key) => typeof key !== "string")) throw new Error("Définitions stockées invalides.");
    return set.definitionKeys as string[];
  }

  private definitions(set: RuleSet): RuleDefinition[] {
    const keys = this.definitionKeys(set);
    return keys.map((key) => {
      const definition = findRuleDefinition(key);
      if (!definition || definition.domain !== set.domain) throw new Error("Catalogue et ensemble de règles incohérents.");
      return definition;
    });
  }

  private validateSetDefinitions(domain: RuleDomain, keys: readonly string[], order: readonly string[]) {
    const issues: Array<{ path: string; code: string; message: string }> = [];
    keys.forEach((key, index) => {
      const definition = findRuleDefinition(key);
      if (!definition || definition.domain !== domain) {
        issues.push({ path: `definitionKeys.${index}`, code: "UNKNOWN_OR_DOMAIN_MISMATCH", message: "Définition inconnue ou appartenant à un autre domaine." });
      } else if (!definition.scopes.some((scope) => order.includes(scope))) {
        issues.push({ path: `definitionKeys.${index}`, code: "NO_COMPATIBLE_SCOPE", message: "Aucune portée de l’ordre n’est autorisée pour cette définition." });
      }
    });
    if (issues.length > 0) throw new BadRequestException({ code: ERROR_CODES.VALIDATION_ERROR, issues });
  }

  private async findSet(organizationId: string, id: string): Promise<RuleSet> {
    const set = await this.prisma.ruleSet.findFirst({ where: { id, organizationId } });
    if (!set) this.notFound();
    return set;
  }

  private async findVersion(organizationId: string, id: string): Promise<VersionWithSet> {
    const version = await this.prisma.ruleSetVersion.findFirst({
      where: { id, ruleSet: { organizationId } },
      include: versionWithSet,
    });
    if (!version) this.notFound();
    return version;
  }

  private async assertDomain(actor: SessionAuthContext, organizationId: string, domain: RuleDomain, action: RuleAction) {
    await this.authorization.assertAuthorized({
      userId: actor.session.userId,
      organizationId,
      permission: domainPermissions[domain][action],
      resourceId: organizationId,
      resourceType: "organization",
    });
  }

  private async recordRevision(db: Db, input: {
    organizationId: string;
    versionId: string;
    action: string;
    actor: SessionAuthContext;
    context: AuthRequestContext;
    reason?: string;
    before?: unknown;
    after: unknown;
  }) {
    await db.ruleRevision.create({
      data: {
        organizationId: input.organizationId,
        ruleSetVersionId: input.versionId,
        action: input.action,
        reason: input.reason,
        actorUserId: input.actor.session.userId,
        requestId: input.context.requestId,
        before: input.before === undefined ? undefined : asJson(input.before),
        after: asJson(input.after),
      },
    });
    await this.audit.record({
      organizationId: input.organizationId,
      actorUserId: input.actor.session.userId,
      action: input.action,
      resourceType: "rule_version",
      resourceId: input.versionId,
      outcome: AuditOutcome.SUCCESS,
      requestId: input.context.requestId,
      reason: input.reason,
      context: { scope: "rules" },
    }, db);
  }

  private invalid(path: string, code: string, message: string): never {
    throw new BadRequestException({ code: ERROR_CODES.VALIDATION_ERROR, issues: [{ path, code, message }] });
  }

  private conflict(code: string, message: string): never {
    throw new ConflictException({ code: ERROR_CODES.CONFLICT, issues: [{ path: "version", code, message }] });
  }

  private notFound(): never {
    throw new NotFoundException({ code: ERROR_CODES.NOT_FOUND });
  }

  private translateDatabaseError(error: unknown): never {
    if (error instanceof BadRequestException || error instanceof ConflictException || error instanceof NotFoundException || error instanceof ForbiddenException) throw error;
    const message = error instanceof Error ? error.message : "";
    if (message.includes("rule_version_overlapping_scope")) this.conflict("OVERLAPPING_VERSION", "Une version incompatible possède déjà cette date d’effet et cette portée.");
    if (message.includes("rule_publication_requires")) this.conflict("PUBLICATION_GUARD", "Les preuves de publication sont absentes ou ne correspondent plus au contenu.");
    if (message.includes("rule_version_immutable") || message.includes("rule_children_immutable")) this.conflict("VERSION_IMMUTABLE", "Une version publiée ou validée ne peut pas être modifiée.");
    if (message.includes("P2002")) this.conflict("DUPLICATE_RULE_RESOURCE", "Un identifiant ou une version équivalente existe déjà.");
    throw error;
  }
}
