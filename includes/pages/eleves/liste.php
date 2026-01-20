<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col-auto ">
                        <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                    </div>
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Élèves</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">856</div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                    </div>
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Nouveaux <small>(ce
                                mois)</small></div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">32</div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card warning h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-user-check fa-2x text-gray-300"></i>
                    </div>
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Présence
                            <small>Aujourd'hui</small>
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">92%</div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card danger h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col-auto">
                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                    </div>
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">En retard
                            <small>paiement</small>
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">18</div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Student List Card -->
<div class="card mb-3">
    <div id=""
        data-list="{&quot;valueNames&quot;:[&quot;name&quot;,&quot;matricule&quot;,&quot;option&quot;,&quot;address&quot;,&quot;joined&quot;],&quot;page&quot;:10,&quot;pagination&quot;:true}">
        <div class="card-header">
            <div class="row justify-content-end g-0">
                <div class="col-auto col-sm-5">
                    <form>
                        <div class="input-group"><input class="form-control form-control-sm shadow-none search"
                                type="search" placeholder="Search..." aria-label="search" />
                            <div class="input-group-text bg-transparent"><span
                                    class="fa fa-search fs-10 text-600"></span></div>
                        </div>
                    </form>
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
                    <tbody class="list">
                        <tr class="btn-reveal-trigger">

                            <td class="name align-middle white-space-nowrap py-2"><a href="eleve-details.php">
                                    <div class="d-flex d-flex align-items-center">
                                        <div class="avatar avatar-xl me-2">
                                            <div class="avatar-name rounded-circle"><span>RA</span></div>
                                        </div>
                                        <div class="flex-1">
                                            <h5 class="mb-0 fs-10">Ricky Antony</h5>
                                        </div>
                                    </div>
                                </a></td>
                            <td class="email align-middle text-center py-2"><a href="eleve-details.php?id=">EL-5004</a>
                            </td>
                            <td class="phone align-middle text-center white-space-nowrap py-2"><a
                                    href="tel:2012001851">Commercial</a>
                            </td>
                            <td class="address align-middle text-center white-space-nowrap ps-5 py-2">2392 Main Avenue, Penasauka,
                                New
                                Jersey 02139</td>
                            <td class="joined align-middle text-center py-2">30/03/2018</td>
                            <td class="align-middle text-center white-space-nowrap py-2 text-end">
                                <div class="dropdown font-sans-serif position-static"><button
                                        class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal" type="button"
                                        id="customer-dropdown-0" data-bs-toggle="dropdown" data-boundary="window"
                                        aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs-10"></span></button>
                                    <div class="dropdown-menu dropdown-menu-end border py-0"
                                        aria-labelledby="customer-dropdown-0" style="">
                                        <div class="py-2"><a class="dropdown-item" href="#!">Edité</a><a
                                                class="dropdown-item text-danger" href="#!">supprimé</a></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-center "><button class="btn btn-sm btn-falcon-default me-1" type="button"
                    title="Previous" data-list-pagination="prev"><span class="fas fa-chevron-left"></span></button>
                <ul class="pagination mb-0"></ul><button class="btn btn-sm btn-falcon-default ms-1" type="button"
                    title="Next" data-list-pagination="next"><span class="fas fa-chevron-right"> </span></button>
            </div>
        </div>
    </div>
</div>
