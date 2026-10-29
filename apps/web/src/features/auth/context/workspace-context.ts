"use client";

import { createContext } from "react";
import type { RoleDefinition, RoleId } from "../data/roles";
import type { RoleWorkspace } from "../data/workspaces";

export interface WorkspaceContextValue {
  role: RoleId;
  roleDefinition: RoleDefinition;
  workspace: RoleWorkspace;
  hydrated: boolean;
  setRole: (role: RoleId) => void;
}

export const WorkspaceContext = createContext<WorkspaceContextValue | null>(null);
