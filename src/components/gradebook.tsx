"use client";

import { useMemo, useState } from "react";
import {
  Check,
  CheckCircle2,
  ClipboardList,
  Download,
  Info,
  Save,
  TrendingDown,
  TrendingUp,
} from "lucide-react";
import { useSchool } from "./school-provider";
import { Avatar, Badge, EmptyState, Notice, PageHeader } from "./ui";
import { SelectForm } from "./form-controls";
import { formatAverage, getAppreciation, getFullName, round } from "@/lib/academic";
import { downloadCsv } from "@/lib/utils";
import { TERMS, type Term } from "@/lib/types";

export function Gradebook() {
  const { state, saveGrades } = useSchool();
  const [classId, setClassId] = useState(state.classes[0]?.id ?? "");
  const [subjectId, setSubjectId] = useState(state.subjects[0]?.id ?? "");
  const [term, setTerm] = useState<Term>(state.settings.currentTerm);
  const [drafts, setDrafts] = useState<Record<string, string>>({});
  const [dirty, setDirty] = useState(false);
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");

  const schoolClass = state.classes.find((item) => item.id === classId);
  const subject = state.subjects.find((item) => item.id === subjectId);
  const students = state.students.filter((student) => student.classId === classId && student.status === "Actif");

  const valueFor = (studentId: string) => {
    const key = `${studentId}:${subjectId}:${term}`;
    if (key in drafts) return drafts[key];
    return state.grades.find((grade) => grade.studentId === studentId && grade.subjectId === subjectId && grade.term === term)?.value.toString() ?? "";
  };

  const currentValues = useMemo(
    () => students.map((student) => {
      const raw = valueFor(student.id);
      if (raw.trim() === "") return null;
      const value = Number(raw);
      return Number.isFinite(value) && value >= 0 && value <= 20 ? value : null;
    }),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [students, drafts, subjectId, term, state.grades],
  );
  const numericValues = currentValues.filter((value): value is number => value !== null);
  const average = numericValues.length ? round(numericValues.reduce((sum, value) => sum + value, 0) / numericValues.length) : null;
  const completion = students.length ? Math.round((numericValues.length / students.length) * 100) : 0;

  const save = () => {
    const invalid = students.some((student) => {
      const raw = valueFor(student.id).trim().replace(",", ".");
      return raw !== "" && (!Number.isFinite(Number(raw)) || Number(raw) < 0 || Number(raw) > 20);
    });
    if (invalid) {
      setError("Chaque note doit être un nombre compris entre 0 et 20.");
      setMessage("");
      return;
    }
    saveGrades(students.map((student) => {
      const raw = valueFor(student.id).trim().replace(",", ".");
      return { studentId: student.id, subjectId, term, value: raw === "" ? null : Number(raw) };
    }));
    setError("");
    setDirty(false);
    setMessage(`Les notes de ${subject?.name ?? "la matière"} ont été enregistrées.`);
  };

  const exportGrades = () => {
    downloadCsv([
      ["Matricule", "Élève", "Classe", "Matière", "Période", "Note /20"],
      ...students.map((student) => [student.matricule, getFullName(student), schoolClass?.name ?? "", subject?.name ?? "", term, valueFor(student.id)]),
    ], `notes-${schoolClass?.name ?? "classe"}-${subject?.code ?? "matiere"}.csv`);
  };

  return (
    <>
      <PageHeader eyebrow="Évaluations" title="Saisie des notes" icon={<ClipboardList size={20} />} description="Sélectionnez une classe et une matière, puis enregistrez toutes les notes en une seule opération." actions={<><button className="button button-secondary" type="button" onClick={exportGrades}><Download size={17} /> Exporter</button><button className="button button-primary" type="button" onClick={save} disabled={!students.length}><Save size={17} /> Enregistrer les notes</button></>} />

      <section className="card gradebook-selector-card">
        <div className="gradebook-selectors">
          <SelectForm label="Classe" value={classId} options={state.classes.map((item) => ({ value: item.id, label: item.name }))} onChange={(value) => { setClassId(value); setMessage(""); setError(""); setDirty(false); }} />
          <SelectForm label="Matière" value={subjectId} options={state.subjects.map((item) => ({ value: item.id, label: item.name, description: `Coefficient ${item.coefficient}` }))} onChange={(value) => { setSubjectId(value); setMessage(""); setError(""); setDirty(false); }} />
          <SelectForm label="Période" value={term} options={TERMS.map((item) => ({ value: item, label: item }))} onChange={(value) => { setTerm(value as Term); setMessage(""); setError(""); setDirty(false); }} />
        </div>
        <div className="gradebook-completion"><div><span>Progression de la saisie</span><strong>{numericValues.length}/{students.length} notes</strong></div><div className="progress-track"><span style={{ width: `${completion}%` }} /></div></div>
      </section>

      {message && <Notice tone="success" title={message}>Les bulletins et le classement utilisent désormais ces valeurs.</Notice>}
      {error && <Notice tone="warning" title="Saisie à vérifier">{error}</Notice>}
      {dirty && <Notice title="Modifications non enregistrées">Cliquez sur « Enregistrer les notes » pour mettre à jour les bulletins.</Notice>}

      <div className="gradebook-layout">
        <section className="card grade-entry-card">
          <header className="card-header"><div><p className="eyebrow">{schoolClass?.name} · {term}</p><h2>{subject?.name}</h2><p>Coefficient {subject?.coefficient} · Barème de 0 à 20</p></div><Badge tone={completion === 100 ? "green" : "amber"} dot>{completion === 100 ? "Saisie complète" : "Saisie en cours"}</Badge></header>
          {students.length ? <div className="table-wrap"><table className="data-table grade-entry-table"><thead><tr><th>#</th><th>Élève</th><th>Note / 20</th><th>Appréciation</th><th>État</th></tr></thead><tbody>
            {students.map((student, index) => {
              const raw = valueFor(student.id);
              const value = raw.trim() === "" ? null : Number(raw.replace(",", "."));
              const valid = value === null || (Number.isFinite(value) && value >= 0 && value <= 20);
              return <tr key={student.id}><td className="row-number">{String(index + 1).padStart(2, "0")}</td><td><div className="student-cell"><Avatar student={student} /><span><strong>{getFullName(student)}</strong><small>{student.matricule}</small></span></div></td><td><div className={`grade-input-wrap ${!valid ? "invalid" : ""}`}><input type="number" inputMode="decimal" min="0" max="20" step="0.25" value={raw} onChange={(event) => { const key = `${student.id}:${subjectId}:${term}`; setDrafts((current) => ({ ...current, [key]: event.target.value })); setDirty(true); setMessage(""); }} aria-label={`Note de ${getFullName(student)}`} placeholder="—" /><span>/20</span></div></td><td><span className={value !== null && value < 10 ? "text-danger" : ""}>{valid && value !== null ? getAppreciation(value) : "—"}</span></td><td>{raw === "" ? <Badge>Non saisie</Badge> : valid ? <Badge tone="green"><Check size={13} /> Saisie</Badge> : <Badge tone="red">Invalide</Badge>}</td></tr>;
            })}
          </tbody></table></div> : <EmptyState icon={<ClipboardList size={28} />} title="Aucun élève actif" description="Cette classe ne contient aucun élève actif pour la période sélectionnée." />}
          <footer className="grade-entry-footer"><span><Info size={15} /> Les cellules vides sont considérées comme des notes non saisies.</span><button className="button button-primary" type="button" onClick={save} disabled={!students.length}><Save size={16} /> Enregistrer</button></footer>
        </section>

        <aside className="gradebook-sidebar">
          <section className="card grade-stats-card"><p className="eyebrow">Aperçu de la classe</p><h3>Résultats de la saisie</h3><div className="grade-main-stat"><small>Moyenne de la matière</small><strong>{formatAverage(average)}<span> /20</span></strong></div><div className="grade-stat-row"><span><TrendingUp size={17} />Note maximale</span><strong>{numericValues.length ? Math.max(...numericValues).toString().replace(".", ",") : "—"}</strong></div><div className="grade-stat-row"><span><TrendingDown size={17} />Note minimale</span><strong>{numericValues.length ? Math.min(...numericValues).toString().replace(".", ",") : "—"}</strong></div><div className="grade-stat-row"><span><CheckCircle2 size={17} />Notes ≥ 10</span><strong>{numericValues.filter((value) => value >= 10).length}/{numericValues.length}</strong></div></section>
          <section className="card subject-shortcuts"><p className="eyebrow">Accès rapide</p><h3>Autres matières</h3>{state.subjects.map((item) => <button type="button" className={item.id === subjectId ? "active" : ""} key={item.id} onClick={() => { setSubjectId(item.id); setDirty(false); setMessage(""); }}><span>{item.code}</span><strong>{item.name}</strong><small>Coef. {item.coefficient}</small></button>)}</section>
        </aside>
      </div>
    </>
  );
}
