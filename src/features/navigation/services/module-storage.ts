import { MODULE_DEFINITIONS, createDefaultModulePreferences, type ModulePreferences } from "../data/modules";

const MODULE_STORAGE_KEY = "lavision.modules.v1";

export function loadModulePreferences(): ModulePreferences {
  const defaults = createDefaultModulePreferences();
  try {
    const saved = window.localStorage.getItem(MODULE_STORAGE_KEY);
    if (!saved) return defaults;
    const parsed: unknown = JSON.parse(saved);
    if (!parsed || typeof parsed !== "object" || Array.isArray(parsed)) return defaults;
    const values = parsed as Record<string, unknown>;
    const preferences = { ...defaults };
    for (const definition of MODULE_DEFINITIONS) {
      const value = values[definition.id];
      if (typeof value === "boolean") preferences[definition.id] = value;
    }
    return preferences;
  } catch {
    return defaults;
  }
}

export function saveModulePreferences(preferences: ModulePreferences): void {
  try {
    window.localStorage.setItem(MODULE_STORAGE_KEY, JSON.stringify(preferences));
  } catch {
    // Navigation remains usable when storage is blocked or full.
  }
}
