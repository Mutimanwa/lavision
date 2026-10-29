import type { Metadata } from "next";
import { ClassManager } from "@/features/classes/pages/classes-page";

export const metadata: Metadata = { title: "Classes & matières" };

export default function ClassesPage() {
  return <ClassManager />;
}
