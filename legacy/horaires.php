<?php include 'header.php'; ?>

<div class="card mb-3">
    <div class="card-header ">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0">Gestion des Horaires</h5>
            </div>
            <div class="col-auto">
                <button class="btn btn-falcon-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                    <span class="fas fa-plus me-1"></span>Nouveau Cours
                </button>
                <div class="btn-group ms-2" role="group">
                    <button class="btn btn-falcon-default btn-sm" type="button" id="prevWeek">
                        <span class="fas fa-chevron-left"></span>
                    </button>
                    <button class="btn btn-falcon-default btn-sm" type="button" id="nextWeek">
                        <span class="fas fa-chevron-right"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row flex-between-center">
                    <div class="col-auto">
                        <h5 class="mb-0" id="currentWeekRange">Semaine du 20 au 26 Mai 2024</h5>
                    </div>
                    <div class="col-auto">
                        <select class="form-select form-select-sm" id="classFilter">
                            <option value="all">Toutes les classes</option>
                            <option value="6ème A">6ème A</option>
                            <option value="5ème B">5ème B</option>
                            <option value="4ème C">4ème C</option>
                            <option value="3ème D">3ème D</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 fs-10 schedule-calendar">
                        <thead class="bg-200">
                            <tr>
                                <th class="text-center" style="width: 100px;">Jour/Heure</th>
                                <th class="text-center align-middle">Lundi</th>
                                <th class="text-center align-middle">Mardi</th>
                                <th class="text-center align-middle">Mercredi</th>
                                <th class="text-center align-middle">Jeudi</th>
                                <th class="text-center align-middle">Vendredi</th>
                                <th class="text-center align-middle">Samedi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Matin -->
                            <tr class="bg-100">
                                <td colspan="8" class="text-center fw-bold">Matin</td>
                            </tr>
                            <tr>
                                <!-- heure du cours -->
                                <td class="text-center bg-100 fw-bold">8h-9h</td>

                                <td class="schedule-cell align-middle " data-day="lundi" data-hour="8">
                                    <div class="schedule-item">
                                        <div class="fw-semi-bold text-uppercase">
                                            Mathématiques
                                        </div>
                                        <!-- <div class="fs-10">6ème A - Salle 101</div> -->
                                        <div class="fs-9"> <small>Prof:</small> M. Dupont</div> 
                                    </div>
                                </td>

                                <td class="schedule-cell align-middle " data-day="mardi" data-hour="8">
                                    <div class="schedule-item  ">
                                        <div class="fw-semi-bold text-uppercase ">Physique</div>
                                        <!-- <div class="fs-10">5ème B - Labo 201</div> -->
                                        <div class="fs-9"> <small>Prof:</small> Mme. Martin</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="mercredi" data-hour="8">
                                    <div class="schedule-item  ">
                                        <div class="fw-semi-bold text-uppercase ">Français</div>
                                        <!-- <div class="fs-10">4ème C - Salle 103</div> -->
                                        <div class="fs-9"> <small>Prof:</small> M. Leroy</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="jeudi" data-hour="8">
                                    <div class="schedule-item ">
                                        <div class="fw-semi-bold text-uppercase ">Histoire</div>
                                        <!-- <div class="fs-10">3ème D - Salle 104</div> -->
                                        <div class="fs-9"> <small>Prof:</small> Mme. Bernard</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="vendredi" data-hour="8">
                                    <div class="schedule-item  ">
                                        <div class="fw-semi-bold text-uppercase ">SVT</div>
                                        <!-- <div class="fs-10">6ème A - Labo 202</div> -->
                                        <div class="fs-9"> <small>Prof:</small> M. Petit</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="samedi" data-hour="8">
                                <div class="schedule-item ">
                                        <div class="fw-semi-bold text-uppercase ">Histoire</div>
                                        <!-- <div class="fs-10">3ème D - Salle 104</div> -->
                                        <div class="fs-9"> <small>Prof:</small> Mme. Bernard</div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center bg-100 fw-bold">9h-10h</td>
                                <td class="schedule-cell align-middle " data-day="lundi" data-hour="9">
                                    <div class="schedule-item ">
                                        <div class="fw-semi-bold text-uppercase ">Physique</div>
                                        <!-- <div class="fs-10">5ème B - Labo 201</div> -->
                                        <div class="fs-9"> <small>Prof:</small> Mme. Martin</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="mardi" data-hour="9">
                                    <div class="schedule-item ">
                                        <div class="fw-semi-bold text-uppercase ">Mathématiques</div>
                                        <!-- <div class="fs-10">6ème A - Salle 101</div> -->
                                        <div class="fs-9"> <small>Prof:</small> M. Dupont</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="mercredi" data-hour="9">
                                    <div class="schedule-item ">
                                        <div class="fw-semi-bold text-uppercase ">Histoire</div>
                                        <!-- <div class="fs-10">3ème D - Salle 104</div> -->
                                        <div class="fs-9"> <small>Prof:</small> Mme. Bernard</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="jeudi" data-hour="9">
                                    <div class="schedule-item  ">
                                        <div class="fw-semi-bold text-uppercase ">Français</div>
                                        <!-- <div class="fs-10">4ème C - Salle 103</div> -->
                                        <div class="fs-9"> <small>Prof:</small> M. Leroy</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="vendredi" data-hour="9">
                                    <div class="schedule-item ">
                                        <div class="fw-semi-bold text-uppercase ">Mathématiques</div>
                                        <!-- <div class="fs-10">6ème A - Salle 101</div> -->
                                        <div class="fs-9"> <small>Prof:</small> M. Dupont</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="samedi" data-hour="9">
                                <div class="schedule-item ">
                                        <div class="fw-semi-bold text-uppercase ">Histoire</div>
                                        <!-- <div class="fs-10">3ème D - Salle 104</div> -->
                                        <div class="fs-9"> <small>Prof:</small> Mme. Bernard</div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center bg-100 fw-bold">10h-11h</td>
                                <td class="schedule-cell align-middle " data-day="lundi" data-hour="10">
                                    <div class="schedule-item  ">
                                        <div class="fw-semi-bold text-uppercase ">Français</div>
                                        <!-- <div class="fs-10">4ème C - Salle 103</div> -->
                                        <div class="fs-9"> <small>Prof:</small> M. Leroy</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="mardi" data-hour="10">
                                    <div class="schedule-item ">
                                        <div class="fw-semi-bold text-uppercase ">Histoire</div>
                                        <!-- <div class="fs-10">3ème D - Salle 104</div> -->
                                        <div class="fs-9"> <small>Prof:</small> Mme. Bernard</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="mercredi" data-hour="10">
                                    <div class="schedule-item ">
                                        <div class="fw-semi-bold text-uppercase ">Mathématiques</div>
                                        <!-- <div class="fs-10">6ème A - Salle 101</div> -->
                                        <div class="fs-9"> <small>Prof:</small> M. Dupont</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="jeudi" data-hour="10">
                                    <div class="schedule-item ">
                                        <div class="fw-semi-bold text-uppercase ">Physique</div>
                                        <!-- <div class="fs-10">5ème B - Labo 201</div> -->
                                        <div class="fs-9"> <small>Prof:</small> Mme. Martin</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="vendredi" data-hour="10">
                                    <div class="schedule-item  ">
                                        <div class="fw-semi-bold text-uppercase ">Français</div>
                                        <!-- <div class="fs-10">4ème C - Salle 103</div> -->
                                        <div class="fs-9"> <small>Prof:</small> M. Leroy</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="samedi" data-hour="10">
                                <div class="schedule-item ">
                                        <div class="fw-semi-bold text-uppercase ">Histoire</div>
                                        <!-- <div class="fs-10">3ème D - Salle 104</div> -->
                                        <div class="fs-9"> <small>Prof:</small> Mme. Bernard</div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center bg-100 fw-bold">11h-12h</td>
                                <td class="schedule-cell align-middle " data-day="lundi" data-hour="11">
                                    <div class="schedule-item ">
                                        <div class="fw-semi-bold text-uppercase ">Histoire</div>
                                        <!-- <div class="fs-10">3ème D - Salle 104</div> -->
                                        <div class="fs-9"> <small>Prof:</small> Mme. Bernard</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="mardi" data-hour="11">
                                    <div class="schedule-item  ">
                                        <div class="fw-semi-bold text-uppercase ">Français</div>
                                        <!-- <div class="fs-10">4ème C - Salle 103</div> -->
                                        <div class="fs-9"> <small>Prof:</small> M. Leroy</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="mercredi" data-hour="11">
                                    <div class="schedule-item ">
                                        <div class="fw-semi-bold text-uppercase ">Physique</div>
                                        <!-- <div class="fs-10">5ème B - Labo 201</div> -->
                                        <div class="fs-9"> <small>Prof:</small> Mme. Martin</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="jeudi" data-hour="11">
                                    <div class="schedule-item ">
                                        <div class="fw-semi-bold text-uppercase ">Mathématiques</div>
                                        <!-- <div class="fs-10">6ème A - Salle 101</div> -->
                                        <div class="fs-9"> <small>Prof:</small> M. Dupont</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="vendredi" data-hour="11">
                                    <div class="schedule-item ">
                                        <div class="fw-semi-bold text-uppercase ">Histoire</div>
                                        <!-- <div class="fs-10">3ème D - Salle 104</div> -->
                                        <div class="fs-9"> <small>Prof:</small> Mme. Bernard</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="samedi" data-hour="11">
                                <div class="schedule-item ">
                                        <div class="fw-semi-bold text-uppercase ">Histoire</div>
                                        <!-- <div class="fs-10">3ème D - Salle 104</div> -->
                                        <div class="fs-9"> <small>Prof:</small> Mme. Bernard</div>
                                    </div>
                                </td>
                            </tr>

                            <!-- Après-midi -->
                            <tr class="bg-100">
                                <td colspan="8" class="text-center fw-bold">Après-midi</td>
                            </tr>
                            <tr>
                                <td class="text-center bg-100 fw-bold">14h-15h</td>
                                <td class="schedule-cell align-middle " data-day="lundi" data-hour="14">
                                    <div class="schedule-item  ">
                                        <div class="fw-semi-bold text-uppercase ">Anglais</div>
                                        <!-- <div class="fs-10">5ème B - Salle 102</div> -->
                                        <div class="fs-9"> <small>Prof:</small> Mme. Johnson</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="mardi" data-hour="14">
                                    <div class="schedule-item  ">
                                        <div class="fw-semi-bold text-uppercase ">SVT</div>
                                        <!-- <div class="fs-10">6ème A - Labo 202</div> -->
                                        <div class="fs-9"> <small>Prof:</small> M. Petit</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="mercredi" data-hour="14"></td>
                                <td class="schedule-cell align-middle " data-day="jeudi" data-hour="14">
                                    <div class="schedule-item  ">
                                        <div class="fw-semi-bold text-uppercase ">Anglais</div>
                                        <!-- <div class="fs-10">5ème B - Salle 102</div> -->
                                        <div class="fs-9"> <small>Prof:</small> Mme. Johnson</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="vendredi" data-hour="14">
                                    <div class="schedule-item ">
                                        <div class="fw-semi-bold text-uppercase ">Physique</div>
                                        <!-- <div class="fs-10">5ème B - Labo 201</div> -->
                                        <div class="fs-9"> <small>Prof:</small> Mme. Martin</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="samedi" data-hour="14"></td>
                            </tr>
                            <tr>
                                <td class="text-center bg-100 fw-bold">15h-16h</td>
                                <td class="schedule-cell align-middle " data-day="lundi" data-hour="15">
                                    <div class="schedule-item  ">
                                        <div class="fw-semi-bold text-uppercase ">SVT</div>
                                        <!-- <div class="fs-10">6ème A - Labo 202</div> -->
                                        <div class="fs-9"> <small>Prof:</small> M. Petit</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="mardi" data-hour="15">
                                    <div class="schedule-item  ">
                                        <div class="fw-semi-bold text-uppercase ">Anglais</div>
                                        <!-- <div class="fs-10">5ème B - Salle 102</div> -->
                                        <div class="fs-9"> <small>Prof:</small> Mme. Johnson</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="mercredi" data-hour="15"></td>
                                <td class="schedule-cell align-middle " data-day="jeudi" data-hour="15">
                                    <div class="schedule-item  ">
                                        <div class="fw-semi-bold text-uppercase ">SVT</div>
                                        <!-- <div class="fs-10">6ème A - Labo 202</div> -->
                                        <div class="fs-9"> <small>Prof:</small> M. Petit</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="vendredi" data-hour="15">
                                    <div class="schedule-item  ">
                                        <div class="fw-semi-bold text-uppercase ">Anglais</div>
                                        <!-- <div class="fs-10">5ème B - Salle 102</div> -->
                                        <div class="fs-9"> <small>Prof:</small> Mme. Johnson</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="samedi" data-hour="15">
                                <div class="schedule-item ">
                                        <div class="fw-semi-bold text-uppercase text-warning">Pas de cours</div>
                                       
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-center bg-100 fw-bold">16h-17h</td>
                                <td class="schedule-cell align-middle " data-day="lundi" data-hour="16">
                                    <div class="schedule-item ">
                                        <div class="fw-semi-bold text-uppercase ">Physique</div>
                                        <!-- <div class="fs-10">5ème B - Labo 201</div> -->
                                        <div class="fs-9"> <small>Prof:</small> Mme. Martin</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="mardi" data-hour="16">
                                    <div class="schedule-item  ">
                                        <div class="fw-semi-bold text-uppercase ">Français</div>
                                        <!-- <div class="fs-10">4ème C - Salle 103</div> -->
                                        <div class="fs-9"> <small>Prof:</small> M. Leroy</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="mercredi" data-hour="16"></td>
                                <td class="schedule-cell align-middle " data-day="jeudi" data-hour="16">
                                    <div class="schedule-item ">
                                        <div class="fw-semi-bold text-uppercase ">Histoire</div>
                                        <!-- <div class="fs-10">3ème D - Salle 104</div> -->
                                        <div class="fs-9"> <small>Prof:</small> Mme. Bernard</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="vendredi" data-hour="16">
                                    <div class="schedule-item  ">
                                        <div class="fw-semi-bold text-uppercase ">SVT</div>
                                        <!-- <div class="fs-10">6ème A - Labo 202</div> -->
                                        <div class="fs-9"> <small>Prof:</small> M. Petit</div>
                                    </div>
                                </td>
                                <td class="schedule-cell align-middle " data-day="samedi" data-hour="16"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour ajouter un nouveau cours -->
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Planifier un nouveau cours</h5>
                <button class="btn p-1" type="button" data-bs-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times fs-9"> <small>Prof:</small> </span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="scheduleClass">Classe</label>
                            <select class="form-select" id="scheduleClass" required>
                                <option value="" selected disabled>Sélectionner une classe</option>
                                <option value="6ème A">6ème A</option>
                                <option value="5ème B">5ème B</option>
                                <option value="4ème C">4ème C</option>
                                <option value="3ème D">3ème D</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="scheduleSubject">Matière</label>
                            <select class="form-select" id="scheduleSubject" required>
                                <option value="" selected disabled>Sélectionner une matière</option>
                                <option value="Mathématiques">Mathématiques</option>
                                <option value="Physique">Physique</option>
                                <option value="Français">Français</option>
                                <option value="Histoire">Histoire</option>
                                <option value="SVT">SVT</option>
                                <option value="Anglais">Anglais</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="scheduleProfessor">Professeur</label>
                            <select class="form-select" id="scheduleProfessor" required>
                                <option value="" selected disabled>Sélectionner un professeur</option>
                                <option value="M. Dupont">M. Dupont</option>
                                <option value="Mme. Martin">Mme. Martin</option>
                                <option value="M. Leroy">M. Leroy</option>
                                <option value="Mme. Bernard">Mme. Bernard</option>
                                <option value="M. Petit">M. Petit</option>
                                <option value="Mme. Johnson">Mme. Johnson</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="scheduleRoom">Salle</label>
                            <input class="form-control" id="scheduleRoom" type="text" placeholder="Ex: Salle 101" required />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label" for="scheduleDay">Jour</label>
                            <select class="form-select" id="scheduleDay" required>
                                <option value="" selected disabled>Sélectionner un jour</option>
                                <option value="lundi">Lundi</option>
                                <option value="mardi">Mardi</option>
                                <option value="mercredi">Mercredi</option>
                                <option value="jeudi">Jeudi</option>
                                <option value="vendredi">Vendredi</option>
                                <option value="samedi">Samedi</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="scheduleTime">Heure</label>
                            <select class="form-select" id="scheduleTime" required>
                                <option value="" selected disabled>Sélectionner une heure</option>
                                <option value="8">8h-9h</option>
                                <option value="9">9h-10h</option>
                                <option value="10">10h-11h</option>
                                <option value="11">11h-12h</option>
                                <option value="14">14h-15h</option>
                                <option value="15">15h-16h</option>
                                <option value="16">16h-17h</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" id="scheduleRecurring" type="checkbox" checked />
                            <label class="form-check-label" for="scheduleRecurring">Cours récurrent (toute la semaine)</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-falcon-secondary" type="button" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-falcon-primary" type="button" id="saveSchedule">Planifier</button>
            </div>
        </div>
    </div>
