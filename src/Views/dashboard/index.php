<!-- statistiques  -->
<div class="card mb-3">
    <div class="card-body px-xxl-0 pt-4">
        <div class="row g-0">
            <div
                class="col-xxl-3 col-md-6 px-3 text-center border-end-md border-bottom border-bottom-xxl-0 pb-3 p-xxl-0 ps-md-0">
                <div class="icon-circle icon-circle-primary">
                     <span class="fs-7 fas fa-user-graduate text-primary"></span> 
                </div>
                <h4 class="mb-1 font-sans-serif"><span class="text-700 mx-2"
                        data-countup="{&quot;endValue&quot;:&quot;4968&quot;}">480</span><span
                        class="fw-normal text-600">Total des Eleves</span></h4>
                <p class="fs-10 fw-semi-bold mb-0">420 <span class="text-600 fw-normal">année derniere</span></p>
            </div>
            <div
                class="col-xxl-3 col-md-6 px-3 text-center border-end-xxl border-bottom border-bottom-xxl-0 pb-3 pt-4 pt-md-0 pe-md-0 p-xxl-0">
                <div class="icon-circle icon-circle-info">
                 <span class="fs-7 fas fa-chalkboard-teacher text-info"></span>
                </div>
                <h4 class="mb-1 font-sans-serif"><span class="text-700 mx-2"
                        data-countup="{&quot;endValue&quot;:&quot;324&quot;}">50</span><span
                        class="fw-normal text-600">Total des enseignant</span></h4>
                <p class="fs-10 fw-semi-bold mb-0">301 <span class="text-600 fw-normal">année derniere</span></p>
            </div>
            <div
                class="col-xxl-3 col-md-6 px-3 text-center border-end-md border-bottom border-bottom-md-0 pb-3 pt-4 p-xxl-0 pb-md-0 ps-md-0">
                <div class="icon-circle icon-circle-success">
                    <span class="fs-7 fas fa-book-open text-success"></span>
                </div>
                <h4 class="mb-1 font-sans-serif"><span class="text-700 mx-2"
                        data-countup="{&quot;endValue&quot;:&quot;3712&quot;}">7</span><span
                        class="fw-normal text-600">Option / Section</span></h4>
                <p class="fs-10 fw-semi-bold mb-0">20 <span class="text-600 fw-normal">année derniere</span></p>
            </div>
            <div class="col-xxl-3 col-md-6 px-3 text-center pt-4 p-xxl-0 pb-0 pe-md-0">
                <div class="icon-circle icon-circle-warning">
                    <span class="fs-7 fas fa-dollar-sign text-warning"></span> 
                </div>
                <h4 class="mb-1 font-sans-serif"><span class="text-700 mx-2"
                        data-countup="{&quot;endValue&quot;:&quot;1054&quot;}">1500</span><span
                        class="fw-normal text-600">Revenue</span></h4>
                <p class="fs-10 fw-semi-bold mb-0">1500 <span class="text-600 fw-normal">année derniere</span></p>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- Welcome user  -->
