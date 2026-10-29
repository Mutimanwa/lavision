import { createServer } from "node:http";
import { readFile, stat } from "node:fs/promises";
import { extname, resolve, sep } from "node:path";

const root = resolve("apps/web/out");
const port = Number(process.env.PORT || 3000);
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

const server = createServer(async (request, response) => {
  if (!request.url || !["GET", "HEAD"].includes(request.method || "")) {
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
