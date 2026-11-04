export const DEFAULT_POST_LOGIN_PATH = "/";

/** Only same-origin application paths can be used after authentication. */
export function safePostLoginPath(value: string | null | undefined): string {
  if (!value || !value.startsWith("/") || value.startsWith("//") || value.startsWith("/\\")) {
    return DEFAULT_POST_LOGIN_PATH;
  }
  if (value.startsWith("/connexion") || value.includes("\u0000") || value.includes("\\")) {
    return DEFAULT_POST_LOGIN_PATH;
  }
  return value;
}

export function isMfaChallenge(response: { status: string }): response is {
  status: "mfa_required";
  challengeToken: string;
  enrollmentRequired: boolean;
} {
  return response.status === "mfa_required";
}
