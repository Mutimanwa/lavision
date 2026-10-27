import type { Metadata, Viewport } from "next";
import "@fontsource-variable/dm-sans";
import "./globals.css";
import { AppShell } from "@/components/app-shell";
import { SchoolProvider } from "@/components/school-provider";

export const metadata: Metadata = {
  title: {
    default: "LaVision — Gestion scolaire",
    template: "%s · LaVision",
  },
  description:
    "LaVision centralise la gestion scolaire, de l’inscription des élèves à la proclamation des résultats.",
  applicationName: "LaVision",
  icons: { icon: "/icon.svg" },
};

export const viewport: Viewport = {
  width: "device-width",
  initialScale: 1,
  themeColor: "#173f35",
};

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="fr">
      <body>
        <SchoolProvider>
          <AppShell>{children}</AppShell>
        </SchoolProvider>
      </body>
    </html>
  );
}
