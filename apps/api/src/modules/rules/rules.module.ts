import { Module } from "@nestjs/common";
import { AccessModule } from "../access/access.module.js";
import { RulesController } from "./rules.controller.js";
import { RulesService } from "./rules.service.js";

@Module({
  imports: [AccessModule],
  controllers: [RulesController],
  providers: [RulesService],
  exports: [RulesService],
})
export class RulesModule {}
