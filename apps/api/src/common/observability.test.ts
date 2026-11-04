import { describe, expect, it } from "vitest";
import { MetricsRegistry, metricsTokenMatches } from "./observability.js";

describe("observabilité bornée", () => {
  it("n’accepte que le token technique exact", () => {
    expect(metricsTokenMatches("secret", "secret")).toBe(true);
    expect(metricsTokenMatches("secret", "different")).toBe(false);
    expect(metricsTokenMatches("secret", undefined)).toBe(false);
    expect(metricsTokenMatches(undefined, "secret")).toBe(false);
  });

  it("agrège les routes sans identifiants de requête et expose les métriques processus", () => {
    const registry = new MetricsRegistry();
    registry.recordRequest("GET", "/health/ready", 200, 0.01);
    registry.recordRequest("GET", "/health/ready", 200, 0.02);
    registry.recordRequest("POST", "/auth/login", 401, 0.01);
    const output = registry.render();
    expect(output).toContain("lavision_http_requests_total{method=\"GET\",route=\"/health/ready\",status=\"200\"} 2");
    expect(output).toContain("lavision_http_request_duration_seconds_count{method=\"GET\",route=\"/health/ready\",status=\"200\"} 2");
    expect(output).toContain("lavision_process_uptime_seconds");
    expect(output).not.toContain("requestId");
  });
});
