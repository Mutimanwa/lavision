"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useEffect, useMemo, useRef, useState, type ReactNode } from "react";
import {
  Award,
  Bell,
  BookOpenCheck,
  CalendarCheck2,
  CheckCircle2,
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
import { HeaderDropdown } from "./header-dropdown";
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
      <Search size={17} />
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
        <kbd><span>CTRL</span><span>K</span></kbd>
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

interface SchoolNotification {
  id: string;
  title: string;
  message: string;
  meta: string;
  href: string;
  tone: "warning" | "info";
  icon: LucideIcon;
}

function Notifications() {
  const { state } = useSchool();
  const [open, setOpen] = useState(false);
  const [readIds, setReadIds] = useState<Set<string>>(new Set());
  const containerRef = useRef<HTMLDivElement>(null);
  const pending = state.students.filter((student) => student.status === "En attente").length;
  const activeStudentIds = new Set(state.students.filter((student) => student.status === "Actif").map((student) => student.id));
  const expected = activeStudentIds.size * state.subjects.length;
  const currentGrades = state.grades.filter((grade) => grade.term === state.settings.currentTerm && activeStudentIds.has(grade.studentId)).length;
  const missing = Math.max(0, expected - currentGrades);
  const notifications: SchoolNotification[] = [];

  if (pending > 0) {
    notifications.push({
      id: `inscriptions-${pending}`,
      title: `${pending} inscription${pending > 1 ? "s" : ""} à valider`,
      message: "Des dossiers provisoires attendent une vérification par la scolarité.",
      meta: "Scolarité",
      href: "/eleves/",
      tone: "warning",
      icon: UserRoundPlus,
    });
  }
  if (missing > 0) {
    notifications.push({
      id: `notes-${state.settings.currentTerm}-${missing}`,
      title: `${missing} note${missing > 1 ? "s" : ""} à compléter`,
      message: "La grille de saisie de la période active contient encore des cellules vides.",
      meta: state.settings.currentTerm,
      href: "/notes/",
      tone: "info",
      icon: NotebookPen,
    });
  }

  const unreadCount = notifications.filter((notification) => !readIds.has(notification.id)).length;

  useEffect(() => {
    if (!open) return;
    const closeOutside = (event: MouseEvent) => {
      if (containerRef.current && !containerRef.current.contains(event.target as Node)) setOpen(false);
    };
    document.addEventListener("mousedown", closeOutside);
    return () => document.removeEventListener("mousedown", closeOutside);
  }, [open]);

  const content = (
    <>
      <div className="header-popup-heading">
        <div><strong>Notifications</strong>{unreadCount > 0 && <span>{unreadCount} nouvelle{unreadCount > 1 ? "s" : ""}</span>}</div>
        {unreadCount > 0 && (
          <button type="button" onClick={() => setReadIds(new Set(notifications.map((notification) => notification.id)))}>
            Tout marquer comme lu
          </button>
        )}
      </div>
      <div className="header-notification-list">
        {notifications.length ? notifications.map((notification) => {
          const Icon = notification.icon;
          const unread = !readIds.has(notification.id);
          return (
            <Link
              href={notification.href}
              key={notification.id}
              className={`school-notification ${unread ? "unread" : ""}`}
              onClick={() => {
                setReadIds((current) => new Set([...current, notification.id]));
                setOpen(false);
              }}
            >
              <span className={`school-notification-icon ${notification.tone}`}><Icon size={16} /></span>
              <span className="school-notification-copy">
                <span><strong>{notification.title}</strong>{unread && <i />}</span>
                <p>{notification.message}</p>
                <small>{notification.meta}</small>
              </span>
            </Link>
          );
        }) : (
          <div className="header-notification-empty"><CheckCircle2 size={25} /><strong>Tout est à jour</strong><p>Aucune action urgente dans les modules de scolarité.</p></div>
        )}
      </div>
      <div className="header-popup-footer"><Link href="/" onClick={() => setOpen(false)}>Voir le tableau de bord</Link></div>
    </>
  );

  return (
    <div className="header-popup-container" ref={containerRef}>
      <button
        type="button"
        className="icon-button header-circle-button"
        onClick={() => setOpen((current) => !current)}
        aria-label="Ouvrir les notifications"
        aria-haspopup="dialog"
        aria-expanded={open}
      >
        <Bell size={18} />
        {unreadCount > 0 && <span className="header-notification-badge">{unreadCount}</span>}
      </button>
      <HeaderDropdown open={open} onClose={() => setOpen(false)} title="Notifications" width="wide">
        {content}
      </HeaderDropdown>
    </div>
  );
}

function UserPopup({ onHelp }: { onHelp: () => void }) {
  const { state } = useSchool();
  const [open, setOpen] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);
  const initials = state.settings.directionName
    .split(/\s+/)
    .filter(Boolean)
    .map((part) => part[0])
    .slice(0, 2)
    .join("")
    .toUpperCase() || "LV";

  useEffect(() => {
    if (!open) return;
    const closeOutside = (event: MouseEvent) => {
      if (containerRef.current && !containerRef.current.contains(event.target as Node)) setOpen(false);
    };
    document.addEventListener("mousedown", closeOutside);
    return () => document.removeEventListener("mousedown", closeOutside);
  }, [open]);

  const content = (
    <>
      <div className="user-popup-summary">
        <span className="profile-avatar">{initials}</span>
        <div><strong>{state.settings.directionName}</strong><small>{state.settings.email}</small><span>Admin / Direction</span></div>
      </div>
      <div className="user-popup-school"><strong>{state.settings.schoolName}</strong><small>Année {state.settings.academicYear}</small></div>
      <nav className="user-popup-links" aria-label="Liens du compte">
        <Link href="/parametres/" onClick={() => setOpen(false)}><SlidersHorizontal size={16} /> Configuration de l’école</Link>
        <Link href="/classes/" onClick={() => setOpen(false)}><BookOpenCheck size={16} /> Classes & matières</Link>
        <Link href="/proclamation/" onClick={() => setOpen(false)}><Award size={16} /> Proclamations</Link>
        <button type="button" onClick={() => { setOpen(false); onHelp(); }}><CircleHelp size={16} /> Centre d’aide</button>
      </nav>
      <div className="user-popup-note"><Database size={14} /> Prototype · données enregistrées localement</div>
    </>
  );

  return (
    <div className="header-popup-container" ref={containerRef}>
      <button
        type="button"
        className="user-popup-trigger"
        onClick={() => setOpen((current) => !current)}
        aria-label="Ouvrir le menu du profil"
        aria-haspopup="dialog"
        aria-expanded={open}
      >
        <span className="profile-avatar">{initials}</span>
      </button>
      <HeaderDropdown open={open} onClose={() => setOpen(false)} title="Mon compte">
        {content}
      </HeaderDropdown>
    </div>
  );
}

