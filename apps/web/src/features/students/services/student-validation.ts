import type { Student } from "@/lib/types";

/** Contrôle d’interface du prototype : l’élève en cours de modification est exclu du décompte. */
export function activeStudentsInClass(
  students: Student[],
  classId: string,
  excludedStudentId?: string,
): number {
  return students.filter(
    (student) => student.id !== excludedStudentId
      && student.classId === classId
      && student.status === "Actif",
  ).length;
}

export function hasSeatForActiveStudent(
  students: Student[],
  classId: string,
  capacity: number,
  excludedStudentId?: string,
): boolean {
  return Number.isFinite(capacity)
    && capacity > 0
    && activeStudentsInClass(students, classId, excludedStudentId) < capacity;
}
