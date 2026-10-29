import { z } from "zod";
import {
  ruleBindingInputSchema,
  ruleCatalogQuerySchema,
  ruleCatalogResponseSchema,
  ruleDefinitionSchema,
  ruleDiffResponseSchema,
  ruleOrganizationQuerySchema,
  ruleResolveInputSchema,
  ruleResolutionResponseSchema,
  ruleResolutionScopeSchema,
  ruleRevisionResponseSchema,
  ruleRevisionsQuerySchema,
  ruleRevisionsResponseSchema,
  ruleSetCreateInputSchema,
  ruleSetDetailResponseSchema,
  ruleSetListQuerySchema,
  ruleSetResponseSchema,
  ruleSetsResponseSchema,
  ruleSimulationInputSchema,
  ruleSimulationResponseSchema,
  ruleSnapshotInputSchema,
  ruleSnapshotResponseSchema,
  ruleValidationInputSchema,
  ruleValidationIssueSchema,
  ruleValidationResponseSchema,
  ruleValueResponseSchema,
  ruleVersionActionInputSchema,
  ruleVersionCreateInputSchema,
  ruleVersionDuplicateInputSchema,
  ruleVersionResponseSchema,
  ruleVersionUpdateInputSchema,
} from "./rules.js";

const success = (schema: string) => ({
  description: "Réponse conforme au contrat",
  content: { "application/json": { schema: { $ref: `#/components/schemas/${schema}` } } },
});
const failure = (description: string) => ({
  description,
  content: { "application/problem+json": { schema: { $ref: "#/components/schemas/ProblemDetails" } } },
});
const body = (schema: string) => ({
  required: true,
  content: { "application/json": { schema: { $ref: `#/components/schemas/${schema}` } } },
});
const organizationParameter = {
  name: "organizationId",
  in: "query",
  required: true,
  schema: { type: "string", format: "uuid" },
};
const idParameter = {
  name: "id",
  in: "path",
  required: true,
  schema: { type: "string", format: "uuid" },
};
const againstParameter = {
  name: "againstId",
  in: "path",
  required: true,
  schema: { type: "string", format: "uuid" },
};
const readOperation = (operationId: string, response: string, parameters: unknown[] = [organizationParameter]) => ({
  security: [{ sessionCookie: [] }],
  operationId,
  tags: ["Règles #39"],
  parameters,
  responses: {
    "200": success(response),
    "400": failure("Filtre ou identifiant invalide."),
    "401": failure("Session requise."),
    "403": failure("Lecture du domaine refusée."),
    "404": failure("Ressource inconnue dans cette organisation."),
  },
});
const commandOperation = (operationId: string, request: string, response: string, status: "200" | "201" = "200", parameters: unknown[] = [idParameter]) => ({
  security: [{ sessionCookie: [] }],
  operationId,
  tags: ["Règles #39"],
  description: "Mutation protégée par session et CSRF ; la permission du domaine est revérifiée par le service.",
  parameters,
  requestBody: body(request),
  responses: {
    [status]: success(response),
    "400": failure("Entrée, valeur, portée ou configuration invalide ; les issues détaillent le cas échéant."),
    "401": failure("Session requise."),
    "403": failure("Permission du domaine, séparation des tâches ou CSRF refusée."),
    "404": failure("Ressource inconnue dans cette organisation."),
    "409": failure("État, chevauchement, simulation ou immutabilité incompatible."),
  },
});

