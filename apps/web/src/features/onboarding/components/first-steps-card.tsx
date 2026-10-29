"use client";

import { CheckCircle2, CirclePlay, RotateCcw } from "lucide-react";
import { useOnboarding } from "../hooks/use-onboarding";

export function FirstStepsCard() {
  const { progress, stepCount, openGuide, restartGuide } = useOnboarding();
  const completed = progress?.status === "completed";
  const currentStep = progress?.status === "in_progress" ? progress.step + 1 : 1;

  return (
    <section className={`card first-steps-card ${completed ? "complete" : ""}`} aria-labelledby="first-steps-title">
      <span className="first-steps-icon" aria-hidden="true">
        {completed ? <CheckCircle2 size={22} /> : <CirclePlay size={22} />}
      </span>
      <div className="first-steps-copy">
        <p className="eyebrow">Premiers pas</p>
        <h2 id="first-steps-title">{completed ? "Vous connaissez votre espace" : "Découvrez votre espace en quelques étapes"}</h2>
        <p>{completed ? "Le guide reste disponible si vous souhaitez revoir les principes de navigation." : "Un guide court présente les menus, les états des modules et votre première action utile."}</p>
        {!completed && <small>Étape {currentStep} sur {stepCount}{progress?.status === "skipped" ? " · guide passé" : ""}</small>}
      </div>
      <button className="button button-secondary" type="button" onClick={completed ? restartGuide : openGuide}>
        {completed ? <RotateCcw size={16} /> : <CirclePlay size={16} />}
        {completed ? "Relancer" : progress ? "Reprendre" : "Commencer"}
      </button>
    </section>
  );
}
