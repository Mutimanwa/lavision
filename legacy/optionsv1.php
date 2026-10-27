<?php include 'header.php' ; ?> 

            <div class="content">
                <!-- Navigation bar from header.php would be included here -->
                
                <div class="card mb-3">
                    <div class="card-header ">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="mb-0">Gestion des Options</h5>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-falcon-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addOptionModal">
                                    <span class="fas fa-plus me-1"></span>Nouvelle Option
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="card option-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h5 class="card-title mb-1">Scientifique</h5>
                                            <span class="badge bg-success badge-status">Active</span>
                                        </div>
                                        <p class="card-text text-700">Option pour les élèves intéressés par les sciences et les mathématiques</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">25 élèves inscrits</small>
                                            <div class="action-buttons">
                                                <button class="btn btn-falcon-info btn-sm" data-bs-toggle="tooltip" title="Modifier">
                                                    <span class="fas fa-edit"></span>
                                                </button>
                                                <button class="btn btn-falcon-danger btn-sm" data-bs-toggle="tooltip" title="Supprimer">
                                                    <span class="fas fa-trash"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="card option-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h5 class="card-title mb-1">Littéraire</h5>
                                            <span class="badge bg-success badge-status">Active</span>
                                        </div>
                                        <p class="card-text text-700">Option axée sur les langues, la littérature et les arts</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">18 élèves inscrits</small>
                                            <div class="action-buttons">
                                                <button class="btn btn-falcon-info btn-sm" data-bs-toggle="tooltip" title="Modifier">
                                                    <span class="fas fa-edit"></span>
                                                </button>
                                                <button class="btn btn-falcon-danger btn-sm" data-bs-toggle="tooltip" title="Supprimer">
                                                    <span class="fas fa-trash"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4 col-md-6 mb-3">
                                <div class="card option-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h5 class="card-title mb-1">Économique</h5>
                                            <span class="badge bg-warning badge-status">Inactive</span>
                                        </div>
                                        <p class="card-text text-700">Option spécialisée en sciences économiques et sociales</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">0 élèves inscrits</small>
                                            <div class="action-buttons">
                                                <button class="btn btn-falcon-info btn-sm" data-bs-toggle="tooltip" title="Modifier">
                                                    <span class="fas fa-edit"></span>
                                                </button>
                                                <button class="btn btn-falcon-danger btn-sm" data-bs-toggle="tooltip" title="Supprimer">
                                                    <span class="fas fa-trash"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <nav aria-label="Page navigation example">
                            <ul class="pagination justify-content-center mt-4">
                                <li class="page-item disabled"><a class="page-link" href="#" tabindex="-1" aria-disabled="true">Précédent</a></li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item"><a class="page-link" href="#">Suivant</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
            
            <!-- Modal pour ajouter une nouvelle option -->
            <div class="modal fade" id="addOptionModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Ajouter une nouvelle option</h5>
                            <button class="btn p-1" type="button" data-bs-dismiss="modal" aria-label="Close">
                                <span class="fas fa-times fs-9"></span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form>
                                <div class="mb-3">
                                    <label class="form-label" for="optionName">Nom de l'option</label>
                                    <input class="form-control" id="optionName" type="text" placeholder="Saisir le nom de l'option" />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="optionDescription">Description</label>
                                    <textarea class="form-control" id="optionDescription" rows="3" placeholder="Description de l'option"></textarea>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" id="optionStatus" type="checkbox" checked />
                                        <label class="form-check-label" for="optionStatus">Option active</label>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-falcon-secondary" type="button" data-bs-dismiss="modal">Annuler</button>
                            <button class="btn btn-falcon-primary" type="button">Enregistrer</button>
                        </div>
                    </div>
                </div>
            </div>
            
    <script>
        
        // Gestion de la soumission du formulaire
        document.querySelector('#addOptionModal .btn-falcon-primary').addEventListener('click', function() {
            const optionName = document.getElementById('optionName').value;
            const optionDescription = document.getElementById('optionDescription').value;
            
            if (optionName && optionDescription) {
                // Ici, vous ajouteriez le code pour envoyer les données au serveur
                alert(`Option "${optionName}" ajoutée avec succès!`);
                bootstrap.Modal.getInstance(document.getElementById('addOptionModal')).hide();
                
                // Réinitialiser le formulaire
                document.getElementById('optionName').value = '';
                document.getElementById('optionDescription').value = '';
            } else {
                alert('Veuillez remplir tous les champs obligatoires.');
            }
        });
    </script>
<?php include 'footer.php'; ?>