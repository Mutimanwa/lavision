import { Controller, Get } from "@nestjs/common";
import {
  API_VERSION,
  SERVICE_NAME,
  type VersionResponse,
} from "@lavision/contracts";

@Controller("version")
export class VersionController {
  @Get()
  getVersion(): VersionResponse {
    return {
      name: SERVICE_NAME,
      version: process.env.APP_VERSION ?? "0.1.0",
      apiVersion: API_VERSION,
    };
  }
}
