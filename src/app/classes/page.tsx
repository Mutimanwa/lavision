import type { Metadata } from "next";
import { ClassManager } from "@/components/class-manager";

export const metadata: Metadata = { title: "Classes & matières" };

export default function ClassesPage() {
  return <ClassManager />;
}
