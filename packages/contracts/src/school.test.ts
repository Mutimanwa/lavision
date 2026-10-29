import { describe, expect, it } from "vitest";
import {
  periodCreateInputSchema,
  schoolHistoryQuerySchema,
  schoolYearCloseInputSchema,
  schoolYearCreateInputSchema,
  schoolYearUpdateInputSchema,
} from "./index.js";

const organizationId = "00000000-0000-4000-8000-000000000001";
const schoolYearId = "00000000-0000-4000-8000-000000000002";

describe("contrats des référentiels scolaires", () => {
  it("accepte une année et des périodes typées sans champ de transition libre", () => {
    expect(
      schoolYearCreateInputSchema.safeParse({
        organizationId,
        code: "2026-2027",
        name: "Année 2026-2027",
        startDate: "2026-09-01",
        endDate: "2027-07-31",
      }).success,
    ).toBe(true);
    expect(
      periodCreateInputSchema.safeParse({
        organizationId,
        schoolYearId,
        code: "P1",
        name: "Première période",
        sequence: 1,
        startDate: "2026-09-01",
        endDate: "2026-12-20",
        status: "CLOSED",
      }).success,
    ).toBe(false);
  });

  it("exige une modification et un motif de clôture", () => {
    expect(schoolYearUpdateInputSchema.safeParse({ organizationId }).success).toBe(false);
    expect(
      schoolYearCloseInputSchema.safeParse({ organizationId, reason: "Clôture validée" }).success,
    ).toBe(true);
    expect(schoolYearCloseInputSchema.safeParse({ organizationId }).success).toBe(false);
  });

  it("borne la consultation de l’historique", () => {
    expect(schoolHistoryQuerySchema.parse({ organizationId }).limit).toBe(50);
    expect(schoolHistoryQuerySchema.safeParse({ organizationId, limit: "101" }).success).toBe(false);
    expect(
      schoolHistoryQuerySchema.safeParse({ organizationId, entityType: "unknown" }).success,
    ).toBe(false);
  });
});
