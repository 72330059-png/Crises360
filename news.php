<?php
session_start();
require_once("class/DAL.class.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$dal = new DAL();

?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <?php include('includes/header.php'); ?>
    <style>

    </style>

</head>

<body>

    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>
    <div class="main-content">

        <div class="row g-3 mb-4">
            <div class="col">
                <div class="dashboard-card">
                    <div class="card-icon" style="background: #f4f7fe; color: #4318ff;">
                        <i class="fa-solid fa-newspaper"></i>
                    </div>
                    <div class="card-content">
                        <span class="card-title">Total News</span>
                        <span class="card-value">85</span>
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
                        <span class="card-value">70</span>
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
                        <span class="card-value">10</span>
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
                        <span class="card-value">5</span>
                        <span class="card-subtext">Main page</span>
                    </div>
                </div>
            </div>

            <div class="col">
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
            </div>
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
                                    <th class="col-shrink">ID</th>
                                    <th class="col-grow">Title</th>
                                    <th class="col-shrink">Category</th>
                                    <th class="col-shrink">Date</th>
                                    <th class="col-shrink">Status</th>
                                    <th class="text-end col-shrink">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="col-shrink">#301</td>
                                    <td class="td-title-bold">Storm Warning Issued for Northern Coastal Regions</td>
                                    <td class="col-shrink">Weather</td>
                                    <td class="text-muted col-shrink">2026-04-28</td>
                                    <td class="col-shrink"><span class="status-text text-resolved">Published</span></td>
                                    <td class="text-end col-shrink">
                                        <i class="fa-regular fa-eye text-muted me-2 cursor-pointer"></i>
                                        <i class="fa-solid fa-trash text-danger cursor-pointer"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="col-shrink">#302</td>
                                    <td class="td-title-bold">New Technology Park to Open Downtown Next Month</td>
                                    <td class="col-shrink">Tech</td>
                                    <td class="text-muted col-shrink">2026-04-27</td>
                                    <td class="col-shrink"><span class="status-text text-resolved">Published</span></td>
                                    <td class="text-end col-shrink">
                                        <i class="fa-regular fa-eye text-muted me-2 cursor-pointer"></i>
                                        <i class="fa-solid fa-trash text-danger cursor-pointer"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="col-shrink">#303</td>
                                    <td class="td-title-bold">Local Sports Team Wins Championship Title</td>
                                    <td class="col-shrink">Sports</td>
                                    <td class="text-muted col-shrink">2026-04-26</td>
                                    <td class="col-shrink"><span class="status-text text-resolved">Published</span></td>
                                    <td class="text-end col-shrink">
                                        <i class="fa-regular fa-eye text-muted me-2 cursor-pointer"></i>
                                        <i class="fa-solid fa-trash text-danger cursor-pointer"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="col-shrink">#304</td>
                                    <td class="td-title-bold">City Council Approves New Budget for Education</td>
                                    <td class="col-shrink">Politics</td>
                                    <td class="text-muted col-shrink">2026-04-25</td>
                                    <td class="col-shrink"><span class="status-text text-investigating">Draft</span></td>
                                    <td class="text-end col-shrink">
                                        <i class="fa-regular fa-eye text-muted me-2 cursor-pointer"></i>
                                        <i class="fa-solid fa-trash text-danger cursor-pointer"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="col-shrink">#305</td>
                                    <td class="td-title-bold">Rising Gas Prices Impacting Local Commuters</td>
                                    <td class="col-shrink">Economy</td>
                                    <td class="text-muted col-shrink">2026-04-24</td>
                                    <td class="col-shrink"><span class="status-text text-resolved">Published</span></td>
                                    <td class="text-end col-shrink">
                                        <i class="fa-regular fa-eye text-muted me-2 cursor-pointer"></i>
                                        <i class="fa-solid fa-trash text-danger cursor-pointer"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="col-shrink">#306</td>
                                    <td class="td-title-bold">New Medical Discovery Could Lead to Cancer Cure</td>
                                    <td class="col-shrink">Health</td>
                                    <td class="text-muted col-shrink">2026-04-23</td>
                                    <td class="col-shrink"><span class="status-text text-resolved">Published</span></td>
                                    <td class="text-end col-shrink">
                                        <i class="fa-regular fa-eye text-muted me-2 cursor-pointer"></i>
                                        <i class="fa-solid fa-trash text-danger cursor-pointer"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="col-shrink">#307</td>
                                    <td class="td-title-bold">Annual Music Festival Set for Summer Return</td>
                                    <td class="col-shrink">Entertainment</td>
                                    <td class="text-muted col-shrink">2026-04-22</td>
                                    <td class="col-shrink"><span class="status-text text-resolved">Published</span></td>
                                    <td class="text-end col-shrink">
                                        <i class="fa-regular fa-eye text-muted me-2 cursor-pointer"></i>
                                        <i class="fa-solid fa-trash text-danger cursor-pointer"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="col-shrink">#308</td>
                                    <td class="td-title-bold">Upcoming Road Closures for Bridge Maintenance</td>
                                    <td class="col-shrink">Traffic</td>
                                    <td class="text-muted col-shrink">2026-04-21</td>
                                    <td class="col-shrink"><span class="status-text text-investigating">Draft</span></td>
                                    <td class="text-end col-shrink">
                                        <i class="fa-regular fa-eye text-muted me-2 cursor-pointer"></i>
                                        <i class="fa-solid fa-trash text-danger cursor-pointer"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="col-shrink">#309</td>
                                    <td class="td-title-bold">New Restaurant Opening in the Heart of the City</td>
                                    <td class="col-shrink">Lifestyle</td>
                                    <td class="text-muted col-shrink">2026-04-20</td>
                                    <td class="col-shrink"><span class="status-text text-resolved">Published</span></td>
                                    <td class="text-end col-shrink">
                                        <i class="fa-regular fa-eye text-muted me-2 cursor-pointer"></i>
                                        <i class="fa-solid fa-trash text-danger cursor-pointer"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="col-shrink">#310</td>
                                    <td class="td-title-bold">Major Security Breach Fixed by Software Team</td>
                                    <td class="col-shrink">Tech</td>
                                    <td class="text-muted col-shrink">2026-04-19</td>
                                    <td class="col-shrink"><span class="status-text text-resolved">Published</span></td>
                                    <td class="text-end col-shrink">
                                        <i class="fa-regular fa-eye text-muted me-2 cursor-pointer"></i>
                                        <i class="fa-solid fa-trash text-danger cursor-pointer"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="col-shrink">#311</td>
                                    <td class="td-title-bold">Rare Bird Sighting Excites Nature Enthusiasts</td>
                                    <td class="col-shrink">Environment</td>
                                    <td class="text-muted col-shrink">2026-04-18</td>
                                    <td class="col-shrink"><span class="status-text text-resolved">Published</span></td>
                                    <td class="text-end col-shrink">
                                        <i class="fa-regular fa-eye text-muted me-2 cursor-pointer"></i>
                                        <i class="fa-solid fa-trash text-danger cursor-pointer"></i>
                                    </td>
                                </tr>
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

                    <div class="card shadow-sm border-0 rounded-4">
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