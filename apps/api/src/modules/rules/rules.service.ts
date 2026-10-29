import { Injectable } from "@nestjs/common";
import {
  PERMISSIONS,
  RULE_CATALOG_VERSION,
  RULE_DEFINITIONS,
  type Permission,
  type RuleCatalogQuery,
  type RuleDomain,
  type RuleDefinition,
  type RuleValidationInput,
} from "@lavision/contracts";
import { AuthorizationService } from "../access/authorization.service.js";
import type { SessionAuthContext } from "../auth/auth.types.js";
import { findRuleDefinition, validateRuleValue } from "./rule-catalog.js";

const domainReadPermissions: Record<RuleDomain, Permission> = {
  academic: PERMISSIONS.rulesAcademicRead,
  attendance: PERMISSIONS.rulesAttendanceRead,
  finance: PERMISSIONS.rulesFinanceRead,
  hr: PERMISSIONS.rulesHrRead,
  payroll: PERMISSIONS.rulesPayrollRead,
  documents: PERMISSIONS.rulesDocumentsRead,
  calendar: PERMISSIONS.rulesCalendarRead,
};

@Injectable()
export class RulesService {
  constructor(private readonly authorization: AuthorizationService) {}

  catalog(input: RuleCatalogQuery) {
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

    await this.authorization.assertAuthorized({
      userId: actor.session.userId,
      organizationId: input.organizationId,
      permission: domainReadPermissions[definition.domain],
      resourceId: input.organizationId,
      resourceType: "organization",
    });
    const result = validateRuleValue(definition, input.value);
    return { key: input.key, ...result };
  }
}
