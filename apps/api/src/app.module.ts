import { Module } from "@nestjs/common";
import { AccessModule } from "./modules/access/access.module.js";
import { AuthModule } from "./modules/auth/auth.module.js";
import { InfrastructureModule } from "./modules/infrastructure/infrastructure.module.js";
import { SystemModule } from "./modules/system/system.module.js";
import { SchoolModule } from "./modules/school/school.module.js";

@Module({
  imports: [InfrastructureModule, AuthModule, AccessModule, SchoolModule, SystemModule],
})
export class AppModule {}
