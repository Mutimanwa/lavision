import { z } from "zod";
import {
  API_PREFIX,
  livenessResponseSchema,
  problemDetailsSchema,
  readinessResponseSchema,
  versionResponseSchema,
} from "./system.js";

const success = (schema: string) => ({
  description: "Réponse conforme au contrat",
  content: {
    "application/json": {
      schema: { $ref: `#/components/schemas/${schema}` },
    },
  },
});

const failure = (description: string) => ({
  description,
  content: {
    "application/problem+json": {
      schema: { $ref: "#/components/schemas/ProblemDetails" },
    },
  },
});

export function createOpenApiDocument() {
  return {
    openapi: "3.1.0",
    info: {
      title: "lavision API",
      version: "0.1.0",
      description:
        "Socle technique M1. Aucune route métier publique n’est exposée avant l’authentification et les contrôles RBAC.",
    },
    servers: [{ url: API_PREFIX }],
    paths: {
      "/health/live": {
        get: {
          operationId: "getLiveness",
          tags: ["Système"],
          responses: { "200": success("LivenessResponse") },
        },
      },
      "/health/ready": {
        get: {
          operationId: "getReadiness",
          tags: ["Système"],
          responses: {
            "200": success("ReadinessResponse"),
            "503": failure("PostgreSQL ne répond pas."),
          },
        },
      },
      "/version": {
        get: {
          operationId: "getVersion",
          tags: ["Système"],
          responses: { "200": success("VersionResponse") },
        },
      },
    },
    components: {
      schemas: {
        LivenessResponse: z.toJSONSchema(livenessResponseSchema),
        ReadinessResponse: z.toJSONSchema(readinessResponseSchema),
        VersionResponse: z.toJSONSchema(versionResponseSchema),
        ProblemDetails: z.toJSONSchema(problemDetailsSchema),
      },
    },
  };
}
