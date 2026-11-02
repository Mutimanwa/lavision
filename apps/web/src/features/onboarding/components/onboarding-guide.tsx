"use client";

import { useEffect, useRef } from "react";
import { driver } from "driver.js";
import { useOnboarding } from "../hooks/use-onboarding";
import { getProductTourSteps } from "../services/product-tour";

export function OnboardingGuide() {
  const {
    open,
    tourId,
    session,
    initialStep,
    reportStep,
    dismissGuide,
    skipGuide,
    completeGuide,
  } = useOnboarding();
  const handlersRef = useRef({ reportStep, dismissGuide, skipGuide, completeGuide });

  useEffect(() => {
    handlersRef.current = { reportStep, dismissGuide, skipGuide, completeGuide };
  }, [completeGuide, dismissGuide, reportStep, skipGuide]);

  useEffect(() => {
    if (!open || !tourId) return;

    let cancelled = false;
    let skipRequested = false;
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    const steps = getProductTourSteps(tourId);

    const driverInstance = driver({
      steps,
      animate: !reducedMotion,
      smoothScroll: !reducedMotion,
      allowClose: true,
      allowScroll: true,
      allowKeyboardControl: true,
      overlayClickBehavior: "none",
      overlayColor: "#102f28",
      overlayOpacity: 0.64,
      disableActiveInteraction: true,
      skipMissingElement: true,
      stagePadding: 8,
      stageRadius: 10,
      popoverOffset: 12,
      popoverClass: "lavision-product-tour",
      showButtons: ["next", "previous", "close"],
      showProgress: true,
      progressText: "Étape {{current}} sur {{total}}",
      nextBtnText: "Suivant",
      prevBtnText: "Précédent",
      doneBtnText: "Terminer",
      closeBtnLabel: "Fermer la visite",
      onHighlighted: (_element, _step, { index }) => {
        if (!cancelled && index !== undefined) handlersRef.current.reportStep(index);
      },
      onPopoverRender: (popover) => {
        if (popover.footer.querySelector("[data-product-tour-skip]")) return;
        const skipButton = document.createElement("button");
        skipButton.type = "button";
        skipButton.className = "driver-popover-skip-btn";
        skipButton.dataset.productTourSkip = "true";
        skipButton.textContent = "Passer la visite";
        skipButton.setAttribute("aria-label", "Passer la visite guidée");
        skipButton.addEventListener("click", () => {
          skipRequested = true;
          handlersRef.current.skipGuide(driverInstance.getActiveIndex());
          driverInstance.destroy();
        });
        popover.footer.insertBefore(skipButton, popover.footerButtons);
      },
      onDestroyed: (_element, _step, { reason, index }) => {
        if (cancelled || skipRequested) return;
        if (reason === "done") handlersRef.current.completeGuide(index);
        else if (reason !== "replaced") handlersRef.current.dismissGuide(index);
      },
    });

    const frame = window.requestAnimationFrame(() => {
      if (!cancelled) driverInstance.drive(Math.min(initialStep, steps.length - 1));
    });

    return () => {
      cancelled = true;
      window.cancelAnimationFrame(frame);
      driverInstance.destroy();
    };
  }, [initialStep, open, session, tourId]);

  return null;
}
