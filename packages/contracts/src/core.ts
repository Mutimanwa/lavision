import { z } from "zod";

const codeSchema = z
  .string()
  .trim()
  .min(2)
  .max(32)
  .regex(/^[A-Z0-9][A-Z0-9_-]*$/, "Le code doit utiliser des majuscules, chiffres, tirets ou soulignés.");

const displayNameSchema = z.string().trim().min(1).max(160);

export const coreSeedSchema = z
  .object({
    organization: z
      .object({
        code: codeSchema,
        name: displayNameSchema,
      })
      .strict(),
    site: z
      .object({
        code: codeSchema,
        name: displayNameSchema,
      })
      .strict(),
    user: z
      .object({
        email: z.email().max(320),
        displayName: displayNameSchema,
      })
      .strict(),
  })
  .strict();

export type CoreSeed = z.infer<typeof coreSeedSchema>;
