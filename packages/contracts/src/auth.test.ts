import { describe, expect, it } from "vitest";
import {
  authenticatedResponseSchema,
  invitationInputSchema,
  loginInputSchema,
  mfaVerifyInputSchema,
  ROLE_IDS,
} from "./auth.js";

describe("contrats d’authentification", () => {
  it("normalise l’email et refuse les champs inattendus", () => {
    expect(
      loginInputSchema.parse({
        email: "  PERSON@EXAMPLE.INVALID ",
        password: "secret",
      }),
    ).toEqual({ email: "person@example.invalid", password: "secret" });
    expect(
      loginInputSchema.safeParse({
        email: "person@example.invalid",
        password: "secret",
        sessionToken: "ne-pas-accepter",
      }).success,
    ).toBe(false);
  });

  it("exige un mot de passe long et des rôles connus sans doublon", () => {
    expect(ROLE_IDS).toHaveLength(13);
    expect(
      invitationInputSchema.safeParse({
        organizationId: "00000000-0000-4000-8000-000000000001",
        email: "person@example.invalid",
        displayName: "Personne Test",
        roles: ["teacher"],
        password: "court",
      }).success,
    ).toBe(false);
    expect(
      invitationInputSchema.safeParse({
        organizationId: "00000000-0000-4000-8000-000000000001",
        email: "person@example.invalid",
        displayName: "Personne Test",
        roles: ["teacher", "teacher"],
      }).success,
    ).toBe(false);
  });

  it("valide le format MFA et rejette une réponse contenant un secret de session", () => {
    expect(
      mfaVerifyInputSchema.safeParse({
        challengeToken: "a".repeat(43),
        code: "123456",
      }).success,
    ).toBe(true);
    const response = {
      status: "authenticated",
      user: {
        id: "00000000-0000-4000-8000-000000000001",
        email: "person@example.invalid",
        displayName: "Personne Test",
        status: "ACTIVE",
      },
      session: {
        id: "00000000-0000-4000-8000-000000000002",
        createdAt: new Date().toISOString(),
        lastSeenAt: new Date().toISOString(),
        expiresAt: new Date().toISOString(),
        absoluteExpiresAt: new Date().toISOString(),
        mfaVerifiedAt: null,
        userAgent: null,
        current: true,
      },
      token: "ne-pas-exposer",
    };
    expect(authenticatedResponseSchema.safeParse(response).success).toBe(false);
  });
});
