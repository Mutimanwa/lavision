import type { Metadata } from "next";
import { EnrollmentForm } from "@/components/enrollment-form";

export const metadata: Metadata = { title: "Nouvelle inscription" };

export default function EnrollmentsPage() {
  return <EnrollmentForm />;
}
