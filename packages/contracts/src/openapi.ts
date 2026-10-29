import { z } from "zod";
import {
  acceptedResponseSchema,
  acceptInvitationInputSchema,
  authenticatedResponseSchema,
  authMeResponseSchema,
  authSessionSchema,
  authUserSchema,
  invitationInputSchema,
  invitationResponseSchema,
  loginInputSchema,
  membershipResponseSchema,
  mfaRecoveryInputSchema,
  mfaRequiredResponseSchema,
  mfaSetupConfirmResponseSchema,
  mfaSetupInputSchema,
  mfaSetupResponseSchema,
  mfaVerifyInputSchema,
  organizationActionInputSchema,
  passwordResetConfirmInputSchema,
  passwordResetRequestInputSchema,
  sessionsResponseSchema,
} from "./auth.js";
import {
  accessScopeSchema,
  auditEventsResponseSchema,
  auditEventSchema,
  auditQuerySchema,
  permissionGrantInputSchema,
  permissionGrantResponseSchema,
  permissionRevokeInputSchema,
  roleAssignmentInputSchema,
  roleAssignmentResponseSchema,
  roleRevokeInputSchema,
} from "./access.js";
import {
  createSchoolOpenApiPaths,
  createSchoolOpenApiSchemas,
} from "./school-openapi.js";
import {
  createRulesOpenApiPaths,
  createRulesOpenApiSchemas,
} from "./rules-openapi.js";
import {
  API_PREFIX,
  livenessResponseSchema,
  problemDetailsSchema,
  readinessResponseSchema,
  versionResponseSchema,
} from "./system.js";

const success = (schema: string) => ({
  description: "Réponse conforme au contrat",
  content: {
    "application/json": {
      schema: { $ref: `#/components/schemas/${schema}` },
    },
  },
});

const failure = (description: string) => ({
  description,
  content: {
    "application/problem+json": {
      schema: { $ref: "#/components/schemas/ProblemDetails" },
    },
  },
});

const body = (schema: string) => ({
  required: true,
  content: {
    "application/json": {
      schema: { $ref: `#/components/schemas/${schema}` },
    },
  },
});

const noContent = { description: "Opération effectuée, sans contenu." };
const secured = { security: [{ sessionCookie: [] }] };
const csrfNote =
  "Pour toute méthode non sûre, le header x-csrf-token doit correspondre au cookie lisible lavision_csrf.";
const idParameter = {
  name: "id",
  in: "path",
  required: true,
  schema: { type: "string", format: "uuid" },
};

