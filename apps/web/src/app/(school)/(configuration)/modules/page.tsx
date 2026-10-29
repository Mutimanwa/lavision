import type { Metadata } from "next";
import { ModulesPage as ModulesFeaturePage } from "@/features/navigation/pages/modules-page";

export const metadata: Metadata = { title: "Modules et activation" };

export default function ModulesPage() {
  return <ModulesFeaturePage />;
}
