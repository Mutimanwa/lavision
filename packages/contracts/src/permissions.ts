export const PERMISSIONS = {
  referentialsAcademicRead: "referentials.academic.read",
  referentialsAcademicDraft: "referentials.academic.draft",
  referentialsAcademicReview: "referentials.academic.review",
  referentialsAcademicApprove: "referentials.academic.approve",
  referentialsAcademicPublish: "referentials.academic.publish",
  rulesAcademicRead: "rules.academic.read",
  rulesAcademicDraft: "rules.academic.draft",
  rulesAcademicSimulate: "rules.academic.simulate",
  rulesAcademicSubmit: "rules.academic.submit",
  rulesAcademicReview: "rules.academic.review",
  rulesAcademicApprove: "rules.academic.approve",
  rulesAcademicPublish: "rules.academic.publish",
} as const;

export type Permission = (typeof PERMISSIONS)[keyof typeof PERMISSIONS];
