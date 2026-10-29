"use client";

import {
  useCallback,
  useEffect,
  useMemo,
  useState,
  type ReactNode,
} from "react";
import { createSeedState } from "../data/seed";
import { loadSchoolState, saveSchoolState } from "../services/school-storage";
import { SchoolContext, type SchoolContextValue } from "./school-context";
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
import { createId } from "@/lib/utils";

export function SchoolProvider({ children }: { children: ReactNode }) {
  const [state, setState] = useState<AppState>(() => createSeedState());
  const [hydrated, setHydrated] = useState(false);

  useEffect(() => {
    const timer = window.setTimeout(() => {
      const saved = loadSchoolState();
      if (saved) setState(saved);
      setHydrated(true);
    }, 0);
    return () => window.clearTimeout(timer);
  }, []);

  useEffect(() => {
    if (hydrated) saveSchoolState(state);
  }, [hydrated, state]);

  const addStudent = useCallback(
    (input: EnrollmentInput) => {
      const year = state.settings.academicYear.match(/\d{4}/)?.[0] ?? "2026";
      const highest = state.students.reduce((maximum, student) => {
        const sequence = Number(student.matricule.split("-").at(-1));
        return Number.isFinite(sequence) ? Math.max(maximum, sequence) : maximum;
      }, 0);
      const student: Student = {
        ...input,
        id: createId("eleve"),
        matricule: `LV-${year}-${String(highest + 1).padStart(3, "0")}`,
      };
      setState((current) => ({
        ...current,
        students: [student, ...current.students],
      }));
      return student;
    },
    [state.settings.academicYear, state.students],
  );

  const updateStudent = useCallback(
    (id: string, updates: Partial<Student>) => {
      setState((current) => ({
        ...current,
        students: current.students.map((student) =>
          student.id === id ? { ...student, ...updates, id: student.id } : student,
        ),
      }));
    },
    [],
  );

  const addClass = useCallback((input: ClassInput) => {
    const schoolClass: SchoolClass = { ...input, id: createId("classe") };
    setState((current) => ({
      ...current,
      classes: [...current.classes, schoolClass],
    }));
    return schoolClass;
  }, []);

  const addSubject = useCallback((input: SubjectInput) => {
    const subject: Subject = { ...input, id: createId("matiere") };
    setState((current) => ({
      ...current,
      subjects: [...current.subjects, subject],
    }));
    return subject;
  }, []);

  const updateSubject = useCallback((id: string, coefficient: number) => {
    setState((current) => ({
      ...current,
      subjects: current.subjects.map((subject) =>
        subject.id === id ? { ...subject, coefficient } : subject,
      ),
    }));
  }, []);

  const saveGrades = useCallback((inputs: GradeInput[]) => {
    setState((current) => {
      const keys = new Set(
        inputs.map((input) => `${input.studentId}:${input.subjectId}:${input.term}`),
      );
      const untouched = current.grades.filter(
        (grade) => !keys.has(`${grade.studentId}:${grade.subjectId}:${grade.term}`),
      );
      const saved = inputs
        .filter(
          (input): input is GradeInput & { value: number } =>
            input.value !== null && input.value >= 0 && input.value <= 20,
        )
        .map((input) => ({
          ...input,
          value: input.value,
          id: `${input.studentId}-${input.subjectId}-${input.term}`,
          updatedAt: new Date().toISOString(),
        }));
      return { ...current, grades: [...untouched, ...saved] };
    });
  }, []);

  const saveAttendance = useCallback((inputs: AttendanceInput[]) => {
    setState((current) => {
      const keys = new Set(inputs.map((input) => `${input.date}:${input.studentId}`));
      const untouched = current.attendance.filter(
        (record) => !keys.has(`${record.date}:${record.studentId}`),
      );
      const saved = inputs.map((input) => ({
        ...input,
        id: `${input.date}-${input.studentId}`,
      }));
      return { ...current, attendance: [...untouched, ...saved] };
    });
  }, []);

  const setPublication = useCallback(
    (classId: string, term: Term, published: boolean) => {
      setState((current) => ({
        ...current,
        publications: published
          ? [
              ...current.publications.filter(
                (item) => !(item.classId === classId && item.term === term),
              ),
              {
                id: createId("proclamation"),
                classId,
                term,
                publishedAt: new Date().toISOString(),
              },
            ]
          : current.publications.filter(
              (item) => !(item.classId === classId && item.term === term),
            ),
      }));
    },
    [],
  );

  const updateSettings = useCallback((settings: SchoolSettings) => {
    setState((current) => ({ ...current, settings }));
  }, []);

  const importState = useCallback((imported: AppState) => {
    setState(imported);
  }, []);

  const resetState = useCallback(() => {
    setState(createSeedState());
  }, []);

  const value = useMemo<SchoolContextValue>(
    () => ({
      state,
      hydrated,
      addStudent,
      updateStudent,
      addClass,
      addSubject,
      updateSubject,
      saveGrades,
      saveAttendance,
      setPublication,
      updateSettings,
      importState,
      resetState,
    }),
    [
      state,
      hydrated,
      addStudent,
      updateStudent,
      addClass,
      addSubject,
      updateSubject,
      saveGrades,
      saveAttendance,
      setPublication,
      updateSettings,
      importState,
      resetState,
    ],
  );

  return <SchoolContext.Provider value={value}>{children}</SchoolContext.Provider>;
}
