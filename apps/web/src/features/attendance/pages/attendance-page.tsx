"use client";

import { useState } from "react";
import {
  CalendarCheck2,
  Check,
  CheckCheck,
  Clock3,
  Download,
  Save,
  ShieldCheck,
  UserX,
  X,
} from "lucide-react";
import { useSchool } from "@/features/school/hooks/use-school";
import { Avatar, Badge, Notice, PageHeader } from "@/components/ui";
import { SelectForm } from "@/components/forms";
import { getFullName } from "@/lib/academic";
import { downloadCsv, formatDate, toInputDate } from "@/lib/utils";
import type { AttendanceStatus } from "@/lib/types";

const STATUS_OPTIONS: Array<{ value: AttendanceStatus; label: string; icon: typeof Check }> = [
  { value: "present", label: "Présent", icon: Check },
  { value: "retard", label: "Retard", icon: Clock3 },
  { value: "absent", label: "Absent", icon: X },
  { value: "excuse", label: "Excusé", icon: ShieldCheck },
];

export function AttendanceManager() {
  const { state, saveAttendance } = useSchool();
  const [classId, setClassId] = useState(state.classes[0]?.id ?? "");
  const [date, setDate] = useState(toInputDate());
  const [drafts, setDrafts] = useState<Record<string, AttendanceStatus>>({});
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");
  const students = state.students.filter((student) => student.classId === classId && student.status === "Actif");
  const schoolClass = state.classes.find((item) => item.id === classId);

  const currentStatus = (studentId: string): AttendanceStatus | null => {
    const key = `${date}:${studentId}`;
    return drafts[key] ?? state.attendance.find((record) => record.date === date && record.studentId === studentId)?.status ?? null;
  };
  const statuses = students.map((student) => currentStatus(student.id));
  const marked = statuses.filter(Boolean).length;
  const present = statuses.filter((status) => status === "present" || status === "retard").length;
  const absent = statuses.filter((status) => status === "absent").length;
  const late = statuses.filter((status) => status === "retard").length;

  const setStatus = (studentId: string, status: AttendanceStatus) => {
    setDrafts((current) => ({ ...current, [`${date}:${studentId}`]: status }));
    setMessage("");
    setError("");
  };

  const markAllPresent = () => {
    setDrafts((current) => {
      const next = { ...current };
      students.forEach((student) => { next[`${date}:${student.id}`] = "present"; });
      return next;
    });
    setMessage("");
    setError("");
  };

  const save = () => {
    if (marked !== students.length) {
      setError("Renseignez le statut de chaque élève avant d’enregistrer l’appel.");
      return;
    }
    saveAttendance(students.map((student) => ({ studentId: student.id, classId, date, status: currentStatus(student.id)! })));
    setError("");
    setMessage(`Appel du ${formatDate(date)} enregistré pour ${schoolClass?.name}.`);
  };

  const historyDates = [...new Set(state.attendance.filter((record) => record.classId === classId).map((record) => record.date))].sort().reverse().slice(0, 5);

  return (
    <>
      <PageHeader eyebrow="Vie scolaire" title="Présences & appel" icon={<CalendarCheck2 size={20} />} description="Enregistrez l’appel quotidien et consultez l’assiduité reprise dans les bulletins." actions={<><button className="button button-secondary" type="button" onClick={() => downloadCsv([["Matricule", "Élève", "Date", "Statut"], ...students.map((student) => [student.matricule, getFullName(student), date, STATUS_OPTIONS.find((item) => item.value === currentStatus(student.id))?.label ?? "Non pointé"])], `appel-${schoolClass?.name ?? "classe"}-${date}.csv`)}><Download size={17} /> Exporter</button><button className="button button-primary" type="button" onClick={save} disabled={!students.length}><Save size={17} /> Enregistrer l’appel</button></>} />
      {message && <Notice tone="success" title={message}>Les totaux d’assiduité des bulletins ont été mis à jour.</Notice>}
      {error && <Notice tone="warning" title="Appel incomplet">{error}</Notice>}
      <div className="attendance-layout">
        <section className="card attendance-card">
          <div className="attendance-controls"><SelectForm label="Classe" value={classId} options={state.classes.map((item) => ({ value: item.id, label: item.name }))} onChange={(value) => { setClassId(value); setMessage(""); setError(""); }} /><label><span>Date de l’appel</span><input type="date" value={date} max={toInputDate()} onChange={(event) => { setDate(event.target.value); setMessage(""); setError(""); }} /></label><button className="button button-soft" type="button" onClick={markAllPresent} disabled={!students.length}><CheckCheck size={17} /> Tous présents</button></div>
          <div className="attendance-overview"><div><small>Statuts renseignés</small><strong>{marked}<span> / {students.length}</span></strong></div><div><small>Présents</small><strong>{present}</strong></div><div><small>Absents</small><strong>{absent}</strong></div><div><small>Retards</small><strong>{late}</strong></div></div>
          <div className="attendance-list">
            <div className="attendance-list-head"><span>Élève</span><span>Statut de présence</span></div>
            {students.map((student) => {
              const status = currentStatus(student.id);
              return <div className="attendance-row" key={student.id}><div className="student-cell"><Avatar student={student} /><span><strong>{getFullName(student)}</strong><small>{student.matricule}</small></span></div><div className="attendance-status-buttons">{STATUS_OPTIONS.map((option) => { const Icon = option.icon; return <button type="button" key={option.value} className={`${status === option.value ? "active" : ""} status-${option.value}`} onClick={() => setStatus(student.id, option.value)} aria-pressed={status === option.value}><Icon size={15} />{option.label}</button>; })}</div></div>;
            })}
            {!students.length && <div className="attendance-empty"><UserX size={25} /><p>Aucun élève actif dans cette classe.</p></div>}
          </div>
          <footer className="attendance-footer"><span>{marked === students.length && students.length ? <Badge tone="green" dot>Appel complet</Badge> : <Badge tone="amber" dot>{students.length - marked} statut(s) restant(s)</Badge>}</span><button className="button button-primary" type="button" onClick={save} disabled={!students.length}><Save size={16} /> Enregistrer</button></footer>
        </section>
        <aside className="attendance-sidebar">
          <section className="card"><header className="card-header"><div><p className="eyebrow">Historique</p><h2>Derniers appels</h2></div><CalendarCheck2 size={20} /></header>{historyDates.length ? <div className="attendance-history">{historyDates.map((historyDate) => { const records = state.attendance.filter((record) => record.classId === classId && record.date === historyDate); const presentCount = records.filter((record) => record.status === "present" || record.status === "retard").length; const rate = records.length ? Math.round((presentCount / records.length) * 100) : 0; return <button type="button" key={historyDate} onClick={() => setDate(historyDate)}><span className="history-date"><strong>{formatDate(historyDate, { day: "2-digit" })}</strong><small>{formatDate(historyDate, { month: "short" })}</small></span><span><strong>{formatDate(historyDate, { weekday: "long" })}</strong><small>{records.length} élèves pointés</small></span><Badge tone={rate >= 90 ? "green" : "amber"}>{rate} %</Badge></button>; })}</div> : <p className="muted-paragraph">Aucun appel enregistré pour cette classe.</p>}</section>
          <section className="card attendance-help"><span><CalendarCheck2 size={22} /></span><h3>Bon à savoir</h3><p>Un élève en retard est comptabilisé comme présent dans le taux de présence, mais le retard reste visible sur son bulletin.</p></section>
        </aside>
      </div>
    </>
  );
}
