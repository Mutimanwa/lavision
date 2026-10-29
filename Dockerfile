FROM node:22-alpine AS build
WORKDIR /app

COPY package.json package-lock.json tsconfig.base.json ./
COPY apps/web/package.json ./apps/web/package.json
COPY apps/api/package.json ./apps/api/package.json
COPY packages/contracts/package.json ./packages/contracts/package.json
COPY packages/testing/package.json ./packages/testing/package.json
RUN npm ci

COPY apps/web ./apps/web
RUN npm run build:web

FROM nginx:1.27-alpine AS runtime
COPY --from=build /app/apps/web/out /usr/share/nginx/html
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
