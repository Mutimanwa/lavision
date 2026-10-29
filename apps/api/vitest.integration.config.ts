import { defineConfig } from "vitest/config";

export default defineConfig({
  test: {
    environment: "node",
    include: ["test/integration/**/*.test.ts"],
    fileParallelism: false,
    pool: "forks",
    testTimeout: 30_000,
    hookTimeout: 30_000,
  },
});
