export const TERMS = ["Trimestre 1", "Trimestre 2", "Trimestre 3"] as const;

export type Term = (typeof TERMS)[number];
export type StudentStatus = "Actif" | "En attente" | "Inactif";
export type Gender = "Fille" | "Garçon";

export interface Guardian {
  name: string;
  relationship: string;
  phone: string;
  email: string;
}

export interface Student {
  id: string;
  matricule: string;
  firstName: string;
  lastName: string;
  gender: Gender;
  birthDate: string;
  birthPlace: string;
  nationality: string;
  address: string;
  classId: string;
  enrolledAt: string;
  status: StudentStatus;
  previousSchool: string;
  guardian: Guardian;
}

export interface SchoolClass {
  id: string;
  name: string;
  level: string;
  room: string;
  capacity: number;
  headTeacher: string;
}

export interface Subject {
  id: string;
  name: string;
  code: string;
  coefficient: number;
}

export interface Grade {
  id: string;
  studentId: string;
  subjectId: string;
  term: Term;
  value: number;
  updatedAt: string;
}

export type AttendanceStatus = "present" | "absent" | "retard" | "excuse";

export interface AttendanceRecord {
  id: string;
  studentId: string;
  classId: string;
  date: string;
  status: AttendanceStatus;
}

export interface Publication {
  id: string;
  classId: string;
  term: Term;
  publishedAt: string;
}

export interface SchoolSettings {
  schoolName: string;
  academicYear: string;
  currentTerm: Term;
  passMark: number;
  directionName: string;
  email: string;
  phone: string;
  address: string;
}

export interface AppState {
  version: 1;
  students: Student[];
  classes: SchoolClass[];
  subjects: Subject[];
  grades: Grade[];
  attendance: AttendanceRecord[];
  publications: Publication[];
  settings: SchoolSettings;
}

export type EnrollmentInput = Omit<Student, "id" | "matricule">;
export type ClassInput = Omit<SchoolClass, "id">;
export type SubjectInput = Omit<Subject, "id">;

export interface GradeInput {
  studentId: string;
  subjectId: string;
  term: Term;
  value: number | null;
}

export interface AttendanceInput {
  studentId: string;
  classId: string;
  date: string;
  status: AttendanceStatus;
}
