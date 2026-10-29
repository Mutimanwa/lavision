import { createCipheriv, createDecipheriv, createHash, randomBytes } from "node:crypto";
import { Injectable } from "@nestjs/common";
import {
  Algorithm,
  hash as argon2Hash,
  parseOptions,
  verify as argon2Verify,
} from "@node-rs/argon2";
import { generateSecret, generateURI, verify as verifyOtp } from "otplib";
import { AUTH_POLICY, AuthConfigService } from "./auth.config.js";

@Injectable()
export class AuthCryptoService {
  private dummyHash?: Promise<string>;

  constructor(private readonly config: AuthConfigService) {}

  async hashPassword(password: string): Promise<string> {
    return argon2Hash(password, this.argonOptions());
  }

  async verifyPassword(password: string, passwordHash?: string | null): Promise<boolean> {
    const candidate =
      passwordHash ??
      (await (this.dummyHash ??= argon2Hash(
        "lavision-dummy-password-not-a-user",
        this.argonOptions(),
      )));
    const valid = await argon2Verify(candidate, password).catch(() => false);
    return passwordHash ? valid : false;
  }

  needsRehash(passwordHash: string): boolean {
    try {
      const options = parseOptions(passwordHash);
      return (
        options.algorithm !== Algorithm.Argon2id ||
        options.memoryCost !== AUTH_POLICY.argon2.memoryCost ||
        options.timeCost !== AUTH_POLICY.argon2.timeCost ||
        options.parallelism !== AUTH_POLICY.argon2.parallelism
      );
    } catch {
      return true;
    }
  }

  createOpaqueToken(): string {
    return randomBytes(32).toString("base64url");
  }

  hashToken(token: string): string {
    return createHash("sha256").update(token).digest("hex");
  }

  encryptSecret(secret: string): string {
    const iv = randomBytes(12);
    const cipher = createCipheriv("aes-256-gcm", this.config.encryptionKey, iv);
    const encrypted = Buffer.concat([cipher.update(secret, "utf8"), cipher.final()]);
    return ["v1", iv, cipher.getAuthTag(), encrypted]
      .map((part) => (Buffer.isBuffer(part) ? part.toString("base64url") : part))
      .join(".");
  }

  decryptSecret(payload: string): string {
    const [version, ivValue, tagValue, encryptedValue] = payload.split(".");
    if (version !== "v1" || !ivValue || !tagValue || !encryptedValue) {
      throw new Error("Credential MFA chiffré invalide.");
    }

    const decipher = createDecipheriv(
      "aes-256-gcm",
      this.config.encryptionKey,
      Buffer.from(ivValue, "base64url"),
    );
    decipher.setAuthTag(Buffer.from(tagValue, "base64url"));
    return Buffer.concat([
      decipher.update(Buffer.from(encryptedValue, "base64url")),
      decipher.final(),
    ]).toString("utf8");
  }

  createTotpSecret(): string {
    return generateSecret();
  }

  createTotpUri(email: string, secret: string): string {
    return generateURI({ issuer: "lavision", label: email, secret });
  }

  async verifyTotp(
    secret: string,
    token: string,
    lastAcceptedTimeStep?: number | null,
  ) {
    return verifyOtp({
      secret,
      token,
      epochTolerance: [30, 0],
      afterTimeStep: lastAcceptedTimeStep ?? undefined,
    });
  }

  generateRecoveryCodes(): string[] {
    return Array.from({ length: AUTH_POLICY.recoveryCodeCount }, () => {
      const value = randomBytes(10).toString("hex").toUpperCase();
      return value.match(/.{1,4}/g)!.join("-");
    });
  }

  normalizeRecoveryCode(code: string): string {
    return code.replace(/[\s-]/g, "").toUpperCase();
  }

  private argonOptions() {
    return {
      algorithm: Algorithm.Argon2id,
      memoryCost: AUTH_POLICY.argon2.memoryCost,
      timeCost: AUTH_POLICY.argon2.timeCost,
      parallelism: AUTH_POLICY.argon2.parallelism,
    };
  }
}
