import type { Metadata } from "next";
import { Proclamation } from "@/features/proclamation/pages/proclamation-page";

export const metadata: Metadata = { title: "Proclamation des résultats" };

export default function ProclamationPage() {
  return <Proclamation />;
}
