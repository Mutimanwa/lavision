import { BadRequestException } from "@nestjs/common";
import { ERROR_CODES } from "@lavision/contracts";
import type { ZodType } from "zod";

export function parseInput<T>(schema: ZodType<T>, input: unknown): T {
  const result = schema.safeParse(input);
  if (!result.success) {
    throw new BadRequestException({ code: ERROR_CODES.VALIDATION_ERROR });
  }
  return result.data;
}
