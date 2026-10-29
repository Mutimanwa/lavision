import Link from "next/link";
import { ArrowLeft, Compass } from "lucide-react";

export default function NotFound() {
  return (
    <div className="empty-state not-found">
      <span className="empty-state-icon"><Compass size={30} /></span>
      <p className="eyebrow">Erreur 404</p>
      <h1>Cette page est introuvable</h1>
      <p>Le contenu demandé n’existe pas ou a été déplacé.</p>
      <Link className="button button-primary" href="/"><ArrowLeft size={17} /> Retour au tableau de bord</Link>
    </div>
  );
}
