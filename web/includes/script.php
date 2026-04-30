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
    section.addEventListener('click', function () {

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
    if(currentPage && currentPage !== "index") {
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
            var table = $('#alertTable').DataTable({
                "paging": true,
                "info": true,
                "ordering": true,
                "lengthChange": false,
                "dom": 'rt<"d-flex justify-content-between align-items-center"ip>', 
                "language": {
                    "paginate": {
                        "previous": "<i class='fa-solid fa-chevron-left'></i>",
                        "next": "<i class='fa-solid fa-chevron-right'></i>"
                    }
                }
            });

            $('#alertSearch').keyup(function(){
                table.search($(this).val()).draw();
            })
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
        "columnDefs": [
            { "orderable": false, "targets": 5 }
        ],
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

</script>