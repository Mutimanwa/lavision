import { beforeEach, describe, expect, it, vi } from "vitest";
import type { AuthenticatedResponse, MfaRequiredResponse, MfaSetupResponse } from "@lavision/contracts";
import { apiFetch } from "@/lib/api-client";
import { confirmMfaSetup, login, logout, recoverMfa, setupMfa, verifyMfa } from "./auth-api";

vi.mock("@/lib/api-client", () => ({ apiFetch: vi.fn() }));

const authenticated = { status: "authenticated" } as AuthenticatedResponse;
const challenge: MfaRequiredResponse = { status: "mfa_required", challengeToken: "challenge-1", enrollmentRequired: false };
const setup: MfaSetupResponse = { status: "mfa_setup", secret: "secret-1", otpauthUrl: "otpauth://totp/lavision" };

beforeEach(() => {
  vi.mocked(apiFetch).mockReset();
});

describe("contrat du parcours d’authentification", () => {
  it("envoie les identifiants à la connexion sans fabriquer de session locale", async () => {
    vi.mocked(apiFetch).mockResolvedValue(authenticated);
    await expect(login({ email: "agent@example.cd", password: "un-mot-de-passe-long" })).resolves.toBe(authenticated);
    expect(apiFetch).toHaveBeenCalledWith("/auth/login", {
      method: "POST",
      body: JSON.stringify({ email: "agent@example.cd", password: "un-mot-de-passe-long" }),
    });
  });

  it("conserve le défi MFA et appelle la préparation TOTP", async () => {
    vi.mocked(apiFetch).mockResolvedValueOnce(challenge).mockResolvedValueOnce(setup);
    await expect(login({ email: "agent@example.cd", password: "un-mot-de-passe-long" })).resolves.toBe(challenge);
    await expect(setupMfa(challenge.challengeToken)).resolves.toBe(setup);
    expect(apiFetch).toHaveBeenNthCalledWith(2, "/auth/mfa/setup", {
      method: "POST",
      body: JSON.stringify({ challengeToken: challenge.challengeToken }),
    });
  });

  it("distingue vérification, activation et récupération", async () => {
    vi.mocked(apiFetch).mockResolvedValue(authenticated);
    await verifyMfa({ challengeToken: "challenge-1", code: "123456" });
    await confirmMfaSetup({ challengeToken: "challenge-1", code: "123456" });
    await recoverMfa({ challengeToken: "challenge-1", recoveryCode: "recup-1234" });
    expect(apiFetch).toHaveBeenNthCalledWith(1, "/auth/mfa/verify", { method: "POST", body: JSON.stringify({ challengeToken: "challenge-1", code: "123456" }) });
    expect(apiFetch).toHaveBeenNthCalledWith(2, "/auth/mfa/setup/confirm", { method: "POST", body: JSON.stringify({ challengeToken: "challenge-1", code: "123456" }) });
    expect(apiFetch).toHaveBeenNthCalledWith(3, "/auth/mfa/recovery", { method: "POST", body: JSON.stringify({ challengeToken: "challenge-1", recoveryCode: "recup-1234" }) });
  });

  it("utilise l’endpoint de déconnexion de l’API", async () => {
    vi.mocked(apiFetch).mockResolvedValue(null);
    await logout();
    expect(apiFetch).toHaveBeenCalledWith("/auth/logout", { method: "POST" });
  });
});
