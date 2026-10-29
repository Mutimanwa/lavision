import type { ReactNode } from "react";

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
      {error ? <small className="field-error" role="alert">{error}</small> : hint ? <small>{hint}</small> : null}
    </div>
  );
}
