import { describe, expect, it } from "vitest";
import {
  getProductTourId,
  getProductTourSteps,
  PRODUCT_TOUR_IDS,
} from "./product-tour";

describe("définition du Product Tour", () => {
  it("associe les routes existantes à une visite et ignore la connexion", () => {
    expect(getProductTourId("/")).toBe("dashboard");
    expect(getProductTourId("/eleves/")).toBe("students");
    expect(getProductTourId("/notes")).toBe("grades");
    expect(getProductTourId("/parametres/")).toBe("settings");
    expect(getProductTourId("/connexion/")).toBeNull();
    expect(getProductTourId("/route-inconnue")).toBeNull();
  });

  it("fournit une introduction et des étapes ancrées pour chaque page", () => {
    for (const tourId of PRODUCT_TOUR_IDS) {
      const steps = getProductTourSteps(tourId);
      expect(steps.length).toBeGreaterThan(6);
      expect(steps[0].element).toBeUndefined();
      expect(steps[0].popover?.title).toBe("Bienvenue dans lavision");
      for (const current of steps.slice(1)) {
        expect(typeof current.element).toBe("string");
        expect(current.popover?.title).toBeTruthy();
        expect(current.popover?.description).toBeTruthy();
        expect(current.skipMissingElement).toBe(true);
      }
    }
  });
});
