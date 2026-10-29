import { Module } from "@nestjs/common";
import { APP_GUARD } from "@nestjs/core";
import { AuthConfigService } from "./auth.config.js";
import { AuthController } from "./auth.controller.js";
import { AuthCookieService } from "./auth.cookies.js";
import { AuthCryptoService } from "./auth.crypto.js";
import { AuthEventService } from "./auth.events.js";
import { AuthMailService } from "./auth.mail.js";
import { AuthRateLimitService } from "./auth.rate-limit.js";
import { AuthService } from "./auth.service.js";
import { SessionGuard } from "./session.guard.js";

@Module({
  controllers: [AuthController],
  providers: [
    AuthConfigService,
    AuthCookieService,
    AuthCryptoService,
    AuthEventService,
    AuthMailService,
    AuthRateLimitService,
    AuthService,
    SessionGuard,
    { provide: APP_GUARD, useExisting: SessionGuard },
  ],
  exports: [AuthService, SessionGuard],
})
export class AuthModule {}
