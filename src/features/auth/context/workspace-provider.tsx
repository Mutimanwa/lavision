"use client";

import { useEffect, useMemo, useState, type ReactNode } from "react";
import { getRoleDefinition, type RoleId } from "../data/roles";
import { ROLE_WORKSPACES } from "../data/workspaces";
import { loadWorkspaceRole, saveWorkspaceRole } from "../services/workspace-storage";
import { WorkspaceContext } from "./workspace-context";

export function WorkspaceProvider({ children }: { children: ReactNode }) {
  const [role, setRole] = useState<RoleId>("direction");
  const [hydrated, setHydrated] = useState(false);

  useEffect(() => {
    const timer = window.setTimeout(() => {
      const saved = loadWorkspaceRole();
      if (saved) setRole(saved);
      setHydrated(true);
    }, 0);
    return () => window.clearTimeout(timer);
  }, []);

  useEffect(() => {
    if (hydrated) saveWorkspaceRole(role);
  }, [hydrated, role]);

  const value = useMemo(() => ({
    role,
    roleDefinition: getRoleDefinition(role),
    workspace: ROLE_WORKSPACES[role],
    hydrated,
    setRole,
  }), [hydrated, role]);

  return <WorkspaceContext.Provider value={value}>{children}</WorkspaceContext.Provider>;
}
