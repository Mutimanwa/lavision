"use client";

import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";
import { useEffect, useMemo, useRef, useState, type ReactNode } from "react";
import {
  Award,
  Bell,
  Blocks,
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
  Sparkles,
  UserRoundPlus,
  UsersRound,
  X,
  type LucideIcon,
} from "lucide-react";
import { useSchool } from "@/features/school/hooks/use-school";
import { Logo } from "./logo";
import { Avatar, Modal } from "@/components/ui";
import { HeaderDropdown } from "./header-dropdown";
import { getFullName } from "@/lib/academic";
import { isRoleId, ROLE_DEFINITIONS } from "@/features/auth/data/roles";
import { useWorkspace } from "@/features/auth/hooks/use-workspace";
import { useModules } from "@/features/navigation/hooks/use-modules";
import { canRoleOpenModule, getRoleModuleHref } from "@/features/navigation/services/module-state";
import { ModuleGate } from "@/features/navigation/components/module-gate";
import { OnboardingGuide } from "@/features/onboarding/components/onboarding-guide";
import { useOnboarding } from "@/features/onboarding/hooks/use-onboarding";
import type { ModuleId } from "@/features/navigation/data/modules";

const MODULE_ICONS: Partial<Record<ModuleId, LucideIcon>> = {
  students: UsersRound,
  enrollments: GraduationCap,
  classes: BookOpenCheck,
  attendance: CalendarCheck2,
  grades: NotebookPen,
  reports: FileBarChart,
  proclamation: Award,
  settings: Settings,
};

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
  "/modules": "Modules et activation",
};

