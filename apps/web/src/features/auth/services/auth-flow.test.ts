import { describe, expect, it } from "vitest";
import { DEFAULT_POST_LOGIN_PATH, isMfaChallenge, safePostLoginPath } from "./auth-flow";

describe("retour après authentification", () => {
  it("accepte uniquement une route interne de l’application", () => {
    expect(safePostLoginPath("/parametres/")).toBe("/parametres/");
    expect(safePostLoginPath("/notes/?periode=P1")).toBe("/notes/?periode=P1");
  });

  it("refuse les destinations externes, ambiguës ou de connexion", () => {
    for (const value of [null, undefined, "", "https://example.com", "//example.com", "/\\example.com", "/connexion", "/connexion/?next=/", "/parametres\\admin", "/parametres\u0000"]) {
      expect(safePostLoginPath(value)).toBe(DEFAULT_POST_LOGIN_PATH);
    }
  });

  it("reconnaît uniquement une réponse de défi MFA", () => {
    expect(isMfaChallenge({ status: "mfa_required" })).toBe(true);
    expect(isMfaChallenge({ status: "authenticated" })).toBe(false);
  });
});
