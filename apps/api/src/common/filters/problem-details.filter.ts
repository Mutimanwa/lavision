import {
  ArgumentsHost,
  Catch,
  ExceptionFilter,
  HttpException,
} from "@nestjs/common";
import {
  ERROR_CODES,
  errorCodeSchema,
  type ProblemDetails,
} from "@lavision/contracts";
import type { FastifyReply, FastifyRequest } from "fastify";

const messages: Record<number, { title: string; detail: string; code: string }> = {
  400: {
    title: "Requête invalide",
    detail: "La requête n’est pas valide.",
    code: ERROR_CODES.VALIDATION_ERROR,
  },
  404: {
    title: "Non trouvé",
    detail: "La ressource demandée est introuvable.",
    code: ERROR_CODES.NOT_FOUND,
  },
  503: {
    title: "Service indisponible",
    detail: "La base de données n’est pas disponible.",
    code: ERROR_CODES.DATABASE_UNAVAILABLE,
  },
  500: {
    title: "Erreur interne",
    detail: "Une erreur interne est survenue.",
    code: ERROR_CODES.INTERNAL_ERROR,
  },
};

@Catch()
export class ProblemDetailsFilter implements ExceptionFilter {
  catch(exception: unknown, host: ArgumentsHost): void {
    const context = host.switchToHttp();
    const request = context.getRequest<FastifyRequest>();
    const reply = context.getResponse<FastifyReply>();
    const status = exception instanceof HttpException ? exception.getStatus() : 500;
    const fallback = messages[status] ?? messages[500]!;
    const code = this.getExceptionCode(exception) ?? fallback.code;
    const requestId = request.id;

    const problem: ProblemDetails = {
      type: "about:blank",
      title: fallback.title,
      status,
      detail: fallback.detail,
      code: errorCodeSchema.parse(code),
      instance: request.url.startsWith("/") ? request.url : "/",
      requestId,
    };

    void reply
      .code(status)
      .header("content-type", "application/problem+json; charset=utf-8")
      .header("x-request-id", requestId)
      .send(problem);
  }

  private getExceptionCode(exception: unknown): string | undefined {
    if (!(exception instanceof HttpException)) {
      return undefined;
    }

    const response = exception.getResponse();
    if (typeof response !== "object" || response === null || !("code" in response)) {
      return undefined;
    }

    const result = errorCodeSchema.safeParse(response.code);
    return result.success ? result.data : undefined;
  }
}
