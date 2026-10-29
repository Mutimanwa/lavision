import type { Metadata } from "next";
import { Gradebook } from "@/features/grades/pages/grades-page";

export const metadata: Metadata = { title: "Saisie des notes" };

export default function GradesPage() {
  return <Gradebook />;
}