export function createRulesOpenApiPaths() {
  return {
    "/rules/catalog": {
      get: readOperation("getRuleCatalog", "RuleCatalogResponse", [
        organizationParameter,
        { name: "domain", in: "query", required: false, schema: { type: "string" } },
        { name: "search", in: "query", required: false, schema: { type: "string", maxLength: 100 } },
      ]),
    },
    "/rules/validate": {
      post: commandOperation("validateRuleValue", "RuleValidationInput", "RuleValidationResponse", "200", []),
    },
    "/rules/sets": {
      get: readOperation("listRuleSets", "RuleSetsResponse", [
        organizationParameter,
        { name: "domain", in: "query", required: true, schema: { type: "string" } },
        { name: "includeArchived", in: "query", required: false, schema: { type: "string", enum: ["true", "false"] } },
      ]),
      post: commandOperation("createRuleSet", "RuleSetCreateInput", "RuleSetResponse", "201", []),
    },
    "/rules/sets/{id}": {
      get: readOperation("getRuleSet", "RuleSetDetailResponse", [organizationParameter, idParameter]),
    },
    "/rules/sets/{id}/versions": {
      post: commandOperation("createRuleVersion", "RuleVersionCreateInput", "RuleVersionResponse", "201"),
    },
    "/rules/versions/{id}": {
      get: readOperation("getRuleVersion", "RuleVersionResponse", [organizationParameter, idParameter]),
      patch: commandOperation("updateRuleVersion", "RuleVersionUpdateInput", "RuleVersionResponse"),
    },
    "/rules/versions/{id}/duplicate": {
      post: commandOperation("duplicateRuleVersion", "RuleVersionDuplicateInput", "RuleVersionResponse", "201"),
    },
    "/rules/versions/{id}/submit": {
      post: commandOperation("submitRuleVersion", "RuleVersionActionInput", "RuleVersionResponse"),
    },
    "/rules/versions/{id}/review": {
      post: commandOperation("reviewRuleVersion", "RuleVersionActionInput", "RuleVersionResponse"),
    },
    "/rules/versions/{id}/approve": {
      post: commandOperation("approveRuleVersion", "RuleVersionActionInput", "RuleVersionResponse"),
    },
    "/rules/versions/{id}/publish": {
      post: commandOperation("publishRuleVersion", "RuleVersionActionInput", "RuleVersionResponse"),
    },
    "/rules/versions/{id}/archive": {
      post: commandOperation("archiveRuleVersion", "RuleVersionActionInput", "RuleVersionResponse"),
    },
    "/rules/resolve": {
      post: commandOperation("resolveRules", "RuleResolveInput", "RuleResolutionResponse", "200", []),
    },
    "/rules/versions/{id}/simulate": {
      post: commandOperation("simulateRuleVersion", "RuleSimulationInput", "RuleSimulationResponse", "201"),
    },
    "/rules/snapshots": {
      post: commandOperation("createRuleSnapshot", "RuleSnapshotInput", "RuleSnapshotResponse", "201", []),
    },
    "/rules/snapshots/{id}": {
      get: readOperation("getRuleSnapshot", "RuleSnapshotResponse", [organizationParameter, idParameter]),
    },
    "/rules/versions/{id}/diff/{againstId}": {
      get: readOperation("diffRuleVersions", "RuleDiffResponse", [organizationParameter, idParameter, againstParameter]),
    },
    "/rules/versions/{id}/revisions": {
      get: readOperation("listRuleVersionRevisions", "RuleRevisionsResponse", [
        organizationParameter,
        idParameter,
        { name: "limit", in: "query", required: false, schema: { type: "integer", minimum: 1, maximum: 100, default: 50 } },
      ]),
    },
  };
}

export function createRulesOpenApiSchemas() {
  return {
    RuleDefinition: z.toJSONSchema(ruleDefinitionSchema),
    RuleCatalogQuery: z.toJSONSchema(ruleCatalogQuerySchema),
    RuleCatalogResponse: z.toJSONSchema(ruleCatalogResponseSchema),
    RuleValidationInput: z.toJSONSchema(ruleValidationInputSchema),
    RuleValidationIssue: z.toJSONSchema(ruleValidationIssueSchema),
    RuleValidationResponse: z.toJSONSchema(ruleValidationResponseSchema),
    RuleSetCreateInput: z.toJSONSchema(ruleSetCreateInputSchema),
    RuleSetListQuery: z.toJSONSchema(ruleSetListQuerySchema),
    RuleOrganizationQuery: z.toJSONSchema(ruleOrganizationQuerySchema),
    RuleSetResponse: z.toJSONSchema(ruleSetResponseSchema),
    RuleSetsResponse: z.toJSONSchema(ruleSetsResponseSchema),
    RuleSetDetailResponse: z.toJSONSchema(ruleSetDetailResponseSchema),
    RuleValueResponse: z.toJSONSchema(ruleValueResponseSchema),
    RuleBindingInput: z.toJSONSchema(ruleBindingInputSchema),
    RuleVersionCreateInput: z.toJSONSchema(ruleVersionCreateInputSchema),
    RuleVersionUpdateInput: z.toJSONSchema(ruleVersionUpdateInputSchema),
    RuleVersionActionInput: z.toJSONSchema(ruleVersionActionInputSchema),
    RuleVersionDuplicateInput: z.toJSONSchema(ruleVersionDuplicateInputSchema),
    RuleVersionResponse: z.toJSONSchema(ruleVersionResponseSchema),
    RuleResolutionScope: z.toJSONSchema(ruleResolutionScopeSchema),
    RuleResolveInput: z.toJSONSchema(ruleResolveInputSchema),
    RuleResolutionResponse: z.toJSONSchema(ruleResolutionResponseSchema),
    RuleSimulationInput: z.toJSONSchema(ruleSimulationInputSchema),
    RuleSimulationResponse: z.toJSONSchema(ruleSimulationResponseSchema),
    RuleSnapshotInput: z.toJSONSchema(ruleSnapshotInputSchema),
    RuleSnapshotResponse: z.toJSONSchema(ruleSnapshotResponseSchema),
    RuleDiffResponse: z.toJSONSchema(ruleDiffResponseSchema),
    RuleRevisionsQuery: z.toJSONSchema(ruleRevisionsQuerySchema),
    RuleRevisionResponse: z.toJSONSchema(ruleRevisionResponseSchema),
    RuleRevisionsResponse: z.toJSONSchema(ruleRevisionsResponseSchema),
  };
}
