import { describe, expect, it } from "vitest";
import { sanitizeAuditContext } from "./audit.service.js";

describe("minimisation du contexte d’audit", () => {
  it("ne conserve que les clés techniques et les primitives autorisées", () => {
    expect(
      sanitizeAuditContext({
        permission: "notes.write",
        scope: "class",
        version: 2,
        password: "secret",
        nested: { token: "secret" },
        correlationId: "corr-1",
      }),
    ).toEqual({ permission: "notes.write", scope: "class", version: 2, correlationId: "corr-1" });
  });

  it("retourne null pour un contexte non objet ou sans clé autorisée", () => {
    expect(sanitizeAuditContext(null)).toBeNull();
    expect(sanitizeAuditContext(["notes.write"])).toBeNull();
    expect(sanitizeAuditContext({ password: "secret" })).toBeNull();
  });
});
