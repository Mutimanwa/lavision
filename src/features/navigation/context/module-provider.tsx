"use client";

import { useCallback, useEffect, useMemo, useState, type ReactNode } from "react";
import { useWorkspace } from "@/features/auth/hooks/use-workspace";
import { useSchool } from "@/features/school/hooks/use-school";
import {
  MODULE_DEFINITIONS,
  createDefaultModulePreferences,
  type ModuleId,
  type ModuleDefinition,
  type ModulePreferences,
} from "../data/modules";
import { loadModulePreferences, saveModulePreferences } from "../services/module-storage";
import { resolveModules } from "../services/module-state";
import { ModuleContext } from "./module-context";

export function ModuleProvider({ children }: { children: ReactNode }) {
  const { state } = useSchool();
  const { role } = useWorkspace();
  const [preferences, setPreferences] = useState<ModulePreferences>(createDefaultModulePreferences);
  const [hydrated, setHydrated] = useState(false);
  const canManageModules = role === "direction" || role === "technical_admin";

  useEffect(() => {
    const timer = window.setTimeout(() => {
      setPreferences(loadModulePreferences());
      setHydrated(true);
    }, 0);
    return () => window.clearTimeout(timer);
  }, []);

  useEffect(() => {
    if (hydrated) saveModulePreferences(preferences);
  }, [hydrated, preferences]);

  const setModuleEnabled = useCallback((id: ModuleId, enabled: boolean) => {
    const definition: ModuleDefinition | undefined = MODULE_DEFINITIONS.find((module) => module.id === id);
    if (!canManageModules || !definition?.implemented || definition.core) return;
    setPreferences((current) => ({ ...current, [id]: enabled }));
  }, [canManageModules]);

  const resetModules = useCallback(() => {
    if (canManageModules) setPreferences(createDefaultModulePreferences());
  }, [canManageModules]);

  const modules = useMemo(() => resolveModules(state, preferences), [preferences, state]);
  const value = useMemo(() => ({
    modules,
    hydrated,
    canManageModules,
    setModuleEnabled,
    resetModules,
  }), [canManageModules, hydrated, modules, resetModules, setModuleEnabled]);

  return <ModuleContext.Provider value={value}>{children}</ModuleContext.Provider>;
}
