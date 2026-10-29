import { Module } from "@nestjs/common";
import { AccessController } from "./access.controller.js";
import { AccessService } from "./access.service.js";
import { AuditService } from "./audit.service.js";
import { AuthorizationService } from "./authorization.service.js";
import { PermissionGuard } from "./permission.guard.js";

@Module({
  controllers: [AccessController],
  providers: [AccessService, AuditService, AuthorizationService, PermissionGuard],
  exports: [AccessService, AuditService, AuthorizationService, PermissionGuard],
})
export class AccessModule {}
