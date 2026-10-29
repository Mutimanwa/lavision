"use client";

import { createContext } from "react";
import type { ModuleId } from "../data/modules";
import type { ResolvedModule } from "../services/module-state";

export interface ModuleContextValue {
  modules: ResolvedModule[];
  hydrated: boolean;
  canManageModules: boolean;
  setModuleEnabled: (id: ModuleId, enabled: boolean) => void;
  resetModules: () => void;
}

export const ModuleContext = createContext<ModuleContextValue | null>(null);
