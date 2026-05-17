<?php
session_start();
require_once("class/news.class.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$new = new news();

$allnews = $new->getAllNews();
$total = $new->totalNews();
$draft = $new->draftNews();
$published = $new->publishedNews();
$featuredcount = $new->featuredNews();
$featured = $new->getFeaturedNews();
$feat_article = $new->getLatestFeaturedArticle();
$categories = $new->getCategories();
// $categories = $new->getCategories();

$catStyles = [
    'Weather'        => ['icon' => 'fa-cloud-sun',      'color' => '#007bff', 'bg' => '#e7f1ff'],
    'Traffic'        => ['icon' => 'fa-car',            'color' => '#4b0082', 'bg' => '#f1e6ff'],
    'Safety'         => ['icon' => 'fa-shield-halved',  'color' => '#dc3545', 'bg' => '#ffeef0'],
    'Infrastructure' => ['icon' => 'fa-building',       'color' => '#6f42c1', 'bg' => '#f5f0ff'],
    'Medical'        => ['icon' => 'fa-hospital',       'color' => '#198754', 'bg' => '#e8f5e9'],
    'General'        => ['icon' => 'fa-circle-info',    'color' => '#d97706', 'bg' => '#fff4e6'],
    'Tech'           => ['icon' => 'fa-microchip',      'color' => '#0dcaf0', 'bg' => '#e0f7fa'],
    'Sports'         => ['icon' => 'fa-volleyball',     'color' => '#fd7e14', 'bg' => '#fff3e0'],
    'Politics'       => ['icon' => 'fa-landmark',       'color' => '#212529', 'bg' => '#f8f9fa'],
    'Economy'        => ['icon' => 'fa-chart-line',     'color' => '#20c997', 'bg' => '#e6fffa'],
];

?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <?php include('includes/header.php'); ?>
    <style>
        .active-filter {
            background: #4318ff !important;
            color: white !important;
            border-color: #4318ff !important;
        }

        .category-item:hover {
            transform: translateY(-2px);
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .modal-dialog {
            margin: 1.75rem auto;
        }

        .modal-content {
            border-radius: 20px;
        }

        .modal-body {
            overflow-x: hidden;
        }

        .modal input:not([type="checkbox"]),
        .modal textarea,
        .modal select {
            width: 100%;
            max-width: 100%;
        }

        body.modal-open {
            overflow: hidden;
            padding-right: 0 !important;
        }
    </style>
</head>

<?php foreach ($allnews as $row) { ?>
    <div class="modal fade" id="viewNewsModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">

                <div class="row g-0">
                    <div class="col-lg-5 d-none d-lg-block"
                        style="background: url('uploads/<?= $row['image'] ?>') center/cover no-repeat; min-height: 500px;">
                    </div>

                    <div class="col-12 d-lg-none">
                        <img src="uploads/<?= $row['image'] ?>"
                            class="w-100 h-100"
                            style="object-fit: cover; object-position: center; image-rendering: -webkit-optimize-contrast;"
                            alt="News Image">
                    </div>

                    <div class="col-lg-7 d-flex flex-column">

                        <div class="modal-header border-0 px-4 pt-4 pb-0">
                            <div>
                                <?php if ($row['featured'] == 1) { ?>
                                    <span class="badge rounded-pill mb-2" style="background:rgba(255, 181, 71, 0.15); color:#ffb547;">
                                        <i class="fa-solid fa-star me-1"></i> Featured
                                    </span>
                                <?php } ?>
                                <h3 class="modal-title fw-bold d-block text-dark" style="letter-spacing: -0.5px;">
                                    News Preview
                                </h3>
                                <div class="d-flex gap-3 mt-1">
                                    <!-- Created Date -->
                                    <small class="text-muted">
                                        <i class="fa-solid fa-pen-nib me-1" style="font-size: 0.75rem;"></i>
                                        Drafted: <?= date("M d, Y", strtotime($row['created_at'])) ?>
                                    </small>
                                    <!-- Published Date -->
                                    <small class="fw-semibold">
                                        <i class="fa-regular fa-calendar-check me-1"></i>
                                        <?php if (!empty($row['publish_date'])): ?>
                                            Published: <?= date("M d, Y", strtotime($row['publish_date'])) ?>
                                        <?php else: ?>
                                            <span class="text-warning">Status: Draft</span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                            <button type="button" class="btn-close ms-auto align-self-start" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body px-4 py-4 flex-grow-1">
                            <div class="news-content" style="font-size: 1.05rem; line-height: 1.8; color: #444;">
                                <?= nl2br($row['content']) ?>
                            </div>
                        </div>

                        <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex justify-content-start">
                            <a href="view_news.php?id=<?= $row['id'] ?>"
                                target="_blank"
                                class="btn btn-light rounded-3 px-3 py-2 border fw-semibold shadow-sm">

                                <i class="fa-solid fa-file-pdf text-danger me-2"></i>
                                View / Print Article

                            </a>

                            <button type="button" class="btn btn-dark rounded-3 px-4 py-2 fw-semibold ms-auto shadow-sm" data-bs-dismiss="modal">
                                Close
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
<?php } ?>

<body>
    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>
    <div class="main-content">
        <div class="page-header mb-4">
            <h2>News Management</h2>
            <p class="text-muted small">Publish and organize the latest news and announcements</p>
        </div>
        <div class="row g-3 mb-4">
            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #f4f7fe; color: #4318ff;">
                        <i class="fa-solid fa-newspaper"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Total News</span>
                        <span class="card-value"><?= $total ?></span>
                        <span class="card-subtext">All time</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #f2faf8; color: #05cd99;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Published</span>
                        <span class="card-value"><?= $published  ?></span>
                        <span class="card-subtext">Live now</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #f4f7fe; color: #a3adc2;">
                        <i class="fa-solid fa-file-pen"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Drafts</span>
                        <span class="card-value"><?= $draft ?></span>
                        <span class="card-subtext">In progress</span>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #fff9f2; color: #ffb547;">
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Featured</span>
                        <span class="card-value"><?= $featuredcount ?></span>
                        <span class="card-subtext">Main page</span>
                    </div>
                </div>
            </div>

            <!-- <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #fff5f5; color: #ee5d50;">
                        <i class="fa-solid fa-eye"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Total Views</span>
                        <span class="card-value">1.2k</span>
                        <span class="card-subtext">This week</span>
                    </div>
                </div>
            </div> -->
        </div>

        <div class="filter-row-container mb-4 d-flex align-items-center">
            <div class="search-container">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="newsSearch" class="form-control filter-control" placeholder="Search news articles...">
            </div>

            <div class="filter-group-item ms-3">
                <select id="statusFilternews" class="form-select filter-control">
                    <option selected>All Statuses</option>
                    <option>Published</option>
                    <option>Draft</option>
                </select>
            </div>

            <div class="filter-group-item ms-3 position-relative">
                <input type="text" id="newsDateFilter" class="form-control filter-control" placeholder="Select Date" onfocus="(this.type='date')">
                <i class="fa-regular fa-calendar position-absolute" style="right:12px; top:12px; color:#a3adc2; pointer-events:none;"></i>
            </div>

            <button id="resetFilters" class="btn btn-light border ms-2">
                <i class="fa-solid fa-rotate-left me-1"></i>
                Reset
            </button>

            <button class="btn btn-add-navy ms-auto"
                data-bs-toggle="modal"
                data-bs-target="#addNewsModal">
                <i class="fa-solid fa-plus me-1"></i> Publish News
            </button>
        </div>

        <div class="row g-4 align-items-stretch">
            <div class="col-lg-8 d-flex">
                <div class="table-container  w-100 shadow-sm p-4 bg-white rounded-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="table-main-title">News Articles List</h5>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle" id="newsTable" style="width:100%">

                            <thead>
                                <tr>
                                    <th class="col-shrink">Title</th>
                                    <th class="col-shrink"> Category</th>
                                    <th class="col-shrink"> Type </th>
                                    <th class="col-shrink">Status</th>
                                    <th class="text-center col-shrink ">Actions</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php
                                foreach ($allnews as $row) {

                                    if ($row['status'] == 'Published') {

                                        $statusClass = "text-success";
                                    } else {

                                        $statusClass = "text-warning";
                                    }
                                ?>


                                    <tr data-date="<?= !empty($row['publish_date']) ? date('Y-m-d', strtotime($row['publish_date'])) : '' ?>">
                                        <td class="col-shrink">

                                            <div class="d-flex align-items-center">
                                                <div style="font-weight:700;">
                                                    <?= $row['title'] ?>
                                                </div>

                                            </div>

                                        </td>

                                        <td class="col-shrink">
                                            <?= $row['category'] ?>
                                        </td>

                                        <td>
                                            <?php if ($row['type'] == 'Article'): ?>
                                                <span class="badge rounded-3" style="background: #eef2ff; color: #4318ff; border: 1px solid #d0d7ff;">
                                                    <i class="fa-solid fa-file-lines me-1"></i> Article
                                                </span>
                                            <?php else: ?>
                                                <span class="badge rounded-3" style="background: #fff5f5; color: #ee5d50; border: 1px solid #ffdada;">
                                                    <i class="fa-solid fa-bolt-lightning me-1"></i> News
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="col-shrink">

                                            <span class="status-text <?= $statusClass ?>">
                                                <?= $row['status'] ?>
                                            </span>

                                        </td>

                                        <!-- ACTIONS -->
                                        <td class="text-center col-shrink">

                                            <!-- VIEW -->
                                            <i class="fa fa-eye text-primary me-3 viewBtn"
                                                style="cursor:pointer;"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewNewsModal<?= $row['id'] ?>">
                                            </i>

                                            <i class="fa fa-edit text-muted me-3 editNewsBtn"
                                                style="cursor:pointer;"
                                                data-id="<?= $row['id'] ?>"
                                                data-title="<?= htmlspecialchars($row['title']) ?>"
                                                data-content="<?= htmlspecialchars($row['content']) ?>"
                                                data-category="<?= $row['category'] ?>"
                                                data-type="<?= $row['type'] ?>"
                                                data-status="<?= $row['status'] ?>"
                                                data-featured="<?= $row['featured'] ?>"
                                                data-date="<?= !empty($row['publish_date']) ? date('Y-m-d', strtotime($row['publish_date'])) : '' ?>">
                                            </i>

                                            <!-- DELETE -->
                                            <i class="fa fa-trash text-danger deleteNewsBtn"
                                                style="cursor:pointer;"
                                                data-id="<?= $row['id'] ?>">
                                            </i>

                                        </td>

                                    </tr>

                                <?php } ?>

                            </tbody>

                        </table>

                    </div>
                </div>
            </div>
            <div class="col-lg-4 d-flex ">
                <div class="w-100">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0" style="color: #1b2559;">Featured Article</h5>
                                <span class="badge rounded-pill px-3" style="background: rgba(246, 101, 85, 0.1); color: #e60e0e; font-size: 0.7rem;">
                                    <i class="fa-solid fa-bolt me-1"></i> TOP ONE
                                </span>
                            </div>

                            <?php if ($feat_article): ?>
                                <div class="featured-news-card">
                                    <div class="position-relative mb-3">
                                        <img src="uploads/<?= $feat_article['image'] ?>"
                                            class="rounded-4 w-100 shadow-sm"
                                            style="height: 180px; object-fit: cover; filter: brightness(0.95);"
                                            alt="Featured">
                                    </div>

                                    <h6 class="fw-bold mb-2" style="color: #1b2559; font-size: 1.1rem; line-height: 1.4;">
                                        <?= $feat_article['title'] ?>
                                    </h6>

                                    <p class="text-muted mb-3" style="font-size: 0.85rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?= strip_tags($feat_article['content']) ?>
                                    </p>

                                    <div class="d-flex justify-content-between align-items-center mt-auto">
                                        <span class="text-muted small">
                                            <i class="fa-regular fa-calendar me-1"></i>
                                            <?= date("M d, Y", strtotime($feat_article['publish_date'])) ?>
                                        </span>
                                        <button class="btn-read-more"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewNewsModal<?= $feat_article['id'] ?>">
                                            Read More
                                        </button>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fa-solid fa-layer-group text-light mb-2" style="font-size: 2rem;"></i>
                                    <p class="text-muted small">No featured articles currently set.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 rounded-4 mt-2">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3" style="color: #1b2559;">News Categories</h5>
                            <div class="row g-2">
                                <?php foreach ($categories as $cat):
                                    $style = $catStyles[$cat] ?? ['icon' => 'fa-tag', 'color' => '#6c757d', 'bg' => '#f8f9fa'];
                                ?>
                                    <div class="col-6">
                                        <div class="category-filter category-item d-flex align-items-center p-2 rounded-3"
                                            data-category="<?= $cat ?>"
                                            style="cursor: pointer; border: 2px solid transparent;">

                                            <i class="fa-solid <?= $style['icon'] ?> me-2" style="color: <?= $style['color'] ?>;"></i>

                                            <span class="fw-bold" style="color: <?= $style['color'] ?>; font-size: 0.85rem;">
                                                <?= $cat ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="modal fade" id="addNewsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered ">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Publish News</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>


                <div class="modal-body">


                    <input type="text" id="newsTitle" class="form-control mb-2" placeholder="News Title">


                    <textarea id="newsContent" class="form-control mb-2" placeholder="News Content" rows="4"></textarea>


                    <select id="newsCategory" class="form-control mb-2" required>
                        <option value="">Select Category</option>

                        <option value="Weather">Weather</option>
                        <option value="Traffic">Traffic</option>
                        <option value="Safety">Safety</option>
                        <option value="Medical">Medical</option>
                        <option value="Infrastructure">Infrastructure</option>
                        <option value="General">General</option>
                        <option value="Tech">Tech</option>
                        <option value="Sports">Sports</option>
                        <option value="Politics">Politics</option>
                        <option value="Economy">Economy</option>
                    </select>

                    <select id="newsType" class="form-control mb-2">
                        <option value="News">News</option>
                        <option value="Article">Article</option>
                    </select>

                    <select id="newsStatus" class="form-control mb-2">
                        <option value="Published">Published</option>
                        <option value="Draft">Draft</option>
                    </select>

                    <div class="form-check mb-2">
                        <input type="checkbox" id="newsFeatured" class="form-check-input">
                        <label class="form-check-label" for="newsFeatured">
                            Featured News
                        </label>
                    </div>

                    <input type="date" id="newsDate" class="form-control mb-2">

                    <input type="file" id="newsImage" class="form-control mb-3">

                    <button type="button" id="saveNewsBtn" class="btn btn-success w-100">
                        Save News
                    </button>

                </div>

            </div>
        </div>
    </div>
    <div class="modal fade" id="updateNewsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered ">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Update News</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- hidden ID -->
                    <input type="hidden" id="updateNewsId">

                    <input type="text" id="updateNewsTitle" class="form-control mb-2" placeholder="News Title">

                    <textarea id="updateNewsContent" class="form-control mb-2" rows="4"></textarea>

                    <select id="updateNewsCategory" class="form-control mb-2">
                        <option>Weather</option>
                        <option>Traffic</option>
                        <option>Safety</option>
                        <option>Medical</option>
                        <option>Infrastructure</option>
                        <option>General</option>
                        <option>Tech</option>
                        <option>Sports</option>
                        <option>Politics</option>
                        <option>Economy</option>
                    </select>
                    <select id="updateNewsType" class="form-control mb-2">
                        <option value="News">News</option>
                        <option value="Article">Article</option>
                    </select>

                    <select id="updateNewsStatus" class="form-control mb-2">
                        <option>Published</option>
                        <option>Draft</option>
                    </select>

                    <div class="form-check mb-2">
                        <input type="checkbox" id="updateNewsFeatured" class="form-check-input">
                        <label class="form-check-label">Featured</label>
                    </div>

                    <input type="date" id="updateNewsDate" class="form-control mb-2">

                    <input type="file" id="updateNewsImage" class="form-control mb-3">

                    <button type="button" id="updateNewsBtn" class="btn btn-primary w-100">
                        Update News
                    </button>

                </div>

            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {

            $('#saveNewsBtn').click(function() {

                let formData = new FormData();

                formData.append('title', $('#newsTitle').val());
                formData.append('content', $('#newsContent').val());
                formData.append('category', $('#newsCategory').val());
                formData.append('type', $('#newsType').val());
                formData.append('status', $('#newsStatus').val());
                formData.append('featured', $('#newsFeatured').is(':checked') ? 1 : 0);
                formData.append('publish_date', $('#newsDate').val());

                let image = $('#newsImage')[0].files[0];
                if (image) {
                    formData.append('image', image);
                }

                $.ajax({
                    url: 'actions/add_news.php',
                    type: 'POST',
                    data: formData,
                    processData: false, 
                    contentType: false, 
                    dataType: 'json',

                    success: function(response) {

                        console.log(response);

                        if (response.status === 'success') {
                            $('#addNewsModal').modal('hide');

                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });

                            setTimeout(() => {
                                location.reload();
                            }, 1500);

                        } else {

                            Swal.fire(
                                'Error',
                                response.message,
                                'error'
                            );
                        }
                    },

                    error: function() {
                        Swal.fire(
                            'Error',
                            'Something went wrong!',
                            'error'
                        );
                    }
                });

            });

        });

        $(document).on('click', '.deleteNewsBtn', function() {

            let id = $(this).data('id');

            Swal.fire({
                title: 'Delete News?',
                text: "This action cannot be undone",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Delete'
            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({
                        url: 'actions/delete_news.php',
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

                                $('.deleteNewsBtn[data-id="' + id + '"]').closest('tr').fadeOut();

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

        $(document).on('click', '.editNewsBtn', function() {

            $('#updateNewsId').val($(this).data('id'));

            $('#updateNewsTitle').val($(this).data('title'));
            $('#updateNewsContent').val($(this).data('content'));
            $('#updateNewsCategory').val($(this).data('category'));
            $('#updateNewsType').val($(this).data('type'));
            $('#updateNewsStatus').val($(this).data('status'));
            $('#updateNewsDate').val($(this).data('date'));

            $('#updateNewsFeatured').prop('checked', $(this).data('featured') == 1);

            let modal = new bootstrap.Modal(document.getElementById('updateNewsModal'));
            modal.show();
        });

        $(document).on('click', '#updateNewsBtn', function() {

            let formData = new FormData();

            formData.append('id', $('#updateNewsId').val());
            formData.append('title', $('#updateNewsTitle').val());
            formData.append('content', $('#updateNewsContent').val());
            formData.append('category', $('#updateNewsCategory').val());
            formData.append('type', $('#updateNewsType').val());
            formData.append('status', $('#updateNewsStatus').val());
            formData.append('featured', $('#updateNewsFeatured').is(':checked') ? 1 : 0);
            formData.append('publish_date', $('#updateNewsDate').val());

            let image = $('#updateNewsImage')[0].files[0];
            if (image) {
                formData.append('image', image);
            }

            $.ajax({
                url: 'actions/update_news.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',

                success: function(response) {

                    if (response.status === 'success') {

                        $('#updateNewsModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        setTimeout(() => {
                            location.reload();
                        }, 1500);

                    } else {

                        Swal.fire(
                            'Error',
                            response.message,
                            'error'
                        );
                    }
                }
            });

        });
    </script>

    <script>
        function applyNewsFilters() {

            let status = document.getElementById('statusFilternews').value.toLowerCase();

            let date = document.getElementById('newsDateFilter').value;

            let rows = document.querySelectorAll("#newsTable tbody tr");

            rows.forEach(row => {

                let rowStatus = row.querySelector("td:nth-child(4)")
                    .textContent.trim().toLowerCase();

                let rowDate = row.getAttribute("data-date");

                let show = true;

                if (
                    status !== "all statuses" &&
                    rowStatus !== status
                ) {
                    show = false;
                }

                if (
                    date !== "" &&
                    rowDate !== date
                ) {
                    show = false;
                }

                row.style.display = show ? "" : "none";
            });
        }

      
        document.getElementById('statusFilternews')
            .addEventListener('change', applyNewsFilters);

        document.getElementById('newsDateFilter')
            .addEventListener('change', applyNewsFilters);

   
        document.getElementById('resetFilters')
            .addEventListener('click', function() {

                document.getElementById('statusFilternews').value = 'All Statuses';

                document.getElementById('newsDateFilter').value = '';

                applyNewsFilters();
            });
    </script>
    <script>
        document.querySelectorAll('.category-filter').forEach(card => {

            card.addEventListener('click', function() {

                let category = this.getAttribute('data-category').toLowerCase();

                let rows = document.querySelectorAll('#newsTable tbody tr');

                rows.forEach(row => {

                    let rowCategory = row.querySelector('td:nth-child(2)')
                        .textContent.trim().toLowerCase();

                    if (rowCategory === category) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }

                });

            });

        });
    </script>

















    <?php include('includes/script.php'); ?>
</body>

</html>