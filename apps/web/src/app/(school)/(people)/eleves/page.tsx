import type { Metadata } from "next";
import { Suspense } from "react";
import { StudentDirectory } from "@/features/students/pages/students-page";

export const metadata: Metadata = { title: "Élèves" };

export default function StudentsPage() {
  return (
    <Suspense fallback={<div className="page-loading">Chargement des dossiers élèves…</div>}>
      <StudentDirectory />
    </Suspense>
  );
}
