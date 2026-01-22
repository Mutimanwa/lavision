    <!-- Scripts supplémentaires -->
    <?php if (isset($scripts_supplementaires)): ?>
        <?php foreach ($scripts_supplementaires as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Script principal du thème -->
    <script src="<?php echo JS_PATH; ?>/theme.js"></script>
    <script src="<?php echo JS_PATH; ?>/app.js"></script>

    <!-- Script de configuration -->
    <script src="<?php echo JS_PATH; ?>/config.js"></script>

    <!-- Initialisation finale -->
    <script>
        // Masquer le spinner de chargement
        document.getElementById('loading-spinner').classList.add('d-none');

        // Initialisation des tooltips Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Gestion des erreurs JavaScript
        window.addEventListener('error', function(e) {
            console.error('Erreur JavaScript:', e.error);
            // Envoi à un service de monitoring si configuré
        });

        // Gestion des erreurs de promesse non gérées
        window.addEventListener('unhandledrejection', function(e) {
            console.error('Promesse rejetée non gérée:', e.reason);
        });

        // Confirmation avant fermeture de page avec des modifications non sauvegardées
        let hasUnsavedChanges = false;

        window.addEventListener('beforeunload', function(e) {
            if (hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = 'Vous avez des modifications non sauvegardées. Voulez-vous vraiment quitter ?';
                return e.returnValue;
            }
        });

        // Fonction pour marquer les changements comme non sauvegardés
        window.markAsUnsaved = function() {
            hasUnsavedChanges = true;
        };

        // Fonction pour marquer les changements comme sauvegardés
        window.markAsSaved = function() {
            hasUnsavedChanges = false;
        };

        // Auto-save pour les formulaires (optionnel)
        document.addEventListener('input', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
                window.markAsUnsaved();
            }
        });

        // Gestion du thème sombre/clair (si implémenté)
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function() {
                document.body.classList.toggle('dark-theme');
                const isDark = document.body.classList.contains('dark-theme');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            });

            // Restaurer le thème sauvegardé
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-theme');
            }
        }

        // Fonction de recherche globale (si barre de recherche présente)
        const globalSearch = document.getElementById('global-search');
        if (globalSearch) {
            let searchTimeout;
            globalSearch.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const query = this.value.trim();
                    if (query.length >= 3) {
                        // Implémenter la recherche globale
                        console.log('Recherche globale:', query);
                    }
                }, 300);
            });
        }

        // Gestion des raccourcis clavier
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + S pour sauvegarder
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                const saveBtn = document.querySelector('[data-action="save"], .btn-save, #save-btn');
                if (saveBtn) {
                    saveBtn.click();
                }
            }

            // Échap pour fermer les modales
            if (e.key === 'Escape') {
                const modals = document.querySelectorAll('.modal.show');
                modals.forEach(modal => {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        bsModal.hide();
                    }
                });
            }
        });

        // Performance monitoring (optionnel)
        if ('performance' in window && 'timing' in performance) {
            window.addEventListener('load', function() {
                const perfData = performance.timing;
                const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
                console.log('Temps de chargement de la page:', pageLoadTime + 'ms');

                // Envoi à un service de monitoring si configuré
            });
        }

        // Initialisation terminée
        console.log('LaVision - Initialisation terminée');
    </script>
</body>
</html>