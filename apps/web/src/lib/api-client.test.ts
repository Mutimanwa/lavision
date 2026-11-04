import { afterEach, describe, expect, it, vi } from "vitest";
import { ApiError, apiFetch } from "./api-client";

afterEach(() => {
  vi.unstubAllGlobals();
  vi.restoreAllMocks();
});

describe("client API navigateur", () => {
  it("utilise les cookies de session et le chemin API relatif", async () => {
    const fetchMock = vi.fn().mockResolvedValue(new Response('{"user":{}}', { status: 200 }));
    vi.stubGlobal("fetch", fetchMock);
    vi.stubGlobal("document", { cookie: "lavision_csrf=jeton%20csrf" });

    await apiFetch("/auth/me");

    expect(fetchMock).toHaveBeenCalledWith("/api/v1/auth/me", expect.objectContaining({
      method: "GET",
      credentials: "include",
    }));
    const headers = fetchMock.mock.calls[0][1].headers as Headers;
    expect(headers.get("x-csrf-token")).toBeNull();
  });

  it("ajoute le jeton CSRF aux mutations sans le stocker", async () => {
    const fetchMock = vi.fn().mockResolvedValue(new Response('{"valid":true}', { status: 200 }));
    vi.stubGlobal("fetch", fetchMock);
    vi.stubGlobal("document", { cookie: "other=1; lavision_csrf=jeton%20csrf" });

    await apiFetch("/rules/validate", { method: "POST", body: "{}" });

    const headers = fetchMock.mock.calls[0][1].headers as Headers;
    expect(headers.get("x-csrf-token")).toBe("jeton csrf");
    expect(headers.get("content-type")).toBe("application/json");
  });

  it("refuse explicitement une origine desktop sans pont API au lieu de simuler une session", async () => {
    const fetchMock = vi.fn();
    vi.stubGlobal("fetch", fetchMock);
    vi.stubGlobal("window", { __TAURI_INTERNALS__: {}, location: { protocol: "tauri:", hostname: "localhost" } });

    await expect(apiFetch("/auth/me")).rejects.toMatchObject({ status: 0, code: "API_ORIGIN_UNAVAILABLE" });
    expect(fetchMock).not.toHaveBeenCalled();
  });

  it("signale une page statique renvoyée à la place de l’API", async () => {
    vi.stubGlobal("fetch", vi.fn().mockResolvedValue(new Response("<html>export statique</html>", { status: 200 })));
    await expect(apiFetch("/auth/me")).rejects.toMatchObject({ status: 502, code: "INVALID_API_RESPONSE" });
  });

  it("conserve le détail et les problèmes structurés de l’API", async () => {
    vi.stubGlobal("fetch", vi.fn().mockResolvedValue(new Response(JSON.stringify({
      detail: "Valeur refusée",
      code: "VALIDATION_ERROR",
      requestId: "req-1",
      issues: [{ path: "value", code: "TOO_HIGH", message: "Hors limite" }],
    }), { status: 400 })));

    const request = apiFetch("/rules/validate", { method: "POST", body: "{}" });
    await expect(request).rejects.toMatchObject({
      name: "ApiError",
      status: 400,
      code: "VALIDATION_ERROR",
      requestId: "req-1",
      issues: [{ path: "value", code: "TOO_HIGH", message: "Hors limite" }],
    });
    await request.catch((error: unknown) => {
      expect(error).toBeInstanceOf(ApiError);
      expect((error as ApiError).message).toBe("Valeur refusée");
    });
  });
});
