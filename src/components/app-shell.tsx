"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useEffect, useMemo, useRef, useState } from "react";
import {
  Award,
  Bell,
  BookOpenCheck,
  CalendarCheck2,
  CalendarDays,
  ChevronDown,
  CircleHelp,
  ClipboardCheck,
  Database,
  FileBarChart,
  GraduationCap,
  LayoutDashboard,
  Menu,
  NotebookPen,
  Search,
  Settings,
  ShieldCheck,
  SlidersHorizontal,
  Sparkles,
  UserRoundPlus,
  UsersRound,
  X,
  type LucideIcon,
} from "lucide-react";
import { useSchool } from "./school-provider";
import { Logo } from "./logo";
import { Avatar, Modal } from "./ui";
import { getFullName } from "@/lib/academic";

interface NavItem {
  label: string;
  href: string;
  icon: LucideIcon;
  count?: number;
}

const PAGE_TITLES: Record<string, string> = {
  "/": "Tableau de bord",
  "/eleves": "Répertoire des élèves",
  "/inscriptions": "Inscriptions",
  "/classes": "Classes & matières",
  "/notes": "Notes",
  "/bulletins": "Bulletins",
  "/presences": "Présences",
  "/proclamation": "Proclamation",
  "/parametres": "Paramètres",
};

function NavigationSections({ onNavigate }: { onNavigate?: () => void }) {
  const pathname = usePathname();
  const { state } = useSchool();
  const pending = state.students.filter((student) => student.status === "En attente").length;
  const [openSections, setOpenSections] = useState<Record<string, boolean>>({
    Scolarité: true,
    Évaluations: true,
  });

  const sections: { label: string; collapsible?: boolean; items: NavItem[] }[] = [
    {
      label: "Général",
      items: [{ label: "Tableau de bord", href: "/", icon: LayoutDashboard }],
    },
    {
      label: "Scolarité",
      collapsible: true,
      items: [
        { label: "Élèves", href: "/eleves/", icon: UsersRound },
        { label: "Inscriptions", href: "/inscriptions/", icon: GraduationCap, count: pending },
        { label: "Classes & matières", href: "/classes/", icon: BookOpenCheck },
        { label: "Présences", href: "/presences/", icon: CalendarCheck2 },
      ],
    },
    {
      label: "Évaluations",
      collapsible: true,
      items: [
        { label: "Notes", href: "/notes/", icon: NotebookPen },
        { label: "Bulletins", href: "/bulletins/", icon: FileBarChart },
        { label: "Proclamation", href: "/proclamation/", icon: Award },
      ],
    },
  ];

  return (
    <nav className="sidebar-nav" aria-label="Navigation principale">
      {sections.map((section) => {
        const isOpen = !section.collapsible || openSections[section.label];
        return (
          <section className="nav-section" key={section.label}>
            {section.collapsible ? (
              <button
                className="nav-section-toggle"
                type="button"
                aria-expanded={isOpen}
                onClick={() => setOpenSections((current) => ({
                  ...current,
                  [section.label]: !current[section.label],
                }))}
              >
                <span>{section.label}</span>
                <ChevronDown size={14} />
              </button>
            ) : (
              <p>{section.label}</p>
            )}
            {isOpen && section.items.map((item) => {
              const Icon = item.icon;
              const active = pathname === item.href || (item.href !== "/" && pathname === item.href.slice(0, -1));
              return (
                <Link
                  href={item.href}
                  key={item.href}
                  className={active ? "active" : ""}
                  aria-current={active ? "page" : undefined}
                  onClick={onNavigate}
                >
                  <Icon size={18} strokeWidth={1.8} />
                  <span>{item.label}</span>
                  {item.count ? <span className="nav-count">{item.count}</span> : null}
                </Link>
              );
            })}
          </section>
        );
      })}
    </nav>
  );
}

