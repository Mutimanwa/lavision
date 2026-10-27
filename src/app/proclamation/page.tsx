import type { Metadata } from "next";
import { Proclamation } from "@/components/proclamation";

export const metadata: Metadata = { title: "Proclamation des résultats" };

export default function ProclamationPage() {
  return <Proclamation />;
}
