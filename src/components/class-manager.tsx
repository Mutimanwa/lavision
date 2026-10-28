"use client";

import Link from "next/link";
import { useState } from "react";
import {
  ArrowRight,
  BookOpen,
  BookOpenCheck,
  DoorOpen,
  GraduationCap,
  Plus,
  UserRound,
} from "lucide-react";
import { useSchool } from "./school-provider";
import { Badge, FormField, Modal, Notice, PageHeader } from "./ui";
import { SelectForm } from "./form-controls";
import { formatAverage, getClassRanking, round } from "@/lib/academic";

export function ClassManager() {
  const { state, addClass, addSubject, updateSubject } = useSchool();
  const [classModal, setClassModal] = useState(false);
  const [subjectModal, setSubjectModal] = useState(false);
  const [classDraft, setClassDraft] = useState({ name: "", level: "6e", room: "", capacity: 35, headTeacher: "" });
  const [subjectDraft, setSubjectDraft] = useState({ name: "", code: "", coefficient: 2 });
  const [message, setMessage] = useState("");

  const submitClass = () => {
    if (!classDraft.name.trim() || !classDraft.headTeacher.trim() || classDraft.capacity < 1) return;
    addClass({ ...classDraft, name: classDraft.name.trim(), room: classDraft.room.trim() || "À attribuer", headTeacher: classDraft.headTeacher.trim(), capacity: Number(classDraft.capacity) });
    setClassDraft({ name: "", level: "6e", room: "", capacity: 35, headTeacher: "" });
    setClassModal(false);
    setMessage("La nouvelle classe a été ajoutée.");
  };

  const submitSubject = () => {
    if (!subjectDraft.name.trim() || !subjectDraft.code.trim() || subjectDraft.coefficient < 1) return;
    addSubject({ name: subjectDraft.name.trim(), code: subjectDraft.code.trim().toUpperCase(), coefficient: Number(subjectDraft.coefficient) });
    setSubjectDraft({ name: "", code: "", coefficient: 2 });
    setSubjectModal(false);
    setMessage("La matière est disponible dans les notes et les bulletins.");
  };

  return (
    <>
      <PageHeader
        eyebrow="Organisation pédagogique"
        title="Classes & matières"
        icon={<BookOpenCheck size={20} />}
        description="Gérez les capacités, les professeurs principaux et les coefficients utilisés dans les bulletins."
        actions={<button className="button button-primary" type="button" onClick={() => setClassModal(true)}><Plus size={17} /> Nouvelle classe</button>}
      />
      {message && <Notice tone="success" title={message}><button className="text-link" type="button" onClick={() => setMessage("")}>Fermer</button></Notice>}

      <section className="class-card-grid">
        {state.classes.map((schoolClass, index) => {
          const students = state.students.filter((student) => student.classId === schoolClass.id && student.status === "Actif");
          const ranking = getClassRanking(students, state.settings.currentTerm, state.subjects, state.grades, state.settings.passMark);
          const averages = ranking.filter((item) => item.average !== null);
          const average = averages.length ? round(averages.reduce((sum, item) => sum + (item.average ?? 0), 0) / averages.length) : null;
          const occupancy = Math.min(100, Math.round((students.length / schoolClass.capacity) * 100));
          return (
            <article className="card class-card" key={schoolClass.id}>
              <div className="class-card-head"><span className={`class-symbol class-symbol-${index % 3}`}><GraduationCap size={24} /></span><Badge tone="green" dot>Active</Badge></div>
              <h2>{schoolClass.name}</h2><p className="class-level">Niveau {schoolClass.level} · {state.settings.academicYear}</p>
              <div className="class-meta"><span><UserRound size={16} />{schoolClass.headTeacher}</span><span><DoorOpen size={16} />{schoolClass.room}</span></div>
              <div className="class-capacity"><div><span>Effectif</span><strong>{students.length} <small>/ {schoolClass.capacity} places</small></strong></div><div className="progress-track"><span style={{ width: `${occupancy}%` }} /></div><small>{schoolClass.capacity - students.length > 0 ? `${schoolClass.capacity - students.length} places disponibles` : "Capacité maximale atteinte"}</small></div>
              <div className="class-card-footer"><div><small>Moyenne de classe</small><strong>{formatAverage(average)} <span>/ 20</span></strong></div><Link className="text-link" href="/notes/">Voir les notes <ArrowRight size={15} /></Link></div>
            </article>
          );
        })}
        <button className="class-add-card" type="button" onClick={() => setClassModal(true)}><span><Plus size={25} /></span><strong>Ouvrir une classe</strong><small>Définir le niveau, la salle et la capacité</small></button>
      </section>

      <section className="card subjects-card">
        <header className="card-header"><div><p className="eyebrow">Référentiel pédagogique</p><h2>Matières & coefficients</h2><p>Les modifications de coefficient recalculent immédiatement toutes les moyennes.</p></div><button className="button button-secondary" type="button" onClick={() => setSubjectModal(true)}><Plus size={17} /> Ajouter une matière</button></header>
        <div className="table-wrap"><table className="data-table"><thead><tr><th>Matière</th><th>Code</th><th>Coefficient</th><th>Poids dans le bulletin</th><th>Utilisation</th></tr></thead><tbody>
          {state.subjects.map((subject) => {
            const coefficientTotal = state.subjects.reduce((sum, item) => sum + item.coefficient, 0);
            const gradeTotal = state.grades.filter((grade) => grade.subjectId === subject.id).length;
            return <tr key={subject.id}><td><div className="subject-name-cell"><span><BookOpen size={17} /></span><strong>{subject.name}</strong></div></td><td><Badge>{subject.code}</Badge></td><td><input className="coefficient-input" type="number" min="1" max="20" value={subject.coefficient} onChange={(event) => updateSubject(subject.id, Math.max(1, Math.min(20, Number(event.target.value))))} aria-label={`Coefficient de ${subject.name}`} /></td><td><div className="weight-cell"><div className="progress-track"><span style={{ width: `${(subject.coefficient / coefficientTotal) * 100}%` }} /></div><small>{Math.round((subject.coefficient / coefficientTotal) * 100)} %</small></div></td><td>{gradeTotal} note{gradeTotal > 1 ? "s" : ""}</td></tr>;
          })}
        </tbody><tfoot><tr><td colSpan={2}>Total des coefficients</td><td><strong>{state.subjects.reduce((sum, subject) => sum + subject.coefficient, 0)}</strong></td><td colSpan={2}>Base de calcul des moyennes générales</td></tr></tfoot></table></div>
      </section>

      <Notice title="Règle de calcul">La moyenne générale est la somme de chaque note multipliée par son coefficient, divisée par la somme des coefficients des matières notées. La proclamation exige néanmoins un dossier complet.</Notice>

      <Modal open={classModal} onClose={() => setClassModal(false)} title="Ouvrir une nouvelle classe" description="Elle sera immédiatement proposée dans les inscriptions." footer={<><button className="button button-secondary" type="button" onClick={() => setClassModal(false)}>Annuler</button><button className="button button-primary" type="button" onClick={submitClass}><Plus size={16} /> Créer la classe</button></>}>
        <div className="form-grid"><FormField label="Nom de la classe" htmlFor="class-name" required><input id="class-name" value={classDraft.name} onChange={(event) => setClassDraft({ ...classDraft, name: event.target.value })} placeholder="Ex. 3e A" /></FormField><SelectForm id="class-level" label="Niveau" required value={classDraft.level} options={["6e", "5e", "4e", "3e", "2nde", "1ère", "Tle"].map((level) => ({ value: level, label: level }))} onChange={(value) => setClassDraft({ ...classDraft, level: value })} /><FormField label="Salle" htmlFor="class-room"><input id="class-room" value={classDraft.room} onChange={(event) => setClassDraft({ ...classDraft, room: event.target.value })} placeholder="Ex. Salle C02" /></FormField><FormField label="Capacité" htmlFor="class-capacity" required><input id="class-capacity" type="number" min="1" max="100" value={classDraft.capacity} onChange={(event) => setClassDraft({ ...classDraft, capacity: Number(event.target.value) })} /></FormField><FormField label="Professeur principal" htmlFor="class-teacher" required className="form-field-full"><input id="class-teacher" value={classDraft.headTeacher} onChange={(event) => setClassDraft({ ...classDraft, headTeacher: event.target.value })} placeholder="Nom et prénom" /></FormField></div>
      </Modal>

      <Modal open={subjectModal} onClose={() => setSubjectModal(false)} title="Ajouter une matière" description="La matière sera intégrée à tous les nouveaux bulletins." footer={<><button className="button button-secondary" type="button" onClick={() => setSubjectModal(false)}>Annuler</button><button className="button button-primary" type="button" onClick={submitSubject}><BookOpenCheck size={16} /> Ajouter la matière</button></>}>
        <div className="form-grid"><FormField label="Nom de la matière" htmlFor="subject-name" required className="form-field-full"><input id="subject-name" value={subjectDraft.name} onChange={(event) => setSubjectDraft({ ...subjectDraft, name: event.target.value })} placeholder="Ex. Informatique" /></FormField><FormField label="Code" htmlFor="subject-code" required><input id="subject-code" value={subjectDraft.code} onChange={(event) => setSubjectDraft({ ...subjectDraft, code: event.target.value })} placeholder="Ex. INFO" maxLength={8} /></FormField><FormField label="Coefficient" htmlFor="subject-coefficient" required><input id="subject-coefficient" type="number" min="1" max="20" value={subjectDraft.coefficient} onChange={(event) => setSubjectDraft({ ...subjectDraft, coefficient: Number(event.target.value) })} /></FormField></div>
      </Modal>
    </>
  );
}
