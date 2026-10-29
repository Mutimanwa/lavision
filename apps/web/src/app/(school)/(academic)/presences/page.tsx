import type { Metadata } from "next";
import { AttendanceManager } from "@/features/attendance/pages/attendance-page";

export const metadata: Metadata = { title: "Présences" };

export default function AttendancePage() {
  return <AttendanceManager />;
}
