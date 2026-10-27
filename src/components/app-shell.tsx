"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useEffect, useMemo, useRef, useState, type ReactNode } from "react";
import {
  Bell,
  BookOpenCheck,
  CalendarCheck2,
  CheckCircle2,
  ChevronDown,
  ClipboardList,
  Database,
  FileText,
  GraduationCap,
  LayoutDashboard,
  Menu,
  Plus,
  Search,
  Settings,
  UserRoundPlus,
  UsersRound,
  X,
  type LucideIcon,
} from "lucide-react";
import { Logo } from "./logo";
import { Avatar, Badge } from "./ui";
import { useSchool } from "./school-provider";
import { getFullName } from "@/lib/academic";

interface NavigationItem {
  label: string;
  href: string;
  icon: LucideIcon;
}

const NAVIGATION: Array<{ label: string; items: NavigationItem[] }> = [
  {
    label: "Pilotage",
    items: [{ label: "Vue d’ensemble", href: "/", icon: LayoutDashboard }],
  },
  {
    label: "Scolarité",
    items: [
      { label: "Inscriptions", href: "/inscriptions/", icon: UserRoundPlus },
      { label: "Élèves", href: "/eleves/", icon: UsersRound },
      { label: "Classes & matières", href: "/classes/", icon: BookOpenCheck },
      { label: "Présences", href: "/presences/", icon: CalendarCheck2 },
    ],
  },
  {
    label: "Évaluations",
    items: [
      { label: "Saisie des notes", href: "/notes/", icon: ClipboardList },
      { label: "Bulletins", href: "/bulletins/", icon: FileText },
      { label: "Proclamation", href: "/proclamation/", icon: GraduationCap },
    ],
  },
];

const PAGE_NAMES: Record<string, string> = {
  "/": "Vue d’ensemble",
  "/inscriptions": "Inscriptions",
  "/eleves": "Élèves",
  "/classes": "Classes & matières",
  "/presences": "Présences",
  "/notes": "Saisie des notes",
  "/bulletins": "Bulletins",
  "/proclamation": "Proclamation",
  "/parametres": "Paramètres",
};

function GlobalSearch() {
  const { state } = useSchool();
  const [query, setQuery] = useState("");
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    const handleShortcut = (event: KeyboardEvent) => {
      if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === "k") {
        event.preventDefault();
        inputRef.current?.focus();
      }
    };
    document.addEventListener("keydown", handleShortcut);
    return () => document.removeEventListener("keydown", handleShortcut);
  }, []);

  const results = useMemo(() => {
    const normalized = query.trim().toLocaleLowerCase("fr");
    if (normalized.length < 2) return [];
    return state.students
      .filter((student) =>
        `${getFullName(student)} ${student.matricule}`
          .toLocaleLowerCase("fr")
          .includes(normalized),
      )
      .slice(0, 6);
  }, [query, state.students]);

  return (
    <div
      className="global-search"
      onBlur={(event) => {
        if (!event.currentTarget.contains(event.relatedTarget)) setQuery("");
      }}
    >
      <Search size={18} aria-hidden="true" />
      <input
        ref={inputRef}
        type="search"
        value={query}
        onChange={(event) => setQuery(event.target.value)}
        placeholder="Rechercher un élève…"
        aria-label="Rechercher un élève"
      />
      <kbd>⌘ K</kbd>
      {query.trim().length >= 2 && (
        <div className="search-results">
          <p className="search-results-label">Dossiers élèves</p>
          {results.length > 0 ? (
            results.map((student) => {
              const schoolClass = state.classes.find((item) => item.id === student.classId);
              return (
                <Link
                  href={`/eleves/?eleve=${student.id}`}
                  key={student.id}
                  onClick={() => setQuery("")}
                >
                  <Avatar student={student} size="small" />
                  <span>
                    <strong>{getFullName(student)}</strong>
                    <small>{student.matricule} · {schoolClass?.name ?? "Sans classe"}</small>
                  </span>
                </Link>
              );
            })
          ) : (
            <p className="search-no-result">Aucun élève ne correspond à cette recherche.</p>
          )}
        </div>
      )}
    </div>
  );
}

