"use client";

import {
  Children,
  useEffect,
  useId,
  useRef,
  useState,
  useSyncExternalStore,
  type ReactNode,
} from "react";
import { createPortal } from "react-dom";
import {
  AlertCircle,
  CheckCircle2,
  ChevronLeft,
  ChevronRight,
  Info,
  MoreHorizontal,
  X,
} from "lucide-react";
import type { Student } from "@/lib/types";
import { getInitials } from "@/lib/academic";

export function PageHeader({
  eyebrow,
  title,
  description,
  actions,
  icon,
  size = "default",
}: {
  eyebrow?: string;
  title: string;
  description?: string;
  actions?: ReactNode;
  icon?: ReactNode;
  size?: "default" | "large";
}) {
  return (
    <header className={`page-header page-header-${size}`}>
      <div className="page-header-copy">
        {eyebrow && <p className="eyebrow">{eyebrow}</p>}
        <div className="page-header-title-row">
          {icon && <span className="page-header-icon" aria-hidden="true">{icon}</span>}
          <h1>{title}</h1>
        </div>
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

const emptySubscribe = () => () => {};
const useIsClient = () => useSyncExternalStore(emptySubscribe, () => true, () => false);
const MENU_WIDTH = 208;

export function TableMenu({ label, children }: { label: string; children: ReactNode }) {
  const mounted = useIsClient();
  const [open, setOpen] = useState(false);
  const [position, setPosition] = useState({ top: -9999, left: -9999 });
  const triggerRef = useRef<HTMLButtonElement>(null);
  const menuRef = useRef<HTMLDivElement>(null);

  const toggleMenu = () => {
    if (open) {
      setOpen(false);
      return;
    }
    const trigger = triggerRef.current;
    if (!trigger) return;
    const rect = trigger.getBoundingClientRect();
    const estimatedHeight = Children.count(children) * 40 + 14;
    let top = rect.bottom + 6;
    if (top + estimatedHeight > window.innerHeight - 8) {
      top = Math.max(8, rect.top - estimatedHeight - 6);
    }
    const desiredLeft = rect.right - MENU_WIDTH;
    const left = Math.min(
      Math.max(8, desiredLeft),
      Math.max(8, window.innerWidth - MENU_WIDTH - 8),
    );
    setPosition({ top, left });
    setOpen(true);
  };

  useEffect(() => {
    if (!open) return;
    const onPointerDown = (event: PointerEvent) => {
      const target = event.target as Node;
      if (triggerRef.current?.contains(target) || menuRef.current?.contains(target)) return;
      setOpen(false);
    };
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        setOpen(false);
        triggerRef.current?.focus();
      }
    };
    const close = () => setOpen(false);
    document.addEventListener("pointerdown", onPointerDown);
    document.addEventListener("keydown", onKeyDown);
    window.addEventListener("scroll", close, true);
    window.addEventListener("resize", close);
    return () => {
      document.removeEventListener("pointerdown", onPointerDown);
      document.removeEventListener("keydown", onKeyDown);
      window.removeEventListener("scroll", close, true);
      window.removeEventListener("resize", close);
    };
  }, [open]);

  return (
    <div className="table-menu">
      <button
        ref={triggerRef}
        className="table-menu-trigger"
        type="button"
        aria-label={label}
        aria-haspopup="menu"
        aria-expanded={open}
        onClick={toggleMenu}
      >
        <MoreHorizontal size={18} />
      </button>
      {mounted && open && createPortal(
        <div
          ref={menuRef}
          className="table-menu-content"
          role="menu"
          aria-label={label}
          style={{ top: position.top, left: position.left, width: MENU_WIDTH }}
          onClick={(event) => {
            if ((event.target as HTMLElement).closest("a, button")) setOpen(false);
          }}
        >
          {children}
        </div>,
        document.body,
      )}
    </div>
  );
}

function paginationItems(currentPage: number, pageCount: number): Array<number | string> {
  if (pageCount <= 5) return Array.from({ length: pageCount }, (_, index) => index + 1);
  const pages: Array<number | string> = [1];
  let start = Math.max(2, currentPage - 1);
  let end = Math.min(pageCount - 1, currentPage + 1);
  if (currentPage <= 3) end = 4;
  if (currentPage >= pageCount - 2) start = pageCount - 3;
  if (start > 2) pages.push("start-ellipsis");
  for (let value = start; value <= end; value += 1) pages.push(value);
  if (end < pageCount - 1) pages.push("end-ellipsis");
  pages.push(pageCount);
  return pages;
}

