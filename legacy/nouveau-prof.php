<?php include 'header.php'; ?>

<!-- main content -->
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row flex-between-center">
                    <div class="col-md">
                        <h5 class="mb-2 mb-md-0">Ajout du professeur dans le systeme</h5>
                    </div>
                    <div class="col-auto">
                        <a href="professeurs.php" class="btn btn-falcon-outline-secondary   me-3 fw-medium"
                            role="button">Annuler</a>
                        <button class="btn btn-primary" role="button">Enregistrer le professeur</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-0">
            <div class="col-lg-8 pe-lg-2">
                <div class="card mb-3">
                    <div class="card-header bg-body-tertiary">
                        <h6 class="mb-0">Informations personnelles</h6>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="row gx-2">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required-field" for="last-name">Nom:</label>
                                    <input class="form-control" id="last-name" type="text" required />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required-field" for="first-name">Prénom:</label>
                                    <input class="form-control" id="first-name" type="text" required />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="birth-date">Date de
                                        naissance:</label>
                                    <input class="form-control datetimepicker flatpickr-input"
                                        data-options="{dateFormat:dd/mm/yy,disableMobile:true}" placeholder="dd/mm/yy"
                                        id="bootstrap-wizard-validation-wizard-datepicker" type="date" />
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="birth-place">Lieu de
                                        naissance:</label>
                                    <input class="form-control" id="birth-place" type="text" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required-field" for="gender">Genre:</label>
                                    <select class="form-select" id="gender" required>
                                        <option value="">Sélectionner</option>
                                        <option value="M">Masculin</option>
                                        <option value="F">Féminin</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required-field" for="nationality">Nationalité:</label>
                                    <input class="form-control" id="nationality" type="text" required />
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-body-tertiary">
                        <h6 class="mb-0">Informations de contact</h6>
                    </div>
                    <div class="card-body">
                        <div class="row gx-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field" for="address">Adresse:</label>
                                <textarea class="form-control" id="address" rows="2"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="city">Ville:</label>
                                <input class="form-control" id="city" type="text" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="phone">Téléphone:</label>
                                <input class="form-control" id="phone" type="tel" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="email">Email:</label>
                                <input class="form-control" id="email" type="email" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-body-tertiary">
                        <h6 class="mb-0">Informations scolaires</h6>
                    </div>
                    <div class="card-body">
                        <div class="row gx-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field" for="school-year">Année
                                    scolaire:</label>
                                <select class="form-select" id="school-year" required>
                                    <option value="">Sélectionner</option>
                                    <option value="2023-2024">2023-2024</option>
                                    <option value="2024-2025">2024-2025</option>
                                    <option value="2025-2026">2025-2026</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field" for="class">Classe:</label>
                                <select class="form-select" id="class" required>
                                    <option value="">Sélectionner</option>
                                    <option value="6ème">6ème</option>
                                    <option value="5ème">5ème</option>
                                    <option value="4ème">4ème</option>
                                    <option value="3ème">3ème</option>
                                    <option value="2nde">Seconde</option>
                                    <option value="1ère">Première</option>
                                    <option value="Tle">Terminale</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="field">Filière:</label>
                                <select class="form-select" id="field">
                                    <option value="">Sélectionner</option>
                                    <option value="S">Scientifique</option>
                                    <option value="L">Littéraire</option>
                                    <option value="ES">Économique et Social</option>
                                    <option value="STI2D">STI2D</option>
                                    <option value="STMG">STMG</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="option">Option:</label>
                                <select class="form-select" id="option">
                                    <option value="">Sélectionner</option>
                                    <option value="latin">Latin</option>
                                    <option value="grec">Grec ancien</option>
                                    <option value="euro">Section européenne</option>
                                    <option value="arts">Arts plastiques</option>
                                    <option value="music">Musique</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-body-tertiary">
                        <h6 class="mb-0">Informations des parents/tuteurs</h6>
                    </div>
                    <div class="card-body">
                        <div class="row gx-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field" for="parent-name">Nom et prénom
                                    du parent/tuteur:</label>
                                <input class="form-control" id="parent-name" type="text" required />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required-field" for="parent-phone">Téléphone du
                                    parent/tuteur:</label>
                                <input class="form-control" id="parent-phone" type="tel" required />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="parent-email">Email du
                                    parent/tuteur:</label>
                                <input class="form-control" id="parent-email" type="email" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="parent-profession">Profession du
                                    parent/tuteur:</label>
                                <input class="form-control" id="parent-profession" type="text" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 ps-lg-2">
                <div class="sticky-sidebar">
                    <div class="card mb-3">
                        <div class="card-header bg-body-tertiary">
                            <h6 class="mb-0">Photo de l'élève</h6>
                        </div>
                        <div class="card-body text-center" data-dropzone="data-dropzone"
                        data-options="{&quot;maxFiles&quot;:1,&quot;data&quot;:[{&quot;name&quot;:&quot;avatar.png&quot;,&quot;size&quot;:&quot;54kb&quot;,&quot;url&quot;:&quot;assets/img/team&quot;}]}">
                            <div class="mb-3 dz-preview dz-preview-single">
                            <div class="dz-preview-cover d-flex align-items-center justify-content-center mb-3 mb-md-0 dz-image-preview">
                                <div class="avatar avatar-4xl"><img class="rounded-circle border border-3 border-primary"
                                        src="assets/img/team/avatar.png" alt="avatar.png"
                                        data-dz-thumbnail="data-dz-thumbnail"></div>
                                <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress=""></span></div>
                            </div>
                            </div>
                           
                            <button data-dz-message="data-dz-message"
                                class="dz-message dropzone-area w-100 btn btn-sm btn-outline-primary">
                                <i class="fas fa-camera me-1"></i> Télécharger une photo
                            </button>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-body-tertiary">
                            <h6 class="mb-0">Documents requis</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="doc-birth-certificate">
                                <label class="form-check-label" for="doc-birth-certificate">Extrait
                                    d'acte de naissance</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="doc-id">
                                <label class="form-check-label" for="doc-id">Photocopie de la carte
                                    d'identité</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="doc-photos">
                                <label class="form-check-label" for="doc-photos">Photos
                                    d'identité</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="doc-bulletins">
                                <label class="form-check-label" for="doc-bulletins">
                                    Extrait du diplome d'etat
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="doc-certificate">
                                <label class="form-check-label" for="doc-certificate">Certificat de
                                    radiation</label>
                            </div>
                            <hr>
                            <button class="btn btn-sm btn-outline-secondary w-100">
                                <i class="fas fa-upload me-1"></i> Télécharger les documents
                            </button>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-body-tertiary">
                            <h6 class="mb-0">Frais de scolarité</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label" for="fees-amount">Montant des frais:</label>
                                <div class="input-group">
                                    <input class="form-control" id="fees-amount" type="number" value="0" disabled>
                                    <span class="input-group-text">FCFA</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="payment-method">Mode de paiement:</label>
                                <select class="form-select" id="payment-method">
                                    <option value="">Sélectionner</option>
                                    <option value="cash">Espèces</option>
                                    <option value="check">Chèque</option>
                                    <option value="transfer">Virement</option>
                                    <option value="mobile">Paiement mobile</option>
                                </select>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="fees-paid">
                                <label class="form-check-label" for="fees-paid">Frais payés</label>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-body-tertiary">
                            <h6 class="mb-0">Statut de l'inscription</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="status-pending" checked>
                                <label class="form-check-label" for="status-pending">En attente</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="status-complete">
                                <label class="form-check-label" for="status-complete">Complète</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="status-approved">
                                <label class="form-check-label" for="status-approved">Approuvée</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <div class="row justify-content-between align-items-center">
                    <div class="col-md">
                        <h5 class="mb-2 mb-md-0">Inscription presque terminée!</h5>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-link text-secondary p-0 me-3 fw-medium" role="button">Annuler</button>
                        <button class="btn btn-primary" role="button">Finaliser l'inscription</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>