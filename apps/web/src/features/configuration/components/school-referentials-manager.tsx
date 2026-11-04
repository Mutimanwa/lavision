"use client";

import { useState, type FormEvent, type ReactNode } from "react";
import Link from "next/link";
import { Building2, DoorOpen, Layers3, RefreshCw, Save } from "lucide-react";
import type { OrganizationResponse } from "@lavision/contracts";
import { useSchoolReferentials } from "../hooks/use-school-referentials";

function errorMessage(error: unknown) {
  return error instanceof Error ? error.message : "Une erreur inattendue est survenue.";
}

function OrganizationEditor({
  organization,
  onSave,
}: {
  organization: OrganizationResponse;
  onSave: (name: string) => Promise<OrganizationResponse>;
}) {
  const [name, setName] = useState(organization.name);
  const [busy, setBusy] = useState(false);
  const [feedback, setFeedback] = useState<{ kind: "success" | "error"; text: string } | null>(null);

  const submit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setBusy(true);
    setFeedback(null);
    try {
      const saved = await onSave(name);
      setName(saved.name);
      setFeedback({
        kind: "success",
        text: "Le nom de l’établissement a été enregistré dans l’API. Les données locales de démonstration sont inchangées.",
      });
    } catch (error) {
      setFeedback({ kind: "error", text: errorMessage(error) });
    } finally {
      setBusy(false);
    }
  };

  return (
    <form className="school-organization-form" onSubmit={(event) => void submit(event)}>
      <div className="form-grid form-grid-2">
        <label className="field">
          <span>Nom de l’organisation *</span>
          <input
            value={name}
            onChange={(event) => { setName(event.target.value); setFeedback(null); }}
            maxLength={160}
            required
            disabled={busy}
          />
        </label>
        <label className="field">
          <span>Code technique</span>
          <input value={organization.code} readOnly aria-readonly="true" />
        </label>
      </div>
      <div className="button-row">
        <button className="button button-primary" type="submit" disabled={busy || !name.trim()}>
          <Save size={16} /> {busy ? "Enregistrement…" : "Enregistrer dans l’API"}
        </button>
      </div>
      {feedback && <div className={`notice notice-${feedback.kind}`} role={feedback.kind === "error" ? "alert" : "status"}>{feedback.text}</div>}
    </form>
  );
}

function ReferentialCard({
  title,
  icon,
  count,
  empty,
  children,
}: {
  title: string;
  icon: ReactNode;
  count: number;
  empty: string;
  children: ReactNode;
}) {
  return (
    <section className="school-referential-card">
      <header>
        <span className="school-referential-icon" aria-hidden="true">{icon}</span>
        <h3>{title}</h3>
        <span className="badge badge-blue">{count}</span>
      </header>
      {count > 0 ? children : <p className="school-referential-empty">{empty}</p>}
    </section>
  );
}

