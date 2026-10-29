import { Module } from "@nestjs/common";
import { AccessModule } from "../access/access.module.js";
import { CalendarController } from "./calendar.controller.js";
import { CalendarService } from "./calendar.service.js";
import { SchoolController } from "./school.controller.js";
import { SchoolHistoryService } from "./school-history.service.js";
import { SchoolService } from "./school.service.js";

@Module({
  imports: [AccessModule],
  controllers: [SchoolController, CalendarController],
  providers: [SchoolService, CalendarService, SchoolHistoryService],
  exports: [SchoolService, CalendarService, SchoolHistoryService],
})
export class SchoolModule {}
