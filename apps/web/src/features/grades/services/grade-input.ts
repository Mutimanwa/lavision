/**
 * Parsing du champ de note du prototype local.
 * La validation du barème officiel reste confiée au moteur de règles.
 */
export function parseGradeInput(raw: string): number | null {
  const normalized = raw.trim().replace(",", ".");
  if (!normalized) return null;
  const value = Number(normalized);
  return Number.isFinite(value) ? value : null;
}

export function isValidGradeInput(raw: string): boolean {
  if (!raw.trim()) return true;
  const value = parseGradeInput(raw);
  return value !== null && value >= 0 && value <= 20;
}
