import type { Metadata } from "next";
import { AttendanceManager } from "@/components/attendance-manager";

export const metadata: Metadata = { title: "Présences" };

export default function AttendancePage() {
  return <AttendanceManager />;
}
