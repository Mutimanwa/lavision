import { ROLE_DEFINITIONS, type RoleId } from "@/features/auth/data/roles";

export type GuideStatus = "in_progress" | "completed" | "skipped";

export interface GuideProgress {
  step: number;
  status: GuideStatus;
}

export type GuideProgressMap = Partial<Record<RoleId, GuideProgress>>;

const ONBOARDING_STORAGE_KEY = "lavision.onboarding.v1";
const GUIDE_STEP_COUNT = 4;

function isStatus(value: unknown): value is GuideStatus {
  return value === "in_progress" || value === "completed" || value === "skipped";
}

export function loadGuideProgress(): GuideProgressMap {
  try {
    const saved = window.localStorage.getItem(ONBOARDING_STORAGE_KEY);
    if (!saved) return {};
    const parsed: unknown = JSON.parse(saved);
    if (!parsed || typeof parsed !== "object" || Array.isArray(parsed)) return {};
    const values = parsed as Record<string, unknown>;
    const progress: GuideProgressMap = {};
    for (const role of ROLE_DEFINITIONS) {
      const candidate = values[role.id] as Record<string, unknown> | undefined;
      if (!candidate || !isStatus(candidate.status) || typeof candidate.step !== "number") continue;
      progress[role.id] = {
        step: Math.max(0, Math.min(GUIDE_STEP_COUNT - 1, Math.floor(candidate.step))),
        status: candidate.status,
      };
    }
    return progress;
  } catch {
    return {};
  }
}

export function saveGuideProgress(progress: GuideProgressMap): void {
  try {
    window.localStorage.setItem(ONBOARDING_STORAGE_KEY, JSON.stringify(progress));
  } catch {
    // The guide remains usable when storage is unavailable.
  }
}
