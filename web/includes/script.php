<!-- jQuery FIRST -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Other Libraries that need jQuery -->
<script src="lib/easing/easing.min.js"></script>
<script src="lib/waypoints/waypoints.min.js"></script>
<script src="lib/owlcarousel/owl.carousel.min.js"></script>

<!-- Moment / Tempus Dominus (if used) -->
<script src="lib/tempusdominus/js/moment.min.js"></script>
<script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
<script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- test test test  -->
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Other Libraries -->
<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.quilljs.com/1.3.7/quill.js"></script>

<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/main.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<!-- Template main.js (AFTER all libraries) -->
<script src="assets/js/main.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<!-- <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script> -->

<script>
    function swalSuccess(title, text = "", reload = false) {
        Swal.fire({
            icon: "success",
            title: title,
            text: text,
            timer: 1200,
            showConfirmButton: false
        }).then(() => {
            if (reload) location.reload();
        });
    }

    function swalError(title, text = "") {
        Swal.fire({
            icon: "error",
            title: title,
            text: text
        });
    }

    function swalConfirm(title, text, yesCallback) {
        Swal.fire({
            title: title,
            text: text,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Yes",
            cancelButtonText: "Cancel"
        }).then(result => {
            if (result.isConfirmed) yesCallback();
        });
    }
</script>


<script>
    $('#datatablesSimple').on('click', '.delete', function() {
        var user_id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to delete this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'actions/delete_user.php',
                    type: 'POST',
                    data: {
                        id: user_id
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire(
                                'Deleted!',
                                'Your row has been deleted.',
                                'success'
                            ).then(function() {
                                window.location.href = 'users.php';
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message || 'Failed to delete services ',
                                'error'
                            );
                        }
                    },
                    error: function(xhr) {
                        console.log("Ajax error:", xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Server error',
                            text: 'Check console for details'
                        });
                    }
                });
            }
        });
    });
    $('#addForm').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);

        $.ajax({
            url: 'actions/add_users.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Done!',
                        text: response.message,
                        showConfirmButton: true
                    }).then(() => location.reload());
                    $('#addForm')[0].reset();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message,
                        showConfirmButton: true
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Something went wrong. Please try again.'
                });
            }
        });
    });
</script>


