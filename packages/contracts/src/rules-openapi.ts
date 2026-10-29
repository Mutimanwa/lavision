import { z } from "zod";
import {
  ruleCatalogQuerySchema,
  ruleCatalogResponseSchema,
  ruleDefinitionSchema,
  ruleValidationInputSchema,
  ruleValidationIssueSchema,
  ruleValidationResponseSchema,
} from "./rules.js";

const success = (schema: string) => ({
  description: "Réponse conforme au contrat",
  content: { "application/json": { schema: { $ref: `#/components/schemas/${schema}` } } },
});
const failure = (description: string) => ({
  description,
  content: { "application/problem+json": { schema: { $ref: "#/components/schemas/ProblemDetails" } } },
});
const organizationParameter = {
  name: "organizationId",
  in: "query",
  required: true,
  schema: { type: "string", format: "uuid" },
};

export function createRulesOpenApiPaths() {
  return {
    "/rules/catalog": {
      get: {
        security: [{ sessionCookie: [] }],
        operationId: "getRuleCatalog",
        tags: ["Règles #39"],
        parameters: [
          organizationParameter,
          { name: "domain", in: "query", required: false, schema: { type: "string" } },
          { name: "search", in: "query", required: false, schema: { type: "string", maxLength: 100 } },
        ],
        responses: {
          "200": success("RuleCatalogResponse"),
          "400": failure("Filtre invalide."),
          "401": failure("Session requise."),
          "403": failure("Lecture du catalogue refusée."),
        },
      },
    },
    "/rules/validate": {
      post: {
        security: [{ sessionCookie: [] }],
        operationId: "validateRuleValue",
        tags: ["Règles #39"],
        description:
          "Validation sans persistance ni calcul. Le header x-csrf-token est requis. La permission du domaine de la définition est vérifiée en plus de la permission de validation.",
        requestBody: {
          required: true,
          content: { "application/json": { schema: { $ref: "#/components/schemas/RuleValidationInput" } } },
        },
        responses: {
          "200": success("RuleValidationResponse"),
          "400": failure("Corps de requête invalide."),
          "401": failure("Session requise."),
          "403": failure("Permission du domaine ou CSRF refusée."),
        },
      },
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
  };
}
