import { Module } from "@nestjs/common";
import { AuthModule } from "./modules/auth/auth.module.js";
import { InfrastructureModule } from "./modules/infrastructure/infrastructure.module.js";
import { SystemModule } from "./modules/system/system.module.js";

@Module({
  imports: [InfrastructureModule, AuthModule, SystemModule],
})
export class AppModule {}
