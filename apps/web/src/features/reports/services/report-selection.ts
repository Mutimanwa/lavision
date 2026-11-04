import type { Student } from "@/lib/types";

/**
 * Un identifiant demandé explicitement ne doit jamais retomber sur un autre élève.
 * Sans sélection, le premier élève actif de la classe reste le raccourci du prototype.
 */
export function selectReportStudent(
  students: Student[],
  classId: string,
  requestedId: string | null,
  selectedId: string,
  hasRequestedId: boolean,
): Student | null {
  const activeStudents = students.filter(
    (student) => student.classId === classId && student.status === "Actif",
  );
  if (selectedId) return activeStudents.find((student) => student.id === selectedId) ?? null;
  if (hasRequestedId) return activeStudents.find((student) => student.id === requestedId) ?? null;
  return activeStudents[0] ?? null;
}
