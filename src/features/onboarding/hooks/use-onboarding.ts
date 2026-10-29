"use client";

import { useContext } from "react";
import { OnboardingContext } from "../context/onboarding-context";

export function useOnboarding() {
  const context = useContext(OnboardingContext);
  if (!context) throw new Error("useOnboarding doit être utilisé dans OnboardingProvider");
  return context;
}
