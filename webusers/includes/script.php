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
$(document).ready(function() {
    $('#teamsTable').DataTable({
        "pageLength": 5,         
        "searching": false,       
        "lengthChange": false,      
        "info": true,              
        "paging": true,             
        "dom": 'rt<"d-flex justify-content-between align-items-center mt-3"ip>', 
    });
});

$(document).ready(function() {
    $('#shelterTable').DataTable({
        "pageLength": 5,          
        "lengthChange": false,    
        "searching": false,       
        "info": false,            
        "ordering": true,         
        "autoWidth": false,      
        "language": {
            "paginate": {
                "next": '<i class="fa-solid fa-chevron-right"></i>',
                "previous": '<i class="fa-solid fa-chevron-left"></i>'
            }
        },
        "dom": 'tp' 
    });
});

$(document).ready(function() {
    $('#needsTable').DataTable({
        "pageLength": 5,         
        "lengthChange": false,   
        "searching": false,       
        "info": false,         
        "pagingType": "simple",   
        "language": {
            "paginate": {
                "next": '<i class="fa-solid fa-chevron-right"></i>',
                "previous": '<i class="fa-solid fa-chevron-left"></i>'
            }
        },
        "dom": 'tp'               
    });
});

</script>