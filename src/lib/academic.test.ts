import { describe, expect, it } from "vitest";
import {
  getAppreciation,
  getClassRanking,
  getStudentReport,
} from "./academic";
import type { Grade, Student, Subject } from "./types";

const subjects: Subject[] = [
  { id: "math", name: "Mathématiques", code: "MATH", coefficient: 4 },
  { id: "fr", name: "Français", code: "FR", coefficient: 2 },
];

function student(id: string): Student {
  return {
    id,
    matricule: id,
    firstName: id,
    lastName: "Test",
    gender: "Fille",
    birthDate: "2014-01-01",
    birthPlace: "Bujumbura",
    nationality: "Burundaise",
    address: "",
    classId: "c1",
    enrolledAt: "2026-09-01",
    status: "Actif",
    previousSchool: "",
    guardian: { name: "", relationship: "", phone: "", email: "" },
  };
}

function grade(studentId: string, subjectId: string, value: number): Grade {
  return {
    id: `${studentId}-${subjectId}`,
    studentId,
    subjectId,
    term: "Trimestre 1",
    value,
    updatedAt: "2026-10-27T08:00:00.000Z",
  };
}

describe("calcul des bulletins", () => {
  it("pondère chaque note par le coefficient de sa matière", () => {
    const report = getStudentReport(
      "a",
      "Trimestre 1",
      subjects,
      [grade("a", "math", 15), grade("a", "fr", 9)],
    );

    expect(report.average).toBe(13);
    expect(report.gradedCount).toBe(2);
    expect(report.isComplete).toBe(true);
    expect(report.lines[0].weightedPoints).toBe(60);
  });

  it("signale un bulletin incomplet sans confondre absence de note et zéro", () => {
    const report = getStudentReport(
      "a",
      "Trimestre 1",
      subjects,
      [grade("a", "math", 0)],
    );

    expect(report.average).toBe(0);
    expect(report.isComplete).toBe(false);
    expect(report.lines[1].grade).toBeNull();
  });

  it("retourne une moyenne vide lorsqu'aucune note n'existe", () => {
    const report = getStudentReport("a", "Trimestre 1", subjects, []);
    expect(report.average).toBeNull();
    expect(report.gradedCount).toBe(0);
  });
});

describe("classement et proclamation", () => {
  it("classe les dossiers complets, gère les ex æquo et exclut les incomplets du rang", () => {
    const ranking = getClassRanking(
      [student("a"), student("b"), student("c"), student("d")],
      "Trimestre 1",
      subjects,
      [
        grade("a", "math", 14),
        grade("a", "fr", 14),
        grade("b", "math", 16),
        grade("b", "fr", 16),
        grade("c", "math", 14),
        grade("c", "fr", 14),
        grade("d", "math", 20),
      ],
      10,
    );

    expect(ranking.map((item) => item.studentId)).toEqual(["b", "a", "c", "d"]);
    expect(ranking.map((item) => item.rank)).toEqual([1, 2, 2, null]);
    expect(ranking[3].outcome).toBe("Incomplet");
  });

  it("n'admet un élève que si son dossier est complet et sa moyenne suffisante", () => {
    const ranking = getClassRanking(
      [student("a"), student("b")],
      "Trimestre 1",
      subjects,
      [
        grade("a", "math", 9),
        grade("a", "fr", 9),
        grade("b", "math", 18),
      ],
      10,
    );

    expect(ranking.find((item) => item.studentId === "a")?.outcome).toBe("Ajourné");
    expect(ranking.find((item) => item.studentId === "b")?.outcome).toBe("Incomplet");
  });
});

describe("appréciations", () => {
  it("utilise des seuils cohérents", () => {
    expect(getAppreciation(16)).toBe("Excellent");
    expect(getAppreciation(14)).toBe("Très bien");
    expect(getAppreciation(12)).toBe("Bien");
    expect(getAppreciation(10)).toBe("Passable");
    expect(getAppreciation(9.99)).toBe("Insuffisant");
  });
});
