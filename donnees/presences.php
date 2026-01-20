<?php include 'header.php' ?>

<div class="row">
    <div class="col-xl-4  col-md-6 mb-4">
        <div class="card stat-card primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col-auto ">
                        <i class="fas fa-user-check fa-2x text-gray-300"></i>
                    </div>
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Presences</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">856</div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card stat-card success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col-auto">
                        <i class=" fas fa-user-times fa-2x text-gray-300"></i>
                    </div>
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Absences <small>(ce
                                mois)</small></div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">32</div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card stat-card warning h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col-auto">
                        <i class="  fas fa-user-astronaut fa-2x text-gray-300"></i>
                    </div>
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Absences
                            <small>justifier</small>
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">92%</div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-0">
<div class="col-lg-3 pe-lg-2">
        <div class="sticky-sidebar">
            <div class="card mb-lg-0">
                <div class="card-header">
                    <h5 class="mb-0">Trie les information</h5>
                </div>
                <div class="card-body bg-body-tertiary">
                    <div class="mb-3">
                    <form>
                        <div class="input-group"><input class="form-control form-control-sm shadow-none search" type="search" placeholder="Search..." aria-label="search">
                            <div class="input-group-text bg-transparent">
                                <span class="fa fa-search fs-10 text-600"></span> 
                        </div>
                        </div>
                    </form>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex flex-between-center"><label class="form-label"
                                for="organizer">Options</label>
                        </div><select class="form-select js-choice">
                            <option value="">Selectioner l'option...</option>
                            <option>Commercial</option>
                            <option>Scientifique</option>
                            <option>Social</option>
                            <option>Pedagogie</option>
                        </select>
                    </div>

                    <div class="mb-3"><label class="form-label" for="event-type">Classes </label>
                        <select class="form-select" id="event-type" name="event-type">
                            <option>Select la classe ...</option>
                            <option>1ière</option>
                            <option>2ieme</option>
                            <option value="">3ieme</option>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label" for="event-topic">Status</label>
                        <select
                            class="form-select">
                            <option value="" selected="selected">Selectioner la status</option>
                            <option value="">Presents</option>
                            <option value="">Absents</option>
                            <option value="">Justifier</option>
                           
                        </select>
                    </div>
            
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-9 ps-lg-2">
        <div class="card mb-3" id="ElevesTable"
            data-list="{&quot;valueNames&quot;:[&quot;name&quot;,&quot;matricule&quot;,&quot;option&quot;,&quot;address&quot;,&quot;joined&quot;],&quot;page&quot;:10,&quot;pagination&quot;:true}">
            <div class="card-header">
                <div class="row flex-between-center">
                    <div class="col-4 col-sm-auto d-flex align-items-center pe-0">
                        <h5 class="fs-9 mb-0 text-nowrap py-2 py-xl-0">Liste des eleves présent</h5>
                    </div>
                    <div class="col-8 col-sm-auto text-end ps-2">
                        <div class="" id="table-Eleves-actions">
                            <div class="d-flex">
                                <a href="appel.php" class="btn btn-falcon-default btn-sm ms-2" type="button">Faire l'appel</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive scrollbar">
                    <table class="table table-sm table-striped table-bordered fs-10 mb-0 overflow-hidden">
                        <thead class="bg-200">
                            <tr>
                            
                                <th class="text-900 sort pe-1 align-middle text-center" data-sort="name">Name
                                </th>
                                <th class="text-900 sort pe-1 align-middle text-center" data-sort="matricule">N°
                                    Matricule</th>
                                <th class="text-900 sort pe-1 align-middle text-center" data-sort="option">Option
                                </th>
                                <th class="text-900 sort pe-1 align-middle text-center ps-5" data-sort="address"
                                    style="min-width: 200px;"> Classe</th>
                                <th class="text-900 sort pe-1 align-middle text-center" data-sort="joined">Jours
                                </th>
                            </tr>
                        </thead>
                        <tbody class="list" id="table-Eleves-body">
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
                                <td class="address align-middle text-center white-space-nowrap ps-5 py-2">3ieme</td>
                                <td class="joined align-middle text-center py-2">Lundi 30/03/2018</td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-center">
                <button class="btn btn-sm btn-falcon-default me-1 disabled" type="button" title="Previous"
                    data-list-pagination="prev" disabled="">
                    <span class="fas fa-chevron-left"></span> 
                </button>
                <ul class="pagination mb-0">
                    <li class="active"><button class="page" type="button" data-i="1" data-page="10">1</button></li>
                    <li><button class="page" type="button" data-i="2" data-page="10">2</button></li>
                    <li><button class="page" type="button" data-i="3" data-page="10">3</button></li>
                </ul><button class="btn btn-sm btn-falcon-default ms-1" type="button" title="Next" data-list-pagination="next">
                    <span class="fas fa-chevron-right"></span> </button>
            </div>
        </div>
    </div>

</div>

<?php include 'footer.php' ?>