import { createOpenApiDocument } from "@lavision/contracts";
import { Controller, Get } from "@nestjs/common";
import { Public } from "../auth/auth.decorators.js";

@Public()
@Controller()
export class OpenApiController {
  @Get("openapi.json")
  getDocument(): ReturnType<typeof createOpenApiDocument> {
    return createOpenApiDocument();
  }
}