function GlobalSearch() {
  const { state } = useSchool();
  const [query, setQuery] = useState("");
  const [focused, setFocused] = useState(false);
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    const handleShortcut = (event: KeyboardEvent) => {
      if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === "k") {
        event.preventDefault();
        inputRef.current?.focus();
      }
      if (event.key === "Escape") {
        setQuery("");
        inputRef.current?.blur();
      }
    };
    window.addEventListener("keydown", handleShortcut);
    return () => window.removeEventListener("keydown", handleShortcut);
  }, []);

  const results = useMemo(() => {
    const normalized = query.trim().toLocaleLowerCase("fr");
    if (!normalized) return [];
    return state.students.filter((student) =>
      `${getFullName(student)} ${student.matricule}`.toLocaleLowerCase("fr").includes(normalized),
    ).slice(0, 5);
  }, [query, state.students]);

  return (
    <div className="global-search">
      <Search size={18} />
      <input
        ref={inputRef}
        type="search"
        value={query}
        onChange={(event) => setQuery(event.target.value)}
        onFocus={() => setFocused(true)}
        onBlur={() => window.setTimeout(() => setFocused(false), 150)}
        placeholder="Rechercher un élève, un matricule…"
        aria-label="Recherche globale"
      />
      {query ? (
        <button type="button" className="search-clear" onClick={() => setQuery("")} aria-label="Effacer la recherche">
          <X size={15} />
        </button>
      ) : (
        <kbd>Ctrl K</kbd>
      )}
      {focused && query.trim() && (
        <div className="search-results">
          <div className="search-results-label">Résultats élèves</div>
          {results.length ? results.map((student) => (
            <Link href={`/eleves/?eleve=${student.id}`} key={student.id} onClick={() => setQuery("")}>
              <Avatar student={student} size="small" />
              <span>
                <strong>{getFullName(student)}</strong>
                <small>{student.matricule}</small>
              </span>
            </Link>
          )) : (
            <p className="search-no-result">Aucun élève trouvé pour « {query} »</p>
          )}
        </div>
      )}
    </div>
  );
}

function Notifications() {
  const { state } = useSchool();
  const pending = state.students.filter((student) => student.status === "En attente").length;
  const activeStudentIds = new Set(state.students.filter((student) => student.status === "Actif").map((student) => student.id));
  const expected = activeStudentIds.size * state.subjects.length;
  const currentGrades = state.grades.filter((grade) => grade.term === state.settings.currentTerm && activeStudentIds.has(grade.studentId)).length;
  const missing = Math.max(0, expected - currentGrades);

  return (
    <details className="popover notification-popover">
      <summary className="icon-button" aria-label="Ouvrir les notifications">
        <Bell size={19} />
        {(pending > 0 || missing > 0) && <span className="notification-dot" />}
      </summary>
      <div className="popover-content">
        <div className="popover-title">
          <strong>À traiter</strong>
          <span className="badge badge-neutral">{state.settings.currentTerm}</span>
        </div>
        <Link href="/eleves/" className="notification-item">
          <span className="notification-icon notification-icon-amber"><UserRoundPlus size={18} /></span>
          <span>
            <strong>{pending || "Aucune"} inscription{pending > 1 ? "s" : ""} en attente</strong>
            <small>Vérifier les dossiers provisoires</small>
          </span>
        </Link>
        <Link href="/notes/" className="notification-item">
          <span className="notification-icon notification-icon-blue"><NotebookPen size={18} /></span>
          <span>
            <strong>{missing || "Aucune"} note{missing > 1 ? "s" : ""} à compléter</strong>
            <small>Grille de saisie de la période active</small>
          </span>
        </Link>
        <Link href="/parametres/" className="notification-item">
          <span className="notification-icon notification-icon-green"><ShieldCheck size={18} /></span>
          <span>
            <strong>Données enregistrées localement</strong>
            <small>Exportez une sauvegarde depuis les paramètres</small>
          </span>
        </Link>
      </div>
    </details>
  );
}

