import { createServer, request as httpRequest } from "node:http";
import { request as httpsRequest } from "node:https";
import { readFile, stat } from "node:fs/promises";
import { extname, resolve, sep } from "node:path";

const root = resolve("apps/web/out");
const port = Number(process.env.PORT || 3000);
// Proxy côté serveur uniquement : le navigateur conserve toujours des URLs relatives.
const apiProxyTarget = new URL(process.env.API_PROXY_TARGET || "http://127.0.0.1:3001");
if (!["http:", "https:"].includes(apiProxyTarget.protocol)) {
  throw new Error("API_PROXY_TARGET doit utiliser HTTP ou HTTPS.");
}
const hopByHopHeaders = new Set(["connection", "keep-alive", "proxy-authenticate", "proxy-authorization", "te", "trailer", "transfer-encoding", "upgrade"]);
const mimeTypes = {
  ".css": "text/css; charset=utf-8",
  ".html": "text/html; charset=utf-8",
  ".ico": "image/x-icon",
  ".jpeg": "image/jpeg",
  ".jpg": "image/jpeg",
  ".js": "text/javascript; charset=utf-8",
  ".json": "application/json; charset=utf-8",
  ".png": "image/png",
  ".svg": "image/svg+xml",
  ".txt": "text/plain; charset=utf-8",
  ".webp": "image/webp",
  ".woff": "font/woff",
  ".woff2": "font/woff2",
};

async function fileDetails(pathname) {
  let file = resolve(root, `.${pathname}`);
  if (file !== root && !file.startsWith(`${root}${sep}`)) return null;
  try {
    const info = await stat(file);
    if (info.isDirectory()) {
      if (!pathname.endsWith("/")) return { redirect: `${pathname}/` };
      file = resolve(file, "index.html");
    }
    return { file };
  } catch {
    return null;
  }
}

function proxyApi(request, response, url) {
  const target = new URL(`${url.pathname}${url.search}`, apiProxyTarget);
  const headers = { ...request.headers, host: target.host };
  for (const header of hopByHopHeaders) delete headers[header];
  headers["x-forwarded-host"] = request.headers.host || "";
  headers["x-forwarded-proto"] = "http";
  const proxyRequest = (target.protocol === "https:" ? httpsRequest : httpRequest)(target, {
    method: request.method,
    headers,
  }, (upstream) => {
    const upstreamHeaders = Object.fromEntries(
      Object.entries(upstream.headers).filter(([name]) => !hopByHopHeaders.has(name)),
    );
    response.writeHead(upstream.statusCode || 502, upstreamHeaders);
    upstream.pipe(response);
  });
  proxyRequest.on("error", () => {
    if (response.headersSent) {
      response.destroy();
      return;
    }
    response.writeHead(502, { "Content-Type": "application/problem+json; charset=utf-8", "Cache-Control": "no-store" });
    response.end(JSON.stringify({
      title: "Bad Gateway",
      status: 502,
      code: "API_PROXY_UNAVAILABLE",
      detail: "Le proxy Web ne peut pas joindre l’API configurée.",
    }));
  });
  request.pipe(proxyRequest);
}

const server = createServer(async (request, response) => {
  if (!request.url) {
    response.writeHead(400, { "Content-Type": "text/plain; charset=utf-8" });
    response.end("Requête invalide");
    return;
  }
  const requestUrl = new URL(request.url, "http://localhost");
  if (requestUrl.pathname.startsWith("/api/")) {
    proxyApi(request, response, requestUrl);
    return;
  }
  if (!["GET", "HEAD"].includes(request.method || "")) {
    response.writeHead(405, { Allow: "GET, HEAD" });
    response.end("Méthode non autorisée");
    return;
  }

  try {
    const url = new URL(request.url, "http://localhost");
    const pathname = decodeURIComponent(url.pathname);
    const details = await fileDetails(pathname);
    if (details?.redirect) {
      response.writeHead(308, { Location: `${details.redirect}${url.search}` });
      response.end();
      return;
    }

    const notFound = !details;
    const file = notFound ? resolve(root, "404.html") : details.file;
    const content = await readFile(file);
    const extension = extname(file);
    response.writeHead(notFound ? 404 : 200, {
      "Content-Type": mimeTypes[extension] || "application/octet-stream",
      "Cache-Control": pathname.startsWith("/_next/static/")
        ? "public, max-age=31536000, immutable"
        : "no-cache",
      "X-Content-Type-Options": "nosniff",
    });
    response.end(request.method === "HEAD" ? undefined : content);
  } catch {
    response.writeHead(500, { "Content-Type": "text/plain; charset=utf-8" });
    response.end("Impossible de servir l’application. Exécutez npm run build.");
  }
});

server.listen(port, "0.0.0.0", () => {
  console.log(`lavision est disponible sur http://0.0.0.0:${port}`);
});
