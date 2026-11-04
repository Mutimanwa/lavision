"use client";

import { useCallback, useEffect, useState } from "react";
import type { AuthMeResponse, OrganizationResponse } from "@lavision/contracts";
import { isApiError } from "@/lib/api-client";
import { getConfigurationSession } from "../services/rule-configuration-api";
import {
  getSchoolReferentials,
  updateSchoolOrganization,
  type SchoolReferentials,
} from "../services/school-referentials-api";

export type ReferentialsSessionState = "loading" | "unauthenticated" | "ready" | "error";
export type ReferentialsDataState = "idle" | "loading" | "ready" | "error";

function requestErrorMessage(error: unknown, fallback: string) {
  if (isApiError(error, 401)) return "Authentification requise ou session expirée. Reconnectez-vous puis réessayez.";
  if (isApiError(error, 403)) return "Accès non autorisé aux référentiels de cet établissement.";
  return error instanceof Error ? error.message : fallback;
}

export function useSchoolReferentials() {
  const [sessionState, setSessionState] = useState<ReferentialsSessionState>("loading");
  const [session, setSession] = useState<AuthMeResponse | null>(null);
  const [sessionError, setSessionError] = useState("");
  const [organizationId, setOrganizationId] = useState("");
  const [referentials, setReferentials] = useState<SchoolReferentials | null>(null);
  const [dataState, setDataState] = useState<ReferentialsDataState>("idle");
  const [dataError, setDataError] = useState("");

  const loadSession = useCallback(async () => {
    setSessionState("loading");
    setSessionError("");
    try {
      const result = await getConfigurationSession();
      setSession(result);
      setOrganizationId((current) => result.memberships.some((org) => org.organizationId === current)
        ? current
        : result.memberships[0]?.organizationId ?? "");
      setSessionState("ready");
    } catch (error) {
      setSession(null);
      setOrganizationId("");
      setSessionState(isApiError(error, 401) ? "unauthenticated" : "error");
      setSessionError(isApiError(error, 401) ? "" : requestErrorMessage(error, "Session indisponible."));
    }
  }, []);

  useEffect(() => {
    let active = true;
    queueMicrotask(() => {
      if (active) void loadSession();
    });
    return () => { active = false; };
  }, [loadSession]);

  const loadReferentials = useCallback(async () => {
    if (!organizationId || sessionState !== "ready") return;
    setDataState("loading");
    setDataError("");
    try {
      const result = await getSchoolReferentials(organizationId);
      setReferentials(result);
      setDataState("ready");
    } catch (error) {
      setReferentials(null);
      setDataState("error");
      setDataError(requestErrorMessage(error, "Référentiels indisponibles."));
    }
  }, [organizationId, sessionState]);

  useEffect(() => {
    let active = true;
    queueMicrotask(() => {
      if (!active) return;
      if (!organizationId || sessionState !== "ready") {
        setReferentials(null);
        setDataState("idle");
        return;
      }
      setDataState("loading");
      setDataError("");
      void getSchoolReferentials(organizationId).then((result) => {
        if (!active) return;
        setReferentials(result);
        setDataState("ready");
      }).catch((error: unknown) => {
        if (!active) return;
        setReferentials(null);
        setDataState("error");
        setDataError(requestErrorMessage(error, "Référentiels indisponibles."));
      });
    });
    return () => { active = false; };
  }, [organizationId, sessionState]);

  const saveOrganization = useCallback(async (name: string): Promise<OrganizationResponse> => {
    if (!organizationId) throw new Error("Sélectionnez un établissement autorisé.");
    const organization = await updateSchoolOrganization(organizationId, name);
    setReferentials((current) => current ? { ...current, organization } : current);
    return organization;
  }, [organizationId]);

  return {
    sessionState,
    session,
    sessionError,
    organizationId,
    setOrganizationId,
    referentials,
    dataState,
    dataError,
    reloadSession: loadSession,
    reloadReferentials: loadReferentials,
    saveOrganization,
  };
}
