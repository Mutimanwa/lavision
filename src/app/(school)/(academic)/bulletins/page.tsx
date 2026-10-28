import type { Metadata } from "next";
import { Suspense } from "react";
import { ReportCards } from "@/features/reports/pages/reports-page";

export const metadata: Metadata = { title: "Bulletins scolaires" };

export default function ReportsPage() {
  return (
    <Suspense fallback={<div className="page-loading">Calcul des bulletins…</div>}>
      <ReportCards />
    </Suspense>
  );
}
