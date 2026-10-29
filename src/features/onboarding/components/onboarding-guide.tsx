"use client";

import Link from "next/link";
import {
  ArrowLeft,
  ArrowRight,
  Check,
  CircleHelp,
  Eye,
  ListChecks,
  MousePointer2,
  Sparkles,
} from "lucide-react";
import { Modal } from "@/components/ui";
import { useWorkspace } from "@/features/auth/hooks/use-workspace";
import { useOnboarding } from "../hooks/use-onboarding";

export function OnboardingGuide() {
  const { roleDefinition, workspace } = useWorkspace();
  const {
    open,
    step,
    stepCount,
    dismissGuide,
    skipGuide,
    nextStep,
    previousStep,
  } = useOnboarding();

  const steps = [
    {
      title: `Bienvenue dans ${workspace.spaceLabel}`,
      description: workspace.guideIntroduction,
      icon: Sparkles,
    },
    {
      title: "Votre navigation suit votre rôle",
      description: "Les menus présentent vos modules utiles. Un badge « À configurer » indique un vrai prérequis, tandis qu’un module planifié n’est jamais présenté comme disponible.",
      icon: ListChecks,
    },
    {
      title: "Votre première action",
      description: workspace.firstTask,
      icon: MousePointer2,
    },
    {
      title: "Le guide reste disponible",
      description: "Vous pouvez le reprendre ou le relancer depuis le centre d’aide. Votre progression est enregistrée séparément pour chaque espace de démonstration.",
      icon: CircleHelp,
    },
  ];
  const current = steps[step];
  const Icon = current.icon;

  return (
    <Modal
      open={open}
      onClose={dismissGuide}
      title={current.title}
      description={`${roleDefinition.label} · Étape ${step + 1} sur ${stepCount}`}
      size="small"
      closeOnBackdrop={false}
      footer={(
        <div className="onboarding-footer">
          <button className="button button-ghost" type="button" onClick={skipGuide}>Passer le guide</button>
          <div>
            {step > 0 && <button className="button button-secondary" type="button" onClick={previousStep}><ArrowLeft size={16} /> Retour</button>}
            <button className="button button-primary" type="button" onClick={nextStep}>
              {step === stepCount - 1 ? <><Check size={16} /> Terminer</> : <>Continuer <ArrowRight size={16} /></>}
            </button>
          </div>
        </div>
      )}
    >
      <div className="onboarding-progress" aria-label={`Progression : étape ${step + 1} sur ${stepCount}`}>
        {steps.map((item, index) => (
          <span
            key={item.title}
            className={index === step ? "current" : index < step ? "complete" : ""}
            aria-current={index === step ? "step" : undefined}
          />
        ))}
      </div>
      <div className="onboarding-illustration" aria-hidden="true"><Icon size={28} /></div>
      <p className="onboarding-description">{current.description}</p>
      {step === 2 && (
        <Link className="button button-secondary button-full onboarding-action" href={workspace.primaryAction.href} onClick={dismissGuide}>
          {workspace.primaryAction.label} <ArrowRight size={16} />
        </Link>
      )}
      <div className="onboarding-preview-note"><Eye size={15} /><span>Aperçu d’interface local. Le choix du rôle ne constitue pas une authentification ni une habilitation.</span></div>
    </Modal>
  );
}
