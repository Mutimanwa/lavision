import "dotenv/config";
import { PrismaPg } from "@prisma/adapter-pg";

import { PrismaClient } from "../generated/prisma/client.js";

export * from "../generated/prisma/client.js";

export function requireDatabaseUrl(): string {
  const databaseUrl = process.env.DATABASE_URL?.trim();

  if (!databaseUrl) {
    throw new Error("DATABASE_URL est requis pour accéder à PostgreSQL.");
  }

  return databaseUrl;
}

export function createPrismaClient(
  databaseUrl: string = requireDatabaseUrl(),
): PrismaClient {
  return new PrismaClient({
    adapter: new PrismaPg({ connectionString: databaseUrl }),
  });
}