<!-- ======================================================== -->
<div class="row g-3 mb-3">
  <div class="col-xxl-6 col-xl-12">
    <div class="row g-3">
      <div class="col-12">
        <div class="card bg-transparent-50 overflow-hidden">
          <div class="card-header position-relative">
            <div class="position-relative z-2">
              <div>
                <h3 class="text-primary mb-1">Hello, Admin !</h3>
                <p>Voici ce qui se passe dans votre établisement</p>
              </div>
              <div class="d-flex justify-content-between py-3">
                <div class="pe-3">
                  <p class="text-600 fs-10 fw-medium">Nouveaux élèves aujourd'hui</p>
                  <h4 class="text-800 mb-0">5</h4>
                </div>
                <div class="ps-3">
                  <p class="text-600 fs-10">Inscriptions totales aujourd'hui</p>
                  <h4 class="text-800 mb-0">15</h4>
                </div>
              </div>
            </div>
          </div>
          <div class="card-body p-0">
            <ul class="mb-0 list-unstyled list-group font-sans-serif">
              
              <!-- Nouvelles inscriptions -->
              <li class="list-group-item mb-0 rounded-0 py-3 px-x1 greetings-item text-700 border-x-0 border-top-0">
                <div class="row flex-between-center">
                  <div class="col">
                    <div class="d-flex">
                      <div class="fas fa-circle mt-1 fs-11 text-primary"></div>
                      <p class="fs-10 ps-2 mb-0"><strong>15 nouvelles</strong> inscriptions aujourd'hui</p>
                    </div>
                  </div>
                  <div class="col-auto d-flex align-items-center">
                    <a class="fs-10 fw-medium" href="eleves/liste.php?filter=new_today">Voir plus<i class="fas fa-chevron-right ms-1 fs-11"></i></a>
                  </div>
                </div>
              </li>


              <!-- Classes sans enseignant -->

              <li class="list-group-item mb-0 rounded-0 py-3 px-x1 greetings-item text-700 border-x-0 border-top-0">
                <div class="row flex-between-center">
                  <div class="col">
                    <div class="d-flex">
                      <div class="fas fa-circle mt-1 fs-11 text-danger"></div>
                      <p class="fs-10 ps-2 mb-0"><strong>3 classes</strong> sans enseignant assigné</p>
                    </div>
                  </div>
                  <div class="col-auto d-flex align-items-center">
                    <a class="fs-10 fw-medium text-danger" href="academique/classes.php?filter=no_teacher">Voir plus<i class="fas fa-chevron-right ms-1 fs-11"></i></a>
                  </div>
                </div>
              </li>


              <!-- Élèves sans classe -->

              <li class="list-group-item mb-0 rounded-0 py-3 px-x1 greetings-item text-700 border-0">
                <div class="row flex-between-center">
                  <div class="col">
                    <div class="d-flex">
                      <div class="fas fa-circle mt-1 fs-11 text-warning"></div>
                      <p class="fs-10 ps-2 mb-0"><strong>8 élèves</strong> sans classe assignée</p>
                    </div>
                  </div>
                  <div class="col-auto d-flex align-items-center">
                    <a class="fs-10 fw-medium" href="eleves/liste.php?filter=no_class">Voir plus<i class="fas fa-chevron-right ms-1 fs-11"></i></a>
                  </div>
                </div>
              </li>
      
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- academic metrics -->
  <div class="col-xxl-6 col-xl-12">
    <div class="card py-3 mb-3">
      <div class="card-body py-3">
        <div class="row g-0">
          <div class="col-6 col-md-4 border-200 border-bottom border-end pb-4">
            <h6 class="pb-1 text-700">Professeurs actifs</h6>
            <p class="font-sans-serif lh-1 mb-1 fs-7">45</p>
          </div>
          <div class="col-6 col-md-4 border-200 border-bottom border-end-md pb-4 ps-3">
            <h6 class="pb-1 text-700">Classes actives</h6>
            <p class="font-sans-serif lh-1 mb-1 fs-7">12</p>
          </div>
          <div class="col-6 col-md-4 border-200 border-bottom border-end border-end-md-0 pb-4 pt-4 pt-md-0 ps-md-3">
            <h6 class="pb-1 text-700">Matières enseignées</h6>
            <p class="font-sans-serif lh-1 mb-1 fs-7">28</p>
          </div>
          <div class="col-6 col-md-4 border-200 border-bottom border-bottom-md-0 border-end-md pt-4 pb-md-0 ps-3 ps-md-0">
            <h6 class="pb-1 text-700">Élèves inscrits</h6>
            <p class="font-sans-serif lh-1 mb-1 fs-7">480</p>
          </div>
          <div class="col-6 col-md-4 border-200 border-bottom-md-0 border-end pt-4 pb-md-0 ps-md-3">
            <h6 class="pb-1 text-700">Examens prévus</h6>
            <p class="font-sans-serif lh-1 mb-1 fs-7">5</p>
          </div>
          <div class="col-6 col-md-4 pb-0 pt-4 ps-3">
            <h6 class="pb-1 text-700">Réunions parents</h6>
            <p class="font-sans-serif lh-1 mb-1 fs-7">2</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>




