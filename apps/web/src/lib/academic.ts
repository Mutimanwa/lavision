import type { Grade, Student, Subject, Term } from "./types";

export interface SubjectResult {
  subject: Subject;
  grade: Grade | null;
  weightedPoints: number | null;
}

export interface StudentReport {
  studentId: string;
  term: Term;
  lines: SubjectResult[];
  average: number | null;
  gradedCount: number;
  totalSubjects: number;
  isComplete: boolean;
}

export type Outcome = "Admis" | "Ajourné" | "Incomplet";

export interface RankedStudent {
  studentId: string;
  report: StudentReport;
  average: number | null;
  rank: number | null;
  outcome: Outcome;
}

export function round(value: number, precision = 2): number {
  const factor = 10 ** precision;
  return Math.round((value + Number.EPSILON) * factor) / factor;
}

export function getStudentReport(
  studentId: string,
  term: Term,
  subjects: Subject[],
  grades: Grade[],
): StudentReport {
  const lines = subjects.map((subject) => {
    const grade =
      grades.find(
        (item) =>
          item.studentId === studentId &&
          item.subjectId === subject.id &&
          item.term === term,
      ) ?? null;

    return {
      subject,
      grade,
      weightedPoints: grade ? round(grade.value * subject.coefficient) : null,
    };
  });

  const gradedLines = lines.filter((line) => line.grade !== null);
  const coefficientTotal = gradedLines.reduce(
    (total, line) => total + line.subject.coefficient,
    0,
  );
  const weightedTotal = gradedLines.reduce(
    (total, line) => total + (line.weightedPoints ?? 0),
    0,
  );

  return {
    studentId,
    term,
    lines,
    average:
      coefficientTotal > 0 ? round(weightedTotal / coefficientTotal) : null,
    gradedCount: gradedLines.length,
    totalSubjects: subjects.length,
    isComplete: subjects.length > 0 && gradedLines.length === subjects.length,
  };
}

export function getClassRanking(
  students: Student[],
  term: Term,
  subjects: Subject[],
  grades: Grade[],
  passMark: number,
): RankedStudent[] {
  const sorted = students
    .map((student) => ({
      studentId: student.id,
      report: getStudentReport(student.id, term, subjects, grades),
    }))
    .sort((a, b) => {
      if (a.report.isComplete !== b.report.isComplete) {
        return a.report.isComplete ? -1 : 1;
      }
      if (a.report.average === null && b.report.average === null) {
        return a.studentId.localeCompare(b.studentId);
      }
      if (a.report.average === null) return 1;
      if (b.report.average === null) return -1;
      return b.report.average - a.report.average;
    });

  let previousAverage: number | null = null;
  let previousRank = 0;

  return sorted.map((item, index) => {
    const { average, isComplete } = item.report;
    let rank: number | null = null;

    if (isComplete && average !== null) {
      if (previousAverage === null || average !== previousAverage) {
        previousRank = index + 1;
        previousAverage = average;
      }
      rank = previousRank;
    }

    return {
      ...item,
      average,
      rank,
      outcome: !isComplete
        ? "Incomplet"
        : average !== null && average >= passMark
          ? "Admis"
          : "Ajourné",
    };
  });
}

export function getAppreciation(average: number | null): string {
  if (average === null) return "Non évalué";
  if (average >= 16) return "Excellent";
  if (average >= 14) return "Très bien";
  if (average >= 12) return "Bien";
  if (average >= 10) return "Passable";
  return "Insuffisant";
}

export function formatAverage(value: number | null): string {
  return value === null ? "—" : value.toFixed(2).replace(".", ",");
}

export function getInitials(student: Pick<Student, "firstName" | "lastName">) {
  return `${student.firstName.charAt(0)}${student.lastName.charAt(0)}`.toUpperCase();
}

export function getFullName(student: Pick<Student, "firstName" | "lastName">) {
  return `${student.firstName} ${student.lastName}`;
}