</div>


<?php include 'footer.php'; ?>
<script>
    // Initialisation des tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Gestion de la navigation par semaine
    let currentWeek = 0;
    
    document.getElementById('prevWeek').addEventListener('click', function() {
        currentWeek--;
        updateWeekDisplay();
    });
    
    document.getElementById('nextWeek').addEventListener('click', function() {
        currentWeek++;
        updateWeekDisplay();
    });
    
    function updateWeekDisplay() {
        const today = new Date();
        const startOfWeek = new Date(today);
        startOfWeek.setDate(today.getDate() - today.getDay() + 1 + (currentWeek * 7));
        
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 5);
        
        const options = { day: 'numeric', month: 'long' };
        const startStr = startOfWeek.toLocaleDateString('fr-FR', options);
        const endStr = endOfWeek.toLocaleDateString('fr-FR', options);
        const year = startOfWeek.getFullYear();
        
        document.getElementById('currentWeekRange').textContent = `Semaine du ${startStr} au ${endStr} ${year}`;
    }
    
    // Initialiser l'affichage de la semaine
    updateWeekDisplay();
    
    // Filtrage par classe
    document.getElementById('classFilter').addEventListener('change', function() {
        const selectedClass = this.value;
        const scheduleItems = document.querySelectorAll('.schedule-item');
        
        scheduleItems.forEach(item => {
            if (selectedClass === 'all' || item.textContent.includes(selectedClass)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    // Gestion de l'événement d'enregistrement
    document.getElementById('saveSchedule').addEventListener('click', function() {
        const scheduleClass = document.getElementById('scheduleClass').value;
        const scheduleSubject = document.getElementById('scheduleSubject').value;
        const scheduleProfessor = document.getElementById('scheduleProfessor').value;
        const scheduleRoom = document.getElementById('scheduleRoom').value;
        const scheduleDay = document.getElementById('scheduleDay').value;
        const scheduleTime = document.getElementById('scheduleTime').value;
        
        if (scheduleClass && scheduleSubject && scheduleProfessor && scheduleRoom && scheduleDay && scheduleTime) {
            // Ici, vous ajouteriez le code pour envoyer les données au serveur
            alert(`Cours de ${scheduleSubject} planifié avec succès!`);
            bootstrap.Modal.getInstance(document.getElementById('addScheduleModal')).hide();
            
            // Réinitialiser le formulaire
            document.getElementById('scheduleClass').value = '';
            document.getElementById('scheduleSubject').value = '';
            document.getElementById('scheduleProfessor').value = '';
            document.getElementById('scheduleRoom').value = '';
            document.getElementById('scheduleDay').value = '';
            document.getElementById('scheduleTime').value = '';
        } else {
            alert('Veuillez remplir tous les champs obligatoires.');
        }
    });

    // Permettre de cliquer sur une cellule pour ajouter un cours
    document.querySelectorAll('.schedule-cell').forEach(cell => {
        cell.addEventListener('click', function() {
            const day = this.getAttribute('data-day');
            const hour = this.getAttribute('data-hour');
            
            // Pré-remplir le modal avec le jour et l'heure sélectionnés
            document.getElementById('scheduleDay').value = day;
            document.getElementById('scheduleTime').value = hour;
            
            // Ouvrir le modal
            const modal = new bootstrap.Modal(document.getElementById('addScheduleModal'));
            modal.show();
        });
    });
</script>