export function createOpenApiDocument() {
  return {
    openapi: "3.1.0",
    info: {
      title: "lavision API",
      version: "0.1.0",
      description:
        "Socle M1, authentification #6, RBAC/audit #7, référentiels/calendrier #8 et catalogue de règles #39. Les modules métier appliquent ces gardes à leurs ressources enregistrées.",
    },
    servers: [{ url: API_PREFIX }],
    paths: {
      ...createSchoolOpenApiPaths(),
      ...createRulesOpenApiPaths(),
      "/health/live": {
        get: {
          operationId: "getLiveness",
          tags: ["Système"],
          responses: { "200": success("LivenessResponse") },
        },
      },
      "/health/ready": {
        get: {
          operationId: "getReadiness",
          tags: ["Système"],
          responses: {
            "200": success("ReadinessResponse"),
            "503": failure("PostgreSQL ne répond pas."),
          },
        },
      },
      "/version": {
        get: {
          operationId: "getVersion",
          tags: ["Système"],
          responses: { "200": success("VersionResponse") },
        },
      },
      "/security/permissions/grant": {
        post: {
          ...secured,
          operationId: "grantPermission",
          tags: ["Sécurité"],
          description: csrfNote,
          requestBody: body("PermissionGrantInput"),
          responses: {
            "201": success("PermissionGrantResponse"),
            "400": failure("Entrée ou portée invalide."),
            "401": failure("Session requise."),
            "403": failure("Permission d’administration ou portée refusée."),
          },
        },
      },
      "/security/permissions/revoke": {
        post: {
          ...secured,
          operationId: "revokePermission",
          tags: ["Sécurité"],
          description: csrfNote,
          requestBody: body("PermissionRevokeInput"),
          responses: {
            "200": success("PermissionGrantResponse"),
            "400": failure("Entrée invalide."),
            "401": failure("Session requise."),
            "403": failure("Permission d’administration ou portée refusée."),
          },
        },
      },
      "/security/roles/assign": {
        post: {
          ...secured,
          operationId: "assignRole",
          tags: ["Sécurité"],
          description: csrfNote,
          requestBody: body("RoleAssignmentInput"),
          responses: {
            "201": success("RoleAssignmentResponse"),
            "400": failure("Entrée ou portée invalide."),
            "401": failure("Session requise."),
            "403": failure("Permission d’administration ou portée refusée."),
          },
        },
      },
      "/security/roles/revoke": {
        post: {
          ...secured,
          operationId: "revokeRole",
          tags: ["Sécurité"],
          description: csrfNote,
          requestBody: body("RoleRevokeInput"),
          responses: {
            "200": success("RoleAssignmentResponse"),
            "400": failure("Entrée invalide."),
            "401": failure("Session requise."),
            "403": failure("Permission d’administration ou portée refusée."),
          },
        },
      },
      "/security/audit": {
        get: {
          ...secured,
          operationId: "listAuditEvents",
          tags: ["Sécurité"],
          parameters: [
            {
              name: "organizationId",
              in: "query",
              required: true,
              schema: { type: "string", format: "uuid" },
            },
            {
              name: "action",
              in: "query",
              required: false,
              schema: { type: "string" },
            },
            {
              name: "resourceType",
              in: "query",
              required: false,
              schema: { type: "string" },
            },
            {
              name: "before",
              in: "query",
              required: false,
              schema: { type: "string", format: "date-time" },
            },
            {
              name: "limit",
              in: "query",
              required: false,
              schema: { type: "integer", minimum: 1, maximum: 100, default: 50 },
            },
          ],
          responses: {
            "200": success("AuditEventsResponse"),
            "400": failure("Filtre invalide."),
            "401": failure("Session requise."),
            "403": failure("Lecture d’audit ou organisation refusée."),
          },
        },
      },
      "/auth/login": {
        post: {
          operationId: "login",
          tags: ["Authentification"],
          requestBody: body("LoginInput"),
          responses: {
            "200": success("AuthenticatedResponse"),
            "202": success("MfaRequiredResponse"),
            "400": failure("Entrée invalide."),
            "401": failure("Identifiants invalides."),
            "429": failure("Limitation temporaire des tentatives."),
          },
        },
      },
      "/auth/admin/invitations": {
        post: {
          ...secured,
          operationId: "createInvitation",
          tags: ["Administration technique"],
          description: csrfNote,
          requestBody: body("InvitationInput"),
          responses: {
            "201": success("InvitationResponse"),
            "400": failure("Entrée invalide."),
            "401": failure("Session requise."),
            "403": failure("Administration technique et CSRF requis."),
            "409": failure("Utilisateur déjà existant."),
            "503": failure("Transport mail indisponible."),
          },
        },
      },
      "/auth/invitations/accept": {
        post: {
          operationId: "acceptInvitation",
          tags: ["Authentification"],
          requestBody: body("AcceptInvitationInput"),
          responses: {
            "200": success("AuthenticatedResponse"),
            "202": success("MfaRequiredResponse"),
            "400": failure("Lien invalide, expiré ou déjà consommé."),
          },
        },
      },
      "/auth/password-reset/request": {
        post: {
          operationId: "requestPasswordReset",
          tags: ["Authentification"],
          requestBody: body("PasswordResetRequestInput"),
          responses: {
            "202": success("AcceptedResponse"),
            "400": failure("Entrée invalide."),
            "429": failure("Limitation temporaire des demandes."),
          },
        },
      },
      "/auth/password-reset/confirm": {
        post: {
          operationId: "confirmPasswordReset",
          tags: ["Authentification"],
          requestBody: body("PasswordResetConfirmInput"),
          responses: {
            "204": noContent,
            "400": failure("Lien invalide, expiré ou déjà consommé."),
          },
        },
      },
      "/auth/mfa/setup": {
        post: {
          operationId: "startMfaSetup",
          tags: ["MFA"],
          requestBody: body("MfaSetupInput"),
          responses: {
            "200": success("MfaSetupResponse"),
            "400": failure("Challenge invalide ou expiré."),
          },
        },
      },
      "/auth/mfa/setup/confirm": {
        post: {
          operationId: "confirmMfaSetup",
          tags: ["MFA"],
          requestBody: body("MfaVerifyInput"),
          responses: {
            "200": success("MfaSetupConfirmResponse"),
            "400": failure("Challenge invalide ou expiré."),
            "401": failure("Code MFA invalide."),
            "429": failure("Limitation temporaire des tentatives."),
          },
        },
      },
      "/auth/mfa/verify": {
        post: {
          operationId: "verifyMfa",
          tags: ["MFA"],
          requestBody: body("MfaVerifyInput"),
          responses: {
            "200": success("AuthenticatedResponse"),
            "400": failure("Challenge invalide ou expiré."),
            "401": failure("Code MFA invalide ou déjà utilisé."),
            "429": failure("Limitation temporaire des tentatives."),
          },
        },
      },
      "/auth/mfa/recovery": {
        post: {
          operationId: "recoverMfa",
          tags: ["MFA"],
          requestBody: body("MfaRecoveryInput"),
          responses: {
            "200": success("AuthenticatedResponse"),
            "400": failure("Challenge invalide ou expiré."),
            "401": failure("Code de récupération invalide ou déjà utilisé."),
            "429": failure("Limitation temporaire des tentatives."),
          },
        },
      },
      "/auth/me": {
        get: {
          ...secured,
          operationId: "getCurrentIdentity",
          tags: ["Session"],
          responses: {
            "200": success("AuthMeResponse"),
            "401": failure("Session absente, expirée ou révoquée."),
            "403": failure("Second facteur requis."),
          },
        },
      },
      "/auth/refresh": {
        post: {
          ...secured,
          operationId: "rotateSession",
          tags: ["Session"],
          description: csrfNote,
          responses: {
            "200": success("AuthenticatedResponse"),
            "401": failure("Session absente, expirée ou déjà tournée."),
            "403": failure("CSRF requis."),
          },
        },
      },
      "/auth/logout": {
        post: {
          ...secured,
          operationId: "logout",
          tags: ["Session"],
          description: csrfNote,
          responses: {
            "204": noContent,
            "401": failure("Session requise."),
            "403": failure("CSRF requis."),
          },
        },
      },
      "/auth/sessions": {
        get: {
          ...secured,
          operationId: "listActiveSessions",
          tags: ["Session"],
          responses: {
            "200": success("SessionsResponse"),
            "401": failure("Session requise."),
          },
        },
      },
      "/auth/sessions/{id}": {
        delete: {
          ...secured,
          operationId: "revokeOwnSession",
          tags: ["Session"],
          description: csrfNote,
          parameters: [idParameter],
          responses: {
            "204": noContent,
            "401": failure("Session requise."),
            "403": failure("CSRF requis."),
            "404": failure("Session inconnue dans ce compte."),
          },
        },
      },
      "/auth/admin/users/{id}/disable": {
        post: {
          ...secured,
          operationId: "disableUser",
          tags: ["Administration technique"],
          description: csrfNote,
          parameters: [idParameter],
          requestBody: body("OrganizationActionInput"),
          responses: {
            "204": noContent,
            "401": failure("Session requise."),
            "403": failure("Portée ou CSRF refusé."),
          },
        },
      },
      "/auth/admin/users/{id}/sessions/revoke": {
        post: {
          ...secured,
          operationId: "revokeUserSessions",
          tags: ["Administration technique"],
          description: csrfNote,
          parameters: [idParameter],
          requestBody: body("OrganizationActionInput"),
          responses: {
            "204": noContent,
            "401": failure("Session requise."),
            "403": failure("Portée ou CSRF refusé."),
          },
        },
      },
    },
    components: {
      securitySchemes: {
        sessionCookie: {
          type: "apiKey",
          in: "cookie",
          name: "lavision_session",
          description:
            "Jeton opaque dans un cookie HttpOnly, Secure en production et SameSite=Strict.",
        },
        csrfHeader: {
          type: "apiKey",
          in: "header",
          name: "x-csrf-token",
          description: "Jeton CSRF à recopier depuis lavision_csrf.",
        },
      },
      schemas: {
        ...createSchoolOpenApiSchemas(),
        ...createRulesOpenApiSchemas(),
        AccessScope: z.toJSONSchema(accessScopeSchema),
        PermissionGrantInput: z.toJSONSchema(permissionGrantInputSchema),
        PermissionRevokeInput: z.toJSONSchema(permissionRevokeInputSchema),
        PermissionGrantResponse: z.toJSONSchema(permissionGrantResponseSchema),
        RoleAssignmentInput: z.toJSONSchema(roleAssignmentInputSchema),
        RoleRevokeInput: z.toJSONSchema(roleRevokeInputSchema),
        RoleAssignmentResponse: z.toJSONSchema(roleAssignmentResponseSchema),
        AuditQuery: z.toJSONSchema(auditQuerySchema),
        AuditEventResponse: z.toJSONSchema(auditEventSchema),
        AuditEventsResponse: z.toJSONSchema(auditEventsResponseSchema),
        LivenessResponse: z.toJSONSchema(livenessResponseSchema),
        ReadinessResponse: z.toJSONSchema(readinessResponseSchema),
        VersionResponse: z.toJSONSchema(versionResponseSchema),
        ProblemDetails: z.toJSONSchema(problemDetailsSchema),
        LoginInput: z.toJSONSchema(loginInputSchema),
        InvitationInput: z.toJSONSchema(invitationInputSchema),
        OrganizationActionInput: z.toJSONSchema(organizationActionInputSchema),
        AcceptInvitationInput: z.toJSONSchema(acceptInvitationInputSchema),
        PasswordResetRequestInput: z.toJSONSchema(passwordResetRequestInputSchema),
        PasswordResetConfirmInput: z.toJSONSchema(passwordResetConfirmInputSchema),
        MfaSetupInput: z.toJSONSchema(mfaSetupInputSchema),
        MfaVerifyInput: z.toJSONSchema(mfaVerifyInputSchema),
        MfaRecoveryInput: z.toJSONSchema(mfaRecoveryInputSchema),
        AuthUser: z.toJSONSchema(authUserSchema),
        AuthSession: z.toJSONSchema(authSessionSchema),
        AuthenticatedResponse: z.toJSONSchema(authenticatedResponseSchema),
        MfaRequiredResponse: z.toJSONSchema(mfaRequiredResponseSchema),
        MfaSetupResponse: z.toJSONSchema(mfaSetupResponseSchema),
        MfaSetupConfirmResponse: z.toJSONSchema(mfaSetupConfirmResponseSchema),
        MembershipResponse: z.toJSONSchema(membershipResponseSchema),
        AuthMeResponse: z.toJSONSchema(authMeResponseSchema),
        SessionsResponse: z.toJSONSchema(sessionsResponseSchema),
        AcceptedResponse: z.toJSONSchema(acceptedResponseSchema),
        InvitationResponse: z.toJSONSchema(invitationResponseSchema),
      },
    },
  };
}
