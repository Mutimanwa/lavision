import { createOpenApiDocument } from "@lavision/contracts";
import { Controller, Get } from "@nestjs/common";

@Controller()
export class OpenApiController {
  @Get("openapi.json")
  getDocument(): ReturnType<typeof createOpenApiDocument> {
    return createOpenApiDocument();
  }
}
