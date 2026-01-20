<?php
/**
 * Contrôleur pour la gestion des erreurs
 * LaVision - Système de gestion scolaire
 *
 * Ce contrôleur gère l'affichage des pages d'erreur (404, 403, 500)
 */

class ErrorController extends BaseController {
    /**
     * Affiche la page 404 - Page non trouvée
     */
    public function notFound() {
        http_response_code(404);
        $this->render('errors/404', [
            'error_code' => 404,
            'error_title' => 'Page non trouvée',
            'error_message' => 'La page que vous recherchez n\'existe pas ou a été déplacée.',
            'show_debug' => DEBUG_MODE ?? false
        ], false);
    }

    /**
     * Affiche la page 403 - Accès refusé
     */
    public function forbidden() {
        http_response_code(403);
        $this->render('errors/403', [
            'error_code' => 403,
            'error_title' => 'Accès refusé',
            'error_message' => 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.',
            'show_debug' => DEBUG_MODE ?? false
        ], false);
    }

    /**
     * Affiche la page 500 - Erreur serveur
     */
    public function serverError() {
        http_response_code(500);
        $this->render('errors/500', [
            'error_code' => 500,
            'error_title' => 'Erreur interne du serveur',
            'error_message' => 'Une erreur inattendue s\'est produite. Veuillez contacter l\'administrateur.',
            'show_debug' => DEBUG_MODE ?? false
        ], false);
    }

    /**
     * Affiche une erreur générique
     */
    public function error($code = 500, $title = 'Erreur', $message = 'Une erreur s\'est produite.') {
        http_response_code($code);
        $this->render('errors/error', [
            'error_code' => $code,
            'error_title' => $title,
            'error_message' => $message,
            'show_debug' => DEBUG_MODE ?? false
        ], false);
    }
}
