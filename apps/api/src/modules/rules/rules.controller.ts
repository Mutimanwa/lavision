import { Body, Controller, Get, Post, Query, UseGuards } from "@nestjs/common";
import {
  PERMISSIONS,
  ruleCatalogQuerySchema,
  ruleValidationInputSchema,
} from "@lavision/contracts";
import { parseInput } from "../../common/validation.js";
import { CurrentAuth } from "../auth/auth.decorators.js";
import type { SessionAuthContext } from "../auth/auth.types.js";
import { RequirePermission } from "../access/access.decorators.js";
import { PermissionGuard } from "../access/permission.guard.js";
import { RulesService } from "./rules.service.js";

const queryOrganization = { in: "query", key: "organizationId" } as const;
const bodyOrganization = { in: "body", key: "organizationId" } as const;

@Controller("rules")
@UseGuards(PermissionGuard)
export class RulesController {
  constructor(private readonly rules: RulesService) {}

  @Get("catalog")
  @RequirePermission({
    permission: PERMISSIONS.rulesCatalogRead,
    organization: queryOrganization,
    resource: queryOrganization,
    resourceType: "organization",
  })
  catalog(@Query() query: unknown) {
    return this.rules.catalog(parseInput(ruleCatalogQuerySchema, query));
  }

  @Post("validate")
  @RequirePermission({
    permission: PERMISSIONS.rulesCatalogValidate,
    organization: bodyOrganization,
    resource: bodyOrganization,
    resourceType: "organization",
  })
  validate(@CurrentAuth() actor: SessionAuthContext, @Body() body: unknown) {
    return this.rules.validate(actor, parseInput(ruleValidationInputSchema, body));
  }
}
