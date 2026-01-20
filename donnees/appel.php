<?php include 'header.php'; ?>

<div class="row">
    <div class="card mb-3">
        <div class="card-header mb-0">
            <h5 class="mb-0">Choisissez la classe</h5>
        </div>
        <div class="card-body ">
            <div class="row ">
                <div class="col-md-4">
                    <label for="" class="form-label">Option</label>
                    <select name="" class="form-select" id="Option">
                        <option value="" selected="true">Selectionner l'option ...</option>
                        <option value="commercial">Commercial</option>
                        <option value="scientifique">Scientifique</option>
                        <option value="social">Social</option>
                    </select>
                </div>
                <div class="col-md-4" id="classeContainer">
                    <label for="" class="form-label">Classe</label>
                    <select name="" class="form-select" id="classe">
                        <option value="" selected="true">Selectionner la classe ...</option>
                        <option value="1iere">1iere</option>
                        <option value="2eme">2eme</option>
                        <option value="3eme">3eme</option>
                        <option value="4eme">4eme</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="row " id="ClassList">
    <div class="card mb-3" id="ElevesTable"
        data-list="{&quot;valueNames&quot;:[&quot;name&quot;,&quot;matricule&quot;,&quot;option&quot;,&quot;address&quot;,&quot;joined&quot;],&quot;page&quot;:10,&quot;pagination&quot;:true}">
        <div class="card-header">
            <div class="row flex-between-center">
                <div class="col-4 col-sm-auto d-flex align-items-center pe-0">
                    <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">Liste des eleves de la classe de 1iere</h5>
                </div>
                <div class="col-8 col-sm-auto text-end ps-2">
                    <div class="" id="table-Eleves-actions">
                        <div class="d-flex">
                            <a href="presences.php" class="btn btn-falcon-default btn-sm ms-2" type="button">Enregistrer
                                l'appel</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive scrollbar">
                <table class="table table-sm table-bordered table-striped fs-10 mb-0 overflow-hidden">
                    <thead class="bg-200">
                        <tr>
                            <th class="text-900 sort pe-1 align-middle text-center" data-sort="name">Name</th>
                            <th class="text-900 sort pe-1 align-middle text-center" data-sort="matricule">N°
                                Matricule</th>
                            <th class="text-900 sort pe-1 align-middle text-center" data-sort="option">Option
                            </th>
                            <th class="text-900 sort pe-1 align-middle text-center ps-5" data-sort="address"
                                style="min-width: 200px;"> Address</th>
                            <th class="text-900 sort pe-1 align-middle text-center" data-sort="joined">date
                                d'inscrit
                            </th>
                            <th class="align-middle no-sort"></th>
                        </tr>
                    </thead>
                    <tbody class="list" id="table-Eleves-body">
                        <tr class="btn-reveal-trigger">
                            <td class="name align-middle text-center white-space-nowrap py-2"><a href="eleve-details.php">
                                    <div class="d-flex d-flex align-items-center">
                                        <div class="avatar avatar-xl me-2">
                                            <div class="avatar-name rounded-circle"><span>RA</span></div>
                                        </div>
                                        <div class="flex-1">
                                            <h5 class="mb-0 fs-9">Ricky Antony</h5>
                                        </div>
                                    </div>
                                </a></td>
                            <td class="email align-middle text-center py-2 fs-9"><a href="eleve-details.php?id=">EL-5004</a>
                            </td>
                            <td class="phone align-middle text-center white-space-nowrap py-2"><a
                                    href="tel:2012001851">Commercial</a>
                            </td>
                            <td class="address align-middle text-center white-space-nowrap ps-5 py-2">Kele </td>
                            <td class="joined align-middle text-center py-2">30/03/2018</td>
                            <td class="align-middle text-center white-space-nowrap py-2 text-end">


                                <div class="d-flex gap-2">
                                    <label class="d-flex align-items-center gap-2">
                                        <input type="radio" name="presence[5004]" data-bs-toogle="tooltip"
                                            data-bs-emplacement="top" title="Present" value="present"
                                            class="form-check-input fs-8"> 
                                            <span class="mt-1 fs-9">✅</span>
                                    </label>
                                    <label class="d-flex align-items-center gap-2">
                                        <input type="radio" name="presence[5004]" data-bs-toogle="tooltip"
                                            data-bs-emplacement="top" title="Absant" value="absent"
                                            class="form-check-input fs-8">
                                        <span class="mt-1 fs-9">❌</span>
                                    </label>
                                    <label class="d-flex align-items-center gap-2">
                                        <input type="radio" name="presence[5004]" data-bs-toggle="modal"
                                            data-bs-target="#justificationModal" value="justifie"
                                            class="form-check-input fs-8">
                                            <span class="mt-1 fs-9">📑</span>
                                    </label>
                                    <!-- <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                                        data-bs-target="#justificationModal">
                                        Justifier
                                    </button> -->
                                </div>


                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex align-items-center justify-content-center"><button
                class="btn btn-sm btn-falcon-default me-1 disabled" type="button" title="Previous"
                data-list-pagination="prev" disabled=""><svg class="svg-inline--fa fa-chevron-left fa-w-10"
                    aria-hidden="true" focusable="false" data-prefix="fas" data-icon="chevron-left" role="img"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" data-fa-i2svg="">
                    <path fill="currentColor"
                        d="M34.52 239.03L228.87 44.69c9.37-9.37 24.57-9.37 33.94 0l22.67 22.67c9.36 9.36 9.37 24.52.04 33.9L131.49 256l154.02 154.75c9.34 9.38 9.32 24.54-.04 33.9l-22.67 22.67c-9.37 9.37-24.57 9.37-33.94 0L34.52 272.97c-9.37-9.37-9.37-24.57 0-33.94z">
                    </path>
                </svg><!-- <span class="fas fa-chevron-left"></span>  --></button>
            <ul class="pagination mb-0">
                <li class="active"><button class="page" type="button" data-i="1" data-page="10">1</button></li>
                <li><button class="page" type="button" data-i="2" data-page="10">2</button></li>
                <li><button class="page" type="button" data-i="3" data-page="10">3</button></li>
            </ul><button class="btn btn-sm btn-falcon-default ms-1" type="button" title="Next"
                data-list-pagination="next"><svg class="svg-inline--fa fa-chevron-right fa-w-10" aria-hidden="true"
                    focusable="false" data-prefix="fas" data-icon="chevron-right" role="img"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" data-fa-i2svg="">
                    <path fill="currentColor"
                        d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z">
                    </path>
                </svg><!-- <span class="fas fa-chevron-right"></span>  --></button>
        </div>
    </div>
</div>

<!-- modal -->

<div class="modal fade" id="justificationModal" tabindex="-1" aria-labelledby="justificationLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="justificationLabel">Justification d'absence</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <form id="justificationForm">
                    <div class="mb-3">
                        <label for="motif" class="form-label">Motif</label>
                        <textarea id="motif" class="form-control" rows="4"
                            placeholder="Écrivez la justification..."></textarea>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="envoyerJustification()">Envoyer</button>
            </div>

        </div>
    </div>
</div>

<script>
    function envoyerJustification() {
      const motif = document.getElementById("motif").value;
      if(motif.trim() === "") {
        alert("Veuillez entrer une justification !");
        return;
      }
      alert("Justification envoyée : " + motif);
      // Ici tu pourras envoyer les données en AJAX vers ton backend PHP/Node
      document.getElementById("justificationForm").reset();
      const modal = bootstrap.Modal.getInstance(document.getElementById("justificationModal"));
      modal.hide();
    }
  </script>


<?php include 'footer.php'; ?>