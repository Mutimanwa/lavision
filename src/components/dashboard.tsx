"use client";

import Link from "next/link";
import {
  ArrowRight,
  BookOpenCheck,
  CalendarCheck2,
  ClipboardList,
  FileCheck2,
  FileText,
  GraduationCap,
  TrendingUp,
  UserRoundPlus,
  UsersRound,
} from "lucide-react";
import { useSchool } from "./school-provider";
import { Avatar, Badge, PageHeader } from "./ui";
import {
  formatAverage,
  getClassRanking,
  getFullName,
  getStudentReport,
  round,
} from "@/lib/academic";
import { capitalize, formatDate, formatLongDate } from "@/lib/utils";

export function Dashboard() {
  const { state } = useSchool();
  const { settings } = state;
  const activeStudents = state.students.filter((student) => student.status === "Actif");
  const reports = activeStudents.map((student) =>
    getStudentReport(student.id, settings.currentTerm, state.subjects, state.grades),
  );
  const evaluated = reports.filter((report) => report.average !== null);
  const generalAverage = evaluated.length
    ? round(evaluated.reduce((sum, report) => sum + (report.average ?? 0), 0) / evaluated.length)
    : null;
  const completeReports = reports.filter((report) => report.isComplete);
  const admitted = completeReports.filter((report) => (report.average ?? 0) >= settings.passMark);
  const successRate = completeReports.length
    ? Math.round((admitted.length / completeReports.length) * 100)
    : 0;
  const gradeCount = activeStudents.reduce(
    (count, student) =>
      count +
      state.grades.filter(
        (grade) => grade.studentId === student.id && grade.term === settings.currentTerm,
      ).length,
    0,
  );
  const gradeCompletion = activeStudents.length && state.subjects.length
    ? Math.round((gradeCount / (activeStudents.length * state.subjects.length)) * 100)
    : 0;

  const classSummaries = state.classes.map((schoolClass) => {
    const classStudents = activeStudents.filter((student) => student.classId === schoolClass.id);
    const ranking = getClassRanking(
      classStudents,
      settings.currentTerm,
      state.subjects,
      state.grades,
      settings.passMark,
    );
    const ranked = ranking.filter((item) => item.average !== null);
    return {
      ...schoolClass,
      studentCount: classStudents.length,
      average: ranked.length
        ? round(ranked.reduce((sum, item) => sum + (item.average ?? 0), 0) / ranked.length)
        : null,
      complete: ranking.filter((item) => item.report.isComplete).length,
    };
  });

  const recentStudents = [...state.students]
    .sort((a, b) => b.enrolledAt.localeCompare(a.enrolledAt))
    .slice(0, 5);

  const today = new Date();
  const todayKey = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, "0")}-${String(today.getDate()).padStart(2, "0")}`;
  const todayAttendance = state.attendance.filter((record) => record.date === todayKey);
  const presentToday = todayAttendance.filter((record) => record.status === "present" || record.status === "retard").length;
  const attendanceRate = todayAttendance.length
    ? Math.round((presentToday / todayAttendance.length) * 100)
    : null;

  return (
    <>
      <PageHeader
        eyebrow={capitalize(formatLongDate(new Date()))}
        title={`Bonjour, ${settings.directionName.split(" ")[0]} 👋`}
        description="Voici l’essentiel de votre établissement aujourd’hui."
        actions={
          <>
            <Link className="button button-secondary" href="/notes/"><ClipboardList size={17} /> Saisir les notes</Link>
            <Link className="button button-primary" href="/inscriptions/"><UserRoundPlus size={17} /> Nouvelle inscription</Link>
          </>
        }
      />

      <section className="workflow-strip" aria-label="Progression de l’année scolaire">
        <div className="workflow-intro">
          <span className="workflow-icon"><GraduationCap size={22} /></span>
          <div><strong>{settings.currentTerm}</strong><small>Année {settings.academicYear}</small></div>
        </div>
        <div className="workflow-step is-complete"><span><UserRoundPlus size={16} /></span><div><small>Étape 1</small><strong>Inscriptions</strong></div></div>
        <ArrowRight className="workflow-arrow" size={16} />
        <div className={`workflow-step ${gradeCompletion === 100 ? "is-complete" : "is-current"}`}><span><ClipboardList size={16} /></span><div><small>Étape 2 · {gradeCompletion} %</small><strong>Évaluations</strong></div></div>
        <ArrowRight className="workflow-arrow" size={16} />
        <div className={`workflow-step ${completeReports.length ? "is-complete" : ""}`}><span><FileText size={16} /></span><div><small>Étape 3</small><strong>Bulletins</strong></div></div>
        <ArrowRight className="workflow-arrow" size={16} />
        <div className={`workflow-step ${state.publications.length ? "is-complete" : ""}`}><span><GraduationCap size={16} /></span><div><small>Étape 4</small><strong>Proclamation</strong></div></div>
      </section>

      <section className="stats-grid" aria-label="Indicateurs principaux">
        <article className="stat-card">
          <div className="stat-card-top"><span className="stat-icon stat-icon-green"><UsersRound size={21} /></span><Badge tone="green">Année en cours</Badge></div>
          <strong className="stat-value">{activeStudents.length}</strong>
          <p>Élèves actifs</p>
          <small>{state.students.filter((student) => student.status === "En attente").length} dossier(s) en attente</small>
        </article>
        <article className="stat-card">
          <div className="stat-card-top"><span className="stat-icon stat-icon-blue"><BookOpenCheck size={21} /></span><Badge tone="blue">{state.subjects.length} matières</Badge></div>
          <strong className="stat-value">{state.classes.length}</strong>
          <p>Classes ouvertes</p>
          <small>{activeStudents.length ? round(activeStudents.length / state.classes.length, 1) : 0} élèves par classe en moyenne</small>
        </article>
        <article className="stat-card">
          <div className="stat-card-top"><span className="stat-icon stat-icon-amber"><TrendingUp size={21} /></span><Badge tone="amber">/ 20</Badge></div>
          <strong className="stat-value">{formatAverage(generalAverage)}</strong>
          <p>Moyenne de l’établissement</p>
          <small>{settings.currentTerm} · notes pondérées</small>
        </article>
        <article className="stat-card">
          <div className="stat-card-top"><span className="stat-icon stat-icon-purple"><FileCheck2 size={21} /></span><Badge tone="purple">{completeReports.length} bulletins</Badge></div>
          <strong className="stat-value">{successRate}<span className="stat-unit">%</span></strong>
          <p>Taux de réussite</p>
          <small>Seuil d’admission : {settings.passMark}/20</small>
        </article>
      </section>

      <div className="dashboard-grid">
        <section className="card performance-card">
          <header className="card-header">
            <div><p className="eyebrow">Résultats scolaires</p><h2>Performance par classe</h2></div>
            <Link className="text-link" href="/proclamation/">Voir le classement <ArrowRight size={15} /></Link>
          </header>
          <div className="performance-chart">
            <div className="chart-scale"><span>20</span><span>15</span><span>10</span><span>5</span><span>0</span></div>
            <div className="chart-plot">
              <div className="chart-grid-lines"><i /><i /><i /><i /><i /></div>
              <div className="chart-bars">
                {classSummaries.map((item, index) => (
                  <div className="chart-bar-group" key={item.id}>
                    <div className="chart-bar-value">{formatAverage(item.average)}</div>
                    <div className="chart-bar-track">
                      <div
                        className={`chart-bar chart-bar-${index % 3}`}
                        style={{ height: `${Math.max(3, ((item.average ?? 0) / 20) * 100)}%` }}
                      />
                    </div>
                    <strong>{item.name}</strong>
                    <small>{item.studentCount} élèves</small>
                  </div>
                ))}
              </div>
            </div>
          </div>
          <div className="chart-caption"><span><i className="legend-dot legend-green" />Moyenne générale sur 20</span><span>{settings.currentTerm}</span></div>
        </section>

        <section className="card completion-card">
          <header className="card-header"><div><p className="eyebrow">Avancement</p><h2>Saisie des notes</h2></div></header>
          <div className="completion-ring-wrap">
            <div className="completion-ring" style={{ "--progress": `${gradeCompletion * 3.6}deg` } as React.CSSProperties}>
              <div><strong>{gradeCompletion}<span>%</span></strong><small>complété</small></div>
            </div>
          </div>
          <p className="completion-copy">{gradeCount} notes saisies sur {activeStudents.length * state.subjects.length} attendues pour les élèves actifs.</p>
          <div className="completion-legend">
            <span><i className="legend-dot legend-green" />Saisies <strong>{gradeCount}</strong></span>
            <span><i className="legend-dot legend-muted" />Restantes <strong>{Math.max(0, activeStudents.length * state.subjects.length - gradeCount)}</strong></span>
          </div>
          <Link className="button button-soft button-full" href="/notes/">Compléter les notes <ArrowRight size={16} /></Link>
        </section>

        <section className="card recent-card">
          <header className="card-header">
            <div><p className="eyebrow">Scolarité</p><h2>Inscriptions récentes</h2></div>
            <Link className="text-link" href="/eleves/">Tous les élèves <ArrowRight size={15} /></Link>
          </header>
          <div className="table-wrap">
            <table className="data-table recent-table">
              <thead><tr><th>Élève</th><th>Classe</th><th>Date</th><th>Statut</th></tr></thead>
              <tbody>
                {recentStudents.map((student) => (
                  <tr key={student.id}>
                    <td><Link className="student-cell" href={`/eleves/?eleve=${student.id}`}><Avatar student={student} /><span><strong>{getFullName(student)}</strong><small>{student.matricule}</small></span></Link></td>
                    <td>{state.classes.find((item) => item.id === student.classId)?.name ?? "—"}</td>
                    <td>{formatDate(student.enrolledAt, { day: "numeric", month: "short" })}</td>
                    <td><Badge tone={student.status === "Actif" ? "green" : student.status === "En attente" ? "amber" : "neutral"} dot>{student.status}</Badge></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </section>

        <div className="dashboard-side-stack">
          <section className="card attendance-summary-card">
            <header className="card-header"><div><p className="eyebrow">Aujourd’hui</p><h2>Présences</h2></div><span className="stat-icon stat-icon-green"><CalendarCheck2 size={20} /></span></header>
            <div className="attendance-rate"><strong>{attendanceRate === null ? "—" : `${attendanceRate} %`}</strong><span>de présence</span></div>
            <div className="progress-track"><span style={{ width: `${attendanceRate ?? 0}%` }} /></div>
            <p>{todayAttendance.length ? `${presentToday} présents sur ${todayAttendance.length} élèves pointés.` : "L’appel n’a pas encore été enregistré aujourd’hui."}</p>
            <Link className="text-link" href="/presences/">Faire l’appel <ArrowRight size={15} /></Link>
          </section>
          <section className="card quick-actions-card">
            <header className="card-header"><div><p className="eyebrow">Raccourcis</p><h2>Actions rapides</h2></div></header>
            <Link href="/bulletins/"><span className="quick-action-icon"><FileText size={18} /></span><span><strong>Consulter un bulletin</strong><small>Notes, moyenne et rang</small></span><ArrowRight size={16} /></Link>
            <Link href="/proclamation/"><span className="quick-action-icon"><GraduationCap size={18} /></span><span><strong>Préparer la proclamation</strong><small>Classement et décisions</small></span><ArrowRight size={16} /></Link>
          </section>
        </div>
      </div>
    </>
  );
}
