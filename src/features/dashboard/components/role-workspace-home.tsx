"use client";

import Link from "next/link";
import {
  ArrowRight,
  Blocks,
  CheckCircle2,
  Eye,
  Layers3,
  ShieldCheck,
} from "lucide-react";
import { Badge, Notice, PageHeader, type BadgeTone } from "@/components/ui";
import { useWorkspace } from "@/features/auth/hooks/use-workspace";
import { useModules } from "@/features/navigation/hooks/use-modules";
import { canRoleOpenModule, type ResolvedModule } from "@/features/navigation/services/module-state";
import { useSchool } from "@/features/school/hooks/use-school";
import { FirstStepsCard } from "@/features/onboarding/components/first-steps-card";
import { getRoleMetrics } from "../services/role-metrics";

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

function ModuleCard({ module, role }: { module: ResolvedModule; role: ReturnType<typeof useWorkspace>["role"] }) {
  const canOpen = module.href && canRoleOpenModule(module, role);
  const href = canOpen && module.href ? module.href : "/modules/";
  return (
    <Link className="workspace-module-row" href={href}>
      <span className="workspace-module-icon" aria-hidden="true"><Blocks size={18} /></span>
      <span className="workspace-module-copy"><strong>{module.label}</strong><small>{module.description}</small></span>
      <Badge tone={STATUS_TONES[module.status]}>{STATUS_LABELS[module.status]}</Badge>
      <ArrowRight size={15} aria-hidden="true" />
    </Link>
  );
}

export function RoleWorkspaceHome() {
  const { role, roleDefinition, workspace } = useWorkspace();
  const { state } = useSchool();
  const { modules } = useModules();
  const metrics = getRoleMetrics(role, state);
  const roleModules = modules.filter((module) => module.roles.includes(role));
  const availableHref = (href: string) => {
    const linkedModule = modules.find((module) => module.href === href);
    return linkedModule && !canRoleOpenModule(linkedModule, role) ? "/modules/" : href;
  };
  const primaryHref = availableHref(workspace.primaryAction.href);
  const activeCount = roleModules.filter((module) => module.status === "active").length;
  const setupCount = roleModules.filter((module) => module.status === "configuring").length;

  return (
    <>
      <PageHeader
        eyebrow={workspace.spaceLabel}
        title={workspace.title}
        description={workspace.description}
        size="large"
        actions={(
          <>
            {primaryHref !== "/modules/" && <Link className="button button-secondary" href="/modules/"><Layers3 size={17} /> Mes modules</Link>}
            <Link className="button button-primary" href={primaryHref}>{workspace.primaryAction.label}<ArrowRight size={17} /></Link>
          </>
        )}
      />

      <Notice title={`Aperçu du rôle ${roleDefinition.label}`}>
        Cette interface utilise les données de démonstration pour présenter le parcours. Elle ne simule ni une authentification ni une restriction de sécurité serveur.
      </Notice>

      {metrics.length > 0 && (
        <section className="workspace-metrics" aria-label="Indicateurs de l’espace">
          {metrics.map((metric) => (
            <article className="card workspace-metric" key={metric.label}>
              <span>{metric.label}</span><strong>{metric.value}</strong><small>{metric.detail}</small>
            </article>
          ))}
        </section>
      )}

      <FirstStepsCard />

      <div className="workspace-home-grid">
        <section className="card workspace-tasks-card">
          <header className="card-header"><div><p className="eyebrow">Votre espace</p><h2>Actions prioritaires</h2></div><Badge tone="neutral">{workspace.tasks.length}</Badge></header>
          <div className="workspace-task-list">
            {workspace.tasks.map((task, index) => (
              <Link href={availableHref(task.href)} key={task.title}>
                <span className="workspace-task-number">{String(index + 1).padStart(2, "0")}</span>
                <span><strong>{task.title}</strong><small>{task.description}</small></span>
                <ArrowRight size={16} aria-hidden="true" />
              </Link>
            ))}
          </div>
        </section>

        <aside className="card workspace-scope-card">
          <span className="workspace-scope-icon" aria-hidden="true"><ShieldCheck size={23} /></span>
          <p className="eyebrow">Périmètre de l’aperçu</p>
          <h2>{roleDefinition.label}</h2>
          <p>{workspace.scopeLabel}</p>
          <dl>
            <div><dt>Modules actifs</dt><dd>{activeCount}</dd></div>
            <div><dt>À configurer</dt><dd>{setupCount}</dd></div>
            <div><dt>Rôle</dt><dd><code>{role}</code></dd></div>
          </dl>
          <span className="workspace-preview-label"><Eye size={14} /> Présentation locale</span>
        </aside>
      </div>

      <section className="card workspace-modules-card">
        <header className="card-header">
          <div><p className="eyebrow">Navigation progressive</p><h2>Modules de votre espace</h2><p>Les états proviennent du catalogue et des prérequis disponibles dans le prototype.</p></div>
          <Link className="text-link" href="/modules/">Tout voir <ArrowRight size={14} /></Link>
        </header>
        <div className="workspace-module-list">
          {roleModules.map((module) => <ModuleCard key={module.id} module={module} role={role} />)}
        </div>
        {activeCount > 0 && setupCount === 0 && <div className="workspace-ready-note"><CheckCircle2 size={16} /> Les modules actifs sont accessibles depuis la navigation.</div>}
      </section>
    </>
  );
}
