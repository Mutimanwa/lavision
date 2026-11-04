import { beforeEach, describe, expect, it, vi } from "vitest";
import type { LevelResponse, OrganizationResponse, RoomResponse, SiteResponse } from "@lavision/contracts";
import { apiFetch } from "@/lib/api-client";
import {
  getSchoolReferentials,
  updateSchoolOrganization,
} from "./school-referentials-api";

vi.mock("@/lib/api-client", () => ({ apiFetch: vi.fn() }));

const organization: OrganizationResponse = {
  id: "org-1",
  code: "ORG",
  name: "Établissement API",
  createdAt: "2026-01-01T00:00:00.000Z",
  updatedAt: "2026-01-01T00:00:00.000Z",
  archivedAt: null,
};
const site: SiteResponse = {
  id: "site-1",
  organizationId: organization.id,
  code: "SITE",
  name: "Site principal",
  createdAt: organization.createdAt,
  updatedAt: organization.updatedAt,
  archivedAt: null,
};
const level: LevelResponse = {
  id: "level-1",
  organizationId: organization.id,
  code: "6E",
  name: "Sixième",
  rank: 1,
  createdAt: organization.createdAt,
  updatedAt: organization.updatedAt,
  archivedAt: null,
};
const room: RoomResponse = {
  id: "room-1",
  organizationId: organization.id,
  siteId: site.id,
  code: "A01",
  name: "Salle A01",
  capacity: null,
  createdAt: organization.createdAt,
  updatedAt: organization.updatedAt,
  archivedAt: null,
};

beforeEach(() => {
  vi.mocked(apiFetch).mockReset();
});

describe("référentiels scolaires API", () => {
  it("charge les quatre ressources de la même organisation sans données locales", async () => {
    vi.mocked(apiFetch).mockImplementation(async (path) => {
      if (path === "/school/organization?organizationId=org-1") return organization;
      if (path === "/school/sites?organizationId=org-1") return { sites: [site] };
      if (path === "/school/levels?organizationId=org-1") return { levels: [level] };
      if (path === "/school/rooms?organizationId=org-1") return { rooms: [room] };
      throw new Error(`Requête inattendue : ${path}`);
    });

    await expect(getSchoolReferentials("org-1")).resolves.toEqual({
      organization,
      sites: [site],
      levels: [level],
      rooms: [room],
    });
    expect(vi.mocked(apiFetch)).toHaveBeenCalledTimes(4);
  });

  it("modifie le nom avec l’organisation explicite et sans changer son code", async () => {
    vi.mocked(apiFetch).mockResolvedValue({ ...organization, name: "Nouveau nom" });

    await expect(updateSchoolOrganization("org-1", "  Nouveau nom  ")).resolves.toMatchObject({ name: "Nouveau nom" });
    expect(vi.mocked(apiFetch)).toHaveBeenCalledWith("/school/organization/org-1", {
      method: "PATCH",
      body: JSON.stringify({ organizationId: "org-1", name: "Nouveau nom" }),
    });
  });

  it("refuse un nom vide avant toute écriture API", async () => {
    await expect(updateSchoolOrganization("org-1", "   ")).rejects.toThrow("nom de l’établissement");
    expect(vi.mocked(apiFetch)).not.toHaveBeenCalled();
  });
});