export function AppShell({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  const { state, hydrated } = useSchool();
  const [mobileOpen, setMobileOpen] = useState(false);
  const [helpOpen, setHelpOpen] = useState(false);
  const basePath = pathname === "/" ? "/" : `/${pathname.split("/").filter(Boolean)[0] ?? ""}`;
  const pageTitle = PAGE_TITLES[basePath] ?? "LaVision";
  const directionInitials = state.settings.directionName
    .split(/\s+/)
    .filter(Boolean)
    .map((part) => part[0])
    .slice(0, 2)
    .join("")
    .toUpperCase() || "LV";

  useEffect(() => {
    document.body.style.overflow = mobileOpen ? "hidden" : "";
    return () => { document.body.style.overflow = ""; };
  }, [mobileOpen]);

  return (
    <div className="app-layout">
      <aside className="sidebar">
        <div className="sidebar-header"><Link href="/" aria-label="Accueil LaVision"><Logo /></Link></div>
        <NavigationSections />
        <div className="sidebar-bottom">
          <Link href="/parametres/" className={pathname === "/parametres/" ? "active" : ""}>
            <Settings size={18} /><span>Paramètres</span>
          </Link>
          <div className="school-year-card">
            <span className="school-year-icon"><CalendarDays size={18} /></span>
            <div><small>Année scolaire</small><strong>{state.settings.academicYear}</strong></div>
            <span className="live-dot" />
          </div>
        </div>
      </aside>

      {mobileOpen && (
        <div className="mobile-navigation">
          <button className="mobile-overlay" type="button" onClick={() => setMobileOpen(false)} aria-label="Fermer le menu" />
          <aside className="mobile-sidebar">
            <div className="sidebar-header">
              <Link href="/" onClick={() => setMobileOpen(false)} aria-label="Accueil LaVision"><Logo /></Link>
              <button className="sidebar-close" type="button" onClick={() => setMobileOpen(false)} aria-label="Fermer le menu">
                <X size={20} />
              </button>
            </div>
            <NavigationSections onNavigate={() => setMobileOpen(false)} />
            <div className="sidebar-bottom">
              <Link href="/parametres/" onClick={() => setMobileOpen(false)}><Settings size={18} /><span>Paramètres</span></Link>
              <div className="school-year-card">
                <span className="school-year-icon"><CalendarDays size={18} /></span>
                <div><small>Année scolaire</small><strong>{state.settings.academicYear}</strong></div>
              </div>
            </div>
          </aside>
        </div>
      )}

      <div className="app-main">
        <header className="topbar">
          <div className="topbar-left">
            <button className="icon-button mobile-menu" type="button" onClick={() => setMobileOpen(true)} aria-label="Ouvrir le menu">
              <Menu size={21} />
            </button>
            <GlobalSearch />
          </div>
          <div className="topbar-actions">
            <span className="environment-badge"><Database size={13} /> {hydrated ? "Données locales" : "Chargement"}</span>
            <Link className="icon-button topbar-action" href="/presences/" aria-label="Ouvrir les présences">
              <CalendarCheck2 size={18} />
            </Link>
            <Notifications />
            <details className="popover profile-popover">
              <summary className="profile-summary">
                <span className="profile-avatar">{directionInitials}</span>
                <span className="profile-copy">
                  <strong>{state.settings.directionName}</strong>
                  <small>Admin / Direction</small>
                </span>
                <ChevronDown size={14} />
              </summary>
              <div className="popover-content">
                <div className="profile-popover-school">
                  <span className="profile-popover-logo"><Logo compact /></span>
                  <span><strong>{state.settings.schoolName}</strong><small>{state.settings.academicYear}</small></span>
                </div>
                <Link href="/parametres/"><SlidersHorizontal size={17} /> Configuration de l’école</Link>
                <Link href="/classes/"><BookOpenCheck size={17} /> Classes & matières</Link>
                <Link href="/proclamation/"><Award size={17} /> Proclamations</Link>
                <button type="button" onClick={() => setHelpOpen(true)}><CircleHelp size={17} /> Centre d’aide</button>
                <div className="profile-popover-note"><Database size={14} /> Prototype de démonstration</div>
              </div>
            </details>
          </div>
        </header>
        <main className="page-content" id="main-content">{children}</main>
        <footer className="app-footer">
          <span>{pageTitle} · {state.settings.academicYear}</span>
          <span><span className="live-dot" /> Données enregistrées sur cet appareil</span>
        </footer>
      </div>

      <Modal open={helpOpen} onClose={() => setHelpOpen(false)} title="Centre d’aide LaVision" description={`${state.settings.schoolName} · ${state.settings.academicYear}`} size="medium">
        <div className="help-intro"><span><Sparkles size={24} /></span><div><h3>Tout pour suivre votre année scolaire</h3><p>Ce prototype couvre le parcours complet, de l’inscription à la proclamation.</p></div></div>
        <div className="help-grid">
          <Link href="/inscriptions/" onClick={() => setHelpOpen(false)}><GraduationCap size={20} /><strong>1. Inscrire</strong><span>Créer un dossier élève et l’affecter à une classe.</span></Link>
          <Link href="/classes/" onClick={() => setHelpOpen(false)}><BookOpenCheck size={20} /><strong>2. Structurer</strong><span>Configurer les salles, matières et coefficients.</span></Link>
          <Link href="/notes/" onClick={() => setHelpOpen(false)}><NotebookPen size={20} /><strong>3. Évaluer</strong><span>Saisir les notes sur 20 et contrôler la complétude.</span></Link>
          <Link href="/proclamation/" onClick={() => setHelpOpen(false)}><ClipboardCheck size={20} /><strong>4. Délibérer</strong><span>Calculer les moyennes, classer puis publier.</span></Link>
        </div>
        <div className="help-data-note"><Database size={18} /><p><strong>Environnement de démonstration</strong>Les modifications sont conservées dans ce navigateur. Exportez une sauvegarde JSON depuis les paramètres avant de réinitialiser.</p></div>
        <div className="modal-actions"><button className="button button-primary" type="button" onClick={() => setHelpOpen(false)}>J’ai compris</button></div>
      </Modal>
    </div>
  );
}