export function AppShell({ children }: { children: ReactNode }) {
  const pathname = usePathname();
  const { state, hydrated } = useSchool();
  const [mobileOpen, setMobileOpen] = useState(false);
  const normalizedPath = pathname.length > 1 ? pathname.replace(/\/$/, "") : pathname;
  const pendingStudents = state.students.filter((student) => student.status === "En attente");

  const sidebar = (
    <>
      <div className="sidebar-header">
        <Link href="/" onClick={() => setMobileOpen(false)}><Logo /></Link>
        <button
          className="icon-button sidebar-close"
          type="button"
          onClick={() => setMobileOpen(false)}
          aria-label="Fermer la navigation"
        >
          <X size={20} />
        </button>
      </div>
      <Link className="sidebar-cta" href="/inscriptions/" onClick={() => setMobileOpen(false)}>
        <Plus size={18} />
        Nouvelle inscription
      </Link>
      <nav className="sidebar-nav" aria-label="Navigation principale">
        {NAVIGATION.map((section) => (
          <div className="nav-section" key={section.label}>
            <p>{section.label}</p>
            {section.items.map((item) => {
              const Icon = item.icon;
              const itemPath = item.href.replace(/\/$/, "") || "/";
              const active = normalizedPath === itemPath;
              return (
                <Link
                  href={item.href}
                  key={item.href}
                  className={active ? "active" : ""}
                  aria-current={active ? "page" : undefined}
                  onClick={() => setMobileOpen(false)}
                >
                  <Icon size={19} strokeWidth={1.8} />
                  <span>{item.label}</span>
                  {item.href === "/inscriptions/" && pendingStudents.length > 0 && (
                    <span className="nav-count">{pendingStudents.length}</span>
                  )}
                </Link>
              );
            })}
          </div>
        ))}
      </nav>
      <div className="sidebar-bottom">
        <Link
          href="/parametres/"
          className={normalizedPath === "/parametres" ? "active" : ""}
          onClick={() => setMobileOpen(false)}
        >
          <Settings size={19} />
          <span>Paramètres</span>
        </Link>
        <div className="school-year-card">
          <span className="school-year-icon"><GraduationCap size={19} /></span>
          <div>
            <small>Année scolaire</small>
            <strong>{state.settings.academicYear}</strong>
          </div>
          <span className="live-dot" title="Année en cours" />
        </div>
      </div>
    </>
  );

  return (
    <div className="app-layout">
      <aside className="sidebar">{sidebar}</aside>
      {mobileOpen && (
        <div className="mobile-navigation">
          <button
            className="mobile-overlay"
            type="button"
            onClick={() => setMobileOpen(false)}
            aria-label="Fermer la navigation"
          />
          <aside className="mobile-sidebar">{sidebar}</aside>
        </div>
      )}
      <div className="app-main">
        <header className="topbar">
          <div className="topbar-left">
            <button
              className="icon-button mobile-menu"
              type="button"
              onClick={() => setMobileOpen(true)}
              aria-label="Ouvrir la navigation"
            >
              <Menu size={22} />
            </button>
            <span className="topbar-page-name">{PAGE_NAMES[normalizedPath] ?? "LaVision"}</span>
            <GlobalSearch />
          </div>
          <div className="topbar-actions">
            <Badge tone="neutral" dot>
              <Database size={13} /> {hydrated ? "Données locales" : "Chargement"}
            </Badge>
            <details className="popover notification-popover">
              <summary className="icon-button" aria-label="Notifications">
                <Bell size={19} />
                {pendingStudents.length > 0 && <span className="notification-dot" />}
              </summary>
              <div className="popover-content">
                <div className="popover-title"><strong>Notifications</strong><Badge tone="green">{pendingStudents.length}</Badge></div>
                {pendingStudents.length > 0 ? (
                  <Link href="/eleves/" className="notification-item">
                    <span className="notification-icon"><UserRoundPlus size={18} /></span>
                    <span><strong>{pendingStudents.length} inscription{pendingStudents.length > 1 ? "s" : ""} à valider</strong><small>Consultez les dossiers en attente.</small></span>
                  </Link>
                ) : (
                  <div className="notification-item"><span className="notification-icon"><CheckCircle2 size={18} /></span><span><strong>Tout est à jour</strong><small>Aucune inscription en attente.</small></span></div>
                )}
              </div>
            </details>
            <details className="popover profile-popover">
              <summary className="profile-summary">
                <span className="profile-avatar">AN</span>
                <span className="profile-copy"><strong>{state.settings.directionName}</strong><small>Direction</small></span>
                <ChevronDown size={15} />
              </summary>
              <div className="popover-content">
                <div className="profile-popover-head"><strong>{state.settings.schoolName}</strong><small>{state.settings.email}</small></div>
                <Link href="/parametres/"><Settings size={17} /> Paramètres de l’établissement</Link>
              </div>
            </details>
          </div>
        </header>
        <main className="page-content">{children}</main>
        <footer className="app-footer">
          <span>LaVision · De l’inscription à la proclamation</span>
          <span>Données de démonstration enregistrées sur cet appareil</span>
        </footer>
      </div>
    </div>
  );
}
