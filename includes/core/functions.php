<?php
// includes/core/functions.php

/**
 * Vérifie si une chaîne est un UUID valide
 */
function is_uuid($string) {
    $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    return preg_match($pattern, $string) === 1;
}

/**
 * Génère une URL pour le routing avec support des UUID
 */
function url($page, $params = []) {
    // Si un UUID est présent dans les paramètres, on le place dans le chemin
    if (isset($params['uuid']) && is_uuid($params['uuid'])) {
        $uuid = $params['uuid'];
        unset($params['uuid']);
        
        // Construction de l'URL avec UUID dans le chemin
        $url = "index.php?page=" . urlencode($page . '/' . $uuid);
        if (!empty($params)) {
            $url .= "&" . http_build_query($params);
        }
    } else {
        // URL normale
        $url = "index.php?page=" . urlencode($page);
        if (!empty($params)) {
            $url .= "&" . http_build_query($params);
        }
    }
    return $url;
}

/**
 * Vérifie si la page actuelle est active
 */
function is_active($page_name) {
    $current = $_GET['page'] ?? 'dashboard';
    
    // Pour les pages parentes (ex: "eleves" active pour "eleves/admission")
    if (strpos($current, $page_name . '/') === 0) {
        return 'active';
    }
    
    // Comparaison exacte
    if ($current === $page_name) {
        return 'active';
    }
    
    // Pour les URLs avec ID (ex: "eleves/123")
    $parts = explode('/', $current);
    if (isset($parts[0]) && $parts[0] === $page_name) {
        return 'active';
    }
    
    return '';
}

/**
 * Génère un chemin vers les assets
 */
function asset($path) {
    // Enlever le slash initial si présent
    $path = ltrim($path, '/');
    
    // Déterminer si on est en local ou production
    $base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    
    // Retourner le chemin complet
    return $base . '/public/assets/' . $path;
}



/**
 * Affiche un chemin d'upload
 */
function upload_path($file = '') {
    $path = '/public/uploads/' . ltrim($file, '/');
    return $path;
}


