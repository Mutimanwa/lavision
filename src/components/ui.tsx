"use client";

import { useEffect, useId, type ReactNode } from "react";
import { AlertCircle, CheckCircle2, Info, X } from "lucide-react";
import type { Student } from "@/lib/types";
import { getInitials } from "@/lib/academic";

export function PageHeader({
  eyebrow,
  title,
  description,
  actions,
}: {
  eyebrow?: string;
  title: string;
  description?: string;
  actions?: ReactNode;
}) {
  return (
    <header className="page-header">
      <div>
        {eyebrow && <p className="eyebrow">{eyebrow}</p>}
        <h1>{title}</h1>
        {description && <p className="page-description">{description}</p>}
      </div>
      {actions && <div className="page-actions">{actions}</div>}
    </header>
  );
}

export type BadgeTone = "green" | "amber" | "red" | "blue" | "neutral" | "purple";

export function Badge({
  children,
  tone = "neutral",
  dot = false,
}: {
  children: ReactNode;
  tone?: BadgeTone;
  dot?: boolean;
}) {
  return (
    <span className={`badge badge-${tone}`}>
      {dot && <span className="badge-dot" aria-hidden="true" />}
      {children}
    </span>
  );
}

const AVATAR_COLORS = [
  ["#e4f2ec", "#236c55"],
  ["#fff0dc", "#9a5a17"],
  ["#eae8fa", "#6654a3"],
  ["#e5eefb", "#386aa5"],
  ["#f8e7e9", "#a44d5c"],
];

export function Avatar({
  student,
  size = "medium",
}: {
  student: Pick<Student, "firstName" | "lastName" | "id">;
  size?: "small" | "medium" | "large";
}) {
  const colorIndex =
    (student.firstName.charCodeAt(0) + student.lastName.charCodeAt(0)) %
    AVATAR_COLORS.length;
  const [background, color] = AVATAR_COLORS[colorIndex];
  return (
    <span
      className={`avatar avatar-${size}`}
      style={{ background, color }}
      aria-hidden="true"
    >
      {getInitials(student)}
    </span>
  );
}

export function Modal({
  open,
  onClose,
  title,
  description,
  children,
  footer,
  size = "medium",
}: {
  open: boolean;
  onClose: () => void;
  title: string;
  description?: string;
  children: ReactNode;
  footer?: ReactNode;
  size?: "small" | "medium" | "large";
}) {
  const titleId = useId();

  useEffect(() => {
    if (!open) return;
    const previousOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    const handleKey = (event: KeyboardEvent) => {
      if (event.key === "Escape") onClose();
    };
    window.addEventListener("keydown", handleKey);
    return () => {
      document.body.style.overflow = previousOverflow;
      window.removeEventListener("keydown", handleKey);
    };
  }, [onClose, open]);

  if (!open) return null;

  return (
    <div className="modal-backdrop" onMouseDown={onClose}>
      <section
        className={`modal modal-${size}`}
        role="dialog"
        aria-modal="true"
        aria-labelledby={titleId}
        onMouseDown={(event) => event.stopPropagation()}
      >
        <header className="modal-header">
          <div>
            <h2 id={titleId}>{title}</h2>
            {description && <p>{description}</p>}
          </div>
          <button className="icon-button" type="button" onClick={onClose} aria-label="Fermer">
            <X size={20} />
          </button>
        </header>
        <div className="modal-body">{children}</div>
        {footer && <footer className="modal-footer">{footer}</footer>}
      </section>
    </div>
  );
}

export function Notice({
  children,
  tone = "info",
  title,
}: {
  children: ReactNode;
  tone?: "info" | "success" | "warning";
  title?: string;
}) {
  const Icon = tone === "success" ? CheckCircle2 : tone === "warning" ? AlertCircle : Info;
  return (
    <div className={`notice notice-${tone}`} role={tone === "warning" ? "alert" : "status"}>
      <Icon size={19} />
      <div>
        {title && <strong>{title}</strong>}
        <div>{children}</div>
      </div>
    </div>
  );
}

export function EmptyState({
  icon,
  title,
  description,
  action,
}: {
  icon: ReactNode;
  title: string;
  description: string;
  action?: ReactNode;
}) {
  return (
    <div className="empty-state">
      <span className="empty-state-icon">{icon}</span>
      <h3>{title}</h3>
      <p>{description}</p>
      {action}
    </div>
  );
}

export function FormField({
  label,
  htmlFor,
  required = false,
  hint,
  error,
  children,
  className = "",
}: {
  label: string;
  htmlFor: string;
  required?: boolean;
  hint?: string;
  error?: string;
  children: ReactNode;
  className?: string;
}) {
  return (
    <div className={`form-field ${className}`}>
      <label htmlFor={htmlFor}>
        {label} {required && <span aria-hidden="true">*</span>}
      </label>
      {children}
      {error ? <small className="field-error">{error}</small> : hint ? <small>{hint}</small> : null}
    </div>
  );
}
