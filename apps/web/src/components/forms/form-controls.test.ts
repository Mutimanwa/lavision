import { describe, expect, it } from "vitest";
import { formatAmountThousands, sanitizeAmount } from "./form-controls";

describe("sanitizeAmount", () => {
  it("retire les séparateurs de milliers et caractères non numériques", () => {
    expect(sanitizeAmount("12,500.50", 2)).toBe("12500.50");
    expect(sanitizeAmount("USD 250.75", 2)).toBe("250.75");
  });

  it("conserve un seul séparateur et limite les décimales", () => {
    expect(sanitizeAmount("12.3456", 2)).toBe("12.34");
    expect(sanitizeAmount("1.2.3", 2)).toBe("1.23");
  });

  it("accepte les devises sans décimales comme le BIF", () => {
    expect(sanitizeAmount("12500.99", 0)).toBe("12500");
  });
});

describe("formatAmountThousands", () => {
  it("formate les milliers sans modifier les décimales saisies", () => {
    expect(formatAmountThousands("12500.5")).toBe("12,500.5");
    expect(formatAmountThousands("0.")).toBe("0.");
  });

  it("conserve une valeur vide", () => {
    expect(formatAmountThousands("")).toBe("");
  });
});
