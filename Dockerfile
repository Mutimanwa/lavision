FROM node:22-alpine AS build
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY next.config.ts tsconfig.json postcss.config.mjs ./
COPY public ./public
COPY src ./src
RUN npm run build

FROM nginx:1.27-alpine AS runtime
COPY --from=build /app/out /usr/share/nginx/html
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