export function SchoolReferentialsManager() {
  const {
    sessionState,
    session,
    sessionError,
    organizationId,
    setOrganizationId,
    referentials,
    dataState,
    dataError,
    reloadSession,
    reloadReferentials,
    saveOrganization,
  } = useSchoolReferentials();

  return (
    <section className="card rule-config school-referentials" aria-labelledby="school-referentials-title">
      <div className="section-heading">
        <div>
          <span className="eyebrow">Paramètres · source API</span>
          <h2 id="school-referentials-title">Référentiels d’établissement</h2>
          <p>Consultez les sites, niveaux et salles autorisés par votre session. Le nom de l’organisation est modifié via l’API, avec son historique serveur.</p>
        </div>
        <span className="badge badge-blue">API authentifiée</span>
      </div>

      {sessionState === "loading" && <p className="rule-config-state" role="status">Vérification de la session API…</p>}
      {sessionState === "unauthenticated" && (
        <div className="notice notice-warning" role="status">
          <strong>Session API requise.</strong>
          <p>Connectez-vous avec un compte autorisé pour consulter les référentiels. Aucun référentiel de démonstration ne sera présenté comme une donnée serveur.</p>
          <div className="button-row">
            <Link className="button button-primary" href="/connexion/?next=%2Fparametres%2F">Se connecter à l’API</Link>
            <button className="button button-secondary" type="button" onClick={() => void reloadSession()}>Revérifier la session</button>
          </div>
        </div>
      )}
      {sessionState === "error" && (
        <div className="notice notice-error" role="alert">
          <strong>Session API indisponible.</strong>
          <p>{sessionError}</p>
          <button className="button button-secondary" type="button" onClick={() => void reloadSession()}><RefreshCw size={15} /> Réessayer</button>
        </div>
      )}
      {sessionState === "ready" && session && session.memberships.length === 0 && (
        <div className="notice notice-warning" role="status">
          <strong>Aucun établissement autorisé.</strong>
          <p>Votre session est valide, mais elle ne comporte aucune organisation accessible. Demandez une habilitation nominative.</p>
        </div>
      )}

      {sessionState === "ready" && session && session.memberships.length > 0 && (
        <>
          <div className="rule-config-context">
            <label className="field">
              <span>Établissement</span>
              <select value={organizationId} onChange={(event) => setOrganizationId(event.target.value)}>
                {session.memberships.map((organization) => (
                  <option key={organization.organizationId} value={organization.organizationId}>
                    {organization.organizationName} · {organization.organizationCode}
                  </option>
                ))}
              </select>
            </label>
            <div className="rule-config-session">
              <span className="eyebrow">Session vérifiée</span>
              <strong>{session.user.displayName}</strong>
              <small>{session.memberships.find((org) => org.organizationId === organizationId)?.roles.join(", ")}</small>
            </div>
          </div>
          <div className="notice notice-info rule-config-note">
            <strong>Données serveur séparées de la démonstration.</strong>
            <p>Cette section lit et modifie uniquement l’API. Elle ne remplace ni ne synchronise les élèves, classes ou paramètres locaux du prototype.</p>
          </div>

          {dataState === "loading" && <p className="rule-config-state" role="status">Chargement des référentiels API…</p>}
          {dataState === "error" && (
            <div className="notice notice-error" role="alert">
              <strong>Référentiels inaccessibles.</strong>
              <p>{dataError}</p>
              <button className="button button-secondary" type="button" onClick={() => void reloadReferentials()}><RefreshCw size={15} /> Réessayer</button>
            </div>
          )}
          {dataState === "ready" && referentials && (
            <>
              <OrganizationEditor
                key={referentials.organization.id}
                organization={referentials.organization}
                onSave={saveOrganization}
              />
              <div className="school-referentials-grid">
                <ReferentialCard title="Sites actifs" icon={<Building2 size={18} />} count={referentials.sites.length} empty="Aucun site actif dans cette organisation.">
                  <ul className="school-referential-list">
                    {referentials.sites.map((site) => <li key={site.id}><strong>{site.name}</strong><small>{site.code}</small></li>)}
                  </ul>
                </ReferentialCard>
                <ReferentialCard title="Niveaux actifs" icon={<Layers3 size={18} />} count={referentials.levels.length} empty="Aucun niveau actif dans cette organisation.">
                  <ul className="school-referential-list">
                    {referentials.levels.map((level) => <li key={level.id}><strong>{level.name}</strong><small>{level.code} · rang {level.rank}</small></li>)}
                  </ul>
                </ReferentialCard>
                <ReferentialCard title="Salles actives" icon={<DoorOpen size={18} />} count={referentials.rooms.length} empty="Aucune salle active dans cette organisation.">
                  <ul className="school-referential-list">
                    {referentials.rooms.map((room) => {
                      const site = referentials.sites.find((candidate) => candidate.id === room.siteId);
                      return <li key={room.id}><strong>{room.name}</strong><small>{room.code} · {site?.name ?? "Site non affiché"} · {room.capacity === null ? "capacité non renseignée" : `${room.capacity} places`}</small></li>;
                    })}
                  </ul>
                </ReferentialCard>
              </div>
            </>
          )}
        </>
      )}
    </section>
  );
}
