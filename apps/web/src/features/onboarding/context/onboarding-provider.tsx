"use client";

import { usePathname } from "next/navigation";
import { useCallback, useEffect, useMemo, useRef, useState, type ReactNode } from "react";
import { useWorkspace } from "@/features/auth/hooks/use-workspace";
import type { GuideStatus } from "../services/onboarding-storage";
import {
  getProductTourId,
  getProductTourStepCount,
  type ProductTourId,
} from "../services/product-tour";
import {
  getProductTourStorageKey,
  loadProductTourProgress,
  saveProductTourProgress,
  type ProductTourProgressMap,
} from "../services/product-tour-storage";
import { OnboardingContext } from "./onboarding-context";

interface ActiveTour {
  key: string;
  tourId: ProductTourId;
  initialStep: number;
  session: number;
}

function boundedStep(step: number, tourId: ProductTourId): number {
  return Math.max(0, Math.min(getProductTourStepCount(tourId) - 1, Math.floor(step)));
}

export function OnboardingProvider({ children }: { children: ReactNode }) {
  const pathname = usePathname();
  const { role, hydrated: workspaceHydrated } = useWorkspace();
  const currentTourId = getProductTourId(pathname);
  const currentKey = currentTourId ? getProductTourStorageKey(role, currentTourId) : null;
  const [progressMap, setProgressMap] = useState<ProductTourProgressMap>({});
  const [hydrated, setHydrated] = useState(false);
  const [activeTour, setActiveTour] = useState<ActiveTour | null>(null);
  const [step, setStep] = useState(0);
  const autoOpenedFor = useRef<string | null>(null);
  const nextSession = useRef(0);
  const progress = currentKey ? progressMap[currentKey] : undefined;

  useEffect(() => {
    const timer = window.setTimeout(() => {
      setProgressMap(loadProductTourProgress());
      setHydrated(true);
    }, 0);
    return () => window.clearTimeout(timer);
  }, []);

  useEffect(() => {
    if (hydrated) saveProductTourProgress(progressMap);
  }, [hydrated, progressMap]);

  const recordProgress = useCallback((key: string, tourId: ProductTourId, status: GuideStatus, nextStep: number) => {
    setProgressMap((current) => ({
      ...current,
      [key]: { status, step: boundedStep(nextStep, tourId) },
    }));
  }, []);

  const startTour = useCallback((restart: boolean) => {
    if (!currentKey || !currentTourId) return;
    const resumeStep = !restart && progress?.status === "in_progress"
      ? boundedStep(progress.step, currentTourId)
      : 0;
    nextSession.current += 1;
    setStep(resumeStep);
    setActiveTour({
      key: currentKey,
      tourId: currentTourId,
      initialStep: resumeStep,
      session: nextSession.current,
    });
    if (restart) recordProgress(currentKey, currentTourId, "in_progress", 0);
  }, [currentKey, currentTourId, progress, recordProgress]);

  const openGuide = useCallback(() => startTour(false), [startTour]);
  const restartGuide = useCallback(() => startTour(true), [startTour]);

  const dismissGuide = useCallback((nextStep?: number) => {
    if (!activeTour) return;
    recordProgress(activeTour.key, activeTour.tourId, "in_progress", nextStep ?? step);
    setActiveTour(null);
  }, [activeTour, recordProgress, step]);

  const skipGuide = useCallback((nextStep?: number) => {
    if (!activeTour) return;
    recordProgress(activeTour.key, activeTour.tourId, "skipped", nextStep ?? step);
    setActiveTour(null);
  }, [activeTour, recordProgress, step]);

  const completeGuide = useCallback((nextStep?: number) => {
    if (!activeTour) return;
    recordProgress(activeTour.key, activeTour.tourId, "completed", nextStep ?? step);
    setActiveTour(null);
  }, [activeTour, recordProgress, step]);

  const reportStep = useCallback((nextStep: number) => {
    if (!activeTour) return;
    const bounded = boundedStep(nextStep, activeTour.tourId);
    setStep(bounded);
    recordProgress(activeTour.key, activeTour.tourId, "in_progress", bounded);
  }, [activeTour, recordProgress]);

  // Une navigation manuelle ferme la visite de la page précédente. La dernière
  // étape connue est conservée, puis la page suivante peut proposer sa propre visite.
  useEffect(() => {
    if (!activeTour || activeTour.key === currentKey) return;
    const timer = window.setTimeout(() => {
      recordProgress(activeTour.key, activeTour.tourId, "in_progress", step);
      setActiveTour(null);
      setStep(0);
    }, 0);
    return () => window.clearTimeout(timer);
  }, [activeTour, currentKey, recordProgress, step]);

  useEffect(() => {
    if (!hydrated || !workspaceHydrated || !currentKey || !currentTourId || activeTour) return;
    if (autoOpenedFor.current === currentKey || progress) return;
    autoOpenedFor.current = currentKey;
    const timer = window.setTimeout(() => startTour(false), 450);
    return () => window.clearTimeout(timer);
  }, [activeTour, currentKey, currentTourId, hydrated, progress, startTour, workspaceHydrated]);

  const stepCount = activeTour
    ? getProductTourStepCount(activeTour.tourId)
    : currentTourId ? getProductTourStepCount(currentTourId) : 0;

  const value = useMemo(() => ({
    open: activeTour !== null,
    step,
    initialStep: activeTour?.initialStep ?? 0,
    stepCount,
    tourId: activeTour?.tourId ?? null,
    session: activeTour?.session ?? 0,
    progress,
    openGuide,
    dismissGuide,
    skipGuide,
    completeGuide,
    reportStep,
    restartGuide,
  }), [activeTour, completeGuide, dismissGuide, openGuide, progress, reportStep, restartGuide, skipGuide, step, stepCount]);

  return <OnboardingContext.Provider value={value}>{children}</OnboardingContext.Provider>;
}
