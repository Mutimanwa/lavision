import type {
  AuthenticatedResponse,
  LoginInput,
  MfaRequiredResponse,
  MfaRecoveryInput,
  MfaSetupResponse,
  MfaVerifyInput,
} from "@lavision/contracts";
import { apiFetch } from "@/lib/api-client";

export type LoginResponse = AuthenticatedResponse | MfaRequiredResponse;
export type MfaSetupConfirmResponse = AuthenticatedResponse & { recoveryCodes: string[] };

export function login(input: LoginInput) {
  return apiFetch<LoginResponse>("/auth/login", { method: "POST", body: JSON.stringify(input) });
}

export function setupMfa(challengeToken: string) {
  return apiFetch<MfaSetupResponse>("/auth/mfa/setup", {
    method: "POST",
    body: JSON.stringify({ challengeToken }),
  });
}

export function verifyMfa(input: MfaVerifyInput) {
  return apiFetch<AuthenticatedResponse>("/auth/mfa/verify", { method: "POST", body: JSON.stringify(input) });
}

export function confirmMfaSetup(input: MfaVerifyInput) {
  return apiFetch<MfaSetupConfirmResponse>("/auth/mfa/setup/confirm", {
    method: "POST",
    body: JSON.stringify(input),
  });
}

export function recoverMfa(input: MfaRecoveryInput) {
  return apiFetch<AuthenticatedResponse>("/auth/mfa/recovery", { method: "POST", body: JSON.stringify(input) });
}

export function logout() {
  return apiFetch<null>("/auth/logout", { method: "POST" });
}
