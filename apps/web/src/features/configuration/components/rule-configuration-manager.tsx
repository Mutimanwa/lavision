"use client";

import { useMemo, useState, type FormEvent } from "react";
import type { RuleDefinitionResponse, RuleDomain, RuleValidationResponse } from "@lavision/contracts";
import { ApiError } from "@/lib/api-client";
import { useRuleConfiguration } from "../hooks/use-rule-configuration";
import { prepareRuleDraft, validateRuleConfigurationValue } from "../services/rule-configuration-api";
import { parseRuleValue, ruleValueTypeLabel } from "../services/rule-value";

const domainLabels: Record<RuleDomain, string> = {
  academic: "Académique",
  attendance: "Présences",
  finance: "Finances",
  hr: "Ressources humaines",
  payroll: "Paie",
  documents: "Documents",
  calendar: "Calendrier",
};

function errorMessage(error: unknown): string {
  if (error instanceof ApiError) {
    const issues = error.issues.map((issue) => `${issue.path} : ${issue.message}`).join(" ");
    const prefix = error.status === 401 ? "Authentification requise ou session expirée." : error.status === 403 ? "Accès non autorisé." : "";
    return `${prefix}${prefix ? " " : ""}${error.message}${issues ? ` ${issues}` : ""}`;
  }
  return error instanceof Error ? error.message : "Une erreur inattendue est survenue.";
}

function definitionConstraints(definition: RuleDefinitionResponse): string {
  const constraints = definition.constraints;
  if (!constraints || Object.keys(constraints).length === 0) return "Aucune contrainte supplémentaire dans le catalogue.";
  return JSON.stringify(constraints);
}

