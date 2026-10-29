import { isRoleId, type RoleId } from "../data/roles";

const WORKSPACE_STORAGE_KEY = "lavision.workspace.role.v1";

export function loadWorkspaceRole(): RoleId | null {
  try {
    const saved = window.localStorage.getItem(WORKSPACE_STORAGE_KEY);
    if (!saved) return null;
    const parsed: unknown = JSON.parse(saved);
    return isRoleId(parsed) ? parsed : null;
  } catch {
    return null;
  }
}

export function saveWorkspaceRole(role: RoleId): void {
  try {
    window.localStorage.setItem(WORKSPACE_STORAGE_KEY, JSON.stringify(role));
  } catch {
    // The interface preview remains usable when storage is unavailable.
  }
}
