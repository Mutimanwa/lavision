import { Module } from "@nestjs/common";
import { InfrastructureModule } from "./modules/infrastructure/infrastructure.module.js";
import { SystemModule } from "./modules/system/system.module.js";

@Module({
  imports: [InfrastructureModule, SystemModule],
})
export class AppModule {}
