import type { Metadata } from "next";
import { SettingsManager } from "@/features/settings/pages/settings-page";

export const metadata: Metadata = { title: "Paramètres" };

export default function SettingsPage() {
  return <SettingsManager />;
}