export function AppShell({ children }: { children: ReactNode }) {
  const pathname = usePathname();
  const { state, hydrated } = useSchool();
  const [mobileOpen, setMobileOpen] = useState(false);
  const [helpOpen, setHelpOpen] = useState(false);
  const basePath = pathname === "/" ? "/" : `/${pathname.split("/").filter(Boolean)[0] ?? ""}`;
  const pageTitle = PAGE_TITLES[basePath] ?? "lavision";

  useEffect(() => {
    document.body.style.overflow = mobileOpen ? "hidden" : "";
    return () => { document.body.style.overflow = ""; };
  }, [mobileOpen]);

  return (
    <div className="app-layout">
      <aside className="sidebar">
        <NavigationSections />
        <div className="sidebar-bottom">
          <Link href="/parametres/" className={pathname === "/parametres/" ? "active" : ""}>
            <Settings size={18} /><span>Paramètres</span>
          </Link>
        </div>
      </aside>

      {mobileOpen && (
        <div className="mobile-navigation">
          <button className="mobile-overlay" type="button" onClick={() => setMobileOpen(false)} aria-label="Fermer le menu" />
          <aside className="mobile-sidebar">
            <div className="sidebar-header">
              <span className="mobile-navigation-title">Navigation</span>
              <button className="sidebar-close" type="button" onClick={() => setMobileOpen(false)} aria-label="Fermer le menu">
                <X size={20} />
              </button>
            </div>
            <NavigationSections onNavigate={() => setMobileOpen(false)} />
            <div className="sidebar-bottom">
              <Link href="/parametres/" onClick={() => setMobileOpen(false)}><Settings size={18} /><span>Paramètres</span></Link>
            </div>
          </aside>
        </div>
      )}

      <div className="app-main">
        <header className="topbar">
          <div className="topbar-left">
            <button className="icon-button mobile-menu" type="button" onClick={() => setMobileOpen(true)} aria-label="Ouvrir le menu">
              <Menu size={20} />
            </button>
            <Link className="topbar-brand" href="/" aria-label="Accueil lavision"><Logo /></Link>
            <GlobalSearch />
          </div>
          <div className="topbar-actions">
            <Notifications />
            <Link className="icon-button header-circle-button topbar-action" href="/presences/" aria-label="Ouvrir les présences">
              <CalendarCheck2 size={18} />
            </Link>
            <UserPopup onHelp={() => setHelpOpen(true)} />
          </div>
        </header>
        <main className="page-content" id="main-content">{children}</main>
        <footer className="app-footer">
          <span>{pageTitle} · {state.settings.academicYear}</span>
          <span><span className="live-dot" /> {hydrated ? "Données enregistrées sur cet appareil" : "Chargement de la sauvegarde locale"}</span>
        </footer>
      </div>

      <Modal open={helpOpen} onClose={() => setHelpOpen(false)} title="Centre d’aide lavision" description={`${state.settings.schoolName} · ${state.settings.academicYear}`} size="medium">
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
