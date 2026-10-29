import { Injectable, ServiceUnavailableException } from "@nestjs/common";
import { ERROR_CODES } from "@lavision/contracts";
import nodemailer, { type Transporter } from "nodemailer";
import { AuthConfigService } from "./auth.config.js";

@Injectable()
export class AuthMailService {
  private readonly transporter: Transporter;

  constructor(private readonly config: AuthConfigService) {
    this.transporter = nodemailer.createTransport(config.smtpUrl);
  }

  async sendInvitation(email: string, token: string): Promise<void> {
    const url = this.url("/auth/accept-invitation", token);
    await this.send({
      to: email,
      subject: "Votre invitation lavision",
      text: [
        "Une invitation lavision vous a été adressée.",
        `Activez votre compte avec ce lien à usage unique : ${url}`,
        "Ce lien expire dans 48 heures. Ne le partagez pas.",
      ].join("\n\n"),
    });
  }

  async sendPasswordReset(email: string, token: string): Promise<void> {
    const url = this.url("/auth/password-reset", token);
    await this.send({
      to: email,
      subject: "Récupération de votre compte lavision",
      text: [
        "Une demande de récupération de compte lavision a été enregistrée.",
        `Choisissez un nouveau mot de passe avec ce lien à usage unique : ${url}`,
        "Ce lien expire dans 30 minutes. Si vous n’êtes pas à l’origine de cette demande, ignorez ce message.",
      ].join("\n\n"),
    });
  }

  private url(path: string, token: string): string {
    return `${this.config.webBaseUrl}${path}?token=${encodeURIComponent(token)}`;
  }

  private async send(message: {
    to: string;
    subject: string;
    text: string;
  }): Promise<void> {
    try {
      await this.transporter.sendMail({
        from: this.config.mailFrom,
        ...message,
      });
    } catch {
      throw new ServiceUnavailableException({ code: ERROR_CODES.MAIL_UNAVAILABLE });
    }
  }
}
