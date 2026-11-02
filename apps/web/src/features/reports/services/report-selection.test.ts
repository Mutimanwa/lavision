import { describe, expect, it } from "vitest";
import type { Student } from "@/lib/types";
import { selectReportStudent } from "./report-selection";

function student(id: string, classId: string, status: Student["status"]): Student {
  return { id, classId, status } as Student;
}

const students = [student("active-1", "c1", "Actif"), student("active-2", "c1", "Actif"), student("pending", "c1", "En attente"), student("other", "c2", "Actif")];

describe("sélection d’un bulletin", () => {
  it("choisit le premier élève actif seulement sans identifiant explicite", () => {
    expect(selectReportStudent(students, "c1", null, "", false)?.id).toBe("active-1");
  });

  it("utilise l’identifiant demandé sans le remplacer par un autre dossier", () => {
    expect(selectReportStudent(students, "c1", "active-2", "", true)?.id).toBe("active-2");
    expect(selectReportStudent(students, "c1", "pending", "", true)).toBeNull();
    expect(selectReportStudent(students, "c1", "inconnu", "", true)).toBeNull();
    expect(selectReportStudent(students, "c1", "other", "", true)).toBeNull();
  });

  it("donne la priorité à un choix explicite dans la classe", () => {
    expect(selectReportStudent(students, "c1", "active-1", "active-2", true)?.id).toBe("active-2");
  });
});
