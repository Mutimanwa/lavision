"use client";

import { useCallback, useEffect, useState } from "react";
import type { AuthMeResponse, RuleDomain } from "@lavision/contracts";
import { getConfigurationSession, getRuleCatalog, type RuleCatalogResult } from "../services/rule-configuration-api";
import { isApiError } from "@/lib/api-client";

export type ConfigurationSessionState = "loading" | "unauthenticated" | "ready" | "error";

function requestErrorMessage(error: unknown, fallback: string): string {
  if (isApiError(error, 401)) return "Authentification requise ou session expirée. Reconnectez-vous puis réessayez.";
  if (isApiError(error, 403)) return "Accès non autorisé pour cet établissement ou ce domaine de règles.";
  return error instanceof Error ? error.message : fallback;
}

export function useRuleConfiguration() {
  const [sessionState, setSessionState] = useState<ConfigurationSessionState>("loading");
  const [session, setSession] = useState<AuthMeResponse | null>(null);
  const [sessionError, setSessionError] = useState("");
  const [organizationId, setOrganizationId] = useState("");
  const [domain, setDomain] = useState<RuleDomain>("academic");
  const [catalog, setCatalog] = useState<RuleCatalogResult | null>(null);
  const [catalogState, setCatalogState] = useState<"idle" | "loading" | "ready" | "error">("idle");
  const [catalogError, setCatalogError] = useState("");

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

  const loadCatalog = useCallback(async () => {
    if (!organizationId || sessionState !== "ready") return;
    setCatalogState("loading");
    setCatalogError("");
    try {
      const result = await getRuleCatalog(organizationId, domain);
      setCatalog(result);
      setCatalogState("ready");
    } catch (error) {
      setCatalog(null);
      setCatalogState("error");
      setCatalogError(requestErrorMessage(error, "Catalogue indisponible."));
    }
  }, [domain, organizationId, sessionState]);

  useEffect(() => {
    let active = true;
    queueMicrotask(() => {
      if (!active) return;
      if (!organizationId || sessionState !== "ready") {
        setCatalog(null);
        setCatalogState("idle");
        return;
      }
      setCatalogState("loading");
      setCatalogError("");
      void getRuleCatalog(organizationId, domain).then((result) => {
        if (!active) return;
        setCatalog(result);
        setCatalogState("ready");
      }).catch((error: unknown) => {
        if (!active) return;
        setCatalog(null);
        setCatalogState("error");
        setCatalogError(requestErrorMessage(error, "Catalogue indisponible."));
      });
    });
    return () => { active = false; };
  }, [domain, organizationId, sessionState]);

  return {
    sessionState,
    session,
    sessionError,
    organizationId,
    setOrganizationId,
    domain,
    setDomain,
    catalog,
    catalogState,
    catalogError,
    reloadSession: loadSession,
    reloadCatalog: loadCatalog,
  };
}
