"use client";

import { CheckCircle2, CirclePlay, RotateCcw } from "lucide-react";
import { useOnboarding } from "../hooks/use-onboarding";

export function FirstStepsCard() {
  const { progress, stepCount, openGuide, restartGuide } = useOnboarding();
  const completed = progress?.status === "completed";
  const currentStep = progress?.status === "in_progress" ? progress.step + 1 : 1;

  return (
    <section className={`card first-steps-card ${completed ? "complete" : ""}`} aria-labelledby="first-steps-title" data-tour="first-steps">
      <span className="first-steps-icon" aria-hidden="true">
        {completed ? <CheckCircle2 size={22} /> : <CirclePlay size={22} />}
      </span>
      <div className="first-steps-copy">
        <p className="eyebrow">Premiers pas</p>
        <h2 id="first-steps-title">{completed ? "Vous connaissez votre espace" : "Découvrez votre espace avec une visite interactive"}</h2>
        <p>{completed ? "La visite reste disponible si vous souhaitez revoir les menus et les actions de cette page." : "Des repères surgissent directement sur les menus, les boutons et les zones de travail pour vous montrer comment les utiliser."}</p>
        {!completed && <small>Étape {currentStep} sur {stepCount}{progress?.status === "skipped" ? " · visite passée" : ""}</small>}
      </div>
      <button className="button button-secondary" type="button" onClick={completed ? restartGuide : openGuide}>
        {completed ? <RotateCcw size={16} /> : <CirclePlay size={16} />}
        {completed ? "Relancer" : progress ? "Reprendre" : "Commencer"}
      </button>
    </section>
  );
}
