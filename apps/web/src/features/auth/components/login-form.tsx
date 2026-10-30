"use client";

import { FormEvent, useState } from "react";
import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { ArrowLeft, ArrowRight, KeyRound, LoaderCircle, LockKeyhole, ShieldCheck, Smartphone } from "lucide-react";
import type { MfaRequiredResponse, MfaSetupResponse } from "@lavision/contracts";
import { ApiError } from "@/lib/api-client";
import { confirmMfaSetup, login, recoverMfa, setupMfa, verifyMfa } from "../services/auth-api";
import { isMfaChallenge, safePostLoginPath } from "../services/auth-flow";

type Step = "credentials" | "mfa" | "recovery" | "recovery-codes";
type BusyAction = "login" | "setup" | "verify" | "recovery" | null;

function authError(error: unknown, fallback: string) {
  if (error instanceof ApiError) {
    if (error.status === 0) return error.message;
    if (error.status === 429) return "Trop de tentatives. Patientez avant de réessayer.";
    return error.message || fallback;
  }
  return fallback;
}

export function LoginForm() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const returnTo = safePostLoginPath(searchParams.get("next"));
  const [step, setStep] = useState<Step>("credentials");
  const [busy, setBusy] = useState<BusyAction>(null);
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [code, setCode] = useState("");
  const [recoveryCode, setRecoveryCode] = useState("");
  const [challenge, setChallenge] = useState<MfaRequiredResponse | null>(null);
  const [setup, setSetup] = useState<MfaSetupResponse | null>(null);
  const [recoveryCodes, setRecoveryCodes] = useState<string[]>([]);
  const [recoverySaved, setRecoverySaved] = useState(false);
  const [error, setError] = useState<string | null>(null);

  function finishAuthentication() {
    setPassword("");
    setChallenge(null);
    setSetup(null);
    router.replace(returnTo);
  }

  async function startSetup(challengeToken: string) {
    setBusy("setup");
    setError(null);
    try {
      setSetup(await setupMfa(challengeToken));
    } catch (cause) {
      setError(authError(cause, "La préparation de la double authentification a échoué."));
    } finally {
      setBusy(null);
    }
  }

  async function handleLogin(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setBusy("login");
    setError(null);
    try {
      const result = await login({ email, password });
      if (isMfaChallenge(result)) {
        setChallenge(result);
        setSetup(null);
        setCode("");
        setStep("mfa");
        if (result.enrollmentRequired) await startSetup(result.challengeToken);
        return;
      }
      finishAuthentication();
    } catch (cause) {
      setError(authError(cause, "La connexion a échoué. Vérifiez vos identifiants et réessayez."));
    } finally {
      setBusy(null);
    }
  }

  async function handleVerify(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!challenge) return;
    if (challenge.enrollmentRequired && !setup) {
      setError("Préparez d’abord votre application d’authentification.");
      return;
    }
    setBusy("verify");
    setError(null);
    try {
      const input = { challengeToken: challenge.challengeToken, code };
      if (challenge.enrollmentRequired) {
        const result = await confirmMfaSetup(input);
        setRecoveryCodes(result.recoveryCodes);
        setRecoverySaved(false);
        setStep("recovery-codes");
      } else {
        await verifyMfa(input);
        finishAuthentication();
      }
    } catch (cause) {
      setError(authError(cause, "Le code n’a pas été accepté. Vérifiez-le et réessayez."));
    } finally {
      setBusy(null);
    }
  }

  async function handleRecovery(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!challenge) return;
    setBusy("recovery");
    setError(null);
    try {
      await recoverMfa({ challengeToken: challenge.challengeToken, recoveryCode });
      finishAuthentication();
    } catch (cause) {
      setError(authError(cause, "Le code de récupération n’a pas été accepté."));
    } finally {
      setBusy(null);
    }
  }

  function backToCredentials() {
    setStep("credentials");
    setChallenge(null);
    setSetup(null);
    setCode("");
    setRecoveryCode("");
    setPassword("");
    setError(null);
  }

  function continueAfterRecovery() {
    if (!recoverySaved) return;
    setRecoveryCodes([]);
    finishAuthentication();
  }

  return (
    <div className="auth-screen">
      <main className="auth-card">
        <div className="auth-brand-row">
          <Link href="/" className="auth-brand" aria-label="Retour à l’accueil lavision">
            <span className="auth-brand-mark">lv</span>
            <span>lavision</span>
          </Link>
          <span className="auth-secure-label"><LockKeyhole size={14} /> Accès sécurisé</span>
        </div>

        {step === "credentials" && (
          <>
            <div className="auth-heading">
              <p className="eyebrow">VOTRE ESPACE DE TRAVAIL</p>
              <h1>Heureux de vous revoir.</h1>
              <p>Connectez-vous avec le compte fourni par votre établissement pour accéder aux données et à la configuration réelles.</p>
            </div>
            <form className="auth-form" onSubmit={handleLogin}>
              <label className="form-field">
                <span>Adresse e-mail</span>
                <input type="email" autoComplete="username" required value={email} onChange={(event) => setEmail(event.target.value)} placeholder="vous@etablissement.cd" />
              </label>
              <label className="form-field">
                <span>Mot de passe</span>
                <input type="password" autoComplete="current-password" required minLength={12} value={password} onChange={(event) => setPassword(event.target.value)} placeholder="Votre mot de passe" />
              </label>
              {error && <p className="form-error" role="alert">{error}</p>}
              <button type="submit" className="button button-primary auth-submit" disabled={busy !== null}>
                {busy === "login" ? <LoaderCircle className="spin" size={18} /> : <ArrowRight size={18} />}
                {busy === "login" ? "Connexion…" : "Se connecter"}
              </button>
            </form>
            <div className="auth-notice"><ShieldCheck size={18} /><p>La double authentification peut être demandée selon la politique de votre organisation. Vos identifiants ne sont jamais enregistrés dans ce navigateur par lavision.</p></div>
          </>
        )}

        {step === "mfa" && challenge && (
          <>
            <button type="button" className="auth-back" onClick={backToCredentials} disabled={busy !== null}><ArrowLeft size={16} /> Retour à la connexion</button>
            <div className="auth-heading">
              <p className="eyebrow">VÉRIFICATION SUPPLÉMENTAIRE</p>
              <h1>{challenge.enrollmentRequired ? "Activez la double authentification." : "Confirmez votre identité."}</h1>
              <p>{challenge.enrollmentRequired ? "Ajoutez la clé ci-dessous dans votre application d’authentification, puis saisissez le code généré." : "Saisissez le code à six chiffres de votre application d’authentification."}</p>
            </div>
            {challenge.enrollmentRequired && (
              <div className="auth-enrollment">
                {busy === "setup" && <p className="auth-loading"><LoaderCircle className="spin" size={18} /> Préparation de votre clé sécurisée…</p>}
                {!setup && busy !== "setup" && <button type="button" className="button button-secondary" onClick={() => void startSetup(challenge.challengeToken)}><Smartphone size={17} /> Préparer mon application</button>}
                {setup && (
                  <>
                    <div className="auth-secret"><span>Clé à saisir dans l’application</span><code>{setup.secret}</code></div>
                    <details className="auth-uri"><summary>Afficher l’URI de configuration</summary><code>{setup.otpauthUrl}</code></details>
                  </>
                )}
              </div>
            )}
            <form className="auth-form" onSubmit={handleVerify}>
              <label className="form-field">
                <span>Code de vérification</span>
                <input className="auth-code-input" type="text" inputMode="numeric" autoComplete="one-time-code" pattern="[0-9]{6}" maxLength={6} required value={code} onChange={(event) => setCode(event.target.value.replace(/\D/g, "").slice(0, 6))} placeholder="000000" disabled={challenge.enrollmentRequired && !setup} />
              </label>
              {error && <p className="form-error" role="alert">{error}</p>}
              <button type="submit" className="button button-primary auth-submit" disabled={busy !== null || (challenge.enrollmentRequired && !setup)}>
                {busy === "verify" ? <LoaderCircle className="spin" size={18} /> : <ShieldCheck size={18} />}
                {busy === "verify" ? "Vérification…" : challenge.enrollmentRequired ? "Activer et continuer" : "Vérifier le code"}
              </button>
            </form>
            {!challenge.enrollmentRequired && <button type="button" className="auth-secondary-action" onClick={() => { setError(null); setStep("recovery"); }} disabled={busy !== null}><KeyRound size={16} /> Utiliser un code de récupération</button>}
          </>
        )}

        {step === "recovery" && challenge && (
          <>
            <button type="button" className="auth-back" onClick={() => { setError(null); setStep("mfa"); }} disabled={busy !== null}><ArrowLeft size={16} /> Retour au code d’authentification</button>
            <div className="auth-heading">
              <p className="eyebrow">ACCÈS DE SECOURS</p>
              <h1>Utilisez un code de récupération.</h1>
              <p>Chaque code ne fonctionne qu’une seule fois. Après validation, votre session sera ouverte normalement.</p>
            </div>
            <form className="auth-form" onSubmit={handleRecovery}>
              <label className="form-field"><span>Code de récupération</span><input type="text" autoComplete="off" required minLength={8} maxLength={32} value={recoveryCode} onChange={(event) => setRecoveryCode(event.target.value)} placeholder="Saisissez un code non utilisé" /></label>
              {error && <p className="form-error" role="alert">{error}</p>}
              <button type="submit" className="button button-primary auth-submit" disabled={busy !== null}>{busy === "recovery" ? <LoaderCircle className="spin" size={18} /> : <KeyRound size={18} />}{busy === "recovery" ? "Vérification…" : "Valider le code"}</button>
            </form>
          </>
        )}

        {step === "recovery-codes" && (
          <>
            <div className="auth-heading">
              <p className="eyebrow">DOUBLE AUTHENTIFICATION ACTIVÉE</p>
              <h1>Conservez vos codes de récupération.</h1>
              <p>Ces dix codes sont affichés une seule fois. Gardez-les dans un endroit sûr, séparé de votre mot de passe.</p>
            </div>
            <ol className="recovery-code-list" aria-label="Codes de récupération">{recoveryCodes.map((item) => <li key={item}><code>{item}</code></li>)}</ol>
            <label className="auth-confirmation"><input type="checkbox" checked={recoverySaved} onChange={(event) => setRecoverySaved(event.target.checked)} /><span>J’ai conservé ces codes dans un endroit sûr.</span></label>
            <button type="button" className="button button-primary auth-submit" onClick={continueAfterRecovery} disabled={!recoverySaved}>Continuer vers lavision <ArrowRight size={18} /></button>
          </>
        )}

        <p className="auth-footer">Besoin d’un accès ? Contactez l’administration de votre établissement. <Link href="/">Retour à l’accueil</Link></p>
      </main>
    </div>
  );
}
