"use client";

import Link from "next/link";
import { useState } from "react";
import {
  Award,
  Check,
  CheckCircle2,
  Download,
  Eye,
  FileText,
  GraduationCap,
  Lock,
  Medal,
  Printer,
  ShieldCheck,
  Trophy,
  Unlock,
  UsersRound,
  XCircle,
} from "lucide-react";
import { useSchool } from "./school-provider";
import { Avatar, Badge, EmptyState, Modal, Notice, PageHeader, TableMenu } from "./ui";
import { formatAverage, getClassRanking as getRanking, getFullName } from "@/lib/academic";
import { downloadCsv, formatDate } from "@/lib/utils";
import { TERMS, type Term } from "@/lib/types";

export function Proclamation() {
  const { state, setPublication } = useSchool();
  const [classId, setClassId] = useState(state.classes[0]?.id ?? "");
  const [term, setTerm] = useState<Term>(state.settings.currentTerm);
  const [confirmation, setConfirmation] = useState<"publish" | "unpublish" | null>(null);
  const schoolClass = state.classes.find((item) => item.id === classId);
  const students = state.students.filter((student) => student.classId === classId && student.status === "Actif");
  const ranking = state.subjects.length
    ? getRanking(students, term, state.subjects, state.grades, state.settings.passMark)
    : [];
  const complete = ranking.filter((item) => item.report.isComplete);
  const admitted = complete.filter((item) => item.outcome === "Admis");
  const incomplete = ranking.filter((item) => !item.report.isComplete);
  const publication = state.publications.find((item) => item.classId === classId && item.term === term);
  const successRate = complete.length ? Math.round((admitted.length / complete.length) * 100) : 0;

  const confirmPublication = () => {
    if (confirmation) setPublication(classId, term, confirmation === "publish");
    setConfirmation(null);
  };

  const exportRanking = () => {
    downloadCsv([
      ["Rang", "Matricule", "Élève", "Moyenne /20", "Matières notées", "Décision", "Publication"],
      ...ranking.map((item) => {
        const student = students.find((candidate) => candidate.id === item.studentId);
        return [item.rank ?? "", student?.matricule ?? "", student ? getFullName(student) : "", item.average ?? "", `${item.report.gradedCount}/${item.report.totalSubjects}`, item.outcome, publication ? "Publié" : "Provisoire"];
      }),
    ], `proclamation-${schoolClass?.name ?? "classe"}-${term.replaceAll(" ", "-")}.csv`);
  };

  return (
    <>
      <div className="no-print">
        <PageHeader eyebrow="Conseil de classe" title="Proclamation des résultats" description="Vérifiez le classement, clôturez les délibérations et publiez les décisions de la classe." actions={<><button className="button button-secondary" type="button" onClick={exportRanking} disabled={!ranking.length}><Download size={17} /> Exporter CSV</button><button className="button button-secondary" type="button" onClick={() => window.print()} disabled={!ranking.length}><Printer size={17} /> Imprimer</button>{publication ? <button className="button button-primary" type="button" onClick={() => setConfirmation("unpublish")}><Unlock size={17} /> Retirer la publication</button> : <button className="button button-primary" type="button" onClick={() => setConfirmation("publish")} disabled={!ranking.length || incomplete.length > 0}><ShieldCheck size={17} /> Publier les résultats</button>}</>} />
        <section className="card proclamation-toolbar"><div><label><span>Classe</span><select value={classId} onChange={(event) => setClassId(event.target.value)}>{state.classes.map((item) => <option value={item.id} key={item.id}>{item.name}</option>)}</select></label><label><span>Période de délibération</span><select value={term} onChange={(event) => setTerm(event.target.value as Term)}>{TERMS.map((item) => <option key={item}>{item}</option>)}</select></label></div><div className={`publication-status ${publication ? "published" : ""}`}><span>{publication ? <Lock size={19} /> : <Unlock size={19} />}</span><div><strong>{publication ? "Résultats publiés" : "Délibération ouverte"}</strong><small>{publication ? `Publié le ${formatDate(publication.publishedAt)}` : "Les résultats sont encore provisoires"}</small></div></div></section>
        {incomplete.length > 0 && <Notice tone="warning" title="La publication est bloquée">{incomplete.length} élève(s) ont au moins une note manquante. Complétez la saisie avant de clôturer la délibération. <Link className="text-link" href="/notes/">Ouvrir les notes</Link></Notice>}
        {publication && <Notice tone="success" title="Proclamation officielle">Les bulletins de cette classe et de cette période portent désormais la mention « Bulletin officiel ».</Notice>}
      </div>

      <section className="proclamation-stats no-print">
        <div><span className="mini-stat-icon blue"><UsersRound size={20} /></span><p><strong>{students.length}</strong><small>Élèves évalués</small></p></div><div><span className="mini-stat-icon green"><CheckCircle2 size={20} /></span><p><strong>{admitted.length}</strong><small>Admis</small></p></div><div><span className="mini-stat-icon red"><XCircle size={20} /></span><p><strong>{complete.length - admitted.length}</strong><small>Ajournés</small></p></div><div><span className="mini-stat-icon amber"><Award size={20} /></span><p><strong>{successRate} %</strong><small>Taux de réussite</small></p></div>
      </section>

      {complete.length >= 3 && (
        <section className="podium-section no-print">
          <div className="podium-heading"><p className="eyebrow">Excellence scolaire</p><h2>Les trois premiers de la classe</h2></div>
          <div className="podium">
            {[complete[1], complete[0], complete[2]].filter(Boolean).map((item) => {
              const student = students.find((candidate) => candidate.id === item.studentId)!;
              const isFirst = item.rank === 1;
              return <article className={`podium-card ${isFirst ? "podium-first" : ""}`} key={item.studentId}><span className="podium-medal">{isFirst ? <Trophy size={23} /> : <Medal size={22} />}</span><Avatar student={student} size="large" /><Badge tone={isFirst ? "amber" : "neutral"}>{item.rank === 1 ? "1er" : `${item.rank}e`}</Badge><h3>{getFullName(student)}</h3><p>{student.matricule}</p><strong>{formatAverage(item.average)}<small> /20</small></strong></article>;
            })}
          </div>
        </section>
      )}

      <article className="card proclamation-document">
        <header className="card-header proclamation-print-header"><div><p className="eyebrow">{schoolClass?.name} · {state.settings.academicYear}</p><h2>Procès-verbal de délibération</h2><p>{term} · Classement par moyenne générale pondérée</p></div><Badge tone={publication ? "green" : "amber"} dot>{publication ? "Officiel" : "Provisoire"}</Badge></header>
        <div className="proclamation-school-only"><GraduationCap size={24} /><div><strong>{state.settings.schoolName}</strong><small>{state.settings.address}</small></div></div>
        {ranking.length ? <div className="table-wrap"><table className="data-table proclamation-table"><thead><tr><th>Rang</th><th>Élève</th><th>Moyenne /20</th><th>Notes complètes</th><th>Décision du conseil</th><th className="no-print table-actions-heading">Actions</th></tr></thead><tbody>
          {ranking.map((item) => {
            const student = students.find((candidate) => candidate.id === item.studentId)!;
            return <tr key={item.studentId} className={item.rank === 1 ? "first-place-row" : ""}><td>{item.rank ? <span className={`rank-badge ${item.rank <= 3 ? `rank-${item.rank}` : ""}`}>{item.rank === 1 ? <Trophy size={15} /> : item.rank}</span> : <span>—</span>}</td><td><div className="student-cell"><Avatar student={student} /><span><strong>{getFullName(student)}</strong><small>{student.matricule}</small></span></div></td><td><strong className="ranking-average">{formatAverage(item.average)}</strong></td><td><div className="grade-completeness"><span>{item.report.gradedCount}/{item.report.totalSubjects}</span><div className="progress-track"><i style={{ width: `${(item.report.gradedCount / item.report.totalSubjects) * 100}%` }} /></div></div></td><td><Badge tone={item.outcome === "Admis" ? "green" : item.outcome === "Ajourné" ? "red" : "amber"}>{item.outcome === "Admis" ? <Check size={13} /> : item.outcome === "Ajourné" ? <XCircle size={13} /> : <FileText size={13} />}{item.outcome}</Badge></td><td className="no-print table-actions-heading"><TableMenu label={`Actions pour ${getFullName(student)}`}><Link href={`/bulletins/?eleve=${student.id}`}><Eye size={16} /> Voir le bulletin</Link></TableMenu></td></tr>;
          })}
        </tbody></table></div> : <EmptyState icon={<GraduationCap size={28} />} title="Aucun résultat à proclamer" description="Cette classe ne contient pas encore d’élève actif." />}
        <footer className="proclamation-document-footer"><div><small>Décision d’admission</small><strong>Moyenne ≥ {state.settings.passMark}/20 et dossier complet</strong></div><div><small>La Direction</small><strong>{state.settings.directionName}</strong></div></footer>
      </article>

      <Modal open={confirmation !== null} onClose={() => setConfirmation(null)} title={confirmation === "publish" ? "Publier la proclamation ?" : "Retirer la publication ?"} description={confirmation === "publish" ? "Cette action marque les bulletins comme officiels." : "Les bulletins redeviendront provisoires."} size="small" footer={<><button className="button button-secondary" type="button" onClick={() => setConfirmation(null)}>Annuler</button><button className="button button-primary" type="button" onClick={confirmPublication}>{confirmation === "publish" ? <ShieldCheck size={16} /> : <Unlock size={16} />}{confirmation === "publish" ? "Confirmer la publication" : "Confirmer le retrait"}</button></>}>
        {confirmation === "publish" ? <div className="publication-confirm"><span><ShieldCheck size={28} /></span><p>Vous vous apprêtez à publier les résultats de <strong>{schoolClass?.name}</strong> pour le <strong>{term}</strong>.</p><ul><li><Check size={15} />{complete.length} bulletins complets</li><li><Check size={15} />{admitted.length} élèves admis</li><li><Check size={15} />{complete.length - admitted.length} élèves ajournés</li></ul><Notice>Les notes restent modifiables, mais toute modification doit faire l’objet d’une nouvelle vérification du conseil.</Notice></div> : <p>Le retrait ne supprime aucune note ni aucun résultat. Vous pourrez publier à nouveau après vérification.</p>}
      </Modal>
    </>
  );
}
