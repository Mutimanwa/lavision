import type {
  LevelResponse,
  LevelsResponse,
  OrganizationResponse,
  RoomResponse,
  RoomsResponse,
  SiteResponse,
  SitesResponse,
} from "@lavision/contracts";
import { apiFetch } from "@/lib/api-client";

export interface SchoolReferentials {
  organization: OrganizationResponse;
  sites: SiteResponse[];
  levels: LevelResponse[];
  rooms: RoomResponse[];
}

function organizationQuery(organizationId: string) {
  return new URLSearchParams({ organizationId }).toString();
}

export function getSchoolOrganization(organizationId: string) {
  return apiFetch<OrganizationResponse>(`/school/organization?${organizationQuery(organizationId)}`);
}

export function listSchoolSites(organizationId: string) {
  return apiFetch<SitesResponse>(`/school/sites?${organizationQuery(organizationId)}`);
}

export function listSchoolLevels(organizationId: string) {
  return apiFetch<LevelsResponse>(`/school/levels?${organizationQuery(organizationId)}`);
}

export function listSchoolRooms(organizationId: string) {
  return apiFetch<RoomsResponse>(`/school/rooms?${organizationQuery(organizationId)}`);
}

export async function getSchoolReferentials(organizationId: string): Promise<SchoolReferentials> {
  const [organization, sites, levels, rooms] = await Promise.all([
    getSchoolOrganization(organizationId),
    listSchoolSites(organizationId),
    listSchoolLevels(organizationId),
    listSchoolRooms(organizationId),
  ]);
  return {
    organization,
    sites: sites.sites,
    levels: levels.levels,
    rooms: rooms.rooms,
  };
}

export function updateSchoolOrganization(organizationId: string, name: string) {
  const trimmedName = name.trim();
  if (!trimmedName) {
    return Promise.reject(new Error("Le nom de l’établissement est requis."));
  }
  return apiFetch<OrganizationResponse>(`/school/organization/${encodeURIComponent(organizationId)}`, {
    method: "PATCH",
    body: JSON.stringify({ organizationId, name: trimmedName }),
  });
}
