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
  Mail,
  MapPin,
  Phone,
  Search,
  UserRound,
  UserRoundPlus,
  UsersRound,
} from "lucide-react";
import { useSchool } from "./school-provider";
import { Avatar, Badge, FormField, Modal, PageHeader, TableMenu } from "./ui";
import { SchoolDataTable, type DataTableColumn } from "./data-table";
import { FilterSelect, SelectForm } from "./form-controls";
import {
  formatAverage,
  getAppreciation,
  getClassRanking,
  getFullName,
  getStudentReport,
} from "@/lib/academic";
import { downloadCsv, formatDate } from "@/lib/utils";
import type { Student, StudentStatus } from "@/lib/types";

const STUDENTS_PER_PAGE = 6;

export function StudentDirectory() {
  const { state, updateStudent } = useSchool();
  const router = useRouter();
  const searchParams = useSearchParams();
  const [query, setQuery] = useState("");
  const [classFilter, setClassFilter] = useState("all");
  const [statusFilter, setStatusFilter] = useState("all");
  const [page, setPage] = useState(1);
  const [selectedIds, setSelectedIds] = useState<Set<string>>(new Set());
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

  const exportStudents = (rows: Student[] = filteredStudents) => {
    downloadCsv(
      [
        ["Matricule", "Prénom", "Nom", "Sexe", "Classe", "Statut", "Date d’inscription", "Responsable", "Téléphone"],
        ...rows.map((student) => [
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

  const columns: DataTableColumn<Student>[] = [
    {
      header: "Élève",
      render: (student) => (
        <button className="student-cell student-cell-button" type="button" onClick={() => setLocalSelectedId(student.id)}>
          <Avatar student={student} />
          <span><strong>{getFullName(student)}</strong><small>{student.matricule}</small></span>
        </button>
      ),
    },
    {
      header: "Classe",
      render: (student) => <Badge tone="blue">{state.classes.find((schoolClass) => schoolClass.id === student.classId)?.name ?? "Non affecté"}</Badge>,
    },
    {
      header: "Responsable légal",
      hideOnMobile: true,
      render: (student) => <div className="guardian-cell"><strong>{student.guardian.name}</strong><small>{student.guardian.phone}</small></div>,
    },
    { header: "Inscription", hideOnMobile: true, render: (student) => formatDate(student.enrolledAt) },
    {
      header: "Statut",
      render: (student) => <Badge tone={student.status === "Actif" ? "green" : student.status === "En attente" ? "amber" : "neutral"} dot>{student.status}</Badge>,
    },
  ];

  return (
    <>
      <PageHeader
        eyebrow="Scolarité"
        title="Répertoire des élèves"
        icon={<UsersRound size={20} />}
        description={`${state.students.length} dossiers pour l’année ${state.settings.academicYear}. Recherchez, filtrez et consultez chaque parcours.`}
        actions={
          <>
            <button className="button button-secondary" type="button" onClick={() => exportStudents()}><Download size={17} /> Exporter CSV</button>
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
        <SchoolDataTable
          data={paginatedStudents}
          selectionData={filteredStudents}
          columns={columns}
          page={currentPage}
          pageCount={pageCount}
          pageSize={STUDENTS_PER_PAGE}
          totalItems={filteredStudents.length}
          onPageChange={setPage}
          searchQuery={query}
          onSearchChange={(value) => { setQuery(value); setPage(1); setSelectedIds(new Set()); }}
          searchPlaceholder="Nom, matricule ou responsable…"
          toolbar={(
            <>
              <FilterSelect
                ariaLabel="Filtrer par classe"
                placeholder="Toutes les classes"
                value={classFilter}
                options={[{ value: "all", label: "Toutes les classes" }, ...state.classes.map((schoolClass) => ({ value: schoolClass.id, label: schoolClass.name }))]}
                onChange={(value) => { setClassFilter(value); setPage(1); setSelectedIds(new Set()); }}
              />
              <FilterSelect
                ariaLabel="Filtrer par statut"
                placeholder="Tous les statuts"
                value={statusFilter}
                options={[
                  { value: "all", label: "Tous les statuts" },
                  { value: "Actif", label: "Actifs" },
                  { value: "En attente", label: "En attente" },
                  { value: "Inactif", label: "Inactifs" },
                ]}
                onChange={(value) => { setStatusFilter(value); setPage(1); setSelectedIds(new Set()); }}
              />
              <span className="filter-result-count">{filteredStudents.length} résultat{filteredStudents.length > 1 ? "s" : ""}</span>
            </>
          )}
          selectable
          selectedIds={selectedIds}
          onSelectionChange={setSelectedIds}
          bulkActions={[{ key: "export", label: "Exporter la sélection", icon: <Download size={15} /> }]}
          onBulkAction={(action, rows) => { if (action === "export") exportStudents(rows); }}
          emptyState={{
            icon: <Search size={25} />,
            title: "Aucun dossier trouvé",
            description: "Modifiez les filtres ou lancez une nouvelle recherche.",
            action: <button className="button button-secondary" type="button" onClick={() => { setQuery(""); setClassFilter("all"); setStatusFilter("all"); setPage(1); setSelectedIds(new Set()); }}>Réinitialiser les filtres</button>,
          }}
          getRowLabel={(student) => getFullName(student)}
          renderActions={(student) => (
            <TableMenu label={`Actions pour ${getFullName(student)}`}>
              <button type="button" onClick={() => setLocalSelectedId(student.id)}><Eye size={16} /> Voir le dossier</button>
              <Link href={`/bulletins/?eleve=${student.id}`}><FileText size={16} /> Voir le bulletin</Link>
            </TableMenu>
          )}
        />
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
          <SelectForm id="edit-class" label="Classe" required value={editDraft.classId} options={state.classes.map((schoolClass) => ({ value: schoolClass.id, label: schoolClass.name }))} onChange={(value) => setEditDraft({ ...editDraft, classId: value })} />
          <SelectForm id="edit-status" label="Statut" required value={editDraft.status} options={[{ value: "Actif", label: "Actif" }, { value: "En attente", label: "En attente" }, { value: "Inactif", label: "Inactif" }]} onChange={(value) => setEditDraft({ ...editDraft, status: value as StudentStatus })} />
          <FormField label="Téléphone du responsable" htmlFor="edit-phone" className="form-field-full"><input id="edit-phone" value={editDraft.guardianPhone} onChange={(event) => setEditDraft({ ...editDraft, guardianPhone: event.target.value })} /></FormField>
        </div>
      </Modal>
    </>
  );
}
