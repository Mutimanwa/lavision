import { describe, expect, it } from "vitest";
import {
  createOpenApiDocument,
  PERMISSIONS,
  problemDetailsSchema,
  versionResponseSchema,
} from "./index.js";

describe("contrats du socle", () => {
  it("refuse les champs calculés inconnus dans une réponse", () => {
    const result = versionResponseSchema.safeParse({
      name: "lavision-api",
      version: "0.1.0",
      apiVersion: "v1",
      computedRank: 1,
    });

    expect(result.success).toBe(false);
  });

  it("sépare les permissions des référentiels et des règles", () => {
    expect(PERMISSIONS.referentialsAcademicReview).not.toBe(
      PERMISSIONS.rulesAcademicReview,
    );
    expect(PERMISSIONS.referentialsAcademicApprove).not.toBe(
      PERMISSIONS.rulesAcademicApprove,
    );
    expect(Object.values(PERMISSIONS)).not.toContain("rules.*");
  });

  it("génère OpenAPI 3.1 sans base de données", () => {
    const document = createOpenApiDocument();

    expect(document.openapi).toBe("3.1.0");
    expect(document.servers[0]?.url).toBe("/api/v1");
    expect(Object.keys(document.paths)).toEqual(
      expect.arrayContaining([
        "/health/live",
        "/health/ready",
        "/version",
        "/auth/login",
        "/auth/mfa/verify",
        "/auth/sessions/{id}",
      ]),
    );
    expect(document.components.schemas.ProblemDetails).toBeDefined();
    expect(document.components.schemas.MfaRequiredResponse).toBeDefined();
    expect(document.components.securitySchemes.sessionCookie).toBeDefined();
  });

  it("valide le contrat d’erreur RFC 9457 étendu", () => {
    const problem = problemDetailsSchema.parse({
      type: "about:blank",
      title: "Non trouvé",
      status: 404,
      detail: "La ressource demandée est introuvable.",
      code: "NOT_FOUND",
      instance: "/api/v1/unknown",
      requestId: "5f0fc5f4-0d84-4cf3-9c6e-ef5a1f1c184f",
    });

    expect(problem.code).toBe("NOT_FOUND");
  });
});
