"use client";

import { createContext } from "react";
import type { GuideProgress } from "../services/onboarding-storage";

export interface OnboardingContextValue {
  open: boolean;
  step: number;
  stepCount: number;
  progress?: GuideProgress;
  openGuide: () => void;
  dismissGuide: () => void;
  skipGuide: () => void;
  nextStep: () => void;
  previousStep: () => void;
  restartGuide: () => void;
}

export const OnboardingContext = createContext<OnboardingContextValue | null>(null);
