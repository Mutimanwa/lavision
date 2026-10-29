import { z } from "zod";
import {
  levelCreateInputSchema,
  levelResponseSchema,
  levelUpdateInputSchema,
  levelsResponseSchema,
  nonWorkingDayCreateInputSchema,
  nonWorkingDayResponseSchema,
  nonWorkingDayUpdateInputSchema,
  nonWorkingDaysResponseSchema,
  organizationResponseSchema,
  organizationUpdateInputSchema,
  periodCreateInputSchema,
  periodResponseSchema,
  periodUpdateInputSchema,
  periodsResponseSchema,
  referentialRevisionResponseSchema,
  referentialRevisionsResponseSchema,
  roomCreateInputSchema,
  roomResponseSchema,
  roomUpdateInputSchema,
  roomsResponseSchema,
  schoolArchiveInputSchema,
  schoolHistoryQuerySchema,
  schoolListQuerySchema,
  schoolYearActivationInputSchema,
  schoolYearCloseInputSchema,
  schoolYearCreateInputSchema,
  schoolYearReopenInputSchema,
  schoolYearResponseSchema,
  schoolYearUpdateInputSchema,
  schoolYearsResponseSchema,
  siteCreateInputSchema,
  siteResponseSchema,
  siteUpdateInputSchema,
  sitesResponseSchema,
} from "./school.js";

const success = (schema: string) => ({
  description: "Réponse conforme au contrat",
  content: { "application/json": { schema: { $ref: `#/components/schemas/${schema}` } } },
});
const failure = (description: string) => ({
  description,
  content: {
    "application/problem+json": { schema: { $ref: "#/components/schemas/ProblemDetails" } },
  },
});
const body = (schema: string) => ({
  required: true,
  content: { "application/json": { schema: { $ref: `#/components/schemas/${schema}` } } },
});
const organizationParameter = {
  name: "organizationId",
  in: "query",
  required: true,
  schema: { type: "string", format: "uuid" },
};
const idParameter = {
  name: "id",
  in: "path",
  required: true,
  schema: { type: "string", format: "uuid" },
};
const yearParameter = {
  name: "yearId",
  in: "path",
  required: true,
  schema: { type: "string", format: "uuid" },
};
const listParameters = [
  organizationParameter,
  {
    name: "siteId",
    in: "query",
    required: false,
    schema: { type: "string", format: "uuid" },
  },
  {
    name: "includeArchived",
    in: "query",
    required: false,
    schema: { type: "string", enum: ["true", "false"] },
  },
];
const historyParameters = [
  organizationParameter,
  {
    name: "entityType",
    in: "query",
    required: false,
    schema: { type: "string" },
  },
  {
    name: "entityId",
    in: "query",
    required: false,
    schema: { type: "string", format: "uuid" },
  },
  {
    name: "limit",
    in: "query",
    required: false,
    schema: { type: "integer", minimum: 1, maximum: 100, default: 50 },
  },
];
const listOperation = (
  operationId: string,
  response: string,
  parameters: unknown[] = listParameters,
) => ({
  security: [{ sessionCookie: [] }],
  operationId,
  tags: ["Référentiels #8"],
  parameters,
  responses: {
    "200": success(response),
    "400": failure("Filtre invalide."),
    "401": failure("Session requise."),
    "403": failure("Lecture ou organisation refusée."),
  },
});
const commandOperation = (
  operationId: string,
  request: string,
  response: string,
  status: "200" | "201" = "200",
  parameters: unknown[] = [idParameter],
) => ({
  security: [{ sessionCookie: [] }],
  operationId,
  tags: ["Référentiels #8"],
  description:
    "Mutation protégée par session et CSRF. Les transitions de clôture exigent leurs permissions dédiées.",
  parameters,
  requestBody: body(request),
  responses: {
    [status]: success(response),
    "400": failure("Entrée invalide."),
    "401": failure("Session requise."),
    "403": failure("Permission ou portée refusée."),
    "409": failure("Doublon, chevauchement ou état de clôture incompatible."),
  },
});

