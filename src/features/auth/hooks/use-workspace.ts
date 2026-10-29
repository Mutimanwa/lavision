"use client";

import { useContext } from "react";
import { WorkspaceContext } from "../context/workspace-context";

export function useWorkspace() {
  const context = useContext(WorkspaceContext);
  if (!context) throw new Error("useWorkspace doit être utilisé dans WorkspaceProvider");
  return context;
}
