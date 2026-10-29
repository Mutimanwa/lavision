import { SetMetadata } from "@nestjs/common";
import type { PermissionRequirement } from "./access.types.js";

export const PERMISSION_REQUIREMENT = "access:permission-requirement";

export const RequirePermission = (requirement: PermissionRequirement) =>
  SetMetadata(PERMISSION_REQUIREMENT, requirement);