<!-- ======================================================== -->
<!-- Academic performance  -->
<!-- ======================================================== -->
<div class="row g-3 mb-3">
  <div class="col-xxl-3 col-md-6 col-lg-5">
    <div class="card shopping-cart-bar-min-height h-100">
      <div class="card-header d-flex flex-between-center">
        <h6 class="mb-0">Performance académique</h6>
        <div class="dropdown font-sans-serif btn-reveal-trigger">
          <button class="btn btn-link text-600 btn-sm dropdown-toggle dropdown-caret-none btn-reveal" type="button" id="dropdown-academic-performance" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
            <span class="fas fa-ellipsis-h fs-11"></span>
          </button>
          <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-academic-performance">
            <a class="dropdown-item" href="rapports/rapport_academique.php">Voir tous les rapports</a>
            <a class="dropdown-item" href="rapports/rapport_academique.php?filter=low_performance">Élèves faibles</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="rapports/tableau_bord.php">Tableau de bord</a>
          </div>
        </div>
      </div>
      <div class="card-body py-0 d-flex align-items-center h-100">
        <div class="flex-1">
          <!-- Excellente performance -->
          <div class="row g-0 align-items-center pb-3">
            <div class="col pe-4">
              <h6 class="fs-11 text-600">Excellente</h6>
              <div class="progress" style="height:5px" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar rounded-3 bg-success" style="width: 45%"></div>
              </div>
            </div>
            <div class="col-auto text-end">
              <p class="mb-0 text-900 font-sans-serif">45%</p>
              <p class="mb-0 fs-11 text-500 fw-semi-bold">216 élèves</p>
            </div>
          </div>

          <!-- Bonne performance -->
          <div class="row g-0 align-items-center pb-3 border-top pt-3">
            <div class="col pe-4">
              <h6 class="fs-11 text-600">Bonne</h6>
              <div class="progress" style="height:5px" role="progressbar" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar rounded-3 bg-info" style="width: 35%"></div>
              </div>
            </div>
            <div class="col-auto text-end">
              <p class="mb-0 text-900 font-sans-serif">35%</p>
              <p class="mb-0 fs-11 text-500 fw-semi-bold">168 élèves</p>
            </div>
          </div>

          <!-- À améliorer -->
          <div class="row g-0 align-items-center pb-3 border-top pt-3">
            <div class="col pe-4">
              <h6 class="fs-11 text-600">À améliorer</h6>
              <div class="progress" style="height:5px" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar rounded-3 bg-warning" style="width: 20%"></div>
              </div>
            </div>
            <div class="col-auto text-end">
              <p class="mb-0 text-900 font-sans-serif">20%</p>
              <p class="mb-0 fs-11 text-500 fw-semi-bold">96 élèves</p>
            </div>
          </div>


        </div>
      </div>
    </div>
  </div>
  
  <!-- recent orders -->
  <div class="col-xxl-9 col-md-12">
    <div class="card z-1" id="recentPurchaseTable" data-list='{"valueNames":["name","email","product","payment","amount"],"page":7,"pagination":true}'>
      <div class="card-header">
        <div class="row flex-between-center">
          <div class="col-6 col-sm-auto d-flex align-items-center pe-0">
            <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">Inscription récentes</h5>
          </div>
          <div class="col-6 col-sm-auto ms-auto text-end ps-0">
            <div class="d-none" id="table-purchases-actions">
              <div class="d-flex">
                <select class="form-select form-select-sm" aria-label="Bulk actions">
                  <option selected="">Actions groupées</option>
                  <option value="Refund">Rembourser</option>
                  <option value="Delete">Supprimer</option>
                  <option value="Archive">Archiver</option>
                </select>
                <button class="btn btn-falcon-default btn-sm ms-2" type="button">Appliquer</button>
              </div>
            </div>
            <div id="table-purchases-replace-element">
              <a href="add_order.php" class="btn btn-falcon-default btn-sm" type="button">
                <span class="fas fa-plus" data-fa-transform="shrink-3 down-2"></span>
                <span class="d-none d-sm-inline-block ms-1">Nouveau</span>
              </a>
              <a href="order-list.php" class="btn btn-falcon-default btn-sm mx-2" type="button">
                <span class="fas fa-filter" data-fa-transform="shrink-3 down-2"></span>
                <span class="d-none d-sm-inline-block ms-1">Tout voir</span>
              </a>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body px-0 py-0">
        <div class="table-responsive scrollbar">
          <table class="table table-sm fs-10 mb-0 overflow-hidden">
            <thead class="bg-200">
              <tr>
                <th class="white-space-nowrap">
                  <div class="form-check mb-0 d-flex align-items-center">
                    <input class="form-check-input" id="checkbox-bulk-purchases-select" type="checkbox" data-bulk-select='{"body":"table-purchase-body","actions":"table-purchases-actions","replacedElement":"table-purchases-replace-element"}' />
                  </div>
                </th>
                <th class="text-900 sort pe-1 align-middle white-space-nowrap" data-sort="name">Client</th>
                <th class="text-900 sort pe-1 align-middle white-space-nowrap" data-sort="email">Email</th>
                <th class="text-900 sort pe-1 align-middle white-space-nowrap" data-sort="date">Date</th>
                <th class="text-900 sort pe-1 align-middle white-space-nowrap text-center" data-sort="payment">Statut</th>
                <th class="text-900 sort pe-1 align-middle white-space-nowrap text-end" data-sort="amount">Montant</th>
                <th class="no-sort pe-1 align-middle data-table-row-action"></th>
              </tr>
            </thead>
            <tbody class="list" id="table-purchase-body">

                <tr>
                  <td colspan="7" class="text-center py-3">Aucune commande récente trouvée</td>
                </tr>
       
                  <tr class="btn-reveal-trigger d-none">
                    <td class="align-middle" style="width: 28px;">
                      <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" id="recent-purchase-1" data-bulk-select-row="data-bulk-select-row" />
                      </div>
                    </td>
                    <th class="align-middle white-space-nowrap name">
                      <a href="customer-details.php?id=">
                        sss
                      </a>
                    </th>
                    <td class="align-middle white-space-nowrap email">
                        
                    </td>
                    <td class="align-middle white-space-nowrap date">
                      23/06/2024
                    </td>
                    <td class="align-middle text-center fs-9 white-space-nowrap payment">
                      <span class="badge badge rounded-pill ">
                       
                        <span class="ms-1" data-fa-transform="shrink-2"></span>
                      </span>
                    </td>
                    <td class="align-middle text-end amount">
                      30 Fbu
                    </td>
                    <td class="align-middle white-space-nowrap text-end">
                      <div class="dropstart font-sans-serif position-static d-inline-block">
                        <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal float-end" type="button" id="dropdown-recent-purchase-table-1" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                          <span class="fas fa-ellipsis-h fs-10"></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-recent-purchase-table-1">
                          <a class="dropdown-item" href="order-details.php?id=">Voir</a>
                          <div class="dropdown-divider"></div>
                          <a class="dropdown-item" href="#!" data-bs-toggle="modal" data-bs-target="#updateStatusModal">Changer statut</a>
                          <a class="dropdown-item text-danger" href="#!" data-bs-toggle="modal" data-bs-target="#deleteOrderModal">Supprimer</a>
                        </div>
                      </div>
                      
                      <!-- Modal pour changer le statut -->
                      <div class="modal fade" id="updateStatusModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title">Changer statut - Commande #</h5>
                              <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="update_order_status.php" method="post">
                              <div class="modal-body">
                                <input type="hidden" name="order_id" value="">
                                <div class="mb-3">
                                  <label class="form-label">Statut actuel</label>
                                  <div>
                                    <span class="badge">
                                      actif
                                    </span>
                                  </div>
                                </div>
                                <div class="mb-3">
                                  <label for="status" class="form-label">Nouveau statut</label>
                                  <select class="form-select" id="status" name="status">
                                    <option value="pending">En attente</option>
                                    <option value="processing">En traitement</option>
                                    <option value="shipped">Expédiée</option>
                                    <option value="delivered">Livrée</option>
                                    <option value="cancelled">Annulée</option>
                                  </select>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Annuler</button>
                                <button class="btn btn-primary" type="submit">Mettre à jour</button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card-footer">
        <div class="row align-items-center">
          <div class="pagination d-none"></div>
          <div class="col">
            <p class="mb-0 fs-10"><span class="d-none d-sm-inline-block me-2" data-list-info="data-list-info"></span></p>
          </div>
          <div class="col-auto d-flex">
            <button class="btn btn-sm btn-falcon-default" type="button" data-list-pagination="prev">
              <span>Précédent</span>
            </button>
            <button class="btn btn-sm btn-falcon-default px-4 ms-2" type="button" data-list-pagination="next">
              <span>Suivant</span>
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- ======================================================== -->
<!-- footer  -->
<!-- ======================================================== -->
