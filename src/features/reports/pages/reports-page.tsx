"use client";

import Link from "next/link";
import { useState } from "react";
import { useRouter, useSearchParams } from "next/navigation";
import {
  ArrowLeft,
  ArrowRight,
  CheckCircle2,
  Download,
  GraduationCap,
  Printer,
  ShieldCheck,
} from "lucide-react";
import { useSchool } from "@/features/school/hooks/use-school";
import { Badge, EmptyState, Notice, PageHeader } from "@/components/ui";
import { SearchSelectForm, SelectForm } from "@/components/forms";
import {
  formatAverage,
  getAppreciation,
  getClassRanking,
  getFullName,
  getStudentReport,
  round,
} from "@/lib/academic";
import { downloadCsv, formatDate } from "@/lib/utils";
import { TERMS, type Term } from "@/lib/types";

export function ReportCards() {
  const { state } = useSchool();
  const router = useRouter();
  const searchParams = useSearchParams();
  const queryStudent = state.students.find((student) => student.id === searchParams.get("eleve"));
  const [localClassId, setLocalClassId] = useState(state.classes[0]?.id ?? "");
  const [localStudentId, setLocalStudentId] = useState("");
  const [term, setTerm] = useState<Term>(state.settings.currentTerm);

  const classId = queryStudent?.classId ?? localClassId;
  const schoolClass = state.classes.find((item) => item.id === classId);
  const classStudents = state.students.filter((student) => student.classId === classId && student.status === "Actif");
  const selectedStudent = classStudents.find((student) => student.id === (localStudentId || queryStudent?.id)) ?? classStudents[0];
  const ranking = getClassRanking(classStudents, term, state.subjects, state.grades, state.settings.passMark);
  const rankedStudent = ranking.find((item) => item.studentId === selectedStudent?.id);
  const report = selectedStudent
    ? getStudentReport(selectedStudent.id, term, state.subjects, state.grades)
    : null;
  const publication = state.publications.find((item) => item.classId === classId && item.term === term);
  const weightedTotal = report?.lines.reduce((sum, line) => sum + (line.weightedPoints ?? 0), 0) ?? 0;
  const coefficientTotal = state.subjects.reduce((sum, subject) => sum + subject.coefficient, 0);
  const attendance = selectedStudent ? state.attendance.filter((record) => record.studentId === selectedStudent.id) : [];
  const currentIndex = classStudents.findIndex((student) => student.id === selectedStudent?.id);

  const changeClass = (nextClass: string) => {
    setLocalClassId(nextClass);
    setLocalStudentId("");
    if (queryStudent) router.replace("/bulletins/");
  };

  const exportReport = () => {
    if (!selectedStudent || !report) return;
    downloadCsv([
      ["Élève", getFullName(selectedStudent)],
      ["Matricule", selectedStudent.matricule],
      ["Classe", schoolClass?.name ?? ""],
      ["Période", term],
      [],
      ["Code", "Matière", "Coefficient", "Note /20", "Points pondérés", "Appréciation"],
      ...report.lines.map((line) => [line.subject.code, line.subject.name, line.subject.coefficient, line.grade?.value ?? "", line.weightedPoints ?? "", getAppreciation(line.grade?.value ?? null)]),
      [],
      ["Moyenne générale", report.average ?? ""],
      ["Rang", rankedStudent?.rank ?? ""],
      ["Décision", rankedStudent?.outcome ?? ""],
    ], `bulletin-${selectedStudent.matricule}-${term.replaceAll(" ", "-")}.csv`);
  };

  return (
    <>
      <div className="no-print">
        <PageHeader eyebrow="Évaluations" title="Bulletins scolaires" icon={<GraduationCap size={20} />} description="Consultez un bulletin calculé automatiquement, puis imprimez-le ou enregistrez-le en PDF." actions={<><button className="button button-secondary" type="button" onClick={exportReport} disabled={!selectedStudent}><Download size={17} /> Exporter CSV</button><button className="button button-primary" type="button" onClick={() => window.print()} disabled={!selectedStudent}><Printer size={17} /> Imprimer / PDF</button></>} />
        <section className="card bulletin-toolbar">
          <SelectForm label="Classe" value={classId} options={state.classes.map((item) => ({ value: item.id, label: item.name }))} onChange={changeClass} />
          <SearchSelectForm label="Élève" value={selectedStudent?.id ?? ""} options={classStudents.map((student) => ({ value: student.id, label: getFullName(student), description: student.matricule }))} onChange={setLocalStudentId} searchPlaceholder="Nom ou matricule…" dropdownLabel="Élèves actifs" />
          <SelectForm label="Période" value={term} options={TERMS.map((item) => ({ value: item, label: item }))} onChange={(value) => setTerm(value as Term)} />
          <div className="bulletin-navigation"><button className="icon-button" type="button" disabled={currentIndex <= 0} onClick={() => setLocalStudentId(classStudents[currentIndex - 1]?.id ?? "")} aria-label="Élève précédent"><ArrowLeft size={18} /></button><span>{currentIndex + 1} / {classStudents.length}</span><button className="icon-button" type="button" disabled={currentIndex >= classStudents.length - 1} onClick={() => setLocalStudentId(classStudents[currentIndex + 1]?.id ?? "")} aria-label="Élève suivant"><ArrowRight size={18} /></button></div>
        </section>
        {report && !report.isComplete && <Notice tone="warning" title="Bulletin provisoire">{report.totalSubjects - report.gradedCount} matière(s) n’ont pas encore de note. La moyenne affichée utilise uniquement les matières évaluées et aucun rang définitif n’est attribué.</Notice>}
      </div>

      {selectedStudent && report ? (
        <article className="report-paper" id="bulletin">
          <header className="report-school-header">
            <div className="report-school-logo"><GraduationCap size={31} /></div>
            <div><p>République du Burundi</p><h2>{state.settings.schoolName}</h2><span>{state.settings.address} · {state.settings.phone}</span></div>
            <div className="report-document-id"><Badge tone={publication ? "green" : "amber"}>{publication ? "Bulletin officiel" : "Bulletin provisoire"}</Badge><small>N° {selectedStudent.matricule}-{term.slice(-1)}</small></div>
          </header>
          <div className="report-title"><p>Année scolaire {state.settings.academicYear}</p><h1>Bulletin de notes</h1><span>{term}</span></div>
          <section className="report-student-info">
            <div><small>Nom et prénom</small><strong>{getFullName(selectedStudent)}</strong></div><div><small>Matricule</small><strong>{selectedStudent.matricule}</strong></div><div><small>Classe</small><strong>{schoolClass?.name}</strong></div><div><small>Date de naissance</small><strong>{formatDate(selectedStudent.birthDate)}</strong></div><div><small>Effectif de la classe</small><strong>{classStudents.length} élèves</strong></div><div><small>Professeur principal</small><strong>{schoolClass?.headTeacher}</strong></div>
          </section>
          <table className="report-table"><thead><tr><th>Matières</th><th>Coef.</th><th>Note /20</th><th>Points</th><th>Appréciations</th></tr></thead><tbody>
            {report.lines.map((line) => <tr key={line.subject.id}><td><strong>{line.subject.name}</strong><small>{line.subject.code}</small></td><td>{line.subject.coefficient}</td><td className="report-grade">{line.grade ? line.grade.value.toString().replace(".", ",") : "—"}</td><td>{line.weightedPoints ?? "—"}</td><td>{getAppreciation(line.grade?.value ?? null)}</td></tr>)}
          </tbody><tfoot><tr><td>Total</td><td>{coefficientTotal}</td><td>—</td><td>{round(weightedTotal)}</td><td>{report.gradedCount}/{report.totalSubjects} matières notées</td></tr></tfoot></table>
          <section className="report-results">
            <div className="report-average"><small>Moyenne générale</small><strong>{formatAverage(report.average)}<span> / 20</span></strong><p>{getAppreciation(report.average)}</p></div>
            <div className="report-result-details"><div><small>Rang</small><strong>{rankedStudent?.rank ? `${rankedStudent.rank}e sur ${ranking.filter((item) => item.rank !== null).length}` : "Non classé"}</strong></div><div><small>Décision</small><strong className={rankedStudent?.outcome === "Admis" ? "text-success" : rankedStudent?.outcome === "Ajourné" ? "text-danger" : ""}>{rankedStudent?.outcome ?? "Incomplet"}</strong></div><div><small>Seuil de réussite</small><strong>{state.settings.passMark} / 20</strong></div></div>
            <div className={`report-decision-seal ${rankedStudent?.outcome === "Admis" ? "admitted" : ""}`}><CheckCircle2 size={25} /><strong>{rankedStudent?.outcome === "Admis" ? "Admis(e)" : rankedStudent?.outcome === "Ajourné" ? "Ajourné(e)" : "À compléter"}</strong><small>{publication ? "Résultat publié" : "Non publié"}</small></div>
          </section>
          <section className="report-bottom-grid">
            <div><h3>Suivi de l’assiduité</h3><p><span>Absences</span><strong>{attendance.filter((record) => record.status === "absent").length}</strong></p><p><span>Retards</span><strong>{attendance.filter((record) => record.status === "retard").length}</strong></p><p><span>Absences excusées</span><strong>{attendance.filter((record) => record.status === "excuse").length}</strong></p></div>
            <div><h3>Appréciation du conseil de classe</h3><p className="council-comment">{report.isComplete ? `${getAppreciation(report.average)}. ${rankedStudent?.outcome === "Admis" ? "Poursuivre les efforts et la régularité dans le travail." : "Un travail soutenu est nécessaire pour atteindre le seuil de réussite."}` : "Le conseil de classe se prononcera après la saisie de toutes les notes."}</p></div>
          </section>
          <footer className="report-signature"><div><small>Fait à Bujumbura, le {formatDate(new Date().toISOString())}</small><span>Cachet de l’établissement</span></div><div><strong>{state.settings.directionName}</strong><span>La Direction</span></div></footer>
          <div className="report-authenticity"><ShieldCheck size={14} /> Document généré par lavision · {publication ? `Publié le ${formatDate(publication.publishedAt)}` : "Version de travail non publiée"}</div>
        </article>
      ) : (
        <div className="card no-print"><EmptyState icon={<GraduationCap size={28} />} title="Aucun bulletin disponible" description="Ajoutez d’abord un élève actif dans cette classe." action={<Link className="button button-primary" href="/inscriptions/">Nouvelle inscription</Link>} /></div>
      )}
      <div className="bulletin-footer-actions no-print"><button className="button button-secondary" type="button" onClick={() => window.print()} disabled={!selectedStudent}><Printer size={17} /> Imprimer ce bulletin</button><Link className="text-link" href="/proclamation/">Aller à la proclamation <ArrowRight size={16} /></Link></div>
    </>
  );
}
