import type { CoreSeed } from "@lavision/contracts";

export const coreSeedFixture = {
  organization: {
    code: "DEMO",
    name: "Établissement fictif lavision",
  },
  site: {
    code: "MAIN",
    name: "Site fictif principal",
  },
  user: {
    email: "admin.demo@example.invalid",
    displayName: "Administrateur fictif",
  },
} satisfies CoreSeed;
