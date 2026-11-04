"use client";

import { createContext } from "react";
import type { GuideProgress } from "../services/onboarding-storage";
import type { ProductTourId } from "../services/product-tour";

export interface OnboardingContextValue {
  open: boolean;
  step: number;
  initialStep: number;
  stepCount: number;
  tourId: ProductTourId | null;
  session: number;
  progress?: GuideProgress;
  openGuide: () => void;
  dismissGuide: (step?: number) => void;
  skipGuide: (step?: number) => void;
  completeGuide: (step?: number) => void;
  reportStep: (step: number) => void;
  restartGuide: () => void;
}

export const OnboardingContext = createContext<OnboardingContextValue | null>(null);
