"use client";

import { useContext } from "react";
import { SchoolContext } from "../context/school-context";

export function useSchool() {
  const context = useContext(SchoolContext);
  if (!context) throw new Error("useSchool doit être utilisé dans SchoolProvider");
  return context;
}
