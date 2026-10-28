"use client";

import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { useMemo, useState } from "react";
import {
  ArrowRight,
  Download,
  Edit3,
  Eye,
  FileText,
  Filter,
  Mail,
  MapPin,
  Phone,
  Search,
  UserRound,
  UserRoundPlus,
  UsersRound,
} from "lucide-react";
import { useSchool } from "./school-provider";
import { Avatar, Badge, EmptyState, FormField, Modal, PageHeader, TableMenu, TablePagination } from "./ui";
import {
  formatAverage,
  getAppreciation,
  getClassRanking,
  getFullName,
  getStudentReport,
} from "@/lib/academic";
import { downloadCsv, formatDate } from "@/lib/utils";
import type { StudentStatus } from "@/lib/types";

const STUDENTS_PER_PAGE = 6;

export function StudentDirectory() {
  const { state, updateStudent } = useSchool();
  const router = useRouter();
  const searchParams = useSearchParams();
  const [query, setQuery] = useState("");
  const [classFilter, setClassFilter] = useState("all");
  const [statusFilter, setStatusFilter] = useState("all");
  const [page, setPage] = useState(1);
  const [localSelectedId, setLocalSelectedId] = useState<string | null>(null);
  const [editing, setEditing] = useState(false);
  const [editDraft, setEditDraft] = useState({
    firstName: "",
    lastName: "",
    classId: "",
    status: "Actif" as StudentStatus,
    guardianPhone: "",
  });

  const selectedId = searchParams.get("eleve") ?? localSelectedId;
  const selectedStudent = state.students.find((student) => student.id === selectedId) ?? null;

  const filteredStudents = useMemo(() => {
    const normalized = query.trim().toLocaleLowerCase("fr");
    return state.students.filter((student) => {
      const matchesQuery =
        !normalized ||
        `${getFullName(student)} ${student.matricule} ${student.guardian.name}`
          .toLocaleLowerCase("fr")
          .includes(normalized);
      return (
        matchesQuery &&
        (classFilter === "all" || student.classId === classFilter) &&
        (statusFilter === "all" || student.status === statusFilter)
      );
    });
  }, [classFilter, query, state.students, statusFilter]);

  const pageCount = Math.max(1, Math.ceil(filteredStudents.length / STUDENTS_PER_PAGE));
  const currentPage = Math.min(page, pageCount);
  const paginatedStudents = filteredStudents.slice(
    (currentPage - 1) * STUDENTS_PER_PAGE,
    currentPage * STUDENTS_PER_PAGE,
  );

  const closeDetails = () => {
    setLocalSelectedId(null);
    setEditing(false);
    if (searchParams.get("eleve")) router.replace("/eleves/");
  };

  const openEdit = (studentId: string) => {
    const student = state.students.find((item) => item.id === studentId);
    if (!student) return;
    setEditDraft({
      firstName: student.firstName,
      lastName: student.lastName,
      classId: student.classId,
      status: student.status,
      guardianPhone: student.guardian.phone,
    });
    setEditing(true);
  };

  const saveEdit = () => {
    if (!selectedStudent || !editDraft.firstName.trim() || !editDraft.lastName.trim()) return;
    updateStudent(selectedStudent.id, {
      firstName: editDraft.firstName.trim(),
      lastName: editDraft.lastName.trim(),
      classId: editDraft.classId,
      status: editDraft.status,
      guardian: { ...selectedStudent.guardian, phone: editDraft.guardianPhone.trim() },
    });
    setEditing(false);
  };

  const exportStudents = () => {
    downloadCsv(
      [
        ["Matricule", "Prénom", "Nom", "Sexe", "Classe", "Statut", "Date d’inscription", "Responsable", "Téléphone"],
        ...filteredStudents.map((student) => [
          student.matricule,
          student.firstName,
          student.lastName,
          student.gender,
          state.classes.find((item) => item.id === student.classId)?.name ?? "",
          student.status,
          student.enrolledAt,
          student.guardian.name,
          student.guardian.phone,
        ]),
      ],
      "lavision-eleves.csv",
    );
  };

  const selectedClass = selectedStudent
    ? state.classes.find((item) => item.id === selectedStudent.classId)
    : null;
  const selectedReport = selectedStudent
    ? getStudentReport(selectedStudent.id, state.settings.currentTerm, state.subjects, state.grades)
    : null;
  const selectedRanking = selectedStudent && selectedClass
    ? getClassRanking(
        state.students.filter((student) => student.classId === selectedClass.id && student.status === "Actif"),
        state.settings.currentTerm,
        state.subjects,
        state.grades,
        state.settings.passMark,
      ).find((item) => item.studentId === selectedStudent.id)
    : null;

  return (
    <>
      <PageHeader
        eyebrow="Scolarité"
        title="Répertoire des élèves"
        description={`${state.students.length} dossiers pour l’année ${state.settings.academicYear}. Recherchez, filtrez et consultez chaque parcours.`}
        actions={
          <>
            <button className="button button-secondary" type="button" onClick={exportStudents}><Download size={17} /> Exporter CSV</button>
            <Link className="button button-primary" href="/inscriptions/"><UserRoundPlus size={17} /> Nouvelle inscription</Link>
          </>
        }
      />

      <section className="mini-stats-grid">
        <div><span className="mini-stat-icon green"><UsersRound size={19} /></span><p><strong>{state.students.filter((student) => student.status === "Actif").length}</strong><small>Élèves actifs</small></p></div>
        <div><span className="mini-stat-icon amber"><UserRound size={19} /></span><p><strong>{state.students.filter((student) => student.status === "En attente").length}</strong><small>Dossiers en attente</small></p></div>
        <div><span className="mini-stat-icon blue"><UserRound size={19} /></span><p><strong>{state.students.filter((student) => student.gender === "Fille").length}</strong><small>Filles inscrites</small></p></div>
        <div><span className="mini-stat-icon purple"><UserRound size={19} /></span><p><strong>{state.students.filter((student) => student.gender === "Garçon").length}</strong><small>Garçons inscrits</small></p></div>
      </section>

      <section className="card directory-card">
        <div className="filter-toolbar">
          <div className="filter-search"><Search size={18} /><input type="search" value={query} onChange={(event) => { setQuery(event.target.value); setPage(1); }} placeholder="Nom, matricule ou responsable…" aria-label="Rechercher dans les élèves" /></div>
          <div className="filter-select"><Filter size={16} /><select value={classFilter} onChange={(event) => { setClassFilter(event.target.value); setPage(1); }} aria-label="Filtrer par classe"><option value="all">Toutes les classes</option>{state.classes.map((schoolClass) => <option value={schoolClass.id} key={schoolClass.id}>{schoolClass.name}</option>)}</select></div>
          <select value={statusFilter} onChange={(event) => { setStatusFilter(event.target.value); setPage(1); }} aria-label="Filtrer par statut"><option value="all">Tous les statuts</option><option value="Actif">Actifs</option><option value="En attente">En attente</option><option value="Inactif">Inactifs</option></select>
          <span className="filter-result-count">{filteredStudents.length} résultat{filteredStudents.length > 1 ? "s" : ""}</span>
        </div>

        {filteredStudents.length ? (
          <div className="table-wrap">
            <table className="data-table student-directory-table">
              <thead><tr><th>Élève</th><th>Classe</th><th>Responsable légal</th><th>Inscription</th><th>Statut</th><th className="table-actions-heading">Actions</th></tr></thead>
              <tbody>
                {paginatedStudents.map((student) => {
                  const schoolClass = state.classes.find((item) => item.id === student.classId);
                  return (
                    <tr key={student.id}>
                      <td><button className="student-cell student-cell-button" type="button" onClick={() => setLocalSelectedId(student.id)}><Avatar student={student} /><span><strong>{getFullName(student)}</strong><small>{student.matricule}</small></span></button></td>
                      <td><Badge tone="blue">{schoolClass?.name ?? "Non affecté"}</Badge></td>
                      <td><div className="guardian-cell"><strong>{student.guardian.name}</strong><small>{student.guardian.phone}</small></div></td>
                      <td>{formatDate(student.enrolledAt)}</td>
                      <td><Badge tone={student.status === "Actif" ? "green" : student.status === "En attente" ? "amber" : "neutral"} dot>{student.status}</Badge></td>
                      <td className="table-actions-heading">
                        <TableMenu label={`Actions pour ${getFullName(student)}`}>
                          <button type="button" onClick={() => setLocalSelectedId(student.id)}><Eye size={16} /> Voir le dossier</button>
                          <Link href={`/bulletins/?eleve=${student.id}`}><FileText size={16} /> Voir le bulletin</Link>
                        </TableMenu>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        ) : (
          <EmptyState icon={<Search size={28} />} title="Aucun dossier trouvé" description="Modifiez les filtres ou lancez une nouvelle recherche." action={<button className="button button-secondary" type="button" onClick={() => { setQuery(""); setClassFilter("all"); setStatusFilter("all"); setPage(1); }}>Réinitialiser les filtres</button>} />
        )}
        {filteredStudents.length > 0 && (
          <TablePagination
            page={currentPage}
            pageCount={pageCount}
            pageSize={STUDENTS_PER_PAGE}
            totalItems={filteredStudents.length}
            onPageChange={setPage}
          />
        )}
      </section>

      <Modal open={Boolean(selectedStudent)} onClose={closeDetails} title="Dossier élève" description={selectedStudent?.matricule} size="large">
        {selectedStudent && (
          <div className="student-profile">
            <div className="student-profile-hero">
              <Avatar student={selectedStudent} size="large" />
              <div><h3>{getFullName(selectedStudent)}</h3><p>{selectedClass?.name} · {state.settings.academicYear}</p><Badge tone={selectedStudent.status === "Actif" ? "green" : selectedStudent.status === "En attente" ? "amber" : "neutral"} dot>{selectedStudent.status}</Badge></div>
              <button className="button button-secondary button-small" type="button" onClick={() => openEdit(selectedStudent.id)}><Edit3 size={15} /> Modifier</button>
            </div>
            <div className="profile-academic-stats">
              <div><small>Moyenne générale</small><strong>{formatAverage(selectedReport?.average ?? null)}<span> / 20</span></strong></div>
              <div><small>Rang de classe</small><strong>{selectedRanking?.rank ? `${selectedRanking.rank}e` : "—"}</strong></div>
              <div><small>Appréciation</small><strong>{getAppreciation(selectedReport?.average ?? null)}</strong></div>
            </div>
            <div className="profile-columns">
              <section><h4>Informations personnelles</h4><dl><div><dt>Sexe</dt><dd>{selectedStudent.gender}</dd></div><div><dt>Date de naissance</dt><dd>{formatDate(selectedStudent.birthDate)}</dd></div><div><dt>Lieu de naissance</dt><dd>{selectedStudent.birthPlace}</dd></div><div><dt>Nationalité</dt><dd>{selectedStudent.nationality}</dd></div><div><dt>Adresse</dt><dd><MapPin size={14} />{selectedStudent.address || "Non renseignée"}</dd></div></dl></section>
              <section><h4>Responsable légal</h4><dl><div><dt>Nom</dt><dd>{selectedStudent.guardian.name}</dd></div><div><dt>Lien</dt><dd>{selectedStudent.guardian.relationship}</dd></div><div><dt>Téléphone</dt><dd><Phone size={14} />{selectedStudent.guardian.phone}</dd></div><div><dt>E-mail</dt><dd><Mail size={14} />{selectedStudent.guardian.email || "Non renseigné"}</dd></div></dl></section>
            </div>
            <div className="profile-school-info"><div><small>Classe</small><strong>{selectedClass?.name}</strong></div><div><small>Professeur principal</small><strong>{selectedClass?.headTeacher}</strong></div><div><small>Date d’inscription</small><strong>{formatDate(selectedStudent.enrolledAt)}</strong></div></div>
            <div className="profile-actions"><Link className="button button-primary" href={`/bulletins/?eleve=${selectedStudent.id}`}><FileText size={17} /> Voir le bulletin <ArrowRight size={16} /></Link><button className="button button-secondary" type="button" onClick={closeDetails}>Fermer le dossier</button></div>
          </div>
        )}
      </Modal>

      <Modal
        open={editing}
        onClose={() => setEditing(false)}
        title="Modifier le dossier"
        description="Les changements sont immédiatement répercutés sur les autres modules."
        footer={<><button className="button button-secondary" type="button" onClick={() => setEditing(false)}>Annuler</button><button className="button button-primary" type="button" onClick={saveEdit}>Enregistrer les modifications</button></>}
      >
        <div className="form-grid">
          <FormField label="Prénom" htmlFor="edit-firstName" required><input id="edit-firstName" value={editDraft.firstName} onChange={(event) => setEditDraft({ ...editDraft, firstName: event.target.value })} /></FormField>
          <FormField label="Nom" htmlFor="edit-lastName" required><input id="edit-lastName" value={editDraft.lastName} onChange={(event) => setEditDraft({ ...editDraft, lastName: event.target.value })} /></FormField>
          <FormField label="Classe" htmlFor="edit-class" required><select id="edit-class" value={editDraft.classId} onChange={(event) => setEditDraft({ ...editDraft, classId: event.target.value })}>{state.classes.map((schoolClass) => <option value={schoolClass.id} key={schoolClass.id}>{schoolClass.name}</option>)}</select></FormField>
          <FormField label="Statut" htmlFor="edit-status" required><select id="edit-status" value={editDraft.status} onChange={(event) => setEditDraft({ ...editDraft, status: event.target.value as StudentStatus })}><option>Actif</option><option>En attente</option><option>Inactif</option></select></FormField>
          <FormField label="Téléphone du responsable" htmlFor="edit-phone" className="form-field-full"><input id="edit-phone" value={editDraft.guardianPhone} onChange={(event) => setEditDraft({ ...editDraft, guardianPhone: event.target.value })} /></FormField>
        </div>
      </Modal>
    </>
  );
}
