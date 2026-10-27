<?php include 'header.php' ; ?>
          <div class="card shadow-none border">
            <div class="bg-holder bg-card d-none d-md-block" style="background-image:url(../../assets/img/illustrations/reports-bg.png);"></div><!--/.bg-holder-->
            <div class="card-header z-1">
              <div class="row flex-between-center gx-0">
                <div class="col-lg-auto d-flex align-items-center"><img class="img-fluid" src="../../assets/img/illustrations/reports-greeting.png" alt="" />
                  <div class="ms-x1">
                    <h6 class="mb-1 text-primary">Welcome to</h6>
                    <h4 class="mb-0 text-primary fw-bold">Falcon <span class="text-info fw-medium">Support - Reports</span></h4>
                  </div>
                </div>
                <div class="col-lg-auto pt-3 pt-lg-0">
                  <form class="row flex-lg-column flex-xxl-row gx-3 gy-2 align-items-center align-items-lg-start align-items-xxl-center">
                    <div class="col-auto">
                      <h6 class="text-700 mb-0">Showing Data For: </h6>
                    </div>
                    <div class="col-md-auto position-relative"><input class="form-control form-control-sm datetimepicker ps-4" id="reportsDateRange" type="text" data-options="{&quot;mode&quot;:&quot;range&quot;,&quot;dateFormat&quot;:&quot;M d&quot;,&quot;disableMobile&quot;:true , &quot;defaultDate&quot;: [&quot;Jul 10&quot;, &quot;Jul 17&quot;] }" /><span class="fas fa-calendar-alt text-primary position-absolute top-50 translate-middle-y ms-2"> </span></div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          <div class="card overflow-hidden mt-3">
            <div class="card-header p-0 bg-body-tertiary scrollbar-overlay">
              <ul class="nav nav-tabs border-0 tab-tickets-status flex-nowrap" id="in-depth-chart-tab" role="tablist">
                <li class="nav-item text-nowrap" role="presentation"><a class="nav-link mb-0 d-flex align-items-center gap-2 py-3 px-x1 active" id="tickets-created-tab" data-bs-toggle="tab" href="#tickets-created" role="tab" aria-controls="tickets-created" aria-selected="true"><span class="fas fa-ticket-alt icon text-600"></span>
                    <h6 class="mb-0 text-600">Tickets Created<span> (25)</span></h6>
                  </a></li>
                <li class="nav-item text-nowrap" role="presentation"><a class="nav-link mb-0 d-flex align-items-center gap-2 py-3 px-x1" id="tickets-resolved-tab" data-bs-toggle="tab" href="#tickets-resolved" role="tab" aria-controls="tickets-resolved" aria-selected="false"><span class="fas fa-check icon text-600"></span>
                    <h6 class="mb-0 text-600">Tickets Resolved</h6>
                  </a></li>
                <li class="nav-item text-nowrap" role="presentation"><a class="nav-link mb-0 d-flex align-items-center gap-2 py-3 px-x1" id="tickets-reopened-tab" data-bs-toggle="tab" href="#tickets-reopened" role="tab" aria-controls="tickets-reopened" aria-selected="false"><span class="fas fa-envelope-open-text icon text-600"></span>
                    <h6 class="mb-0 text-600">Tickets Reopened</h6>
                  </a></li>
                <li class="nav-item text-nowrap" role="presentation"><a class="nav-link mb-0 d-flex align-items-center gap-2 py-3 px-x1" id="tickets-unsolved-tab" data-bs-toggle="tab" href="#tickets-unsolved" role="tab" aria-controls="tickets-unsolved" aria-selected="false"><span class="fas fa-exclamation-triangle icon text-600"></span>
                    <h6 class="mb-0 text-600">Tickets Unsolved</h6>
                  </a></li>
              </ul>
            </div>
            <div class="card-body p-0">
              <div class="tab-content">
                <div class="tab-pane active" id="tickets-created" role="tabpanel" aria-labelledby="tickets-created-tab">
                  <div class="row mx-0 border-bottom border-dashed">
                    <div class="col-md-6 p-x1 border-end-md border-bottom border-bottom-md-0 border-dashed">
                      <h6 class="fs-10 mb-3">Tickets created Split by Source</h6>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Email</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:33%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">33</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Phone</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:60%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">60</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Website</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:70%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">70</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Chat</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="87" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:87%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">87</p>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6 p-x1">
                      <h6 class="fs-10 mb-3">Tickets created Split by Priority</h6>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Urgent</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="87" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:87%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">87</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Medium</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:70%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">70</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">High</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:60%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">60</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Low</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:33%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">33</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="row mx-0">
                    <div class="col-md-6 p-x1 border-end-md border-bottom border-bottom-md-0 border-dashed">
                      <h6 class="fs-10 mb-3">Tickets created Split by Status</h6>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Open</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:20%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">20</p>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6 p-x1">
                      <h6 class="fs-10 mb-3">Tickets created Split by Category</h6>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Question</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="18" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:18%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">18</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="tab-pane" id="tickets-resolved" role="tabpanel" aria-labelledby="tickets-resolved-tab">
                  <div class="row mx-0 border-bottom border-dashed">
                    <div class="col-md-6 p-x1 border-end-md border-bottom border-bottom-md-0 border-dashed">
                      <h6 class="fs-10 mb-3">Tickets resolved Split by Source</h6>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Email</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:70%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">70</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Phone</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:50%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">50</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Website</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:30%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">30</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Chat</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:95%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">95</p>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6 p-x1">
                      <h6 class="fs-10 mb-3">Tickets resolved Split by Priority</h6>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Urgent</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:70%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">70</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Medium</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:60%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">60</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">High</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:70%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">70</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Low</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:25%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">25</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="row mx-0">
                    <div class="col-md-6 p-x1 border-end-md border-bottom border-bottom-md-0 border-dashed">
                      <h6 class="fs-10 mb-3">Tickets resolved Split by Status</h6>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Open</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:30%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">30</p>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6 p-x1">
                      <h6 class="fs-10 mb-3">Tickets resolved Split by Category</h6>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Question</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:30%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">30</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="tab-pane" id="tickets-reopened" role="tabpanel" aria-labelledby="tickets-reopened-tab">
                  <div class="row mx-0 border-bottom border-dashed">
                    <div class="col-md-6 p-x1 border-end-md border-bottom border-bottom-md-0 border-dashed">
                      <h6 class="fs-10 mb-3">Tickets reopened Split by Source</h6>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Email</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:40%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">40</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Phone</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:40%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">40</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Website</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:50%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">50</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Chat</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:20%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">20</p>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6 p-x1">
                      <h6 class="fs-10 mb-3">Tickets reopened Split by Priority</h6>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Urgent</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:90%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">90</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Medium</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:50%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">50</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">High</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:80%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">80</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Low</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:30%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">30</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="row mx-0">
                    <div class="col-md-6 p-x1 border-end-md border-bottom border-bottom-md-0 border-dashed">
                      <h6 class="fs-10 mb-3">Tickets reopened Split by Status</h6>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Open</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:40%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">40</p>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6 p-x1">
                      <h6 class="fs-10 mb-3">Tickets reopened Split by Category</h6>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Question</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:25%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">25</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="tab-pane" id="tickets-unsolved" role="tabpanel" aria-labelledby="tickets-unsolved-tab">
                  <div class="row mx-0 border-bottom border-dashed">
                    <div class="col-md-6 p-x1 border-end-md border-bottom border-bottom-md-0 border-dashed">
                      <h6 class="fs-10 mb-3">Tickets unsolved Split by Source</h6>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Email</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:50%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">50</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Phone</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:20%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">20</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Website</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:75%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">75</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Chat</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:30%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">30</p>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6 p-x1">
                      <h6 class="fs-10 mb-3">Tickets unsolved Split by Priority</h6>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Urgent</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:85%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">85</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Medium</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:40%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">40</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">High</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:90%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">90</p>
                        </div>
                      </div>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Low</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:20%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">20</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="row mx-0">
                    <div class="col-md-6 p-x1 border-end-md border-bottom border-bottom-md-0 border-dashed">
                      <h6 class="fs-10 mb-3">Tickets unsolved Split by Status</h6>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Open</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:50%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">50</p>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6 p-x1">
                      <h6 class="fs-10 mb-3">Tickets unsolved Split by Category</h6>
                      <div class="row mt-2">
                        <div class="col-3 col-sm-2 col-md-3 col-lg-2">
                          <p class="mb-0 fs-10 fw-semi-bold text-600 text-nowrap">Question</p>
                        </div>
                        <div class="col-9 col-sm-10 col-md-9 col-lg-10 d-flex align-items-center">
                          <div class="progress bg-200 w-100 rounded-pill" role="progressbar" aria-label="Basic example" style="height:6px" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar rounded-pill animated-progress-bar" style="--falcon-progressbar-width:15%"></div>
                          </div>
                          <p class="mb-0 fs-10 ps-3 fw-semi-bold text-600">15</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-footer bg-body-tertiary py-2">
              <div class="row flex-between-center">
                <div class="col-auto"><select class="form-select form-select-sm">
                    <option>Last 7 days</option>
                    <option>Last Month</option>
                    <option>Last Year</option>
                  </select></div>
                <div class="col-auto"><a class="btn btn-link btn-sm px-0 fw-medium" href="#!">View all<span class="fas fa-chevron-right ms-1 fs-11"></span></a></div>
              </div>
            </div>
          </div>
          <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center border-bottom py-2">
              <h6 class="mb-0">Distribution of Performance</h6>
              <div class="dropdown font-sans-serif btn-reveal-trigger"><button class="btn btn-link text-600 btn-sm dropdown-toggle dropdown-caret-none btn-reveal" type="button" id="dropdown-distribution-of-performance" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs-11"></span></button>
                <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-distribution-of-performance"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Export</a>
                  <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="#!">Remove</a>
                </div>
              </div>
            </div>
            <div class="card-body scrollbar">
              <div class="echart-distribution-of-performance" data-echart-responsive="true"></div>
            </div>
            <div class="card-footer bg-body-tertiary py-2">
              <div class="row flex-between-center">
                <div class="col-auto"><select class="form-select form-select-sm">
                    <option>January</option>
                    <option>February</option>
                    <option selected="selected">March</option>
                    <option>April</option>
                    <option>May</option>
                    <option>June</option>
                    <option>July</option>
                    <option>August</option>
                    <option>September</option>
                    <option>October</option>
                    <option>Novenber</option>
                    <option>December</option>
                  </select></div>
                <div class="col-auto"><a class="btn btn-sm btn-falcon-default" href="#!">View All</a></div>
              </div>
            </div>
          </div>
          <div class="card mt-3" id="surveyResultTable" data-list='{"valueNames":["name","satisfied","dissatisfied"],"page":5,"pagination":true}'>
            <div class="card-header border-bottom border-200">
              <div class="row flex-between-center gy-2">
                <div class="col-auto">
                  <h6 class="mb-0">Agent Survey Result</h6>
                </div>
                <div class="col-auto">
                  <div class="d-none" id="table-survey-result-actions">
                    <div class="d-flex"><select class="form-select form-select-sm" aria-label="Bulk actions">
                        <option selected="">Bulk actions</option>
                        <option value="Refund">Refund</option>
                        <option value="Delete">Delete</option>
                        <option value="Archive">Archive</option>
                      </select><button class="btn btn-falcon-default btn-sm ms-2" type="button">Apply</button></div>
                  </div><select class="form-select form-select-sm" id="table-survey-result-replace-element">
                    <option selected="selected">Daily</option>
                    <option>Weekly</option>
                    <option>Monthly</option>
                    <option>Yearly</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive scrollbar">
                <table class="table table-sm fs-10 mb-0">
                  <thead class="bg-body-tertiary">
                    <tr>
                      <th class="py-2 fs-9">
                        <div class="form-check d-flex align-items-center"><input class="form-check-input" id="checkbox-bulk-tickets-select" type="checkbox" data-bulk-select='{"body":"table-survey-result-body","actions":"table-survey-result-actions","replacedElement":"table-survey-result-replace-element"}' /></div>
                      </th>
                      <th class="text-800 sort align-middle" data-sort="name" style="width:30%">Agent Name</th>
                      <th class="text-800 sort align-middle" data-sort="satisfied" style="width:30%">Extremely Satisfied</th>
                      <th class="text-800 sort align-middle" data-sort="dissatisfied">Extremely Dissatisfied</th>
                    </tr>
                  </thead>
                  <tbody class="list" id="table-survey-result-body">
                    <tr>
                      <td class="align-middle py-4 fs-9" style="width: 28px;">
                        <div class="form-check mb-0"><input class="form-check-input" type="checkbox" id="agent-survey-result-0" data-bulk-select-row="data-bulk-select-row" /></div>
                      </td>
                      <td class="align-middle name white-space-nowrap pe-4 w-lg-25">
                        <div class="d-flex align-items-center gap-2 position-relative">
                          <div class="avatar avatar-xl">
                            <div class="avatar-name rounded-circle text-success bg-success-subtle fs-9"><span>A</span></div>
                          </div>
                          <h6 class="mb-0"><a class="stretched-link text-800" href="#!">Amelia</a></h6>
                        </div>
                      </td>
                      <td class="align-middle satisfied white-space-nowrap pe-5 pe-xxl-8">
                        <div class="d-flex align-items-center">
                          <div>
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                              <h5 class="mb-0">36</h5>
                              <h6 class="mb-0 fw-semi-bold text-primary"><span class="fs-11 me-1 ms-2 fas fa-caret-up"></span><span class="fs-10">17</span></h6>
                            </div>
                            <h6 class="mb-0 text-700">Last Day 19</h6>
                          </div>
                          <div class="ms-4">
                            <div class="progress rounded-pill mb-1" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-primary" style="width: 80%"></div>
                            </div>
                            <div class="progress rounded-pill" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-info" style="width: 60%"></div>
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle dissatisfied">
                        <div class="d-flex align-items-center">
                          <div>
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                              <h5 class="mb-0">52</h5>
                              <h6 class="mb-0 fs-11 fw-semi-bold text-danger"><span class="me-1 ms-2 fs-11 fas fa-caret-up"></span><span class="fs-10">33</span></h6>
                            </div>
                            <h6 class="mb-0 text-700 text-nowrap">Last Day 19</h6>
                          </div>
                          <div class="ms-4">
                            <div class="progress rounded-pill mb-1" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-primary" style="width: 60%"></div>
                            </div>
                            <div class="progress rounded-pill" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-info" style="width: 50%"></div>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="align-middle py-4 fs-9" style="width: 28px;">
                        <div class="form-check mb-0"><input class="form-check-input" type="checkbox" id="agent-survey-result-1" data-bulk-select-row="data-bulk-select-row" /></div>
                      </td>
                      <td class="align-middle name white-space-nowrap pe-4 w-lg-25">
                        <div class="d-flex align-items-center gap-2 position-relative">
                          <div class="avatar avatar-xl">
                            <div class="avatar-name rounded-circle text-primary bg-primary-subtle fs-9"><span>B</span></div>
                          </div>
                          <h6 class="mb-0"><a class="stretched-link text-800" href="#!">Bentley</a></h6>
                        </div>
                      </td>
                      <td class="align-middle satisfied white-space-nowrap pe-5 pe-xxl-8">
                        <div class="d-flex align-items-center">
                          <div>
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                              <h5 class="mb-0">41</h5>
                              <h6 class="mb-0 fw-semi-bold text-primary"><span class="fs-11 me-1 ms-2 fas fa-caret-up"></span><span class="fs-10">17</span></h6>
                            </div>
                            <h6 class="mb-0 text-700">Last Day 24</h6>
                          </div>
                          <div class="ms-4">
                            <div class="progress rounded-pill mb-1" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-primary" style="width: 90%"></div>
                            </div>
                            <div class="progress rounded-pill" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-info" style="width: 80%"></div>
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle dissatisfied">
                        <div class="d-flex align-items-center">
                          <div>
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                              <h5 class="mb-0">23</h5>
                              <h6 class="mb-0 fs-11 fw-semi-bold text-primary"><span class="me-1 ms-2 fs-11 fas fa-caret-down"></span><span class="fs-10">1</span></h6>
                            </div>
                            <h6 class="mb-0 text-700 text-nowrap">Last Day 24</h6>
                          </div>
                          <div class="ms-4">
                            <div class="progress rounded-pill mb-1" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-primary" style="width: 95%"></div>
                            </div>
                            <div class="progress rounded-pill" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-info" style="width: 75%"></div>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="align-middle py-4 fs-9" style="width: 28px;">
                        <div class="form-check mb-0"><input class="form-check-input" type="checkbox" id="agent-survey-result-2" data-bulk-select-row="data-bulk-select-row" /></div>
                      </td>
                      <td class="align-middle name white-space-nowrap pe-4 w-lg-25">
                        <div class="d-flex align-items-center gap-2 position-relative">
                          <div class="avatar avatar-xl">
                            <div class="avatar-name rounded-circle text-info bg-info-subtle fs-9"><span>A</span></div>
                          </div>
                          <h6 class="mb-0"><a class="stretched-link text-800" href="#!">Abigail</a></h6>
                        </div>
                      </td>
                      <td class="align-middle satisfied white-space-nowrap pe-5 pe-xxl-8">
                        <div class="d-flex align-items-center">
                          <div>
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                              <h5 class="mb-0">52</h5>
                              <h6 class="mb-0 fw-semi-bold text-primary"><span class="fs-11 me-1 ms-2 fas fa-caret-up"></span><span class="fs-10">27</span></h6>
                            </div>
                            <h6 class="mb-0 text-700">Last Day 25</h6>
                          </div>
                          <div class="ms-4">
                            <div class="progress rounded-pill mb-1" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-primary" style="width: 70%"></div>
                            </div>
                            <div class="progress rounded-pill" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-info" style="width: 80%"></div>
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle dissatisfied">
                        <div class="d-flex align-items-center">
                          <div>
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                              <h5 class="mb-0">12</h5>
                              <h6 class="mb-0 fs-11 fw-semi-bold text-primary"><span class="me-1 ms-2 fs-11 fas fa-caret-down"></span><span class="fs-10">13</span></h6>
                            </div>
                            <h6 class="mb-0 text-700 text-nowrap">Last Day 25</h6>
                          </div>
                          <div class="ms-4">
                            <div class="progress rounded-pill mb-1" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-primary" style="width: 70%"></div>
                            </div>
                            <div class="progress rounded-pill" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-info" style="width: 90%"></div>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="align-middle py-4 fs-9" style="width: 28px;">
                        <div class="form-check mb-0"><input class="form-check-input" type="checkbox" id="agent-survey-result-3" data-bulk-select-row="data-bulk-select-row" /></div>
                      </td>
                      <td class="align-middle name white-space-nowrap pe-4 w-lg-25">
                        <div class="d-flex align-items-center gap-2 position-relative">
                          <div class="avatar avatar-xl">
                            <div class="avatar-name rounded-circle text-warning bg-warning-subtle fs-9"><span>C</span></div>
                          </div>
                          <h6 class="mb-0"><a class="stretched-link text-800" href="#!">Christopher</a></h6>
                        </div>
                      </td>
                      <td class="align-middle satisfied white-space-nowrap pe-5 pe-xxl-8">
                        <div class="d-flex align-items-center">
                          <div>
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                              <h5 class="mb-0">14</h5>
                              <h6 class="mb-0 fw-semi-bold text-danger"><span class="fs-11 me-1 ms-2 fas fa-caret-down"></span><span class="fs-10">16</span></h6>
                            </div>
                            <h6 class="mb-0 text-700">Last Day 30</h6>
                          </div>
                          <div class="ms-4">
                            <div class="progress rounded-pill mb-1" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-primary" style="width: 50%"></div>
                            </div>
                            <div class="progress rounded-pill" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-info" style="width: 80%"></div>
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle dissatisfied">
                        <div class="d-flex align-items-center">
                          <div>
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                              <h5 class="mb-0">25</h5>
                              <h6 class="mb-0 fs-11 fw-semi-bold text-primary"><span class="me-1 ms-2 fs-11 fas fa-caret-down"></span><span class="fs-10">13</span></h6>
                            </div>
                            <h6 class="mb-0 text-700 text-nowrap">Last Day 38</h6>
                          </div>
                          <div class="ms-4">
                            <div class="progress rounded-pill mb-1" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-primary" style="width: 60%"></div>
                            </div>
                            <div class="progress rounded-pill" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-info" style="width: 70%"></div>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="align-middle py-4 fs-9" style="width: 28px;">
                        <div class="form-check mb-0"><input class="form-check-input" type="checkbox" id="agent-survey-result-4" data-bulk-select-row="data-bulk-select-row" /></div>
                      </td>
                      <td class="align-middle name white-space-nowrap pe-4 w-lg-25">
                        <div class="d-flex align-items-center gap-2 position-relative">
                          <div class="avatar avatar-xl">
                            <img class="rounded-circle" src="../../assets/img/team/2-thumb.png" alt="" />
                          </div>
                          <h6 class="mb-0"><a class="stretched-link text-800" href="#!">Declan</a></h6>
                        </div>
                      </td>
                      <td class="align-middle satisfied white-space-nowrap pe-5 pe-xxl-8">
                        <div class="d-flex align-items-center">
                          <div>
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                              <h5 class="mb-0">15</h5>
                              <h6 class="mb-0 fw-semi-bold text-danger"><span class="fs-11 me-1 ms-2 fas fa-caret-down"></span><span class="fs-10">30</span></h6>
                            </div>
                            <h6 class="mb-0 text-700">Last Day 45</h6>
                          </div>
                          <div class="ms-4">
                            <div class="progress rounded-pill mb-1" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-primary" style="width: 60%"></div>
                            </div>
                            <div class="progress rounded-pill" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-info" style="width: 85%"></div>
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle dissatisfied">
                        <div class="d-flex align-items-center">
                          <div>
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                              <h5 class="mb-0">32</h5>
                              <h6 class="mb-0 fs-11 fw-semi-bold text-primary"><span class="me-1 ms-2 fs-11 fas fa-caret-down"></span><span class="fs-10">7</span></h6>
                            </div>
                            <h6 class="mb-0 text-700 text-nowrap">Last Day 39</h6>
                          </div>
                          <div class="ms-4">
                            <div class="progress rounded-pill mb-1" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-primary" style="width: 30%"></div>
                            </div>
                            <div class="progress rounded-pill" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-info" style="width: 70%"></div>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="align-middle py-4 fs-9" style="width: 28px;">
                        <div class="form-check mb-0"><input class="form-check-input" type="checkbox" id="agent-survey-result-5" data-bulk-select-row="data-bulk-select-row" /></div>
                      </td>
                      <td class="align-middle name white-space-nowrap pe-4 w-lg-25">
                        <div class="d-flex align-items-center gap-2 position-relative">
                          <div class="avatar avatar-xl">
                            <div class="avatar-name rounded-circle text-primary bg-primary-subtle fs-9"><span>B</span></div>
                          </div>
                          <h6 class="mb-0"><a class="stretched-link text-800" href="#!">Bentley</a></h6>
                        </div>
                      </td>
                      <td class="align-middle satisfied white-space-nowrap pe-5 pe-xxl-8">
                        <div class="d-flex align-items-center">
                          <div>
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                              <h5 class="mb-0">41</h5>
                              <h6 class="mb-0 fw-semi-bold text-primary"><span class="fs-11 me-1 ms-2 fas fa-caret-up"></span><span class="fs-10">17</span></h6>
                            </div>
                            <h6 class="mb-0 text-700">Last Day 24</h6>
                          </div>
                          <div class="ms-4">
                            <div class="progress rounded-pill mb-1" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-primary" style="width: 90%"></div>
                            </div>
                            <div class="progress rounded-pill" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-info" style="width: 80%"></div>
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle dissatisfied">
                        <div class="d-flex align-items-center">
                          <div>
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                              <h5 class="mb-0">23</h5>
                              <h6 class="mb-0 fs-11 fw-semi-bold text-primary"><span class="me-1 ms-2 fs-11 fas fa-caret-down"></span><span class="fs-10">1</span></h6>
                            </div>
                            <h6 class="mb-0 text-700 text-nowrap">Last Day 24</h6>
                          </div>
                          <div class="ms-4">
                            <div class="progress rounded-pill mb-1" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-primary" style="width: 95%"></div>
                            </div>
                            <div class="progress rounded-pill" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-info" style="width: 75%"></div>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="align-middle py-4 fs-9" style="width: 28px;">
                        <div class="form-check mb-0"><input class="form-check-input" type="checkbox" id="agent-survey-result-6" data-bulk-select-row="data-bulk-select-row" /></div>
                      </td>
                      <td class="align-middle name white-space-nowrap pe-4 w-lg-25">
                        <div class="d-flex align-items-center gap-2 position-relative">
                          <div class="avatar avatar-xl">
                            <div class="avatar-name rounded-circle text-info bg-info-subtle fs-9"><span>A</span></div>
                          </div>
                          <h6 class="mb-0"><a class="stretched-link text-800" href="#!">Abigail</a></h6>
                        </div>
                      </td>
                      <td class="align-middle satisfied white-space-nowrap pe-5 pe-xxl-8">
                        <div class="d-flex align-items-center">
                          <div>
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                              <h5 class="mb-0">52</h5>
                              <h6 class="mb-0 fw-semi-bold text-primary"><span class="fs-11 me-1 ms-2 fas fa-caret-up"></span><span class="fs-10">27</span></h6>
                            </div>
                            <h6 class="mb-0 text-700">Last Day 25</h6>
                          </div>
                          <div class="ms-4">
                            <div class="progress rounded-pill mb-1" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-primary" style="width: 70%"></div>
                            </div>
                            <div class="progress rounded-pill" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-info" style="width: 80%"></div>
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle dissatisfied">
                        <div class="d-flex align-items-center">
                          <div>
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                              <h5 class="mb-0">12</h5>
                              <h6 class="mb-0 fs-11 fw-semi-bold text-primary"><span class="me-1 ms-2 fs-11 fas fa-caret-down"></span><span class="fs-10">13</span></h6>
                            </div>
                            <h6 class="mb-0 text-700 text-nowrap">Last Day 25</h6>
                          </div>
                          <div class="ms-4">
                            <div class="progress rounded-pill mb-1" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-primary" style="width: 70%"></div>
                            </div>
                            <div class="progress rounded-pill" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-info" style="width: 90%"></div>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="align-middle py-4 fs-9" style="width: 28px;">
                        <div class="form-check mb-0"><input class="form-check-input" type="checkbox" id="agent-survey-result-7" data-bulk-select-row="data-bulk-select-row" /></div>
                      </td>
                      <td class="align-middle name white-space-nowrap pe-4 w-lg-25">
                        <div class="d-flex align-items-center gap-2 position-relative">
                          <div class="avatar avatar-xl">
                            <div class="avatar-name rounded-circle text-warning bg-warning-subtle fs-9"><span>C</span></div>
                          </div>
                          <h6 class="mb-0"><a class="stretched-link text-800" href="#!">Christopher</a></h6>
                        </div>
                      </td>
                      <td class="align-middle satisfied white-space-nowrap pe-5 pe-xxl-8">
                        <div class="d-flex align-items-center">
                          <div>
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                              <h5 class="mb-0">14</h5>
                              <h6 class="mb-0 fw-semi-bold text-danger"><span class="fs-11 me-1 ms-2 fas fa-caret-down"></span><span class="fs-10">16</span></h6>
                            </div>
                            <h6 class="mb-0 text-700">Last Day 30</h6>
                          </div>
                          <div class="ms-4">
                            <div class="progress rounded-pill mb-1" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-primary" style="width: 50%"></div>
                            </div>
                            <div class="progress rounded-pill" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-info" style="width: 80%"></div>
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle dissatisfied">
                        <div class="d-flex align-items-center">
                          <div>
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                              <h5 class="mb-0">25</h5>
                              <h6 class="mb-0 fs-11 fw-semi-bold text-primary"><span class="me-1 ms-2 fs-11 fas fa-caret-down"></span><span class="fs-10">13</span></h6>
                            </div>
                            <h6 class="mb-0 text-700 text-nowrap">Last Day 38</h6>
                          </div>
                          <div class="ms-4">
                            <div class="progress rounded-pill mb-1" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-primary" style="width: 60%"></div>
                            </div>
                            <div class="progress rounded-pill" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-info" style="width: 70%"></div>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="align-middle py-4 fs-9" style="width: 28px;">
                        <div class="form-check mb-0"><input class="form-check-input" type="checkbox" id="agent-survey-result-8" data-bulk-select-row="data-bulk-select-row" /></div>
                      </td>
                      <td class="align-middle name white-space-nowrap pe-4 w-lg-25">
                        <div class="d-flex align-items-center gap-2 position-relative">
                          <div class="avatar avatar-xl">
                            <img class="rounded-circle" src="../../assets/img/team/2-thumb.png" alt="" />
                          </div>
                          <h6 class="mb-0"><a class="stretched-link text-800" href="#!">Declan</a></h6>
                        </div>
                      </td>
                      <td class="align-middle satisfied white-space-nowrap pe-5 pe-xxl-8">
                        <div class="d-flex align-items-center">
                          <div>
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                              <h5 class="mb-0">15</h5>
                              <h6 class="mb-0 fw-semi-bold text-danger"><span class="fs-11 me-1 ms-2 fas fa-caret-down"></span><span class="fs-10">30</span></h6>
                            </div>
                            <h6 class="mb-0 text-700">Last Day 45</h6>
                          </div>
                          <div class="ms-4">
                            <div class="progress rounded-pill mb-1" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-primary" style="width: 60%"></div>
                            </div>
                            <div class="progress rounded-pill" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-info" style="width: 85%"></div>
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle dissatisfied">
                        <div class="d-flex align-items-center">
                          <div>
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                              <h5 class="mb-0">32</h5>
                              <h6 class="mb-0 fs-11 fw-semi-bold text-primary"><span class="me-1 ms-2 fs-11 fas fa-caret-down"></span><span class="fs-10">7</span></h6>
                            </div>
                            <h6 class="mb-0 text-700 text-nowrap">Last Day 39</h6>
                          </div>
                          <div class="ms-4">
                            <div class="progress rounded-pill mb-1" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-primary" style="width: 30%"></div>
                            </div>
                            <div class="progress rounded-pill" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-info" style="width: 70%"></div>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td class="align-middle py-4 fs-9" style="width: 28px;">
                        <div class="form-check mb-0"><input class="form-check-input" type="checkbox" id="agent-survey-result-9" data-bulk-select-row="data-bulk-select-row" /></div>
                      </td>
                      <td class="align-middle name white-space-nowrap pe-4 w-lg-25">
                        <div class="d-flex align-items-center gap-2 position-relative">
                          <div class="avatar avatar-xl">
                            <div class="avatar-name rounded-circle text-success bg-success-subtle fs-9"><span>A</span></div>
                          </div>
                          <h6 class="mb-0"><a class="stretched-link text-800" href="#!">Amelia</a></h6>
                        </div>
                      </td>
                      <td class="align-middle satisfied white-space-nowrap pe-5 pe-xxl-8">
                        <div class="d-flex align-items-center">
                          <div>
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                              <h5 class="mb-0">36</h5>
                              <h6 class="mb-0 fw-semi-bold text-danger"><span class="fs-11 me-1 ms-2 fas fa-caret-down"></span><span class="fs-10">17</span></h6>
                            </div>
                            <h6 class="mb-0 text-700">Last Day 19</h6>
                          </div>
                          <div class="ms-4">
                            <div class="progress rounded-pill mb-1" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-primary" style="width: 80%"></div>
                            </div>
                            <div class="progress rounded-pill" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-info" style="width: 60%"></div>
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle dissatisfied">
                        <div class="d-flex align-items-center">
                          <div>
                            <div class="d-flex align-items-baseline gap-1 mb-1">
                              <h5 class="mb-0">52</h5>
                              <h6 class="mb-0 fs-11 fw-semi-bold text-primary"><span class="me-1 ms-2 fs-11 fas fa-caret-down"></span><span class="fs-10">33</span></h6>
                            </div>
                            <h6 class="mb-0 text-700 text-nowrap">Last Day 19</h6>
                          </div>
                          <div class="ms-4">
                            <div class="progress rounded-pill mb-1" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-primary" style="width: 60%"></div>
                            </div>
                            <div class="progress rounded-pill" style="height:5px; width:174px" role="progressbar" aria-label="Basic example" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                              <div class="progress-bar rounded-pill bg-info" style="width: 50%"></div>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="card-footer d-flex justify-content-center"><button class="btn btn-sm btn-falcon-default me-1" type="button" title="Previous" data-list-pagination="prev"><span class="fas fa-chevron-left"></span></button>
              <ul class="pagination mb-0"></ul><button class="btn btn-sm btn-falcon-default ms-1" type="button" title="Next" data-list-pagination="next"><span class="fas fa-chevron-right"></span></button>
            </div>
          </div>
          <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center bg-body-tertiary py-2">
              <h6 class="mb-0">Customer Satisfaction Survey</h6>
              <div class="dropdown font-sans-serif btn-reveal-trigger"><button class="btn btn-link text-600 btn-sm dropdown-toggle dropdown-caret-none btn-reveal" type="button" id="dropdown-satisfaction-survey" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs-11"></span></button>
                <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-satisfaction-survey"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Export</a>
                  <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="#!">Remove</a>
                </div>
              </div>
            </div>
            <div class="card-body scrollbar">
              <div class="echart-satisfaction-survey" data-echart-responsive="true"></div>
            </div>
            <div class="card-footer bg-body-tertiary py-2 text-center"><a class="btn btn-link btn-sm fw-medium" href="#!">View all<span class="fas fa-chevron-right ms-1 fs-11"></span></a></div>
          </div>
          <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center bg-body-tertiary py-2">
              <h6 class="mb-0">Analysis of the Top Customers</h6>
              <div class="dropdown font-sans-serif btn-reveal-trigger"><button class="btn btn-link text-600 btn-sm dropdown-toggle dropdown-caret-none btn-reveal" type="button" id="dropdown-top-customers" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false"><span class="fas fa-ellipsis-h fs-11"></span></button>
                <div class="dropdown-menu dropdown-menu-end border py-2" aria-labelledby="dropdown-top-customers"><a class="dropdown-item" href="#!">View</a><a class="dropdown-item" href="#!">Export</a>
                  <div class="dropdown-divider"></div><a class="dropdown-item text-danger" href="#!">Remove</a>
                </div>
              </div>
            </div>
            <div class="card-body ps-0 py-0 d-flex">
              <ul class="nav nav-tabs tab-active-caret top-customers-tab border-0 border-end d-inline-block text-center" id="top-customers-chart-tab" role="tablist" data-tab-has-echarts="data-tab-has-echarts">
                <li class="nav-item border-bottom" role="presentation"><a class="nav-link p-x1 mb-0 active" id="monday-tab" data-bs-toggle="tab" href="#monday" role="tab" aria-controls="monday" aria-selected="true">MON</a></li>
                <li class="nav-item border-bottom" role="presentation"><a class="nav-link p-x1 mb-0" id="tuesday-tab" data-bs-toggle="tab" href="#tuesday" role="tab" aria-controls="tuesday" aria-selected="false">TUE</a></li>
                <li class="nav-item border-bottom" role="presentation"><a class="nav-link p-x1 mb-0" id="wednesday-tab" data-bs-toggle="tab" href="#wednesday" role="tab" aria-controls="wednesday" aria-selected="false">WED</a></li>
                <li class="nav-item border-bottom" role="presentation"><a class="nav-link p-x1 mb-0" id="thursday-tab" data-bs-toggle="tab" href="#thursday" role="tab" aria-controls="thursday" aria-selected="false">THU</a></li>
                <li class="nav-item border-bottom" role="presentation"><a class="nav-link p-x1 mb-0" id="friday-tab" data-bs-toggle="tab" href="#friday" role="tab" aria-controls="friday" aria-selected="false">FRI</a></li>
                <li class="nav-item border-bottom" role="presentation"><a class="nav-link p-x1 mb-0" id="saturday-tab" data-bs-toggle="tab" href="#saturday" role="tab" aria-controls="saturday" aria-selected="false">SAT</a></li>
                <li class="nav-item" role="presentation"><a class="nav-link p-x1 mb-0" id="sunday-tab" data-bs-toggle="tab" href="#sunday" role="tab" aria-controls="sunday" aria-selected="false">SUN</a></li>
              </ul>
              <div class="tab-content w-100 ps-x1 pt-x1">
                <div class="tab-pane active" id="monday" role="tabpanel" aria-labelledby="monday-tab"><!-- Find the JS file for the following chart at: src/js/charts/echarts/top-customers.js--><!-- If you are not using gulp based workflow, you can find the transpiled code at: public/assets/js/theme.js-->
                  <div class="d-flex align-items-center gap-2 mb-3">
                    <h4 class="text-primary">65.09%</h4>
                    <h6 class="fs-11 py-1 px-2 fw-semi-bold badge badge-subtle-primary rounded-pill"><span class="fas fa-caret-up me-1"></span><span>13.6%</span></h6>
                  </div>
                  <div class="echart-top-customers" data-echart-responsive="true"></div>
                </div>
                <div class="tab-pane" id="tuesday" role="tabpanel" aria-labelledby="tuesday-tab">
                  <div class="d-flex align-items-center gap-2 mb-3">
                    <h4 class="text-primary">78.35%</h4>
                    <h6 class="fs-11 py-1 px-2 fw-semi-bold badge badge-subtle-primary rounded-pill"><span class="fas fa-caret-up me-1"></span><span>8.3%</span></h6>
                  </div>
                  <div class="echart-top-customers" data-echart-responsive="true"></div>
                </div>
                <div class="tab-pane" id="wednesday" role="tabpanel" aria-labelledby="wednesday-tab">
                  <div class="d-flex align-items-center gap-2 mb-3">
                    <h4 class="text-primary">45.45%</h4>
                    <h6 class="fs-11 py-1 px-2 fw-semi-bold badge badge-subtle-primary rounded-pill"><span class="fas fa-caret-up me-1"></span><span>5.12%</span></h6>
                  </div>
                  <div class="echart-top-customers" data-echart-responsive="true"></div>
                </div>
                <div class="tab-pane" id="thursday" role="tabpanel" aria-labelledby="thursday-tab">
                  <div class="d-flex align-items-center gap-2 mb-3">
                    <h4 class="text-primary">12.19%</h4>
                    <h6 class="fs-11 py-1 px-2 fw-semi-bold badge badge-subtle-primary rounded-pill"><span class="fas fa-caret-up me-1"></span><span>2.03%</span></h6>
                  </div>
                  <div class="echart-top-customers" data-echart-responsive="true"></div>
                </div>
                <div class="tab-pane" id="friday" role="tabpanel" aria-labelledby="friday-tab">
                  <div class="d-flex align-items-center gap-2 mb-3">
                    <h4 class="text-primary">80.09%</h4>
                    <h6 class="fs-11 py-1 px-2 fw-semi-bold badge badge-subtle-primary rounded-pill"><span class="fas fa-caret-up me-1"></span><span>11.6%</span></h6>
                  </div>
                  <div class="echart-top-customers" data-echart-responsive="true"></div>
                </div>
                <div class="tab-pane" id="saturday" role="tabpanel" aria-labelledby="saturday-tab">
                  <div class="d-flex align-items-center gap-2 mb-3">
                    <h4 class="text-primary">55.05%</h4>
                    <h6 class="fs-11 py-1 px-2 fw-semi-bold badge badge-subtle-primary rounded-pill"><span class="fas fa-caret-up me-1"></span><span>5.55%</span></h6>
                  </div>
                  <div class="echart-top-customers" data-echart-responsive="true"></div>
                </div>
                <div class="tab-pane" id="sunday" role="tabpanel" aria-labelledby="sunday-tab">
                  <div class="d-flex align-items-center gap-2 mb-3">
                    <h4 class="text-primary">65.09%</h4>
                    <h6 class="fs-11 py-1 px-2 fw-semi-bold badge badge-subtle-primary rounded-pill"><span class="fas fa-caret-up me-1"></span><span>13.6%</span></h6>
                  </div>
                  <div class="echart-top-customers" data-echart-responsive="true"></div>
                </div>
              </div>
            </div>
            <div class="card-footer bg-body-tertiary py-2">
              <div class="row flex-between-center">
                <div class="col-auto pe-0"><select class="form-select form-select-sm">
                    <option>Last 7 days</option>
                    <option>Last Month</option>
                    <option>Last Year</option>
                  </select></div>
                <div class="col-auto"><a class="btn btn-link btn-sm px-0 fw-medium" href="#!">View all reports<span class="fas fa-chevron-right ms-1 fs-11"></span></a></div>
              </div>
            </div>
          </div>
          <div class="card mt-3">
            <div class="card-header border-bottom border-300">
              <div class="row flex-between-center gy-2">
                <div class="col-auto">
                  <h6 class="mb-0 me-x1">Load Analysis by Received Tickets</h6>
                </div>
                <div class="col-auto"><select class="form-select form-select-sm w-auto">
                    <option>Daily</option>
                    <option>Weekly</option>
                    <option selected="selected">Monthly</option>
                    <option>Yearly</option>
                  </select></div>
              </div>
            </div>
            <div class="card-body scrollbar">
              <div class="echart-received-tickets" data-echart-responsive="true"></div>
            </div>
            <div class="card-footer bg-body-tertiary py-2 text-center"><a class="btn btn-link btn-sm px-0 fw-medium" href="#!">View all report<span class="fas fa-chevron-right ms-1 fs-11"></span></a></div>
          </div>
          <div class="card mt-3">
            <div class="card-header border-bottom border-300">
              <div class="row flex-between-center gy-2">
                <div class="col-auto">
                  <h6 class="mb-0 text-truncate me-x1">Trends in Ticket Volume</h6>
                </div>
                <div class="col-auto"><select class="form-select form-select-sm w-auto">
                    <option>Daily</option>
                    <option>Weekly</option>
                    <option selected="selected">Monthly</option>
                    <option>Yearly</option>
                  </select></div>
              </div>
            </div>
            <div class="card-body">
              <div class="row flex-between-center mx-0 gy-4">
                <div class="col-lg-auto px-0 me-5">
                  <div class="row g-md-0">
                    <div class="col-auto d-md-flex">
                      <div class="d-flex align-items-center me-md-3 form-check mb-0"><input class="form-check-input dot mt-0 shadow-none remove-checked-icon rounded-circle cursor-pointer" type="checkbox" data-ticket-volume="On Hold Tickets" value="" id="onHoldTickets" checked="checked" /><label class="form-check-label mb-0 cursor-pointer text-700 font-base fw-normal" for="onHoldTickets">On Hold Tickets</label></div>
                      <div class="d-flex align-items-center me-md-3 form-check mb-0"><input class="form-check-input dot mt-0 shadow-none remove-checked-icon rounded-circle open-tickets cursor-pointer" type="checkbox" data-ticket-volume="Open Tickets" value="" id="openTickets" checked="checked" /><label class="form-check-label mb-0 cursor-pointer text-700 font-base fw-normal" for="openTickets">Open Tickets</label></div>
                    </div>
                    <div class="col-auto d-md-flex">
                      <div class="d-flex align-items-center me-md-3 form-check mb-0"><input class="form-check-input dot mt-0 shadow-none remove-checked-icon rounded-circle due-tickets-volume cursor-pointer" type="checkbox" data-ticket-volume="Due Tickets" value="" id="dueTickets" checked="checked" /><label class="form-check-label mb-0 cursor-pointer text-700 font-base fw-normal" for="dueTickets">Due Tickets</label></div>
                      <div class="d-flex align-items-center form-check mb-0"><input class="form-check-input dot mt-0 shadow-none remove-checked-icon rounded-circle unassigned-tickets-volume cursor-pointer" type="checkbox" data-ticket-volume="Unassigned Tickets" value="" id="unassignedTickets" checked="checked" /><label class="form-check-label mb-0 cursor-pointer text-700 font-base fw-normal" for="unassignedTickets">Unassigned Tickets</label></div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-auto scrollbar overflow-y-hidden px-0">
                  <div class="d-flex white-space-nowrap justify-content-xl-end w-100">
                    <div class="d-flex align-items-center">
                      <div>
                        <h6 class="fs-9 d-flex align-items-center text-700 mb-1">125<small class="badge text-success bg-transparent px-0"><span class="fas fa-caret-up ms-2 me-1 fs-11"></span><span>5.3%</span></small></h6>
                        <h6 class="text-600 mb-0 fs-11">Total On Hold Tickets</h6>
                      </div>
                      <div class="bg-200 mx-3" style="height: 24px; width: 1px"></div>
                    </div>
                    <div class="d-flex align-items-center">
                      <div>
                        <h6 class="fs-9 d-flex align-items-center text-700 mb-1">100<small class="badge px-0 text-primary"><span class="fas fa-caret-up ms-2 me-1 fs-11"></span><span>3.20%</span></small></h6>
                        <h6 class="fs-11 text-600 mb-0">Total Open Tickets</h6>
                      </div>
                      <div class="bg-200 mx-3" style="height: 24px; width: 1px"></div>
                    </div>
                    <div class="d-flex align-items-center">
                      <div>
                        <h6 class="fs-9 d-flex align-items-center text-700 mb-1">53<small class="badge px-0 text-warning"><span class="fas fa-caret-down ms-2 me-1 fs-11"></span><span>2.3%</span></small></h6>
                        <h6 class="fs-11 text-600 mb-0">Total Due Tickets</h6>
                      </div>
                      <div class="bg-200 mx-3" style="height: 24px; width: 1px"></div>
                    </div>
                    <div>
                      <h6 class="fs-9 d-flex align-items-center text-700 mb-1">136<small class="badge px-0 text-danger"><span class="fas fa-caret-up ms-2 me-1 fs-11"></span><span>3.12%</span></small></h6>
                      <h6 class="fs-11 text-600 mb-0">Total Unassigned Tickets</h6>
                    </div>
                  </div>
                </div>
              </div>
              <div class="echart-ticket-volume" data-echart-responsive="true"></div>
            </div>
            <div class="card-footer bg-body-tertiary py-2 text-center"><a class="btn btn-link btn-sm px-0 fw-medium" href="#!">View all report<span class="fas fa-chevron-right ms-1 fs-11"></span></a></div>
          </div>

    <?php include 'footer.php' ; ?>