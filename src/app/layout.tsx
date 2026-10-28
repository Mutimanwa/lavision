import type { Metadata, Viewport } from "next";
import "@fontsource-variable/dm-sans";
import "./globals.css";
import "./master-layout.css";
import "./component-library.css";
import { AppShell } from "@/components/app-shell";
import { SchoolProvider } from "@/components/school-provider";

export const metadata: Metadata = {
  title: {
    default: "lavision — Gestion scolaire",
    template: "%s · lavision",
  },
  description:
    "lavision centralise la gestion scolaire, de l’inscription des élèves à la proclamation des résultats.",
  applicationName: "lavision",
  icons: { icon: "/icon.svg" },
};

export const viewport: Viewport = {
  width: "device-width",
  initialScale: 1,
    themeColor: "#242626",
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
