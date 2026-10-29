"use client";

import Link from "next/link";
import { useState } from "react";
import {
  ArrowLeft,
  ArrowRight,
  Check,
  CheckCircle2,
  FileText,
  GraduationCap,
  RotateCcw,
  ShieldCheck,
  UserRound,
  UsersRound,
} from "lucide-react";
import { useSchool } from "@/features/school/hooks/use-school";
import { Notice, PageHeader } from "@/components/ui";
import { FormField } from "@/components/forms";
import type { EnrollmentInput, Gender, Student, StudentStatus } from "@/lib/types";
import { formatDate, toInputDate } from "@/lib/utils";
import { getFullName } from "@/lib/academic";

const STEPS = [
  { label: "Identité", icon: UserRound },
  { label: "Scolarité", icon: GraduationCap },
  { label: "Responsable", icon: UsersRound },
  { label: "Confirmation", icon: FileText },
];

interface EnrollmentDraft {
  firstName: string;
  lastName: string;
  gender: Gender;
  birthDate: string;
  birthPlace: string;
  nationality: string;
  address: string;
  classId: string;
  enrolledAt: string;
  status: StudentStatus;
  previousSchool: string;
  guardianName: string;
  guardianRelationship: string;
  guardianPhone: string;
  guardianEmail: string;
}

const initialDraft = (): EnrollmentDraft => ({
  firstName: "",
  lastName: "",
  gender: "Fille",
  birthDate: "",
  birthPlace: "",
  nationality: "Burundaise",
  address: "",
  classId: "",
  enrolledAt: toInputDate(),
  status: "Actif",
  previousSchool: "",
  guardianName: "",
  guardianRelationship: "Mère",
  guardianPhone: "",
  guardianEmail: "",
});

