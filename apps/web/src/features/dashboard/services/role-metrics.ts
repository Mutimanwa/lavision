import type { RoleId } from "@/features/auth/data/roles";
import type { AppState } from "@/lib/types";

export interface RoleMetric {
  label: string;
  value: number;
  detail: string;
}

export function getRoleMetrics(role: RoleId, state: AppState): RoleMetric[] {
  const activeStudents = state.students.filter((student) => student.status === "Actif");
  const currentGrades = state.grades.filter((grade) => grade.term === state.settings.currentTerm);
  const expectedGrades = activeStudents.length * state.subjects.length;

  if (role === "academic_manager") {
    return [
      { label: "Classes ouvertes", value: state.classes.length, detail: "Structure actuelle" },
      { label: "Matières", value: state.subjects.length, detail: "Référentiel pédagogique" },
      { label: "Valeurs à compléter", value: Math.max(0, expectedGrades - currentGrades.length), detail: state.settings.currentTerm },
    ];
  }
  if (role === "school_secretary") {
    return [
      { label: "Dossiers élèves", value: state.students.length, detail: "Tous statuts" },
      { label: "En attente", value: state.students.filter((student) => student.status === "En attente").length, detail: "À vérifier" },
      { label: "Classes", value: state.classes.length, detail: "Affectations possibles" },
    ];
  }
  if (role === "student_affairs") {
    return [
      { label: "Élèves actifs", value: activeStudents.length, detail: "Population suivie" },
      { label: "Appels enregistrés", value: state.attendance.length, detail: "Historique local" },
      { label: "Absences", value: state.attendance.filter((record) => record.status === "absent").length, detail: "Toutes dates" },
    ];
  }
  if (role === "teacher") {
    return [
      { label: "Classes ouvertes", value: state.classes.length, detail: "Référentiel de démonstration" },
      { label: "Matières", value: state.subjects.length, detail: "Référentiel de démonstration" },
      { label: "Valeurs à compléter", value: Math.max(0, expectedGrades - currentGrades.length), detail: state.settings.currentTerm },
    ];
  }
  return [];
}
