"use client";

import { useRef, useState } from "react";
import {
  Building2,
  Database,
  Download,
  HardDrive,
  Laptop,
  RotateCcw,
  Save,
  Settings,
  ShieldAlert,
  Upload,
} from "lucide-react";
import { useSchool } from "@/features/school/hooks/use-school";
import { Modal, Notice, PageHeader } from "@/components/ui";
import { InputForm, SelectForm } from "@/components/forms";
import { downloadJson, isAppState } from "@/lib/utils";
import { TERMS, type SchoolSettings, type Term } from "@/lib/types";
import { RuleConfigurationManager } from "@/features/configuration/components/rule-configuration-manager";

export function SettingsManager() {
  const { state, hydrated, updateSettings, importState, resetState } = useSchool();
  const [draft, setDraft] = useState<SchoolSettings>(state.settings);
  const [dirty, setDirty] = useState(false);
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const [resetOpen, setResetOpen] = useState(false);
  const fileInput = useRef<HTMLInputElement>(null);
  const current = dirty ? draft : state.settings;

  const update = <Key extends keyof SchoolSettings>(key: Key, value: SchoolSettings[Key]) => {
    setDraft({ ...current, [key]: value });
    setDirty(true);
    setMessage("");
  };

  const save = () => {
    if (!current.schoolName.trim() || !current.academicYear.trim() || current.passMark < 0 || current.passMark > 20) {
      setError("Vérifiez le nom de l’établissement, l’année scolaire et le seuil de réussite (0 à 20).");
      return;
    }
    updateSettings({ ...current, schoolName: current.schoolName.trim(), passMark: Number(current.passMark) });
    setDraft(current);
    setDirty(false);
    setError("");
    setMessage("Les paramètres de l’établissement ont été enregistrés.");
  };

  const importBackup = async (file: File | undefined) => {
    if (!file) return;
    try {
      const parsed: unknown = JSON.parse(await file.text());
      if (!isAppState(parsed)) throw new Error("invalid");
      importState(parsed);
      setDirty(false);
      setError("");
      setMessage("La sauvegarde a été restaurée avec succès.");
    } catch {
      setError("Ce fichier n’est pas une sauvegarde lavision valide.");
    } finally {
      if (fileInput.current) fileInput.current.value = "";
    }
  };

  return (
    <>
      <PageHeader eyebrow="Administration" title="Paramètres" icon={<Settings size={20} />} description="Configurez l’établissement, les règles académiques et la conservation de vos données." actions={<button className="button button-primary" type="button" onClick={save} disabled={!dirty}><Save size={17} /> Enregistrer les paramètres</button>} />
      {message && <Notice tone="success" title={message}>Les changements sont disponibles dans tous les modules.</Notice>}
      {error && <Notice tone="warning" title="Action impossible">{error}</Notice>}
      {!hydrated && <Notice>Chargement de la sauvegarde locale…</Notice>}

      <div className="settings-layout">
        <div className="settings-main">
          <section className="card settings-section">
            <header className="settings-section-header"><span><Building2 size={21} /></span><div><h2>Établissement</h2><p>Ces informations apparaissent sur les bulletins et les proclamations.</p></div></header>
            <div className="form-grid">
              <InputForm id="schoolName" label="Nom de l’établissement" required containerClassName="form-field-full" value={current.schoolName} onChange={(event) => update("schoolName", event.target.value)} />
              <InputForm id="directionName" label="Nom de la direction" value={current.directionName} onChange={(event) => update("directionName", event.target.value)} />
              <InputForm id="academicYear" label="Année scolaire" required value={current.academicYear} onChange={(event) => update("academicYear", event.target.value)} placeholder="2026–2027" />
              <InputForm id="schoolPhone" label="Téléphone" type="tel" value={current.phone} onChange={(event) => update("phone", event.target.value)} />
              <InputForm id="schoolEmail" label="Adresse e-mail" type="email" value={current.email} onChange={(event) => update("email", event.target.value)} />
              <InputForm id="schoolAddress" label="Adresse" containerClassName="form-field-full" value={current.address} onChange={(event) => update("address", event.target.value)} />
            </div>
          </section>
          <section className="card settings-section">
            <header className="settings-section-header"><span><Settings size={21} /></span><div><h2>Règles académiques</h2><p>Définissez la période de travail et la règle d’admission par défaut.</p></div></header>
            <div className="form-grid">
              <SelectForm id="currentTerm" label="Période active" helperText="Utilisée par défaut sur le tableau de bord." value={current.currentTerm} options={TERMS.map((term) => ({ value: term, label: term }))} onChange={(value) => update("currentTerm", value as Term)} />
              <InputForm id="passMark" label="Seuil de réussite /20" helperText="Un dossier doit aussi être complet pour être admis." type="number" min="0" max="20" step="0.5" value={current.passMark} onChange={(event) => update("passMark", Number(event.target.value))} />
            </div>
            <Notice title="Moyennes pondérées">Les coefficients se règlent dans le module Classes & matières. La moyenne de chaque bulletin est arrondie à deux décimales.</Notice>
          </section>
        </div>
        <aside className="settings-side">
          <section className="card data-card"><span className="settings-big-icon"><Database size={24} /></span><h2>Données locales</h2><p>Votre travail est automatiquement enregistré dans ce navigateur.</p><div className="data-summary"><div><strong>{state.students.length}</strong><small>élèves</small></div><div><strong>{state.grades.length}</strong><small>notes</small></div><div><strong>{state.attendance.length}</strong><small>présences</small></div></div><button className="button button-secondary button-full" type="button" onClick={() => downloadJson(state)}><Download size={17} /> Télécharger une sauvegarde</button><button className="button button-ghost button-full" type="button" onClick={() => fileInput.current?.click()}><Upload size={17} /> Restaurer une sauvegarde</button><input ref={fileInput} type="file" accept="application/json,.json" hidden onChange={(event) => importBackup(event.target.files?.[0])} /><small className="data-format">Format JSON · version 1</small></section>
          <section className="card desktop-card"><span className="settings-big-icon"><Laptop size={24} /></span><h2>lavision sur desktop</h2><p>Le socle Tauri permet de générer une application Windows avec installateur <strong>.exe</strong>.</p><div className="desktop-command"><HardDrive size={16} /><code>npm run tauri build</code></div><small>La compilation Windows doit être exécutée sur Windows ou dans la CI prévue à cet effet.</small></section>
          <section className="card danger-card"><span><ShieldAlert size={21} /></span><div><h3>Restaurer la démonstration</h3><p>Remplace toutes les données locales par le jeu de démonstration initial.</p><button className="text-link text-danger" type="button" onClick={() => setResetOpen(true)}><RotateCcw size={15} /> Réinitialiser les données</button></div></section>
        </aside>
      </div>
      <RuleConfigurationManager />
      <Notice tone="warning" title="Important pour une mise en production">Le stockage local convient à la démonstration et au prototype desktop. Une utilisation partagée entre plusieurs postes exige une base centralisée, une authentification serveur, des rôles et des sauvegardes automatiques.</Notice>

      <Modal open={resetOpen} onClose={() => setResetOpen(false)} title="Restaurer les données de démonstration ?" description="Cette action remplacera le contenu actuel de ce navigateur." size="small" footer={<><button className="button button-secondary" type="button" onClick={() => setResetOpen(false)}>Annuler</button><button className="button button-danger" type="button" onClick={() => { resetState(); setDirty(false); setResetOpen(false); setMessage("Les données de démonstration ont été restaurées."); }}><RotateCcw size={16} /> Confirmer la réinitialisation</button></>}><Notice tone="warning">Téléchargez d’abord une sauvegarde JSON si vous souhaitez conserver les inscriptions et notes actuelles.</Notice><button className="button button-secondary button-full" type="button" onClick={() => downloadJson(state)}><Download size={16} /> Télécharger avant de réinitialiser</button></Modal>
    </>
  );
}
