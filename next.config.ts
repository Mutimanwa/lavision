import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  output: "export",
  trailingSlash: true,
  images: { unoptimized: true },
  devIndicators: false,
  allowedDevOrigins: ["*.e2b.app"],
};

export default nextConfig;