export function TablePagination({
  page,
  pageCount,
  pageSize,
  totalItems,
  onPageChange,
  selectedCount = 0,
}: {
  page: number;
  pageCount: number;
  pageSize: number;
  totalItems: number;
  onPageChange: (page: number) => void;
  selectedCount?: number;
}) {
  const safePageCount = Math.max(1, pageCount);
  const currentPage = Math.min(Math.max(1, page), safePageCount);
  const start = totalItems ? (currentPage - 1) * pageSize + 1 : 0;
  const end = Math.min(totalItems, currentPage * pageSize);

  return (
    <div className="table-pagination" aria-label="Pagination du tableau">
      <div className="table-pagination-controls">
        <button
          type="button"
          className="table-pagination-arrow"
          onClick={() => onPageChange(currentPage - 1)}
          disabled={currentPage <= 1}
          aria-label="Page précédente"
        >
          <ChevronLeft size={15} />
        </button>
        {paginationItems(currentPage, safePageCount).map((item) => (
          typeof item === "number" ? (
            <button
              key={item}
              type="button"
              className={item === currentPage ? "table-pagination-current" : "table-pagination-page"}
              onClick={() => onPageChange(item)}
              aria-label={`Page ${item}`}
              aria-current={item === currentPage ? "page" : undefined}
            >
              {item}
            </button>
          ) : <span className="table-pagination-ellipsis" key={item}>…</span>
        ))}
        <button
          type="button"
          className="table-pagination-next"
          onClick={() => onPageChange(currentPage + 1)}
          disabled={currentPage >= safePageCount}
          aria-label="Page suivante"
        >
          <ChevronRight size={15} />
        </button>
      </div>
      <span className="table-pagination-range">{start}–{end} sur {totalItems} dossier{totalItems > 1 ? "s" : ""}</span>
      {selectedCount > 0 && <span className="table-pagination-selected">{selectedCount} sélectionné{selectedCount > 1 ? "s" : ""}</span>}
    </div>
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
  closeOnBackdrop = true,
}: {
  open: boolean;
  onClose: () => void;
  title: string;
  description?: string;
  children: ReactNode;
  footer?: ReactNode;
  size?: "small" | "medium" | "large";
  closeOnBackdrop?: boolean;
}) {
  const titleId = useId();
  const descriptionId = useId();
  const dialogRef = useRef<HTMLElement>(null);
  const onCloseRef = useRef(onClose);

  useEffect(() => {
    onCloseRef.current = onClose;
  }, [onClose]);

  useEffect(() => {
    if (!open) return;
    const previousOverflow = document.body.style.overflow;
    const previousFocus = document.activeElement instanceof HTMLElement ? document.activeElement : null;
    const focusableSelector = "a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex='-1'])";
    const getFocusableElements = () => Array.from(
      dialogRef.current?.querySelectorAll<HTMLElement>(focusableSelector) ?? [],
    ).filter((element) => element.offsetParent !== null && element.getAttribute("aria-hidden") !== "true");
    document.body.style.overflow = "hidden";
    const focusFrame = window.requestAnimationFrame(() => {
      const first = getFocusableElements()[0];
      if (first) first.focus();
      else dialogRef.current?.focus();
    });
    const handleKey = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        event.stopPropagation();
        onCloseRef.current();
        return;
      }
      if (event.key !== "Tab") return;
      const dialog = dialogRef.current;
      if (!dialog) return;
      const focusable = getFocusableElements();
      if (!focusable.length) {
        event.preventDefault();
        dialog.focus();
        return;
      }
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      const active = document.activeElement;
      if (!dialog.contains(active)) {
        event.preventDefault();
        (event.shiftKey ? last : first).focus();
      } else if (event.shiftKey && active === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && active === last) {
        event.preventDefault();
        first.focus();
      }
    };
    window.addEventListener("keydown", handleKey);
    return () => {
      window.cancelAnimationFrame(focusFrame);
      document.body.style.overflow = previousOverflow;
      window.removeEventListener("keydown", handleKey);
      if (previousFocus?.isConnected) previousFocus.focus();
    };
  }, [open]);

  if (!open) return null;

  return (
    <div className="modal-backdrop" onMouseDown={closeOnBackdrop ? onClose : undefined}>
      <section
        ref={dialogRef}
        className={`modal modal-${size}`}
        role="dialog"
        tabIndex={-1}
        aria-modal="true"
        aria-labelledby={titleId}
        aria-describedby={description ? descriptionId : undefined}
        onMouseDown={(event) => event.stopPropagation()}
      >
        <header className="modal-header">
          <div>
            <h2 id={titleId}>{title}</h2>
            {description && <p id={descriptionId}>{description}</p>}
          </div>
          <button className="icon-button" type="button" onClick={onClose} aria-label="Fermer la fenêtre">
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