export function EnrollmentForm() {
  const { state, addStudent } = useSchool();
  const [step, setStep] = useState(0);
  const [draft, setDraft] = useState<EnrollmentDraft>(initialDraft);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [enrolledStudent, setEnrolledStudent] = useState<Student | null>(null);

  const update = <Key extends keyof EnrollmentDraft>(key: Key, value: EnrollmentDraft[Key]) => {
    setDraft((current) => ({ ...current, [key]: value }));
    setErrors((current) => {
      if (!current[key]) return current;
      const next = { ...current };
      delete next[key];
      return next;
    });
  };

  const validateStep = (stepToValidate: number) => {
    const nextErrors: Record<string, string> = {};
    if (stepToValidate === 0) {
      if (!draft.firstName.trim()) nextErrors.firstName = "Le prénom est requis.";
      if (!draft.lastName.trim()) nextErrors.lastName = "Le nom est requis.";
      if (!draft.birthDate) nextErrors.birthDate = "La date de naissance est requise.";
      if (!draft.birthPlace.trim()) nextErrors.birthPlace = "Le lieu de naissance est requis.";
      if (!draft.nationality.trim()) nextErrors.nationality = "La nationalité est requise.";
    }
    if (stepToValidate === 1) {
      if (!draft.classId) nextErrors.classId = "Sélectionnez une classe.";
      if (!draft.enrolledAt) nextErrors.enrolledAt = "La date d’inscription est requise.";
      const selectedClass = state.classes.find((item) => item.id === draft.classId);
      if (selectedClass) {
        const occupied = state.students.filter(
          (student) => student.classId === selectedClass.id && student.status === "Actif",
        ).length;
        if (occupied >= selectedClass.capacity && draft.status === "Actif") {
          nextErrors.classId = "Cette classe a atteint sa capacité maximale.";
        }
      }
    }
    if (stepToValidate === 2) {
      if (!draft.guardianName.trim()) nextErrors.guardianName = "Le nom du responsable est requis.";
      if (!draft.guardianPhone.trim()) nextErrors.guardianPhone = "Le téléphone du responsable est requis.";
      if (draft.guardianEmail && !/^\S+@\S+\.\S+$/.test(draft.guardianEmail)) {
        nextErrors.guardianEmail = "Saisissez une adresse e-mail valide.";
      }
    }
    setErrors(nextErrors);
    return Object.keys(nextErrors).length === 0;
  };

  const nextStep = () => {
    if (validateStep(step)) {
      setStep((current) => Math.min(3, current + 1));
      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  };

  const submit = () => {
    const input: EnrollmentInput = {
      firstName: draft.firstName.trim(),
      lastName: draft.lastName.trim(),
      gender: draft.gender,
      birthDate: draft.birthDate,
      birthPlace: draft.birthPlace.trim(),
      nationality: draft.nationality.trim(),
      address: draft.address.trim(),
      classId: draft.classId,
      enrolledAt: draft.enrolledAt,
      status: draft.status,
      previousSchool: draft.previousSchool.trim(),
      guardian: {
        name: draft.guardianName.trim(),
        relationship: draft.guardianRelationship,
        phone: draft.guardianPhone.trim(),
        email: draft.guardianEmail.trim(),
      },
    };
    setEnrolledStudent(addStudent(input));
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  const restart = () => {
    setDraft(initialDraft());
    setErrors({});
    setStep(0);
    setEnrolledStudent(null);
  };

  if (enrolledStudent) {
    const schoolClass = state.classes.find((item) => item.id === enrolledStudent.classId);
    return (
      <div className="enrollment-success card">
        <div className="success-illustration"><CheckCircle2 size={42} /></div>
        <p className="eyebrow">Inscription enregistrée</p>
        <h1>Bienvenue, {enrolledStudent.firstName} !</h1>
        <p>Le dossier de <strong>{getFullName(enrolledStudent)}</strong> a été créé avec succès dans la classe de <strong>{schoolClass?.name}</strong>.</p>
        <div className="matricule-card"><small>Matricule attribué</small><strong>{enrolledStudent.matricule}</strong><BadgeLike /></div>
        <div className="success-summary">
          <div><small>Date d’inscription</small><strong>{formatDate(enrolledStudent.enrolledAt)}</strong></div>
          <div><small>Statut du dossier</small><strong>{enrolledStudent.status}</strong></div>
          <div><small>Responsable légal</small><strong>{enrolledStudent.guardian.name}</strong></div>
        </div>
        <Notice title="Prochaine étape">Les notes pourront être saisies depuis le module Évaluations dès que l’élève aura été évalué.</Notice>
        <div className="success-actions">
          <Link className="button button-primary" href={`/eleves/?eleve=${enrolledStudent.id}`}>Voir le dossier élève <ArrowRight size={17} /></Link>
          <button className="button button-secondary" type="button" onClick={restart}><RotateCcw size={17} /> Inscrire un autre élève</button>
        </div>
      </div>
    );
  }

  const selectedClass = state.classes.find((item) => item.id === draft.classId);

  return (
    <>
      <PageHeader
        eyebrow="Gestion des élèves"
        title="Nouvelle inscription"
        icon={<GraduationCap size={20} />}
        description="Créez un dossier complet en quelques étapes. Les champs marqués d’un astérisque sont obligatoires."
      />
      <div className="enrollment-layout">
        <section className="card enrollment-card">
          <ol className="form-steps">
            {STEPS.map((item, index) => {
              const Icon = item.icon;
              return (
                <li key={item.label} className={index === step ? "active" : index < step ? "complete" : ""}>
                  <button type="button" onClick={() => index < step && setStep(index)} disabled={index > step}>
                    <span>{index < step ? <Check size={17} /> : <Icon size={17} />}</span>
                    <small>Étape {index + 1}</small>
                    <strong>{item.label}</strong>
                  </button>
                </li>
              );
            })}
          </ol>

          <form onSubmit={(event) => {
            event.preventDefault();
            if (step === 3) submit();
            else nextStep();
          }}>
            <div className="form-step-content">
              {step === 0 && (
                <>
                  <div className="form-section-heading"><span><UserRound size={20} /></span><div><h2>Informations de l’élève</h2><p>Renseignez les informations telles qu’elles figurent sur les documents officiels.</p></div></div>
                  <div className="form-grid">
                    <FormField label="Prénom" htmlFor="firstName" required error={errors.firstName}>
                      <input id="firstName" value={draft.firstName} onChange={(event) => update("firstName", event.target.value)} placeholder="Ex. Aline" autoFocus />
                    </FormField>
                    <FormField label="Nom" htmlFor="lastName" required error={errors.lastName}>
                      <input id="lastName" value={draft.lastName} onChange={(event) => update("lastName", event.target.value)} placeholder="Ex. Niyonzima" />
                    </FormField>
                    <FormField label="Sexe" htmlFor="gender" required>
                      <select id="gender" value={draft.gender} onChange={(event) => update("gender", event.target.value as Gender)}><option value="Fille">Fille</option><option value="Garçon">Garçon</option></select>
                    </FormField>
                    <FormField label="Date de naissance" htmlFor="birthDate" required error={errors.birthDate}>
                      <input id="birthDate" type="date" value={draft.birthDate} max={toInputDate()} onChange={(event) => update("birthDate", event.target.value)} />
                    </FormField>
                    <FormField label="Lieu de naissance" htmlFor="birthPlace" required error={errors.birthPlace}>
                      <input id="birthPlace" value={draft.birthPlace} onChange={(event) => update("birthPlace", event.target.value)} placeholder="Ex. Bujumbura" />
                    </FormField>
                    <FormField label="Nationalité" htmlFor="nationality" required error={errors.nationality}>
                      <input id="nationality" value={draft.nationality} onChange={(event) => update("nationality", event.target.value)} />
                    </FormField>
                    <FormField label="Adresse du domicile" htmlFor="address" className="form-field-full" hint="Quartier, commune et province">
                      <input id="address" value={draft.address} onChange={(event) => update("address", event.target.value)} placeholder="Ex. Rohero, Bujumbura" />
                    </FormField>
                  </div>
                </>
              )}

              {step === 1 && (
                <>
                  <div className="form-section-heading"><span><GraduationCap size={20} /></span><div><h2>Affectation scolaire</h2><p>Choisissez la classe et le statut initial du dossier.</p></div></div>
                  <div className="form-grid">
                    <FormField label="Classe d’affectation" htmlFor="classId" required error={errors.classId} className="form-field-full">
                      <select id="classId" value={draft.classId} onChange={(event) => update("classId", event.target.value)} autoFocus>
                        <option value="">Sélectionner une classe…</option>
                        {state.classes.map((schoolClass) => {
                          const occupied = state.students.filter((student) => student.classId === schoolClass.id && student.status === "Actif").length;
                          return <option key={schoolClass.id} value={schoolClass.id}>{schoolClass.name} — {occupied}/{schoolClass.capacity} places</option>;
                        })}
                      </select>
                    </FormField>
                    {selectedClass && <div className="selected-class-summary form-field-full"><span><GraduationCap size={21} /></span><div><strong>{selectedClass.name}</strong><small>{selectedClass.room} · Professeur principal : {selectedClass.headTeacher}</small></div></div>}
                    <FormField label="Date d’inscription" htmlFor="enrolledAt" required error={errors.enrolledAt}>
                      <input id="enrolledAt" type="date" value={draft.enrolledAt} onChange={(event) => update("enrolledAt", event.target.value)} />
                    </FormField>
                    <FormField label="Statut du dossier" htmlFor="status" required hint="« En attente » permet une validation ultérieure.">
                      <select id="status" value={draft.status} onChange={(event) => update("status", event.target.value as StudentStatus)}><option value="Actif">Actif — inscription validée</option><option value="En attente">En attente de validation</option></select>
                    </FormField>
                    <FormField label="École d’origine" htmlFor="previousSchool" className="form-field-full">
                      <input id="previousSchool" value={draft.previousSchool} onChange={(event) => update("previousSchool", event.target.value)} placeholder="Nom de l’établissement précédent" />
                    </FormField>
                  </div>
                </>
              )}

              {step === 2 && (
                <>
                  <div className="form-section-heading"><span><UsersRound size={20} /></span><div><h2>Responsable légal</h2><p>Ajoutez le contact à privilégier pour le suivi de la scolarité.</p></div></div>
                  <div className="form-grid">
                    <FormField label="Nom et prénom" htmlFor="guardianName" required error={errors.guardianName} className="form-field-full">
                      <input id="guardianName" value={draft.guardianName} onChange={(event) => update("guardianName", event.target.value)} placeholder="Nom complet du parent ou tuteur" autoFocus />
                    </FormField>
                    <FormField label="Lien avec l’élève" htmlFor="guardianRelationship" required>
                      <select id="guardianRelationship" value={draft.guardianRelationship} onChange={(event) => update("guardianRelationship", event.target.value)}><option>Mère</option><option>Père</option><option>Tuteur</option><option>Tutrice</option><option>Autre</option></select>
                    </FormField>
                    <FormField label="Téléphone" htmlFor="guardianPhone" required error={errors.guardianPhone}>
                      <input id="guardianPhone" type="tel" value={draft.guardianPhone} onChange={(event) => update("guardianPhone", event.target.value)} placeholder="+257 …" />
                    </FormField>
                    <FormField label="Adresse e-mail" htmlFor="guardianEmail" error={errors.guardianEmail} className="form-field-full" hint="Facultatif — utilisée pour les communications scolaires.">
                      <input id="guardianEmail" type="email" value={draft.guardianEmail} onChange={(event) => update("guardianEmail", event.target.value)} placeholder="responsable@exemple.com" />
                    </FormField>
                  </div>
                  <Notice>Assurez-vous que le responsable a autorisé l’utilisation de ses coordonnées dans le cadre du suivi scolaire.</Notice>
                </>
              )}

              {step === 3 && (
                <>
                  <div className="form-section-heading"><span><ShieldCheck size={20} /></span><div><h2>Vérification du dossier</h2><p>Relisez les informations avant d’enregistrer l’inscription.</p></div></div>
                  <div className="review-student"><span className="review-avatar">{draft.firstName.charAt(0)}{draft.lastName.charAt(0)}</span><div><h3>{draft.firstName} {draft.lastName}</h3><p>{selectedClass?.name} · {state.settings.academicYear}</p></div><button type="button" className="text-link" onClick={() => setStep(0)}>Modifier l’identité</button></div>
                  <div className="review-grid">
                    <div><small>Naissance</small><strong>{formatDate(draft.birthDate)} · {draft.birthPlace}</strong></div>
                    <div><small>Nationalité</small><strong>{draft.nationality}</strong></div>
                    <div><small>Date d’inscription</small><strong>{formatDate(draft.enrolledAt)}</strong></div>
                    <div><small>Statut</small><strong>{draft.status}</strong></div>
                    <div><small>Responsable</small><strong>{draft.guardianName} ({draft.guardianRelationship})</strong></div>
                    <div><small>Contact</small><strong>{draft.guardianPhone}</strong></div>
                    <div className="review-full"><small>Adresse</small><strong>{draft.address || "Non renseignée"}</strong></div>
                    <div className="review-full"><small>École d’origine</small><strong>{draft.previousSchool || "Non renseignée"}</strong></div>
                  </div>
                  <Notice tone="success" title="Dossier prêt à être enregistré">Un matricule unique sera attribué automatiquement. L’inscription sera conservée sur cet appareil.</Notice>
                </>
              )}
            </div>
            <footer className="form-footer">
              {step > 0 ? <button className="button button-secondary" type="button" onClick={() => { setStep((current) => current - 1); setErrors({}); }}><ArrowLeft size={17} /> Précédent</button> : <Link className="button button-ghost" href="/eleves/">Annuler</Link>}
              <span>Étape {step + 1} sur {STEPS.length}</span>
              <button className="button button-primary" type="submit">{step === 3 ? <><Check size={17} /> Confirmer l’inscription</> : <>Continuer <ArrowRight size={17} /></>}</button>
            </footer>
          </form>
        </section>
        <aside className="enrollment-help">
          <div className="card"><span className="help-icon"><ShieldCheck size={22} /></span><h3>Un dossier, tout le parcours</h3><p>Les informations saisies alimentent automatiquement le répertoire, les listes de classe, les notes et les bulletins.</p><ul><li><Check size={15} /> Matricule automatique</li><li><Check size={15} /> Affectation à une classe</li><li><Check size={15} /> Suivi du responsable légal</li></ul></div>
          <div className="help-note"><strong>Besoin de corriger ?</strong><p>Vous pourrez revenir à chaque étape précédente avant la validation finale.</p></div>
        </aside>
      </div>
    </>
  );
}

function BadgeLike() {
  return <span className="matricule-check"><Check size={14} /> Attribué</span>;
}