<script>
    function applyFilters() {
        let rep = document.getElementById('repFilter').value.toLowerCase();
        let date = document.getElementById('dateFilter').value;
        let status = document.getElementById('statusFilter').value.toLowerCase();

        let rows = document.querySelectorAll("table tbody tr");

        rows.forEach(row => {
            let rowRep = row.querySelector("td:nth-child(4)").textContent.trim().toLowerCase();
            let rowDate = row.querySelector("td:nth-child(3)").textContent.trim();
            let rowStatus = row.querySelector("td:nth-child(6)").textContent.trim().toLowerCase();

            let show = true;

            if (rep !== "" && rowRep !== rep) show = false;
            if (date !== "" && rowDate !== date) show = false;
            if (status !== "" && rowStatus !== status) show = false;

            row.style.display = show ? "" : "none";
        });
    }


    const rep = document.getElementById('repFilter');
    const date = document.getElementById('dateFilter');
    const status = document.getElementById('statusFilter');

    if (rep) rep.addEventListener('change', applyFilters);
    if (date) date.addEventListener('change', applyFilters);
    if (status) status.addEventListener('change', applyFilters);


    document.querySelectorAll('.submenu').forEach(menu => {
        menu.style.display = "none";
    });

    document.querySelectorAll('.menu-section.clickable').forEach(section => {
        section.addEventListener('click', function() {

            let submenu = this.nextElementSibling;

            if (!submenu || !submenu.classList.contains('submenu')) return;

            if (submenu.style.display === "block") {
                submenu.style.display = "none";
                this.classList.remove('active');
            } else {
                submenu.style.display = "block";
                this.classList.add('active');
            }

        });
    });


    $(document).ready(function() {
        $('#userDropdownTrigger').on('click', function(e) {
            e.stopPropagation();
            $('#navDropdownMenu').toggleClass('show');
            $('.admin-arrow').toggleClass('rotate');
        });

        $(document).on('click', function() {
            $('#navDropdownMenu').removeClass('show');
            $('.admin-arrow').removeClass('rotate');
        });


        const currentPage = window.location.pathname.split("/").pop().replace(".php", "");
        if (currentPage && currentPage !== "index") {
            let formattedName = currentPage.charAt(0).toUpperCase() + currentPage.slice(1);
            $('#dynamicPageTitle').text(formattedName);
        }
    });




    $(document).ready(function() {
        $('#incidentsTable').DataTable({
            "pageLength": 10,
            "dom": 'rtip',
            "language": {
                "paginate": {
                    "previous": "«",
                    "next": "»"
                }
            }
        });
    });
    $(document).ready(function() {
        $('#myIncidentTable').DataTable({
            "paging": true,
            "info": true,
            "searching": false,
            "lengthChange": false,
            "pageLength": 10,
            "language": {
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "paginate": {
                    "previous": "<i class='fa-solid fa-chevron-left'></i>",
                    "next": "<i class='fa-solid fa-chevron-right'></i>"
                }
            }
        });
    });

    $(document).ready(function() {
        $('#newsTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "lengthChange": false,
            "pageLength": 10,
            "responsive": true,
            "dom": 'rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            "columnDefs": [{
                "orderable": false,
                "targets": 4
            }],
            "language": {
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "paginate": {
                    "previous": "<i class='fa-solid fa-chevron-left'></i>",
                    "next": "<i class='fa-solid fa-chevron-right'></i>"
                }
            }
        });

        $('#newsSearch').on('keyup', function() {
            $('#newsTable').DataTable().search($(this).val()).draw();
        });
    });

    $(document).ready(function() {

        $('#reportsTable').DataTable({
            pageLength: 10,
            dom: 'rt<"bottom"ip>',
            language: {
                paginate: {
                    previous: "<",
                    next: ">"
                }
            }
        });

    });


    const ctx = document.getElementById('reportDonut');

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [35, 28, 22, 25, 18],
                backgroundColor: ['#4318FF', '#05CD99', '#FFB547', '#EE5D50', '#A3AED0'],
                borderWidth: 0,
                cutout: '80%'
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: false
                }
            },
            maintainAspectRatio: false
        }
    });

    $(document).ready(function() {
        $('#alertstablep').DataTable({
            "pageLength": 4,
            "searching": false,
            "lengthChange": false,
            "info": false,
            "pagingType": "simple",
            "language": {

                "paginate": {
                    "next": '<i class="fa-solid fa-chevron-right"></i>',
                    "previous": '<i class="fa-solid fa-chevron-left"></i>'
                }
            }

        });
    });

    ////here for incidents;
    let currentIncidentId = null;

    const modal = document.getElementById('viewIncidentModal');

    modal.addEventListener('show.bs.modal', function(event) {

        const button = event.relatedTarget;

        currentIncidentId = button.getAttribute('data-id');

        const description = button.getAttribute('data-description');

        const title = button.getAttribute('data-title');

        document.getElementById('incidentDescription').innerText = description;

        document.getElementById('incidentTitle').innerText = title;
    });

    $('#editDescriptionBtn').click(function() {

        let currentText = $('#incidentDescription').text();

        $('#editDescriptionTextarea').val(currentText);

        $('#incidentDescription').addClass('d-none');

        $('#editDescriptionTextarea').removeClass('d-none');

        $('#saveDescriptionBtn').removeClass('d-none');

    });

    $('#saveDescriptionBtn').click(function() {

        let description = $('#editDescriptionTextarea').val();

        $.ajax({

            url: 'actions/update_description.php',

            type: 'POST',

            data: {
                id: currentIncidentId,
                description: description
            },

            dataType: 'json',

            success: function(response) {

                if (response.status === 'success') {

                    $('#incidentDescription').text(description);

                    $('#incidentDescription').removeClass('d-none');

                    $('#editDescriptionTextarea').addClass('d-none');

                    $('#saveDescriptionBtn').addClass('d-none');

                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });

                }

            }

        });

    });

    $('#saveIncidentBtn').click(function() {

        let incident_name = $('#addIncidentName').val();

        let location = $('#addLocation').val();

        let severity = $('#addSeverity').val();

        let status = $('#addStatus').val();

        let description = $('#addDescription').val();

        $.ajax({

            url: 'actions/add_incident.php',

            type: 'POST',

            data: {
                incident_name: incident_name,
                location: location,
                severity: severity,
                status: status,
                description: description
            },

            dataType: 'json',

            success: function(response) {

                if (response.status === 'success') {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    setTimeout(() => {

                        window.location.reload();

                    }, 1500);

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });

                }

            }

        });

    });

    $(document).on('click', '.deleteBtn', function() {

        let id = $(this).data('id');

        Swal.fire({
            title: 'Delete Incident?',
            text: "This action cannot be undone",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Delete'
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: 'actions/delete_incident.php',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    dataType: 'json',

                    success: function(response) {

                        if (response.status === 'success') {

                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });

                            $('.deleteBtn[data-id="' + id + '"]').closest('tr').fadeOut();

                        } else {

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    }
                });

            }

        });

    });

    $(document).on('click', '.editBtn', function() {

        let row = $(this).closest('tr');

        let incidentName = row.find('.incident-name').text().trim();
        let location = row.find('.incident-location').text().trim();
        let severity = row.find('.incident-severity').text().trim();
        let status = row.find('.incident-status').text().trim();

        row.find('.incident-name').html(`
        <input type="text"
               class="form-control edit-incident-name"
               value="${incidentName}">
    `);

        row.find('.incident-location').html(`
        <input type="text"
               class="form-control edit-location"
               value="${location}">
    `);

        row.find('.incident-severity').html(`
        <select class="form-select edit-severity">
            <option ${severity == 'Low' ? 'selected' : ''}>Low</option>
            <option ${severity == 'Medium' ? 'selected' : ''}>Medium</option>
            <option ${severity == 'High' ? 'selected' : ''}>High</option>
        </select>
    `);

        row.find('.incident-status').html(`
        <select class="form-select edit-status">
            <option ${status == 'Investigating' ? 'selected' : ''}>Investigating</option>
            <option ${status == 'In Progress' ? 'selected' : ''}>In Progress</option>
            <option ${status == 'Resolved' ? 'selected' : ''}>Resolved</option>
        </select>
    `);

        // replace action buttons
        row.find('td:last').html(`
        <button class="btn btn-success btn-sm saveBtn"data-id="${$(this).data('id')}">
            Save
        </button>

        <button class="btn btn-secondary btn-sm cancelBtn" data-id="${$(this).data('id')}">
            Cancel
        </button>
    `);

    });


    $(document).on('click', '.saveBtn', function() {

        let row = $(this).closest('tr');

        let id = row.find('.cancelBtn').data('id');

        let incidentName = row.find('.edit-incident-name').val();
        let inlocation = row.find('.edit-location').val();
        let severity = row.find('.edit-severity').val();
        let status = row.find('.edit-status').val();

        $.ajax({

            url: 'actions/update_incident.php',

            type: 'POST',

            data: {
                id: id,
                incident_name: incidentName,
                location: inlocation,
                severity: severity,
                status: status

            },

            dataType: 'json',

            success: function(response) {

                if (response.status === 'success') {

                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    window.location.reload();

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            }

        });

    });

    $(document).on('click', '.cancelBtn', function() {

        location.reload();

    });

    function filterIncidents() {

        let search = $('#searchFilter').val().toLowerCase();

        let type = $('#typeFilter').val().toLowerCase();

        let region = $('#regionFilter').val().toLowerCase();

        let status = $('#statusFilter').val().toLowerCase();

        let date = $('#dateFilter').val();

        $('#myIncidentTable tbody tr').each(function() {

            let incidentName = $(this)
                .find('.incident-name')
                .text()
                .toLowerCase();

            let incidentType = $(this)
                .find('.incident-severity')
                .text()
                .toLowerCase();

            let incidentLocation = $(this)
                .find('.incident-location')
                .text()
                .toLowerCase();

            let incidentStatus = $(this)
                .find('.incident-status')
                .text()
                .toLowerCase();

            let incidentDate = $(this)
                .find('.incident-date')
                .data('date');


            let matchSearch =

                incidentName.includes(search) ||

                incidentLocation.includes(search);


            let matchType =

                type === '' ||

                incidentType.includes(type);


            let matchRegion =

                region === '' ||

                incidentLocation.includes(region);


            let matchStatus =

                status === '' ||

                incidentStatus.includes(status);


            let matchDate =

                date === '' ||

                incidentDate === date;


            if (
                matchSearch &&
                matchType &&
                matchRegion &&
                matchStatus &&
                matchDate
            ) {

                $(this).show();

            } else {

                $(this).hide();

            }

        });

    }

    $('#searchFilter').on('keyup', filterIncidents);

    $('#typeFilter').on('change', filterIncidents);

    $('#regionFilter').on('change', filterIncidents);

    $('#statusFilter').on('change', filterIncidents);

    $('#dateFilter').on('change', filterIncidents);
</script>