function NavigationSections({ onNavigate }: { onNavigate?: () => void }) {
  const pathname = usePathname();
  const { state } = useSchool();
  const { role } = useWorkspace();
  const { modules } = useModules();
  const pending = state.students.filter((student) => student.status === "En attente").length;
  const [openSections, setOpenSections] = useState<Record<string, boolean>>({
    Scolarité: true,
    Évaluations: true,
  });
  const visibleModules = modules.filter((module) =>
    module.href
    && module.section
    && module.roles.includes(role)
    && (module.status === "active" || module.status === "configuring"),
  );
  const sections = ["Scolarité", "Évaluations"].map((label) => ({
    label,
    items: visibleModules.filter((module) => module.section === label),
  })).filter((section) => section.items.length > 0);

  return (
    <nav className="sidebar-nav" aria-label="Navigation principale">
      <section className="nav-section">
        <p>Général</p>
        <Link href="/" className={pathname === "/" ? "active" : ""} aria-current={pathname === "/" ? "page" : undefined} onClick={onNavigate}>
          <LayoutDashboard size={18} strokeWidth={1.8} /><span>Mon espace</span>
        </Link>
      </section>
      {sections.map((section) => {
        const isOpen = openSections[section.label];
        return (
          <section className="nav-section" key={section.label}>
            <button
              className="nav-section-toggle"
              type="button"
              aria-expanded={isOpen}
              onClick={() => setOpenSections((current) => ({
                ...current,
                [section.label]: !current[section.label],
              }))}
            >
              <span>{section.label}</span><ChevronDown size={14} />
            </button>
            {isOpen && section.items.map((item) => {
              const Icon = MODULE_ICONS[item.id] ?? Blocks;
              const href = item.href ?? "/";
              const active = pathname === href || pathname === href.slice(0, -1);
              const count = item.id === "enrollments" ? pending : 0;
              return (
                <Link
                  href={href}
                  key={item.id}
                  className={active ? "active" : ""}
                  aria-current={active ? "page" : undefined}
                  onClick={onNavigate}
                  title={item.missingPrerequisites.join(" ") || undefined}
                >
                  <Icon size={18} strokeWidth={1.8} />
                  <span>{item.label}</span>
                  {count > 0 && <span className="nav-count">{count}</span>}
                  {item.status === "configuring" && <span className="nav-setup">À configurer</span>}
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
  const { role } = useWorkspace();
  const { modules } = useModules();
  const canSearchStudents = getRoleModuleHref(modules, role, "/eleves/") === "/eleves/";
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

  if (!canSearchStudents) return null;

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
  const { role } = useWorkspace();
  const { modules } = useModules();
  const canOpenStudents = getRoleModuleHref(modules, role, "/eleves/") === "/eleves/";
  const canOpenGrades = getRoleModuleHref(modules, role, "/notes/") === "/notes/";
  const [open, setOpen] = useState(false);
  const [readIds, setReadIds] = useState<Set<string>>(new Set());
  const containerRef = useRef<HTMLDivElement>(null);
  const pending = state.students.filter((student) => student.status === "En attente").length;
  const activeStudentIds = new Set(state.students.filter((student) => student.status === "Actif").map((student) => student.id));
  const expected = activeStudentIds.size * state.subjects.length;
  const currentGrades = state.grades.filter((grade) => grade.term === state.settings.currentTerm && activeStudentIds.has(grade.studentId)).length;
  const missing = Math.max(0, expected - currentGrades);
  const notifications: SchoolNotification[] = [];

  if (pending > 0 && canOpenStudents) {
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
  if (missing > 0 && canOpenGrades) {
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

  if (!canOpenStudents && !canOpenGrades) return null;

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
  const router = useRouter();
  const { state } = useSchool();
  const { role, roleDefinition, workspace, setRole } = useWorkspace();
  const { modules } = useModules();
  const { restartGuide } = useOnboarding();
  const [open, setOpen] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);
  const profileName = role === "direction" ? state.settings.directionName : workspace.profileName;
  const initials = profileName
    .split(/\s+/)
    .filter(Boolean)
    .map((part) => part[0])
    .slice(0, 2)
    .join("")
    .toUpperCase() || "LV";
  const quickLinks = modules.filter((module) =>
    ["settings", "classes", "proclamation"].includes(module.id)
    && canRoleOpenModule(module, role),
  );

  useEffect(() => {
    if (!open) return;
    const closeOutside = (event: MouseEvent) => {
      if (containerRef.current && !containerRef.current.contains(event.target as Node)) setOpen(false);
    };
    document.addEventListener("mousedown", closeOutside);
    return () => document.removeEventListener("mousedown", closeOutside);
  }, [open]);

  const changeRole = (value: string) => {
    if (!isRoleId(value)) return;
    setRole(value);
    setOpen(false);
    router.push("/");
  };

  const content = (
    <>
      <div className="workspace-role-preview">
        <span className="workspace-role-label">Aperçu des interfaces</span>
        <select aria-label="Choisir un rôle de démonstration" value={role} onChange={(event) => changeRole(event.target.value)}>
          {ROLE_DEFINITIONS.map((item) => <option key={item.id} value={item.id}>{item.label}</option>)}
        </select>
        <small>Changer de rôle modifie uniquement la présentation locale, pas les permissions.</small>
      </div>
      <div className="user-popup-summary">
        <span className="profile-avatar">{initials}</span>
        <div><strong>{profileName}</strong><small>{workspace.spaceLabel}</small><span>Aperçu · {roleDefinition.label}</span></div>
      </div>
      <div className="user-popup-school"><strong>{state.settings.schoolName}</strong><small>{workspace.scopeLabel}</small></div>
      <nav className="user-popup-links" aria-label="Liens du compte">
        <Link href="/modules/" onClick={() => setOpen(false)}><Blocks size={16} /> Modules et activation</Link>
        {quickLinks.map((module) => {
          const Icon = MODULE_ICONS[module.id] ?? Blocks;
          return <Link key={module.id} href={module.href ?? "/modules/"} onClick={() => setOpen(false)}><Icon size={16} /> {module.label}</Link>;
        })}
        <button type="button" onClick={() => { setOpen(false); restartGuide(); }}><Sparkles size={16} /> Relancer le guide</button>
        <button type="button" onClick={() => { setOpen(false); onHelp(); }}><CircleHelp size={16} /> Centre d’aide</button>
      </nav>
      <div className="user-popup-note"><Database size={14} /> Aperçu UX local · aucune habilitation réelle</div>
    </>
  );

  return (
    <div className="header-popup-container" ref={containerRef}>
      <button
        type="button"
        className="user-popup-trigger"
        onClick={() => setOpen((current) => !current)}
        aria-label={`Ouvrir le menu du profil ${roleDefinition.label}`}
        aria-haspopup="dialog"
        aria-expanded={open}
      >
        <span className="profile-avatar">{initials}</span>
      </button>
      <HeaderDropdown open={open} onClose={() => setOpen(false)} title={`Mon compte · ${roleDefinition.label}`}>
        {content}
      </HeaderDropdown>
    </div>
  );
}

export function AppShell({ children }: { children: ReactNode }) {
  const pathname = usePathname();
  const { state, hydrated } = useSchool();
  const { role, workspace } = useWorkspace();
  const { modules } = useModules();
  const { restartGuide } = useOnboarding();
  const [mobileOpen, setMobileOpen] = useState(false);
  const [helpOpen, setHelpOpen] = useState(false);
  const basePath = pathname === "/" ? "/" : `/${pathname.split("/").filter(Boolean)[0] ?? ""}`;
  const pageTitle = PAGE_TITLES[basePath] ?? "lavision";
  const canOpen = (id: ModuleId) => {
    const definition = modules.find((item) => item.id === id);
    return definition ? canRoleOpenModule(definition, role) : false;
  };
  const helpLinks: { id: ModuleId; href: string; icon: LucideIcon; title: string; description: string }[] = [
    { id: "enrollments", href: "/inscriptions/", icon: GraduationCap, title: "Inscrire", description: "Créer un dossier élève et l’affecter à une classe." },
    { id: "classes", href: "/classes/", icon: BookOpenCheck, title: "Structurer", description: "Configurer les salles, matières et coefficients." },
    { id: "grades", href: "/notes/", icon: NotebookPen, title: "Évaluer", description: "Saisir les notes sur 20 et contrôler la complétude." },
    { id: "proclamation", href: "/proclamation/", icon: ClipboardCheck, title: "Délibérer", description: "Calculer les moyennes, classer puis publier." },
  ];

  useEffect(() => {
    document.body.style.overflow = mobileOpen ? "hidden" : "";
    return () => { document.body.style.overflow = ""; };
  }, [mobileOpen]);

  const moduleLinks = (
    <>
      <Link href="/modules/" className={pathname === "/modules/" ? "active" : ""} onClick={mobileOpen ? () => setMobileOpen(false) : undefined}>
        <Blocks size={18} /><span>Modules</span>
      </Link>
      {canOpen("settings") && (
        <Link href="/parametres/" className={pathname === "/parametres/" ? "active" : ""} onClick={mobileOpen ? () => setMobileOpen(false) : undefined}>
          <Settings size={18} /><span>Paramètres</span>
        </Link>
      )}
    </>
  );

  return (
    <div className="app-layout">
      <aside className="sidebar">
        <NavigationSections />
        <div className="sidebar-bottom">{moduleLinks}</div>
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
            <div className="sidebar-bottom">{moduleLinks}</div>
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
            {canOpen("attendance") ? (
              <Link className="icon-button header-circle-button topbar-action" href="/presences/" aria-label="Ouvrir les présences">
                <CalendarCheck2 size={18} />
              </Link>
            ) : (
              <Link className="icon-button header-circle-button topbar-action" href="/modules/" aria-label="Ouvrir les modules">
                <Blocks size={18} />
              </Link>
            )}
            <UserPopup onHelp={() => setHelpOpen(true)} />
          </div>
        </header>
        <main className="page-content" id="main-content"><ModuleGate pathname={pathname}>{children}</ModuleGate></main>
        <footer className="app-footer">
          <span>{pageTitle} · {state.settings.academicYear}</span>
          <span><span className="live-dot" /> {hydrated ? "Données enregistrées sur cet appareil" : "Chargement de la sauvegarde locale"}</span>
        </footer>
      </div>

      <Modal open={helpOpen} onClose={() => setHelpOpen(false)} title="Centre d’aide lavision" description={`${workspace.spaceLabel} · ${state.settings.academicYear}`} size="medium">
        <div className="help-intro"><span><Sparkles size={24} /></span><div><h3>Un guide adapté à votre espace</h3><p>{workspace.guideIntroduction}</p></div></div>
        <button className="help-guide-launch" type="button" onClick={() => { setHelpOpen(false); restartGuide(); }}>
          <Sparkles size={20} /><span><strong>Relancer le guide de prise en main</strong><small>Revoir la navigation, les modules et la première action du rôle.</small></span>
        </button>
        <div className="help-grid">
          {helpLinks.filter((item) => canOpen(item.id)).map((item) => {
            const Icon = item.icon;
            return <Link href={item.href} key={item.id} onClick={() => setHelpOpen(false)}><Icon size={20} /><strong>{item.title}</strong><span>{item.description}</span></Link>;
          })}
          <Link href="/modules/" onClick={() => setHelpOpen(false)}><Blocks size={20} /><strong>Modules</strong><span>Comprendre les états actifs, à configurer, désactivés et planifiés.</span></Link>
        </div>
        <div className="help-data-note"><Database size={18} /><p><strong>Environnement de démonstration</strong>Le rôle et les modules modifient la présentation locale. Ils ne remplacent pas l’authentification et les autorisations du futur serveur.</p></div>
        <div className="modal-actions"><button className="button button-primary" type="button" onClick={() => setHelpOpen(false)}>J’ai compris</button></div>
      </Modal>
      <OnboardingGuide />
    </div>
  );
}