export function createSchoolOpenApiPaths() {
  return {
    "/school/organization": {
      get: listOperation("getSchoolOrganization", "OrganizationResponse", [organizationParameter]),
    },
    "/school/organization/{id}": {
      patch: commandOperation("updateSchoolOrganization", "OrganizationUpdateInput", "OrganizationResponse"),
    },
    "/school/sites": {
      get: listOperation("listSchoolSites", "SitesResponse"),
      post: commandOperation("createSchoolSite", "SiteCreateInput", "SiteResponse", "201", []),
    },
    "/school/sites/{id}": {
      patch: commandOperation("updateSchoolSite", "SiteUpdateInput", "SiteResponse"),
    },
    "/school/sites/{id}/archive": {
      post: commandOperation("archiveSchoolSite", "SchoolArchiveInput", "SiteResponse"),
    },
    "/school/levels": {
      get: listOperation("listSchoolLevels", "LevelsResponse"),
      post: commandOperation("createSchoolLevel", "LevelCreateInput", "LevelResponse", "201", []),
    },
    "/school/levels/{id}": {
      patch: commandOperation("updateSchoolLevel", "LevelUpdateInput", "LevelResponse"),
    },
    "/school/levels/{id}/archive": {
      post: commandOperation("archiveSchoolLevel", "SchoolArchiveInput", "LevelResponse"),
    },
    "/school/rooms": {
      get: listOperation("listSchoolRooms", "RoomsResponse"),
      post: commandOperation("createSchoolRoom", "RoomCreateInput", "RoomResponse", "201", []),
    },
    "/school/rooms/{id}": {
      patch: commandOperation("updateSchoolRoom", "RoomUpdateInput", "RoomResponse"),
    },
    "/school/rooms/{id}/archive": {
      post: commandOperation("archiveSchoolRoom", "SchoolArchiveInput", "RoomResponse"),
    },
    "/school/history": {
      get: listOperation("listSchoolHistory", "ReferentialRevisionsResponse", historyParameters),
    },
    "/school/years": {
      get: listOperation("listSchoolYears", "SchoolYearsResponse"),
      post: commandOperation("createSchoolYear", "SchoolYearCreateInput", "SchoolYearResponse", "201", []),
    },
    "/school/years/{id}": {
      patch: commandOperation("updateSchoolYear", "SchoolYearUpdateInput", "SchoolYearResponse"),
    },
    "/school/years/{id}/activate": {
      post: commandOperation("activateSchoolYear", "SchoolYearActivationInput", "SchoolYearResponse"),
    },
    "/school/years/{id}/close": {
      post: commandOperation("closeSchoolYear", "SchoolYearCloseInput", "SchoolYearResponse"),
    },
    "/school/years/{id}/reopen": {
      post: commandOperation("reopenSchoolYear", "SchoolYearReopenInput", "SchoolYearResponse"),
    },
    "/school/years/{yearId}/periods": {
      get: listOperation("listSchoolPeriods", "PeriodsResponse", [yearParameter, ...listParameters]),
    },
    "/school/periods": {
      post: commandOperation("createSchoolPeriod", "PeriodCreateInput", "PeriodResponse", "201", []),
    },
    "/school/periods/{id}": {
      patch: commandOperation("updateSchoolPeriod", "PeriodUpdateInput", "PeriodResponse"),
    },
    "/school/periods/{id}/archive": {
      post: commandOperation("archiveSchoolPeriod", "SchoolArchiveInput", "PeriodResponse"),
    },
    "/school/years/{yearId}/non-working-days": {
      get: listOperation("listNonWorkingDays", "NonWorkingDaysResponse", [yearParameter, ...listParameters]),
    },
    "/school/non-working-days": {
      post: commandOperation("createNonWorkingDay", "NonWorkingDayCreateInput", "NonWorkingDayResponse", "201", []),
    },
    "/school/non-working-days/{id}": {
      patch: commandOperation("updateNonWorkingDay", "NonWorkingDayUpdateInput", "NonWorkingDayResponse"),
    },
    "/school/non-working-days/{id}/archive": {
      post: commandOperation("archiveNonWorkingDay", "SchoolArchiveInput", "NonWorkingDayResponse"),
    },
  };
}

export function createSchoolOpenApiSchemas() {
  return {
    OrganizationResponse: z.toJSONSchema(organizationResponseSchema),
    OrganizationUpdateInput: z.toJSONSchema(organizationUpdateInputSchema),
    SiteCreateInput: z.toJSONSchema(siteCreateInputSchema),
    SiteUpdateInput: z.toJSONSchema(siteUpdateInputSchema),
    SiteResponse: z.toJSONSchema(siteResponseSchema),
    SitesResponse: z.toJSONSchema(sitesResponseSchema),
    LevelCreateInput: z.toJSONSchema(levelCreateInputSchema),
    LevelUpdateInput: z.toJSONSchema(levelUpdateInputSchema),
    LevelResponse: z.toJSONSchema(levelResponseSchema),
    LevelsResponse: z.toJSONSchema(levelsResponseSchema),
    RoomCreateInput: z.toJSONSchema(roomCreateInputSchema),
    RoomUpdateInput: z.toJSONSchema(roomUpdateInputSchema),
    RoomResponse: z.toJSONSchema(roomResponseSchema),
    RoomsResponse: z.toJSONSchema(roomsResponseSchema),
    SchoolYearCreateInput: z.toJSONSchema(schoolYearCreateInputSchema),
    SchoolYearUpdateInput: z.toJSONSchema(schoolYearUpdateInputSchema),
    SchoolYearActivationInput: z.toJSONSchema(schoolYearActivationInputSchema),
    SchoolYearCloseInput: z.toJSONSchema(schoolYearCloseInputSchema),
    SchoolYearReopenInput: z.toJSONSchema(schoolYearReopenInputSchema),
    SchoolYearResponse: z.toJSONSchema(schoolYearResponseSchema),
    SchoolYearsResponse: z.toJSONSchema(schoolYearsResponseSchema),
    PeriodCreateInput: z.toJSONSchema(periodCreateInputSchema),
    PeriodUpdateInput: z.toJSONSchema(periodUpdateInputSchema),
    PeriodResponse: z.toJSONSchema(periodResponseSchema),
    PeriodsResponse: z.toJSONSchema(periodsResponseSchema),
    NonWorkingDayCreateInput: z.toJSONSchema(nonWorkingDayCreateInputSchema),
    NonWorkingDayUpdateInput: z.toJSONSchema(nonWorkingDayUpdateInputSchema),
    NonWorkingDayResponse: z.toJSONSchema(nonWorkingDayResponseSchema),
    NonWorkingDaysResponse: z.toJSONSchema(nonWorkingDaysResponseSchema),
    SchoolArchiveInput: z.toJSONSchema(schoolArchiveInputSchema),
    SchoolListQuery: z.toJSONSchema(schoolListQuerySchema),
    SchoolHistoryQuery: z.toJSONSchema(schoolHistoryQuerySchema),
    ReferentialRevisionResponse: z.toJSONSchema(referentialRevisionResponseSchema),
    ReferentialRevisionsResponse: z.toJSONSchema(referentialRevisionsResponseSchema),
  };
}
