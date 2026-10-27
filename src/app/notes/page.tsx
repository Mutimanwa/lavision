import type { Metadata } from "next";
import { Gradebook } from "@/components/gradebook";

export const metadata: Metadata = { title: "Saisie des notes" };

export default function GradesPage() {
  return <Gradebook />;
}
