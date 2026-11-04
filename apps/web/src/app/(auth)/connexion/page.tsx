import type { Metadata } from "next";
import { Suspense } from "react";
import { LoginForm } from "@/features/auth/components/login-form";

export const metadata: Metadata = {
  title: "Connexion",
  description: "Connexion sécurisée à l’espace de travail lavision.",
};

export default function LoginPage() {
  return (
    <Suspense fallback={<div className="auth-screen"><main className="auth-card"><p className="auth-loading">Chargement de la connexion…</p></main></div>}>
      <LoginForm />
    </Suspense>
  );
}
