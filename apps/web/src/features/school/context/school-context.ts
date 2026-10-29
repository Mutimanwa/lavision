"use client";

import { createContext } from "react";
import type {
  AppState,
  AttendanceInput,
  ClassInput,
  EnrollmentInput,
  GradeInput,
  SchoolClass,
  SchoolSettings,
  Student,
  Subject,
  SubjectInput,
  Term,
} from "@/lib/types";

export interface SchoolContextValue {
  state: AppState;
  hydrated: boolean;
  addStudent: (input: EnrollmentInput) => Student;
  updateStudent: (id: string, updates: Partial<Student>) => void;
  addClass: (input: ClassInput) => SchoolClass;
  addSubject: (input: SubjectInput) => Subject;
  updateSubject: (id: string, coefficient: number) => void;
  saveGrades: (inputs: GradeInput[]) => void;
  saveAttendance: (inputs: AttendanceInput[]) => void;
  setPublication: (classId: string, term: Term, published: boolean) => void;
  updateSettings: (settings: SchoolSettings) => void;
  importState: (state: AppState) => void;
  resetState: () => void;
}

export const SchoolContext = createContext<SchoolContextValue | null>(null);
