import type { AppState } from "./types";

export function createId(prefix: string): string {
  const value =
    typeof crypto !== "undefined" && "randomUUID" in crypto
      ? crypto.randomUUID()
      : `${Date.now()}-${Math.random().toString(16).slice(2)}`;
  return `${prefix}-${value}`;
}

export function formatDate(value: string, options?: Intl.DateTimeFormatOptions) {
  if (!value) return "—";
  const normalized = value.length === 10 ? `${value}T12:00:00` : value;
  return new Intl.DateTimeFormat("fr-FR", options ?? {
    day: "numeric",
    month: "short",
    year: "numeric",
  }).format(new Date(normalized));
}

export function formatLongDate(value: Date) {
  return new Intl.DateTimeFormat("fr-FR", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  }).format(value);
}

export function toInputDate(date = new Date()) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

export function downloadFile(content: string, filename: string, type: string) {
  const blob = new Blob([content], { type });
  const url = URL.createObjectURL(blob);
  const anchor = document.createElement("a");
  anchor.href = url;
  anchor.download = filename;
  document.body.appendChild(anchor);
  anchor.click();
  anchor.remove();
  URL.revokeObjectURL(url);
}

export function downloadJson(state: AppState) {
  downloadFile(
    JSON.stringify(state, null, 2),
    `lavision-sauvegarde-${toInputDate()}.json`,
    "application/json;charset=utf-8",
  );
}

function escapeCsv(value: string | number) {
  const text = String(value);
  return `"${text.replaceAll('"', '""')}"`;
}

export function downloadCsv(
  rows: Array<Array<string | number>>,
  filename: string,
) {
  const content = `\uFEFF${rows.map((row) => row.map(escapeCsv).join(";")).join("\n")}`;
  downloadFile(content, filename, "text/csv;charset=utf-8");
}

function isRecord(value: unknown): value is Record<string, unknown> {
  return Boolean(value) && typeof value === "object" && !Array.isArray(value);
}

function hasStrings(value: Record<string, unknown>, keys: string[]) {
  return keys.every((key) => typeof value[key] === "string");
}

export function isAppState(value: unknown): value is AppState {
  if (!isRecord(value) || value.version !== 1 || !isRecord(value.settings)) return false;

  const collections = ["students", "classes", "subjects", "grades", "attendance", "publications"];
  if (!collections.every((key) => Array.isArray(value[key]))) return false;

  const students = value.students as unknown[];
  const classes = value.classes as unknown[];
  const subjects = value.subjects as unknown[];
  const grades = value.grades as unknown[];
  const attendance = value.attendance as unknown[];
  const publications = value.publications as unknown[];
  const terms = ["Trimestre 1", "Trimestre 2", "Trimestre 3"];
  const studentStatuses = ["Actif", "En attente", "Inactif"];
  const attendanceStatuses = ["present", "absent", "retard", "excuse"];

  const validStudents = students.every((item) => {
    if (!isRecord(item) || !isRecord(item.guardian)) return false;
    return (
      hasStrings(item, ["id", "matricule", "firstName", "lastName", "classId", "status"]) &&
      studentStatuses.includes(item.status as string) &&
      hasStrings(item.guardian, ["name", "relationship", "phone", "email"])
    );
  });
  const validClasses = classes.every((item) =>
    isRecord(item) &&
    hasStrings(item, ["id", "name", "level", "headTeacher"]) &&
    typeof item.capacity === "number" && Number.isFinite(item.capacity) && item.capacity > 0,
  );
  const validSubjects = subjects.every((item) =>
    isRecord(item) &&
    hasStrings(item, ["id", "name", "code"]) &&
    typeof item.coefficient === "number" && Number.isFinite(item.coefficient) && item.coefficient > 0,
  );
  const validGrades = grades.every((item) =>
    isRecord(item) &&
    hasStrings(item, ["id", "studentId", "subjectId", "term"]) &&
    terms.includes(item.term as string) &&
    typeof item.value === "number" && Number.isFinite(item.value) && item.value >= 0 && item.value <= 20,
  );
  const validAttendance = attendance.every((item) =>
    isRecord(item) &&
    hasStrings(item, ["id", "studentId", "classId", "date", "status"]) &&
    attendanceStatuses.includes(item.status as string),
  );
  const validPublications = publications.every((item) =>
    isRecord(item) &&
    hasStrings(item, ["id", "classId", "term", "publishedAt"]) &&
    terms.includes(item.term as string),
  );
  const validSettings =
    hasStrings(value.settings, ["schoolName", "academicYear", "currentTerm", "directionName"]) &&
    terms.includes(value.settings.currentTerm as string) &&
    typeof value.settings.passMark === "number" &&
    Number.isFinite(value.settings.passMark) &&
    value.settings.passMark >= 0 && value.settings.passMark <= 20;

  return validStudents && validClasses && validSubjects && validGrades && validAttendance && validPublications && validSettings;
}

export function capitalize(value: string) {
  return value.charAt(0).toUpperCase() + value.slice(1);
}
