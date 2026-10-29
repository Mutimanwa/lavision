import type { AppState } from "@/lib/types";
import { isAppState } from "@/lib/utils";

export const SCHOOL_STORAGE_KEY = "lavision.school.v1";

export function loadSchoolState(): AppState | null {
  try {
    const saved = window.localStorage.getItem(SCHOOL_STORAGE_KEY);
    if (!saved) return null;
    const parsed: unknown = JSON.parse(saved);
    return isAppState(parsed) ? parsed : null;
  } catch {
    // A corrupted local save must not prevent access to the application.
    return null;
  }
}

export function saveSchoolState(state: AppState): void {
  try {
    window.localStorage.setItem(SCHOOL_STORAGE_KEY, JSON.stringify(state));
  } catch {
    // The UI remains usable when storage is blocked or full.
  }
}
