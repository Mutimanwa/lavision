import { describe, expect, it } from "vitest";
import type { Student } from "@/lib/types";
import { activeStudentsInClass, hasSeatForActiveStudent } from "./student-validation";

function student(id: string, classId: string, status: Student["status"]): Student {
  return { id, classId, status } as Student;
}

describe("capacité lors de l’affectation d’un élève", () => {
  const students = [student("a", "c1", "Actif"), student("b", "c1", "Actif"), student("c", "c1", "Inactif")];

  it("ne compte que les élèves actifs de la classe", () => {
    expect(activeStudentsInClass(students, "c1")).toBe(2);
    expect(hasSeatForActiveStudent(students, "c1", 3)).toBe(true);
    expect(hasSeatForActiveStudent(students, "c1", 2)).toBe(false);
  });

  it("permet de conserver un élève actif dans une classe déjà pleine", () => {
    expect(hasSeatForActiveStudent(students, "c1", 2, "a")).toBe(true);
    expect(hasSeatForActiveStudent(students, "c1", 2, "c")).toBe(false);
  });

  it("refuse une capacité invalide", () => {
    expect(hasSeatForActiveStudent(students, "c1", 0)).toBe(false);
    expect(hasSeatForActiveStudent(students, "c1", Number.NaN)).toBe(false);
  });
});
