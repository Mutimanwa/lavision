"use client";

import Link from "next/link";
import { useMemo, useState } from "react";
import {
  AlertTriangle,
  ArrowRight,
  CalendarCheck2,
  CheckCircle2,
  ClipboardList,
  Eye,
  FileText,
  GraduationCap,
  Info,
  Plus,
  Search,
  TrendingUp,
  UserRoundPlus,
  UsersRound,
} from "lucide-react";
import { useSchool } from "@/features/school/hooks/use-school";
import { Avatar, Badge, PageHeader, TableMenu } from "@/components/ui";
import { formatAverage, getFullName, getStudentReport } from "@/lib/academic";
import { toInputDate } from "@/lib/utils";
import { useWorkspace } from "@/features/auth/hooks/use-workspace";
import { RoleWorkspaceHome } from "../components/role-workspace-home";
import { FirstStepsCard } from "@/features/onboarding/components/first-steps-card";
import { useModules } from "@/features/navigation/hooks/use-modules";

export function Dashboard() {
  const { role } = useWorkspace();
  if (role !== "direction") return <RoleWorkspaceHome />;
  return <DirectionDashboard />;
}

function DirectionDashboard() {
  const { state } = useSchool();
  const { modules } = useModules();
  const [activityQuery, setActivityQuery] = useState("");
  const moduleHref = (href: string) => {
    const linkedModule = modules.find((module) => module.href && href.startsWith(module.href));
    return linkedModule?.status === "disabled" ? "/modules/" : href;
  };
  const activeStudents = state.students.filter((student) => student.status === "Actif");
  const pendingStudents = state.students.filter((student) => student.status === "En attente");
  const activeStudentIds = new Set(activeStudents.map((student) => student.id));
  const today = toInputDate();
  const todayAttendance = state.attendance.filter((record) => record.date === today);

  const reports = useMemo(() => activeStudents.map((student) => ({
    student,
    report: getStudentReport(student.id, state.settings.currentTerm, state.subjects, state.grades),
  })), [activeStudents, state.grades, state.settings.currentTerm, state.subjects]);

  const completeReports = reports.filter((item) => item.report.isComplete);
  const passedReports = completeReports.filter((item) => (item.report.average ?? 0) >= state.settings.passMark);
  const successRate = completeReports.length ? Math.round((passedReports.length / completeReports.length) * 100) : 0;
  const gradeCount = state.grades.filter((grade) => grade.term === state.settings.currentTerm && activeStudentIds.has(grade.studentId)).length;
  const remainingGrades = Math.max(0, activeStudents.length * state.subjects.length - gradeCount);
  const incompleteBulletins = Math.max(0, activeStudents.length - completeReports.length);
  const attendanceRate = todayAttendance.length
    ? Math.round((todayAttendance.filter((record) => record.status === "present" || record.status === "retard").length / todayAttendance.length) * 100)
    : null;

  const classSummaries = state.classes.map((schoolClass) => {
    const classReports = reports.filter((item) => item.student.classId === schoolClass.id && item.report.average !== null);
    const average = classReports.length
      ? classReports.reduce((sum, item) => sum + (item.report.average ?? 0), 0) / classReports.length
      : null;
    return {
      ...schoolClass,
      students: reports.filter((item) => item.student.classId === schoolClass.id),
      complete: reports.filter((item) => item.student.classId === schoolClass.id && item.report.isComplete).length,
      average,
    };
  });
  const topClasses = [...classSummaries]
    .sort((a, b) => (b.average ?? -1) - (a.average ?? -1))
    .slice(0, 4);
  const scaleMax = Math.max(20, Math.ceil(Math.max(0, ...classSummaries.map((item) => item.average ?? 0)) / 5) * 5);

  const recentStudents = useMemo(() => {
    const normalized = activityQuery.trim().toLocaleLowerCase("fr");
    return [...state.students]
      .sort((a, b) => b.enrolledAt.localeCompare(a.enrolledAt))
      .filter((student) => {
        if (!normalized) return true;
        const className = state.classes.find((schoolClass) => schoolClass.id === student.classId)?.name ?? "";
        return `${getFullName(student)} ${student.matricule} ${className}`
          .toLocaleLowerCase("fr")
          .includes(normalized);
      })
      .slice(0, 6);
  }, [activityQuery, state.classes, state.students]);

  const directionFirstName = state.settings.directionName.split(" ")[0] || "Direction";

  return (
    <>
      <PageHeader
        size="large"
        title={`Ravi de vous revoir, ${directionFirstName} !`}
        description={`Gérez l’activité de votre établissement. Année scolaire ${state.settings.academicYear}`}
        actions={(
          <>
            <Link className="button button-secondary" href={moduleHref("/notes/")}><ClipboardList size={17} /> Saisir les notes</Link>
            <Link className="button button-primary" href={moduleHref("/inscriptions/")}><Plus size={17} /> Nouvelle inscription</Link>
          </>
        )}
      />

      <FirstStepsCard />

      <section className="stats-grid" aria-label="Indicateurs clés">
        <article className="card stat-card">
          <div className="stat-card-top"><span className="stat-icon stat-icon-green"><UsersRound size={19} /></span><Badge tone="green">{activeStudents.length} actifs</Badge></div>
          <div className="stat-value">{state.students.length}</div>
          <p>Élèves inscrits</p>
          <small>{pendingStudents.length} dossier{pendingStudents.length > 1 ? "s" : ""} en attente</small>
        </article>
        <article className="card stat-card">
          <div className="stat-card-top"><span className="stat-icon stat-icon-blue"><GraduationCap size={19} /></span><Badge tone="blue">{state.subjects.length} matières</Badge></div>
          <div className="stat-value">{state.classes.length}</div>
          <p>Classes ouvertes</p>
          <small>{activeStudents.length} élèves affectés</small>
        </article>
        <article className="card stat-card">
          <div className="stat-card-top"><span className="stat-icon stat-icon-amber"><ClipboardList size={19} /></span><Badge tone={remainingGrades ? "amber" : "green"}>{state.settings.currentTerm}</Badge></div>
          <div className="stat-value">{gradeCount}</div>
          <p>Notes enregistrées</p>
          <small>{remainingGrades} valeur{remainingGrades > 1 ? "s" : ""} à compléter</small>
        </article>
        <article className="card stat-card">
          <div className="stat-card-top"><span className="stat-icon stat-icon-purple"><TrendingUp size={19} /></span><Badge tone={successRate >= 70 ? "green" : "amber"}>Seuil {state.settings.passMark}/20</Badge></div>
          <div className="stat-value">{successRate}<span className="stat-unit">%</span></div>
          <p>Taux de réussite</p>
          <small>{completeReports.length} bulletin{completeReports.length > 1 ? "s" : ""} complet{completeReports.length > 1 ? "s" : ""}</small>
        </article>
      </section>

      <div className="dashboard-primary-grid">
        <section className="card performance-card">
          <header className="card-header">
            <div><p className="eyebrow">Résultats scolaires</p><h2>Moyenne par classe</h2></div>
            <Badge tone="neutral">{state.settings.currentTerm}</Badge>
          </header>
          <div className="performance-chart">
            <div className="chart-scale"><span>{scaleMax}</span><span>{Math.round(scaleMax * 0.75)}</span><span>{Math.round(scaleMax * 0.5)}</span><span>{Math.round(scaleMax * 0.25)}</span><span>0</span></div>
            <div className="chart-plot">
              <div className="chart-grid-lines" aria-hidden="true"><i /><i /><i /><i /><i /></div>
              <div className="chart-bars">
                {classSummaries.map((item, index) => (
                  <div className="chart-bar-group" key={item.id}>
                    <div className="chart-bar-value">{item.average === null ? "—" : formatAverage(item.average)}</div>
                    <div className="chart-bar-track"><span className={`chart-bar chart-bar-${index % 4}`} style={{ height: `${Math.max(3, ((item.average ?? 0) / scaleMax) * 100)}%` }} /></div>
                    <strong>{item.name.replace(" - Humanités", "")}</strong>
                    <small>{item.students.length} élève{item.students.length > 1 ? "s" : ""}</small>
                  </div>
                ))}
              </div>
            </div>
          </div>
          <footer className="chart-caption"><Info size={14} /><span>Moyenne calculée à partir des notes disponibles. Barème de passage : {state.settings.passMark}/20.</span></footer>
        </section>

        <aside className="card pending-card">
          <header className="card-header"><div><p className="eyebrow">À traiter</p><h2>Actions en attente</h2></div></header>
          <div className="pending-list">
            <Link href={moduleHref("/inscriptions/")}>
              <span className="pending-icon"><UserRoundPlus size={19} /></span>
              <span className="pending-copy"><strong>Dossiers d’inscription</strong><small>Vérifier les admissions provisoires</small></span>
              <span className="pending-count">{pendingStudents.length}</span>
            </Link>
            <Link href={moduleHref("/notes/")}>
              <span className="pending-icon"><ClipboardList size={19} /></span>
              <span className="pending-copy"><strong>Notes à compléter</strong><small>Période : {state.settings.currentTerm}</small></span>
              <span className="pending-count">{remainingGrades}</span>
            </Link>
            <Link href={moduleHref("/bulletins/")}>
              <span className="pending-icon"><FileText size={19} /></span>
              <span className="pending-copy"><strong>Bulletins incomplets</strong><small>Contrôler les notes manquantes</small></span>
              <span className="pending-count">{incompleteBulletins}</span>
            </Link>
          </div>
        </aside>
      </div>

      <div className="dashboard-secondary-grid">
        <section className="card class-performance-card">
          <header className="card-header"><div><p className="eyebrow">Comparatif</p><h2>Performance des classes</h2></div><Link className="text-link" href={moduleHref("/proclamation/")} aria-label="Voir les classements"><ArrowRight size={17} /></Link></header>
          <div className="class-performance-list">
            {topClasses.map((schoolClass, index) => (
              <div className="class-performance-row" key={schoolClass.id}>
                <span className="class-rank">{index + 1}</span>
                <span className="class-avatar">{schoolClass.name.charAt(0)}</span>
                <div className="class-performance-main">
                  <div className="class-performance-label"><strong>{schoolClass.name}</strong><small>{schoolClass.students.length} élèves</small></div>
                  <div className="progress-track"><span style={{ width: `${Math.min(100, ((schoolClass.average ?? 0) / 20) * 100)}%` }} /></div>
                </div>
                <strong className="class-average">{schoolClass.average === null ? "—" : formatAverage(schoolClass.average)}</strong>
              </div>
            ))}
          </div>
        </section>

        <section className="card dashboard-alerts-card">
          <header className="card-header"><div><p className="eyebrow">Suivi</p><h2>Alertes et rappels</h2></div><Badge tone={remainingGrades || pendingStudents.length ? "amber" : "green"}>{remainingGrades + pendingStudents.length}</Badge></header>
          <div className="dashboard-alert-list">
            {remainingGrades > 0 ? (
              <Link href={moduleHref("/notes/")}>
                <span className="dashboard-alert-icon warning"><AlertTriangle size={16} /></span>
                <span><strong>Saisie des notes incomplète</strong><small>{remainingGrades} notes restantes · {state.settings.currentTerm}</small></span>
                <span className="dashboard-alert-tag">À faire</span>
              </Link>
            ) : (
              <div className="dashboard-alert-static"><span className="dashboard-alert-icon success"><CheckCircle2 size={16} /></span><span><strong>Grille de notes complète</strong><small>Toutes les valeurs attendues sont saisies</small></span></div>
            )}
            {pendingStudents.length > 0 && (
              <Link href={moduleHref("/eleves/")}>
                <span className="dashboard-alert-icon warning"><UserRoundPlus size={16} /></span>
                <span><strong>Dossiers élèves à valider</strong><small>{pendingStudents.length} inscription{pendingStudents.length > 1 ? "s" : ""} en attente</small></span>
                <span className="dashboard-alert-tag">À faire</span>
              </Link>
            )}
            <div className="dashboard-alert-static">
              <span className="dashboard-alert-icon"><Info size={16} /></span>
              <span><strong>Sauvegarde locale active</strong><small>Export JSON recommandé après chaque délibération</small></span>
            </div>
          </div>
        </section>

        <section className="card dashboard-quick-card">
          <div className="quick-illustration" aria-hidden="true"><span><GraduationCap size={24} /></span><i /><span><UserRoundPlus size={23} /></span></div>
          <h2>Développez votre établissement</h2>
          <p>Créez un dossier complet et affectez immédiatement l’élève à sa nouvelle classe.</p>
          <Link className="button button-primary button-full" href={moduleHref("/inscriptions/")}><Plus size={16} /> Nouvelle inscription</Link>
          <Link className="text-link" href={moduleHref("/presences/")}><CalendarCheck2 size={14} /> {attendanceRate === null ? "Faire l’appel · à enregistrer" : `Faire l’appel · ${attendanceRate}% présents`}</Link>
        </section>
      </div>

      <section className="card activity-card">
        <div className="activity-heading">
          <div><h2>Inscriptions récentes</h2><p>Retrouvez rapidement les derniers dossiers enregistrés.</p></div>
          <Link className="text-link" href={moduleHref("/eleves/")}>Tous les élèves <ArrowRight size={14} /></Link>
        </div>
        <div className="table-panel-toolbar">
          <div className="table-search"><Search size={16} /><input type="search" value={activityQuery} onChange={(event) => setActivityQuery(event.target.value)} placeholder="Rechercher un élève, un matricule…" aria-label="Rechercher dans les inscriptions récentes" /></div>
          <small>{recentStudents.length} dossier{recentStudents.length > 1 ? "s" : ""} affiché{recentStudents.length > 1 ? "s" : ""}</small>
        </div>
        <div className="table-wrap">
          <table className="data-table">
            <thead><tr><th>Élève</th><th>Classe</th><th>Date d’inscription</th><th>Statut</th><th className="table-actions-heading">Actions</th></tr></thead>
            <tbody>
              {recentStudents.map((student) => {
                const schoolClass = state.classes.find((item) => item.id === student.classId);
                return (
                  <tr key={student.id}>
                    <td><div className="student-cell"><Avatar student={student} size="small" /><div><strong>{getFullName(student)}</strong><small>{student.matricule}</small></div></div></td>
                    <td><span className="badge badge-neutral">{schoolClass?.name ?? "Non affecté"}</span></td>
                    <td>{new Intl.DateTimeFormat("fr-FR").format(new Date(`${student.enrolledAt}T12:00:00`))}</td>
                    <td><Badge tone={student.status === "Actif" ? "green" : student.status === "En attente" ? "amber" : "red"} dot>{student.status}</Badge></td>
                    <td className="table-actions-heading">
                      <TableMenu label={`Actions pour ${getFullName(student)}`}>
                        <Link href={`/eleves/?eleve=${student.id}`}><Eye size={16} /> Voir le dossier</Link>
                        <Link href={moduleHref(`/bulletins/?eleve=${student.id}`)}><FileText size={16} /> Voir le bulletin</Link>
                      </TableMenu>
                    </td>
                  </tr>
                );
              })}
              {recentStudents.length === 0 && (
                <tr><td colSpan={5} className="table-empty-cell">Aucun dossier ne correspond à cette recherche.</td></tr>
              )}
            </tbody>
          </table>
        </div>
        <footer className="table-footer"><span>{recentStudents.length} inscription{recentStudents.length > 1 ? "s" : ""} récente{recentStudents.length > 1 ? "s" : ""}</span><span>{state.settings.academicYear}</span></footer>
      </section>
    </>
  );
}
