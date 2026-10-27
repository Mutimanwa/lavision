import { Eye } from "lucide-react";

export function Logo({ compact = false }: { compact?: boolean }) {
  return (
    <span className="brand" aria-label="LaVision">
      <span className="brand-mark" aria-hidden="true">
        <Eye size={23} strokeWidth={1.9} />
        <span className="brand-spark" />
      </span>
      {!compact && (
        <span className="brand-copy">
          <strong>LaVision</strong>
          <small>Gestion scolaire</small>
        </span>
      )}
    </span>
  );
}
