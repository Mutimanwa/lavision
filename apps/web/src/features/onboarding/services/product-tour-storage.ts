import { ROLE_DEFINITIONS, type RoleId } from "@/features/auth/data/roles";
import type { GuideProgress, GuideStatus } from "./onboarding-storage";
import { PRODUCT_TOUR_IDS, type ProductTourId } from "./product-tour";

export type ProductTourProgressMap = Record<string, GuideProgress>;

const PRODUCT_TOUR_STORAGE_KEY = "lavision.product-tour.v1";
const MAX_STORED_STEP = 100;

function isStatus(value: unknown): value is GuideStatus {
  return value === "in_progress" || value === "completed" || value === "skipped";
}

function isTourKey(key: string): boolean {
  const separator = key.indexOf(":");
  if (separator < 1) return false;
  const role = key.slice(0, separator);
  const tourId = key.slice(separator + 1);
  return ROLE_DEFINITIONS.some((item) => item.id === role)
    && PRODUCT_TOUR_IDS.includes(tourId as ProductTourId);
}

export function getProductTourStorageKey(role: RoleId, tourId: ProductTourId): string {
  return `${role}:${tourId}`;
}

export function loadProductTourProgress(): ProductTourProgressMap {
  try {
    const saved = window.localStorage.getItem(PRODUCT_TOUR_STORAGE_KEY);
    if (!saved) return {};
    const parsed: unknown = JSON.parse(saved);
    if (!parsed || typeof parsed !== "object" || Array.isArray(parsed)) return {};
    const progress: ProductTourProgressMap = {};
    for (const [key, value] of Object.entries(parsed)) {
      if (!isTourKey(key) || !value || typeof value !== "object" || Array.isArray(value)) continue;
      const candidate = value as Record<string, unknown>;
      if (!isStatus(candidate.status) || typeof candidate.step !== "number" || !Number.isFinite(candidate.step)) continue;
      progress[key] = {
        status: candidate.status,
        step: Math.max(0, Math.min(MAX_STORED_STEP, Math.floor(candidate.step))),
      };
    }
    return progress;
  } catch {
    return {};
  }
}

export function saveProductTourProgress(progress: ProductTourProgressMap): void {
  try {
    window.localStorage.setItem(PRODUCT_TOUR_STORAGE_KEY, JSON.stringify(progress));
  } catch {
    // La visite reste utilisable lorsque le stockage est indisponible.
  }
}
