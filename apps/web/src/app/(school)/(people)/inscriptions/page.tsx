import type { Metadata } from "next";
import { EnrollmentForm } from "@/features/enrollments/pages/enrollment-page";

export const metadata: Metadata = { title: "Nouvelle inscription" };

export default function EnrollmentsPage() {
  return <EnrollmentForm />;
}
