import { Injectable } from "@nestjs/common";

export const AUTH_POLICY = {
  sessionIdleSeconds: 8 * 60 * 60,
  sessionAbsoluteSeconds: 7 * 24 * 60 * 60,
  invitationSeconds: 48 * 60 * 60,
  passwordResetSeconds: 30 * 60,
  mfaChallengeSeconds: 5 * 60,
  mfaMaxAttempts: 5,
  rateWindowSeconds: 15 * 60,
  loginAccountThreshold: 5,
  loginIpThreshold: 20,
  recoveryCodeCount: 10,
  argon2: {
    memoryCost: 19_456,
    timeCost: 2,
    parallelism: 1,
  },
} as const;

@Injectable()
export class AuthConfigService {
  readonly encryptionKey: Buffer;
  readonly cookieSecure: boolean;
  readonly webBaseUrl: string;
  readonly smtpUrl: string;
  readonly mailFrom: string;

  constructor() {
    const encodedKey = process.env.AUTH_ENCRYPTION_KEY;
    if (!encodedKey) {
      throw new Error("AUTH_ENCRYPTION_KEY est requis pour le module d’authentification.");
    }

    const key = Buffer.from(encodedKey, "base64");
    if (key.length !== 32 || key.toString("base64") !== encodedKey) {
      throw new Error("AUTH_ENCRYPTION_KEY doit contenir 32 octets encodés en base64.");
    }
    this.encryptionKey = key;

    const cookieSetting = process.env.AUTH_COOKIE_SECURE;
    if (cookieSetting && !["true", "false"].includes(cookieSetting)) {
      throw new Error("AUTH_COOKIE_SECURE doit être true ou false.");
    }
    this.cookieSecure = cookieSetting
      ? cookieSetting === "true"
      : process.env.NODE_ENV === "production";

    const baseUrl = process.env.AUTH_WEB_BASE_URL ?? "http://localhost:3000";
    const parsedUrl = new URL(baseUrl);
    if (!["http:", "https:"].includes(parsedUrl.protocol)) {
      throw new Error("AUTH_WEB_BASE_URL doit utiliser HTTP ou HTTPS.");
    }
    this.webBaseUrl = baseUrl.replace(/\/$/, "");
    this.smtpUrl = process.env.SMTP_URL ?? "smtp://localhost:1025";
    this.mailFrom = process.env.AUTH_MAIL_FROM ?? "lavision <no-reply@localhost>";
  }
}
