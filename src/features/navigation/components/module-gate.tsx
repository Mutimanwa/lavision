"use client";

import Link from "next/link";
import { ArrowLeft, Blocks, ShieldX } from "lucide-react";
import { EmptyState } from "@/components/ui";
import { useWorkspace } from "@/features/auth/hooks/use-workspace";
import { useModules } from "../hooks/use-modules";

function normalizePath(path: string): string {
  return path === "/" ? path : path.replace(/\/+$/, "");
}

export function ModuleGate({ pathname, children }: { pathname: string; children: React.ReactNode }) {
  const { role, roleDefinition } = useWorkspace();
  const { modules } = useModules();
  const currentModule = modules.find((module) => module.href && normalizePath(module.href) === normalizePath(pathname));

  if (!currentModule) return children;
  const roleAllowed = currentModule.roles.includes(role);
  const moduleOpen = currentModule.status === "active" || currentModule.status === "configuring";
  if (roleAllowed && moduleOpen) return children;

  const disabled = roleAllowed && currentModule.status === "disabled";
  return (
    <EmptyState
      icon={disabled ? <Blocks size={30} /> : <ShieldX size={30} />}
      title={disabled ? `${currentModule.label} est désactivé` : "Cette interface appartient à un autre espace"}
      description={disabled
        ? "Réactivez le module depuis le catalogue pour le faire réapparaître dans cette navigation de démonstration."
        : `L’aperçu ${roleDefinition.label} ne contient pas l’interface ${currentModule.label}. Ce contrôle présente la navigation cible, sans remplacer la future autorisation serveur.`}
      action={(
        <div className="module-gate-actions">
          <Link className="button button-secondary" href="/"><ArrowLeft size={16} /> Retour à mon espace</Link>
          <Link className="button button-primary" href="/modules/">Voir les modules</Link>
        </div>
      )}
    />
  );
}
