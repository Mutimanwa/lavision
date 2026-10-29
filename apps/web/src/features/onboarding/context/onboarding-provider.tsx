"use client";

import { useCallback, useEffect, useMemo, useRef, useState, type ReactNode } from "react";
import { useWorkspace } from "@/features/auth/hooks/use-workspace";
import type { RoleId } from "@/features/auth/data/roles";
import {
  loadGuideProgress,
  saveGuideProgress,
  type GuideProgressMap,
  type GuideStatus,
} from "../services/onboarding-storage";
import { OnboardingContext } from "./onboarding-context";

const STEP_COUNT = 4;

export function OnboardingProvider({ children }: { children: ReactNode }) {
  const { role, hydrated: workspaceHydrated } = useWorkspace();
  const [progressMap, setProgressMap] = useState<GuideProgressMap>({});
  const [hydrated, setHydrated] = useState(false);
  const [open, setOpen] = useState(false);
  const [step, setStep] = useState(0);
  const autoOpenedFor = useRef<RoleId | null>(null);
  const previousRole = useRef(role);
  const progress = progressMap[role];

  useEffect(() => {
    const timer = window.setTimeout(() => {
      setProgressMap(loadGuideProgress());
      setHydrated(true);
    }, 0);
    return () => window.clearTimeout(timer);
  }, []);

  useEffect(() => {
    if (hydrated) saveGuideProgress(progressMap);
  }, [hydrated, progressMap]);

  useEffect(() => {
    if (previousRole.current === role) return;
    previousRole.current = role;
    autoOpenedFor.current = null;
    const timer = window.setTimeout(() => {
      setOpen(false);
      setStep(0);
    }, 0);
    return () => window.clearTimeout(timer);
  }, [role]);

  useEffect(() => {
    if (!hydrated || !workspaceHydrated || autoOpenedFor.current === role) return;
    autoOpenedFor.current = role;
    if (progressMap[role]) return;
    const timer = window.setTimeout(() => {
      setStep(0);
      setOpen(true);
    }, 0);
    return () => window.clearTimeout(timer);
  }, [hydrated, progressMap, role, workspaceHydrated]);

  const record = useCallback((status: GuideStatus, nextStep: number) => {
    setProgressMap((current) => ({
      ...current,
      [role]: { status, step: Math.max(0, Math.min(STEP_COUNT - 1, nextStep)) },
    }));
  }, [role]);

  const openGuide = useCallback(() => {
    const nextStep = progress?.status === "in_progress" ? progress.step : 0;
    setStep(nextStep);
    setOpen(true);
  }, [progress]);

  const dismissGuide = useCallback(() => {
    record("in_progress", step);
    setOpen(false);
  }, [record, step]);

  const skipGuide = useCallback(() => {
    record("skipped", step);
    setOpen(false);
  }, [record, step]);

  const nextStep = useCallback(() => {
    if (step >= STEP_COUNT - 1) {
      record("completed", STEP_COUNT - 1);
      setOpen(false);
      return;
    }
    const next = step + 1;
    setStep(next);
    record("in_progress", next);
  }, [record, step]);

  const previousStep = useCallback(() => {
    const previous = Math.max(0, step - 1);
    setStep(previous);
    record("in_progress", previous);
  }, [record, step]);

  const restartGuide = useCallback(() => {
    setStep(0);
    record("in_progress", 0);
    setOpen(true);
  }, [record]);

  const value = useMemo(() => ({
    open,
    step,
    stepCount: STEP_COUNT,
    progress,
    openGuide,
    dismissGuide,
    skipGuide,
    nextStep,
    previousStep,
    restartGuide,
  }), [dismissGuide, nextStep, open, openGuide, previousStep, progress, restartGuide, skipGuide, step]);

  return <OnboardingContext.Provider value={value}>{children}</OnboardingContext.Provider>;
}
