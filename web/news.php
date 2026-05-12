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
    </style>
</head>

<?php foreach ($allnews as $row) { ?>
    <div class="modal fade" id="viewNewsModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered " style="max-width: 850px;">
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
                            <a href="generate_doc.php?id=<?= $row['id'] ?>" class="btn btn-light rounded-3 px-3 py-2 border fw-semibold shadow-sm">
                                <i class="fa-solid fa-file-word text-primary me-2"></i>
                                Download as Word
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
                <select id="statusFilter" class="form-select filter-control">
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
            <button class="btn btn-add-navy ms-auto">
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

                                            <!-- EDIT -->
                                            <i class="fa fa-edit text-muted me-3 editBtnnews"
                                                style="cursor:pointer;"

                                                data-id="<?= $row['id'] ?>">
                                            </i>

                                            <!-- DELETE -->
                                            <i class="fa fa-trash text-danger deleteBtn"
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
    <?php include('includes/script.php'); ?>
</body>

</html>
