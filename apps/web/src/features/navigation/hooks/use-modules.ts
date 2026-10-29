"use client";

import { useContext } from "react";
import { ModuleContext } from "../context/module-context";

export function useModules() {
  const context = useContext(ModuleContext);
  if (!context) throw new Error("useModules doit être utilisé dans ModuleProvider");
  return context;
}
