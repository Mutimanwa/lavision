import type { AppState } from "@/lib/types";
import type { RoleId } from "@/features/auth/data/roles";
import {
  MODULE_DEFINITIONS,
  type ModuleDefinition,
  type ModulePreferences,
  type ModuleStatus,
} from "../data/modules";

export interface ResolvedModule extends ModuleDefinition {
  enabled: boolean;
  status: ModuleStatus;
  missingPrerequisites: string[];
}

export function getMissingPrerequisites(module: ModuleDefinition, state: AppState): string[] {
  if (!module.implemented || module.core) return [];
  const missing: string[] = [];
  const activeStudents = state.students.filter((student) => student.status === "Actif").length;

  if (module.id === "enrollments" && state.classes.length === 0) {
    missing.push("Créez au moins une classe avant d’inscrire un élève.");
  }
  if (module.id === "attendance") {
    if (state.classes.length === 0) missing.push("Créez une classe pour pouvoir faire l’appel.");
    if (activeStudents === 0) missing.push("Inscrivez des élèves actifs pour pouvoir faire l’appel.");
  }
  if (["grades", "reports", "proclamation"].includes(module.id)) {
    if (activeStudents === 0) missing.push("Inscrivez des élèves actifs pour calculer les résultats.");
    if (state.subjects.length === 0) missing.push("Ajoutez au moins une matière au référentiel.");
  }
  if (module.id === "proclamation" && state.grades.length === 0) {
    missing.push("Enregistrez des notes avant de préparer une proclamation.");
  }
  return missing;
}

export function resolveModule(
  module: ModuleDefinition,
  state: AppState,
  preferences: ModulePreferences = {},
): ResolvedModule {
  const enabled = module.core || (preferences[module.id] ?? module.implemented);
  const missingPrerequisites = enabled ? getMissingPrerequisites(module, state) : [];
  const status: ModuleStatus = !module.implemented
    ? "planned"
    : !enabled
      ? "disabled"
      : missingPrerequisites.length > 0
        ? "configuring"
        : "active";
  return { ...module, enabled, status, missingPrerequisites };
}

export function resolveModules(state: AppState, preferences: ModulePreferences): ResolvedModule[] {
  return MODULE_DEFINITIONS.map((module) => resolveModule(module, state, preferences));
}

export function canRoleOpenModule(module: ResolvedModule, role: RoleId): boolean {
  return module.roles.includes(role) && (module.status === "active" || module.status === "configuring");
}

function normalizePath(path: string): string {
  const pathname = path.split(/[?#]/, 1)[0];
  return pathname === "/" ? pathname : pathname.replace(/\/+$/, "");
}

export function findModuleByHref(modules: readonly ResolvedModule[], href: string): ResolvedModule | undefined {
  const normalized = normalizePath(href);
  return modules.find((module) => module.href && normalizePath(module.href) === normalized);
}

export function getRoleModuleHref(
  modules: readonly ResolvedModule[],
  role: RoleId,
  href: string,
  fallback = "/modules/",
): string {
  const linkedModule = findModuleByHref(modules, href);
  return linkedModule && !canRoleOpenModule(linkedModule, role) ? fallback : href;
}