export function RuleConfigurationManager() {
  const {
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
    reloadSession,
    reloadCatalog,
  } = useRuleConfiguration();
  const [query, setQuery] = useState("");
  const [selectedKey, setSelectedKey] = useState("");
  const [rawValue, setRawValue] = useState("");
  const [notApplicable, setNotApplicable] = useState(false);
  const [effectiveFrom, setEffectiveFrom] = useState("");
  const [timezone, setTimezone] = useState("");
  const [source, setSource] = useState("");
  const [reason, setReason] = useState("");
  const [validation, setValidation] = useState<RuleValidationResponse | null>(null);
  const [feedback, setFeedback] = useState<{ kind: "success" | "error" | "info"; text: string } | null>(null);
  const [busy, setBusy] = useState<"validate" | "save" | null>(null);

  const selected = catalog?.definitions.find((definition) => definition.key === selectedKey) ?? null;
  const visibleDefinitions = useMemo(() => {
    const needle = query.trim().toLocaleLowerCase("fr");
    return (catalog?.definitions ?? []).filter((definition) =>
      !needle || `${definition.key} ${definition.label} ${definition.description}`.toLocaleLowerCase("fr").includes(needle),
    );
  }, [catalog, query]);

  function chooseDefinition(definition: RuleDefinitionResponse) {
    setSelectedKey(definition.key);
    setRawValue("");
    setNotApplicable(false);
    setValidation(null);
    setFeedback(null);
  }

  function changeDomain(nextDomain: RuleDomain) {
    setDomain(nextDomain);
    setSelectedKey("");
    setRawValue("");
    setNotApplicable(false);
    setValidation(null);
    setFeedback(null);
    setQuery("");
  }

  function changeValue(value: string) {
    setRawValue(value);
    setValidation(null);
    setFeedback(null);
  }

  function toggleNotApplicable(checked: boolean) {
    setNotApplicable(checked);
    if (checked) setRawValue("");
    setValidation(null);
    setFeedback(null);
  }

  function parsedValue(definition: RuleDefinitionResponse) {
    const parsed = parseRuleValue(definition, rawValue);
    if (parsed.error) throw new Error(parsed.error);
    if (parsed.value === undefined) {
      throw new Error(definition.required
        ? "Cette règle est obligatoire : saisissez une valeur ou laissez le paramètre non configuré."
        : "Saisissez une valeur pour créer un brouillon ; une règle facultative vide reste non configurée.");
    }
    return parsed.value;
  }

  async function validateCurrentValue(): Promise<{ value?: unknown; notApplicable: boolean } | null> {
    if (!selected || !organizationId) return null;
    if (notApplicable) {
      if (selected.required) throw new Error("Une règle obligatoire ne peut pas être déclarée non applicable.");
      if (!reason.trim()) throw new Error("Le non-applicable exige un motif explicite.");
      return { notApplicable: true };
    }
    const value = parsedValue(selected);
    const result = await validateRuleConfigurationValue(organizationId, selected.key, value);
    setValidation(result);
    if (!result.valid || result.issues.length > 0) {
      setFeedback({ kind: "error", text: "La valeur ne respecte pas les contraintes du catalogue." });
      return null;
    }
    return { notApplicable: false, value: result.normalizedValue === undefined ? value : result.normalizedValue };
  }

  async function handleValidate() {
    if (!selected) return;
    setBusy("validate");
    setFeedback(null);
    try {
      const checked = await validateCurrentValue();
      if (checked?.notApplicable) {
        setFeedback({
          kind: "info",
          text: "Le non-applicable est prêt à être enregistré avec son motif. L’API vérifiera ce choix lors de la création du brouillon ; aucune valeur ne sera envoyée.",
        });
      } else if (checked) {
        setFeedback({
          kind: "success",
          text: "Valeur acceptée par le validateur du catalogue. Cette étape ne publie pas la règle et ne remplace pas la validation pédagogique.",
        });
      }
    } catch (error) {
      setFeedback({ kind: "error", text: errorMessage(error) });
    } finally {
      setBusy(null);
    }
  }

  async function handleSave(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!selected || !organizationId) return;
    setBusy("save");
    setFeedback(null);
    try {
      if (!effectiveFrom || !timezone.trim() || !source.trim() || !reason.trim()) {
        throw new Error("Renseignez la date d’effet, le fuseau, la source et le motif : aucune métadonnée n’est déduite.");
      }
      const checked = await validateCurrentValue();
      if (!checked) return;
      const draft = await prepareRuleDraft({
        organizationId,
        definition: selected,
        value: checked.value,
        notApplicable: checked.notApplicable,
        effectiveFrom,
        timezone: timezone.trim(),
        source: source.trim(),
        reason: reason.trim(),
      });
      setFeedback({
        kind: "success",
        text: `Brouillon version ${draft.version} enregistré dans l’API (${draft.id}). Il reste à passer par le circuit de validation et d’approbation.`,
      });
    } catch (error) {
      setFeedback({ kind: "error", text: errorMessage(error) });
    } finally {
      setBusy(null);
    }
  }

  return (
    <section className="card rule-config" aria-labelledby="rule-config-title">
      <div className="section-heading">
        <div>
          <span className="eyebrow">Paramètres · source API</span>
          <h2 id="rule-config-title">Configuration scolaire</h2>
          <p>Consultez le catalogue versionné, contrôlez une valeur et préparez un brouillon rattaché à votre établissement.</p>
        </div>
        <span className="badge badge-amber">Brouillons uniquement</span>
      </div>

      {sessionState === "loading" && <p className="rule-config-state" role="status">Vérification de la session API…</p>}
      {sessionState === "unauthenticated" && (
        <div className="notice notice-warning" role="status">
          <strong>Session API requise.</strong>
          <p>Connectez-vous avec le compte de l’établissement pour consulter ou préparer une configuration. Aucun compte de démonstration n’est utilisé ici.</p>
          <div className="button-row">
            <button className="button button-secondary" type="button" onClick={() => void reloadSession()}>Revérifier la session</button>
          </div>
        </div>
      )}
      {sessionState === "error" && (
        <div className="notice notice-error" role="alert">
          <strong>Session API indisponible.</strong>
          <p>{sessionError}</p>
          <button className="button button-secondary" type="button" onClick={() => void reloadSession()}>Réessayer</button>
        </div>
      )}
      {sessionState === "ready" && session && session.memberships.length === 0 && (
        <div className="notice notice-warning" role="status">
          <strong>Aucun établissement autorisé.</strong>
          <p>Votre session est valide, mais elle ne comporte aucune organisation accessible. Demandez une habilitation nominative ; aucune configuration ne peut être créée.</p>
        </div>
      )}

      {sessionState === "ready" && session && session.memberships.length > 0 && (
        <>
          <div className="rule-config-context">
            <label className="field">
              <span>Établissement</span>
              <select value={organizationId} onChange={(event) => { setOrganizationId(event.target.value); setSelectedKey(""); setRawValue(""); setNotApplicable(false); setValidation(null); setFeedback(null); }}>
                {session.memberships.map((organization) => (
                  <option key={organization.organizationId} value={organization.organizationId}>
                    {organization.organizationName} · {organization.organizationCode}
                  </option>
                ))}
              </select>
            </label>
            <label className="field">
              <span>Domaine de règles</span>
              <select value={domain} onChange={(event) => changeDomain(event.target.value as RuleDomain)}>
                {(Object.keys(domainLabels) as RuleDomain[]).map((item) => <option key={item} value={item}>{item} · {domainLabels[item]}</option>)}
              </select>
            </label>
            <div className="rule-config-session">
              <span className="eyebrow">Session vérifiée</span>
              <strong>{session.user.displayName}</strong>
              <small>{session.memberships.find((org) => org.organizationId === organizationId)?.roles.join(", ")}</small>
            </div>
          </div>

          <div className="notice notice-info rule-config-note">
            <strong>Les informations absentes restent configurables.</strong>
            <p>Le catalogue ne fournit aucune valeur par défaut. Une règle obligatoire non configurée reste bloquante ; une valeur saisie ici n’est pas encore une règle publiée ni un calcul officiel.</p>
          </div>

          {catalogState === "loading" && <p className="rule-config-state" role="status">Chargement du catalogue API…</p>}
          {catalogState === "error" && (
            <div className="notice notice-error" role="alert">
              <strong>Catalogue inaccessible.</strong>
              <p>{catalogError}</p>
              <button className="button button-secondary" type="button" onClick={() => void reloadCatalog()}>Réessayer</button>
            </div>
          )}
          {catalogState === "ready" && catalog && (
            <div className="rule-config-layout">
              <div className="rule-config-catalog">
                <div className="rule-config-catalog-header">
                  <div>
                    <h3>{domainLabels[domain]}</h3>
                    <p>Catalogue v{catalog.catalogVersion} · {catalog.definitions.length} paramètres</p>
                  </div>
                  <span className="badge badge-blue">API</span>
                </div>
                <label className="field rule-config-search">
                  <span>Rechercher un paramètre</span>
                  <input value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Clé, libellé ou description" />
                </label>
                <div className="rule-definition-list">
                  {visibleDefinitions.map((definition) => (
                    <button
                      className={`rule-definition-item${selectedKey === definition.key ? " is-selected" : ""}`}
                      type="button"
                      key={definition.key}
                      onClick={() => chooseDefinition(definition)}
                    >
                      <span className="rule-definition-item-top">
                        <code>{definition.key}</code>
                        <span className={`badge ${definition.required ? "badge-amber" : ""}`}>{definition.required ? "Obligatoire" : "Facultatif"}</span>
                      </span>
                      <strong>{definition.label}</strong>
                      <small>{ruleValueTypeLabel(definition.type)} · {definition.scopes.join(" · ")}</small>
                    </button>
                  ))}
                  {visibleDefinitions.length === 0 && <p className="rule-config-empty">Aucun paramètre ne correspond à cette recherche.</p>}
                </div>
              </div>

              <div className="rule-config-editor">
                {!selected && (
                  <div className="empty-state">
                    <span className="empty-state-icon" aria-hidden="true">⚙</span>
                    <h3>Choisir un paramètre</h3>
                    <p>Sélectionnez une règle du catalogue pour examiner ses contraintes et préparer une valeur.</p>
                  </div>
                )}
                {selected && (
                  <form onSubmit={(event) => void handleSave(event)}>
                    <div className="rule-config-editor-heading">
                      <div>
                        <span className="eyebrow">{selected.domain} · {selected.key}</span>
                        <h3>{selected.label}</h3>
                      </div>
                      <span className={`badge ${selected.required ? "badge-amber" : ""}`}>{selected.required ? "Obligatoire" : "Facultatif"}</span>
                    </div>
                    <p>{selected.description}</p>
                    <dl className="rule-config-metadata">
                      <div><dt>Type</dt><dd>{ruleValueTypeLabel(selected.type)}</dd></div>
                      <div><dt>Portées</dt><dd>{selected.scopes.join(", ")}</dd></div>
                      <div><dt>Contraintes</dt><dd><code>{definitionConstraints(selected)}</code></dd></div>
                    </dl>

                    <label className="field">
                      <span>Valeur à contrôler</span>
                      {selected.type === "boolean" && (
                        <select value={rawValue} onChange={(event) => changeValue(event.target.value)} required={selected.required && !notApplicable} disabled={notApplicable}>
                          <option value="">Choisir explicitement…</option>
                          <option value="true">Oui</option>
                          <option value="false">Non</option>
                        </select>
                      )}
                      {selected.type === "enum" && (
                        <select value={rawValue} onChange={(event) => changeValue(event.target.value)} required={selected.required && !notApplicable} disabled={notApplicable}>
                          <option value="">Choisir explicitement…</option>
                          {(selected.constraints?.values ?? []).map((value) => <option key={value} value={value}>{value}</option>)}
                        </select>
                      )}
                      {selected.type === "structured" && (
                        <textarea rows={7} value={rawValue} onChange={(event) => changeValue(event.target.value)} placeholder="Objet ou liste JSON fourni et validé par l’établissement" required={selected.required && !notApplicable} disabled={notApplicable} />
                      )}
                      {!["boolean", "enum", "structured"].includes(selected.type) && (
                        <input
                          type={selected.type === "date" ? "date" : selected.type === "integer" ? "number" : "text"}
                          step={selected.type === "integer" ? "1" : undefined}
                          inputMode={selected.type === "decimal" ? "decimal" : undefined}
                          value={rawValue}
                          onChange={(event) => changeValue(event.target.value)}
                          required={selected.required && !notApplicable}
                          disabled={notApplicable}
                          placeholder="Aucune valeur présélectionnée"
                        />
                      )}
                    </label>
                    {!selected.required && (
                      <label className="rule-config-na">
                        <input type="checkbox" checked={notApplicable} onChange={(event) => toggleNotApplicable(event.target.checked)} />
                        <span>Déclarer ce paramètre non applicable pour ce brouillon (motif obligatoire).</span>
                      </label>
                    )}
                    {notApplicable && <p className="field-help">Aucune valeur ne sera envoyée ; l’API conservera le non-applicable et son motif dans la version DRAFT.</p>}

                    <div className="notice notice-info rule-config-scope-note">
                      <strong>Portée explicite : établissement.</strong>
                      <p>Ce brouillon est lié à l’organisation sélectionnée. Les portées plus précises autorisées par le catalogue ({selected.scopes.filter((scope) => scope !== "organization").join(", ") || "aucune"}) devront faire l’objet de bindings distincts ; aucune valeur de cours, section ou option n’est déduite.</p>
                    </div>

                    <fieldset className="rule-config-source">
                      <legend>Traçabilité du brouillon</legend>
                      <div className="form-grid form-grid-2">
                        <label className="field"><span>Date d’effet *</span><input type="date" value={effectiveFrom} onChange={(event) => setEffectiveFrom(event.target.value)} required /></label>
                        <label className="field"><span>Fuseau IANA *</span><input value={timezone} onChange={(event) => setTimezone(event.target.value)} placeholder="À confirmer par l’établissement" required /></label>
                      </div>
                      <label className="field"><span>Source de la valeur *</span><input value={source} onChange={(event) => setSource(event.target.value)} placeholder="Document, décision ou référence vérifiable" required /></label>
                      <label className="field"><span>Motif et contexte *</span><textarea rows={3} value={reason} onChange={(event) => setReason(event.target.value)} placeholder="Pourquoi cette valeur est-elle proposée ? Qui devra la valider ?" required /></label>
                    </fieldset>

                    {validation && validation.issues.length > 0 && (
                      <ul className="rule-config-issues" aria-label="Problèmes de validation">
                        {validation.issues.map((issue, index) => <li key={`${issue.path}-${index}`}><code>{issue.path}</code> {issue.message}</li>)}
                      </ul>
                    )}
                    {feedback && <div className={`notice notice-${feedback.kind}`} role={feedback.kind === "error" ? "alert" : "status"}>{feedback.text}</div>}
                    <div className="button-row">
                      <button className="button button-secondary" type="button" disabled={busy !== null} onClick={() => void handleValidate()}>{busy === "validate" ? "Contrôle…" : "Valider la valeur"}</button>
                      <button className="button button-primary" type="submit" disabled={busy !== null}>{busy === "save" ? "Enregistrement…" : "Enregistrer le brouillon"}</button>
                    </div>
                    <p className="field-help">L’enregistrement crée une nouvelle version DRAFT. La validation pédagogique, l’approbation et la publication ne sont pas déclenchées par cet écran.</p>
                  </form>
                )}
              </div>
            </div>
          )}
        </>
      )}
    </section>
  );
}
