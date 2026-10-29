FROM node:22-alpine AS build
WORKDIR /app

COPY package.json package-lock.json tsconfig.base.json ./
COPY apps/web/package.json ./apps/web/package.json
COPY apps/api/package.json ./apps/api/package.json
COPY apps/worker/package.json ./apps/worker/package.json
COPY packages/contracts/package.json ./packages/contracts/package.json
COPY packages/testing/package.json ./packages/testing/package.json
RUN npm ci

COPY . .
RUN npm run build

FROM node:22-alpine AS api
WORKDIR /app
ENV NODE_ENV=production

COPY package.json package-lock.json tsconfig.base.json ./
COPY apps/web/package.json ./apps/web/package.json
COPY apps/api/package.json ./apps/api/package.json
COPY apps/worker/package.json ./apps/worker/package.json
COPY packages/contracts/package.json ./packages/contracts/package.json
COPY packages/testing/package.json ./packages/testing/package.json
RUN npm ci --omit=dev && npm cache clean --force

COPY --from=build /app/packages/contracts/dist ./packages/contracts/dist
COPY --from=build /app/apps/api/dist ./apps/api/dist
COPY --from=build /app/apps/worker/dist ./apps/worker/dist
# Prisma peut générer des artefacts non TypeScript (notamment WASM) qui ne
# sont pas copiés par tsc. Ils sont nécessaires au runtime des adaptateurs.
COPY --from=build /app/apps/api/src/generated/prisma ./apps/api/dist/generated/prisma

USER node
EXPOSE 3001
CMD ["node", "apps/api/dist/main.js"]

FROM api AS worker
EXPOSE 3002
CMD ["node", "apps/worker/dist/main.js"]

FROM build AS migrate
WORKDIR /app
CMD ["npm", "run", "prisma:migrate"]

FROM nginx:1.27-alpine AS web
COPY deploy/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/apps/web/out /usr/share/nginx/html
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
