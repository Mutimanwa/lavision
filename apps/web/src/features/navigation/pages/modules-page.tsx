"use client";

import Link from "next/link";
import {
  ArrowRight,
  Blocks,
  Check,
  CircleSlash2,
  Clock3,
  RotateCcw,
  Settings2,
} from "lucide-react";
import { Badge, Notice, PageHeader, type BadgeTone } from "@/components/ui";
import { useWorkspace } from "@/features/auth/hooks/use-workspace";
import { useModules } from "../hooks/use-modules";
import { canRoleOpenModule, type ResolvedModule } from "../services/module-state";

const STATUS_LABELS = {
  active: "Actif",
  configuring: "À configurer",
  disabled: "Désactivé",
  planned: "Planifié",
} as const;

const STATUS_TONES: Record<keyof typeof STATUS_LABELS, BadgeTone> = {
  active: "green",
  configuring: "amber",
  disabled: "neutral",
  planned: "blue",
};

function ModuleCard({ module, canManage, role, onToggle }: {
  module: ResolvedModule;
  canManage: boolean;
  role: ReturnType<typeof useWorkspace>["role"];
  onToggle: (enabled: boolean) => void;
}) {
  const canOpen = module.href && canRoleOpenModule(module, role);
  return (
    <article className={`card module-card module-${module.status}`}>
      <header>
        <span className="module-card-icon" aria-hidden="true"><Blocks size={20} /></span>
        <div><h2>{module.label}</h2><code>{module.id}</code></div>
        <Badge tone={STATUS_TONES[module.status]}>{STATUS_LABELS[module.status]}</Badge>
      </header>
      <p>{module.description}</p>
      {module.missingPrerequisites.length > 0 && (
        <ul className="module-prerequisites">
          {module.missingPrerequisites.map((prerequisite) => <li key={prerequisite}>{prerequisite}</li>)}
        </ul>
      )}
      {module.status === "disabled" && <small className="module-state-note">Le module est masqué dans la navigation de démonstration.</small>}
      {module.status === "planned" && <small className="module-state-note">Interface recensée, sans opérations ni données fictives dans cette version.</small>}
      <footer>
        {canOpen && <Link className="text-link" href={module.href ?? "/modules/"}>Ouvrir <ArrowRight size={14} /></Link>}
        {module.core && <span className="module-core-note"><Settings2 size={14} /> Module essentiel</span>}
        {canManage && module.implemented && !module.core && (
          <button className={`button ${module.status === "disabled" ? "button-primary" : "button-secondary"}`} type="button" onClick={() => onToggle(module.status === "disabled")}>
            {module.status === "disabled" ? <><Check size={15} /> Activer la configuration</> : <><CircleSlash2 size={15} /> Désactiver</>}
          </button>
        )}
        {!canManage && module.implemented && <small>Gestion réservée à la direction ou à l’administrateur technique.</small>}
      </footer>
    </article>
  );
}

export function ModulesPage() {
  const { role } = useWorkspace();
  const { modules, canManageModules, setModuleEnabled, resetModules } = useModules();
  const visibleModules = canManageModules ? modules : modules.filter((module) => module.roles.includes(role));
  const implemented = visibleModules.filter((module) => module.implemented);
  const planned = visibleModules.filter((module) => !module.implemented);

  return (
    <>
      <PageHeader
        eyebrow="Navigation progressive"
        title="Modules et activation"
        description="Consultez les interfaces de votre espace et les prérequis calculés depuis les données de démonstration."
        actions={canManageModules ? <button className="button button-secondary" type="button" onClick={resetModules}><RotateCcw size={16} /> Réinitialiser les modules</button> : undefined}
      />
      <Notice title="Aperçu fonctionnel local">
        Les changements ci-dessous pilotent la navigation de ce prototype. Ils ne remplacent ni les autorisations serveur ni l’activation réelle d’un backend.
      </Notice>

      <section className="modules-section" aria-labelledby="available-modules-title">
        <header className="modules-section-heading"><div><p className="eyebrow">Prototype actuel</p><h2 id="available-modules-title">Interfaces disponibles</h2></div><Badge tone="neutral">{implemented.length}</Badge></header>
        <div className="modules-grid">
          {implemented.map((module) => (
            <ModuleCard key={module.id} module={module} canManage={canManageModules} role={role} onToggle={(enabled) => setModuleEnabled(module.id, enabled)} />
          ))}
        </div>
      </section>

      {planned.length > 0 && (
        <section className="modules-section" aria-labelledby="planned-modules-title">
          <header className="modules-section-heading"><div><p className="eyebrow">Jalons suivants</p><h2 id="planned-modules-title">Interfaces planifiées</h2><p>Ces modules sont visibles pour comprendre le parcours, sans simuler leurs données.</p></div><Clock3 size={20} /></header>
          <div className="modules-grid">
            {planned.map((module) => (
              <ModuleCard key={module.id} module={module} canManage={false} role={role} onToggle={() => undefined} />
            ))}
          </div>
        </section>
      )}
    </>
  );
}
