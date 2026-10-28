"use client";

import { useEffect, type ReactNode } from "react";
import { X } from "lucide-react";

export function HeaderDropdown({
  open,
  onClose,
  title,
  children,
  width = "default",
}: {
  open: boolean;
  onClose: () => void;
  title: string;
  children: ReactNode;
  width?: "default" | "wide";
}) {
  useEffect(() => {
    if (!open) return;
    const handleEscape = (event: KeyboardEvent) => {
      if (event.key === "Escape") onClose();
    };
    document.addEventListener("keydown", handleEscape);
    return () => document.removeEventListener("keydown", handleEscape);
  }, [onClose, open]);

  useEffect(() => {
    if (!open || !window.matchMedia("(max-width: 639px)").matches) return;
    const previous = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => { document.body.style.overflow = previous; };
  }, [open]);

  if (!open) return null;

  return (
    <>
      <div className={`header-dropdown header-dropdown-${width}`} role="dialog" aria-label={title}>
        {children}
      </div>
      <div className="header-sheet">
        <button className="header-sheet-backdrop" type="button" onClick={onClose} aria-label={`Fermer ${title}`} />
        <section className="header-sheet-panel" role="dialog" aria-modal="true" aria-label={title}>
          <div className="header-sheet-handle" aria-hidden="true"><span /></div>
          <header className="header-sheet-header">
            <h2>{title}</h2>
            <button type="button" onClick={onClose} aria-label="Fermer"><X size={17} /></button>
          </header>
          <div className="header-sheet-content">{children}</div>
        </section>
      </div>
    </>
  );
}
