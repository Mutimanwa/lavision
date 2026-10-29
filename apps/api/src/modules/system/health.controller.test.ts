import { ServiceUnavailableException } from "@nestjs/common";
import { describe, expect, it, vi } from "vitest";
import type { PrismaService } from "../infrastructure/prisma.service.js";
import { HealthController } from "./health.controller.js";

describe("HealthController", () => {
  it("répond au liveness sans dépendre de PostgreSQL", () => {
    const prisma = { isReady: vi.fn() } as unknown as PrismaService;
    const controller = new HealthController(prisma);

    expect(controller.getLiveness()).toEqual({
      status: "ok",
      service: "lavision-api",
    });
    expect(prisma.isReady).not.toHaveBeenCalled();
  });

  it("vérifie PostgreSQL pour le readiness", async () => {
    const prisma = {
      isReady: vi.fn().mockResolvedValue(undefined),
    } as unknown as PrismaService;
    const controller = new HealthController(prisma);

    await expect(controller.getReadiness()).resolves.toEqual({
      status: "ready",
      service: "lavision-api",
      checks: { database: "up" },
    });
  });

  it("retourne une indisponibilité contrôlée si PostgreSQL échoue", async () => {
    const prisma = {
      isReady: vi.fn().mockRejectedValue(new Error("SQL à ne pas divulguer")),
    } as unknown as PrismaService;
    const controller = new HealthController(prisma);

    await expect(controller.getReadiness()).rejects.toBeInstanceOf(
      ServiceUnavailableException,
    );
  });
});
