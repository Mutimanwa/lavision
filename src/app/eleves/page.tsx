import type { Metadata } from "next";
import { Suspense } from "react";
import { StudentDirectory } from "@/components/student-directory";

export const metadata: Metadata = { title: "Élèves" };

export default function StudentsPage() {
  return (
    <Suspense fallback={<div className="page-loading">Chargement des dossiers élèves…</div>}>
      <StudentDirectory />
    </Suspense>
  );
}
