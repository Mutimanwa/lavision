import { generate } from "otplib";
import { beforeEach, describe, expect, it } from "vitest";
import { AuthConfigService } from "./auth.config.js";
import { AuthCryptoService } from "./auth.crypto.js";

const key = Buffer.alloc(32, 11).toString("base64");

describe("AuthCryptoService", () => {
  beforeEach(() => {
    process.env.AUTH_ENCRYPTION_KEY = key;
    process.env.NODE_ENV = "test";
  });

  it("hash et vérifie les mots de passe avec Argon2id", async () => {
    const crypto = new AuthCryptoService(new AuthConfigService());
    const passwordHash = await crypto.hashPassword("une phrase longue 2026!");

    expect(passwordHash).toContain("$argon2id$");
    expect(passwordHash).not.toContain("une phrase longue 2026!");
    await expect(
      crypto.verifyPassword("une phrase longue 2026!", passwordHash),
    ).resolves.toBe(true);
    await expect(crypto.verifyPassword("incorrect", passwordHash)).resolves.toBe(
      false,
    );
    await expect(crypto.verifyPassword("incorrect", null)).resolves.toBe(false);
    expect(crypto.needsRehash(passwordHash)).toBe(false);
  });

  it("chiffre le secret TOTP et détecte toute modification", () => {
    const crypto = new AuthCryptoService(new AuthConfigService());
    const encrypted = crypto.encryptSecret("JBSWY3DPEHPK3PXP");

    expect(encrypted).not.toContain("JBSWY3DPEHPK3PXP");
    expect(crypto.decryptSecret(encrypted)).toBe("JBSWY3DPEHPK3PXP");
    expect(() => crypto.decryptSecret(`${encrypted}x`)).toThrow();
  });

  it("vérifie TOTP et refuse le rejeu du pas déjà accepté", async () => {
    const crypto = new AuthCryptoService(new AuthConfigService());
    const secret = crypto.createTotpSecret();
    const code = await generate({ secret });
    const first = await crypto.verifyTotp(secret, code);

    expect(first.valid).toBe(true);
    if (!first.valid || !("timeStep" in first)) throw new Error("TOTP invalide");
    const replay = await crypto.verifyTotp(secret, code, first.timeStep);
    expect(replay.valid).toBe(false);
    expect(crypto.createTotpUri("person@example.invalid", secret)).toContain(
      "otpauth://totp/",
    );
  });

  it("génère des codes de récupération uniques et hashables", () => {
    const crypto = new AuthCryptoService(new AuthConfigService());
    const codes = crypto.generateRecoveryCodes();

    expect(codes).toHaveLength(10);
    expect(new Set(codes).size).toBe(10);
    expect(crypto.normalizeRecoveryCode(` ${codes[0]!.toLowerCase()} `)).toBe(
      codes[0]!.replaceAll("-", ""),
    );
    expect(crypto.hashToken(crypto.normalizeRecoveryCode(codes[0]!))).toMatch(
      /^[a-f0-9]{64}$/,
    );
  });
});
