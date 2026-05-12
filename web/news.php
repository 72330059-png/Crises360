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

?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <?php include('includes/header.php'); ?>

</head>

<?php foreach ($allnews as $row) { ?>

    <div class="modal fade"
        id="viewNewsModal<?= $row['id'] ?>"
        tabindex="-1"
        aria-hidden="true">

        <div class="modal-dialog modal-lg modal-dialog-centered">

            <div class="modal-content border-0 rounded-4 overflow-hidden">

                <!-- HEADER -->
                <div class="modal-header border-0">

                    <h5 class="modal-title fw-bold">
                        News Preview
                    </h5>

                    <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>

                <!-- BODY -->
                <div class="modal-body p-0">

                    <!-- IMAGE -->
                    <img src="uploads/<?= $row['image'] ?>"
                        class="w-100"
                        style="height:320px; object-fit:cover;"
                        alt="News Image">

                    <div class="p-4">

                        <!-- FEATURED -->
                        <?php if ($row['featured'] == 1) { ?>

                            <div class="mb-3">

                                <span class="badge rounded-pill px-3 py-2"
                                    style="background:#fff4db; color:#ffb547; font-weight:600;">

                                    <i class="fa-solid fa-star me-1"></i>
                                    Featured News

                                </span>

                            </div>

                        <?php } ?>

                        <!-- CONTENT -->
                        <p class="text-muted mb-4"
                            style="line-height:1.9;">

                            <?= $row['content'] ?>

                        </p>

                        <!-- FOOTER DETAILS -->
                        <div class="d-flex justify-content-between align-items-center flex-wrap">

                            <div class="text-muted small">

                                <i class="fa-regular fa-clock me-1"></i>

                                Created:
                                <?= date("F d, Y", strtotime($row['created_at'])) ?>

                            </div>

                            <!-- Optional later -->
                            <!--
                        <div class="text-muted small">
                            <i class="fa-regular fa-eye me-1"></i>
                            <?= $row['views'] ?> views
                        </div>
                        -->

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
                <select class="form-select filter-control">
                    <option selected>All Statuses</option>
                    <option>Published</option>
                    <option>Draft</option>
                </select>
            </div>

            <div class="filter-group-item ms-3 position-relative">
                <input type="text" class="form-control filter-control" placeholder="Select Date" onfocus="(this.type='date')">
                <i class="fa-regular fa-calendar position-absolute" style="right:12px; top:12px; color:#a3adc2; pointer-events:none;"></i>
            </div>

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
                                    <!-- <th>ID</th> -->
                                    <th class="col-shrink">Title</th>
                                    <th class="col-shrink"> Category</th>
                                    <th class="col-shrink">Published At</th>
                                    <th class="col-shrink">Status</th>
                                    <th class="text-center col-shrink ">Actions</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php
                                foreach ($allnews as $row) {

                                    // STATUS COLORS
                                    if ($row['status'] == 'Published') {

                                        $statusClass = "text-success";
                                    } else {

                                        $statusClass = "text-warning";
                                    }
                                ?>

                                    <tr>
                                        <!-- TITLE -->
                                        <td class="col-shrink">

                                            <div class="d-flex align-items-center">
                                                <div style="font-weight:700;">
                                                    <?= $row['title'] ?>
                                                </div>

                                            </div>

                                        </td>

                                        <!-- CATEGORY -->
                                        <td class="col-shrink">
                                            <?= $row['category'] ?>
                                        </td>

                                        <!-- PUBLISHED DATE -->
                                        <td class="text-muted col-shrink">

                                            <?php
                                            if (!empty($row['publish_date'])) {
                                                echo date("Y-m-d", strtotime($row['publish_date']));
                                            } else {
                                                echo '<span class="text-muted">Not published yet</span>';
                                            }
                                            ?>

                                        </td>

                                        <!-- STATUS -->
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
                                            <i class="fa fa-edit text-muted me-3 editBtn"
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
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3" style="color: #1b2559;">Featured News</h5>
                            <div class="featured-news-card">
                                <img src="uploads/news1.jpg" class="news-img mb-3" alt="Featured">
                                <h6 class="news-title">Flood situation update in Beirut</h6>
                                <p class="news-text mb-3">
                                    Heavy rainfall has caused flooding in several areas. Citizens are advised to follow safety instructions.
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">May 18, 2025</span>
                                    <button class="btn-read-more">Read More</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 rounded-4 mt-2">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3" style="color: #1b2559;">News Categories</h5>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="category-item cat-weather">
                                        <i class="fa-solid fa-cloud-sun"></i>
                                        <span>Weather</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="category-item cat-traffic">
                                        <i class="fa-solid fa-car"></i>
                                        <span>Traffic</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="category-item cat-safety">
                                        <i class="fa-solid fa-shield-halved"></i>
                                        <span>Safety</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="category-item cat-infra">
                                        <i class="fa-solid fa-building"></i>
                                        <span>Infrastructure</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="category-item cat-medical">
                                        <i class="fa-solid fa-hospital"></i>
                                        <span>Medical</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="category-item cat-general">
                                        <i class="fa-solid fa-circle-info"></i>
                                        <span>General</span>
                                    </div>
                                </div>
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