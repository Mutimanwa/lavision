import type { ArgumentsHost } from "@nestjs/common";
import { describe, expect, it, vi } from "vitest";
import { ProblemDetailsFilter } from "./problem-details.filter.js";

function createHost() {
  const request = {
    id: "5f0fc5f4-0d84-4cf3-9c6e-ef5a1f1c184f",
    url: "/api/v1/unknown",
  };
  const reply = {
    code: vi.fn().mockReturnThis(),
    header: vi.fn().mockReturnThis(),
    send: vi.fn(),
  };
  const host = {
    switchToHttp: () => ({
      getRequest: () => request,
      getResponse: () => reply,
    }),
  } as unknown as ArgumentsHost;

  return { host, reply };
}

describe("ProblemDetailsFilter", () => {
  it("ne divulgue ni SQL ni pile pour une erreur inconnue", () => {
    const { host, reply } = createHost();
    const filter = new ProblemDetailsFilter();

    filter.catch(new Error("SQL secret et pile interne"), host);

    expect(reply.code).toHaveBeenCalledWith(500);
    const problem = reply.send.mock.calls[0]?.[0];
    expect(problem).toMatchObject({
      type: "about:blank",
      status: 500,
      code: "INTERNAL_ERROR",
      detail: "Une erreur interne est survenue.",
      requestId: "5f0fc5f4-0d84-4cf3-9c6e-ef5a1f1c184f",
    });
    expect(JSON.stringify(problem)).not.toContain("SQL secret");
    expect(JSON.stringify(problem)).not.toContain("stack");
  });
});
