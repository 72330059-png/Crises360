<?php
session_start();

require_once("class/municipality.class.php");

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}
$municipality = new Municipality();
$org_id = $_SESSION['org_id'];

$totalShelters = $municipality->totalShelters($org_id);
$totalCapacity = $municipality->totalCapacity($org_id);
$totalOccupied = $municipality->totalOccupied($org_id);
$totalAvailable = $municipality->totalAvailable($org_id);

$shelters = $municipality->getShelters($org_id);
$needs = $municipality->getNeeds($org_id);
$resources = $municipality->getResources($org_id);
$donations = $municipality->getDonations($org_id);
$categories = $municipality->getResourceCategories();
$activeNeeds = $municipality->activeNeeds($org_id);
$openResources = $municipality->openResources($org_id);

$categories = $municipality->getEnumValues('needs', 'category');
$priorities = $municipality->getEnumValues('needs', 'priority');
$statuses = $municipality->getEnumValues('needs', 'status');
$categories = $municipality->getEnumValues("resources", "category");
$donationTypes = $municipality->getDonationTypes();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <?php include('includes/header.php'); ?>
    <style>
        .action-buttons {
            white-space: nowrap;
        }

        .card-box {
            border-radius: 12px;
            padding: 15px;
            background: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .stat-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #fff;
        }

        .icon-green {
            background: #28a745;
        }

        .icon-blue {
            background: #0d6efd;
        }

        .icon-orange {
            background: #fd7e14;
        }

        .icon-purple {
            background: #6f42c1;
        }

        .icon-teal {
            background: #20c997;
        }

        .small-text {
            font-size: 12px;
            color: gray;
        }

        .progress {
            height: 6px;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        .status-open {
            color: green;
            font-weight: 500;
        }

        .status-closed {
            color: red;
            font-weight: 500;
        }

        .map-box {
            height: 200px;
            background: #eaeaea;
            border-radius: 10px;
        }

        .badge-soft {
            background: #eef2ff;
            padding: 6px 10px;
            border-radius: 8px;
        }

        .footer-note {
            text-align: center;
            margin-top: 20px;
            color: green;
            font-weight: 500;
        }

        .icon-box {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .bg-soft-orange {
            background-color: #fff4e6;
            color: #ff922b;
        }

        .bg-soft-blue {
            background-color: #e7f5ff;
            color: #228be6;
        }

        .bg-soft-green {
            background-color: #ebfbee;
            color: #40c057;
        }

        .bg-soft-purple {
            background-color: #f3f0ff;
            color: #7950f2;
        }


        .status-urgent {
            color: #e03131;
        }

        .status-normal {
            background-color: #f8f9fa;
            color: #495057;
            border: 1px solid #dee2e6;
        }

        #needsTable td {
            padding: 12px 8px;
        }

        .status-urgent {
            background-color: #fff5f5;
            color: #e03131;
            border: 1px solid #ffc9c9;
            font-size: 0.65rem;
            padding: 4px 8px;
        }

        .status-normal {
            background-color: #f8f9fa;
            color: #495057;
            border: 1px solid #dee2e6;
            font-size: 0.65rem;
            padding: 4px 8px;
        }

        .icon-box {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            align-items: center;
            justify-content: center;
        }

        #shelterTable th:nth-child(n+3),
        #shelterTable td:nth-child(n+3) {
            text-align: center;
        }

        #shelterTable th:nth-child(-n+2),
        #shelterTable td:nth-child(-n+2) {
            text-align: left;
        }

        .resource-modal {
            border-radius: 26px;
            background: #ffffff;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
        }

        .resource-modal-header {
            padding: 22px 24px 14px;
        }

        .resource-main-icon {
            width: 68px;
            height: 68px;
            border-radius: 20px;
            background: #f5f7fa;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
        }

        .resource-main-icon i {
            font-size: 1.7rem;
            color: #198754;
        }

        .resource-status-badge {
            background: #e9f7ef;
            color: #198754;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 5px 14px;
            border-radius: 50px;
        }

        .modal-body {
            padding: 10px 24px 20px;
        }

        .resource-card {
            border: 1px solid #edf1f5;
            border-radius: 20px;
            padding: 18px;
            background: #ffffff;
            display: flex;
            gap: 16px;
            transition: 0.2s ease;
        }

        .resource-card:hover {
            border-color: #dce4ea;
        }

        .resource-card-icon {
            width: 56px;
            height: 56px;
            min-width: 56px;
            border-radius: 16px;
            background: #f7f9fb;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .resource-card-icon i {
            color: #198754;
            font-size: 1.15rem;
        }

        .resource-card-content {
            flex: 1;
        }

        .resource-card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .resource-label {
            font-size: 0.74rem;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 1px;
        }

        .resource-view {
            font-size: 1.08rem;
            font-weight: 500;
            color: #374151;
            line-height: 1.5;
        }

        .resource-input {
            border-radius: 14px;
            border: 1px solid #dbe3ea;
            height: 48px;
            padding: 10px 14px;
            box-shadow: none !important;
        }

        .resource-input:focus {
            border-color: #198754;
        }

        .resource-select {
            border-radius: 14px;
            border: 1px solid #dbe3ea;
            height: 48px;
            box-shadow: none !important;
        }

        .resource-select:focus {
            border-color: #198754;
        }

        .resource-edit-btn {
            border: none;
            background: transparent;
            width: 32px;
            height: 32px;
            border-radius: 10px;
            color: #94a3b8;
            transition: 0.2s ease;
        }

        .resource-edit-btn:hover {
            background: #f4f6f8;
            color: #198754;
        }

        .hours-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            margin-top: 8px;
        }

        .hours-small-label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.78rem;
            color: #94a3b8;
            font-weight: 600;
        }

        .modal-footer {
            padding: 0 24px 24px;
        }

        .btn-success {
            background: #198754;
            border: none;
        }

        .btn-success:hover {
            background: #157347;
        }

        @media(max-width:768px) {

            .hours-grid {
                grid-template-columns: 1fr;
            }

            .resource-main-icon {
                width: 58px;
                height: 58px;
            }

            .resource-main-icon i {
                font-size: 1.4rem;
            }

            .resource-view {
                font-size: 1rem;
            }

        }
    </style>

</head>

<?php foreach ($needs as $need): ?>
    <div class="modal fade" id="needModal<?= $need['id'] ?>" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered modal-sm">

            <div class="modal-content border-0 rounded-4 shadow-sm">

                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body pt-2">

                    <div class="mb-3">

                        <small class="text-success d-block">
                            Category
                        </small>

                        <span class="fw-semibold text-capitalize">
                            <?= $need['category'] ?>
                        </span>

                    </div>

                    <div class="mb-3">
                        <small class="text-success d-block">
                            Quantity
                        </small>

                        <span class="fw-semibold">
                            <?= $need['quantity'] ?>
                        </span>

                    </div>

                    <div class="mb-3">

                        <small class="text-success d-block">
                            Status
                        </small>

                        <span class="fw-semibold text-capitalize">
                            <?= str_replace('_', ' ', $need['status']) ?>
                        </span>

                    </div>

                    <div>

                        <small class="text-success d-block">
                            Description
                        </small>

                        <small style="line-height:1.6;">

                            <?= $municipality->e($need['description']) ?>

                        </small>

                    </div>

                </div>

            </div>

        </div>

    </div>
<?php endforeach; ?>

<div class="modal fade" id="addShelterModal">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content rounded-4 border-0">

            <div class="modal-header border-0">

                <h5 class="fw-bold">
                    Add Shelter
                </h5>

                <button class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <form id="addShelterForm">

                    <input type="text" name="shelter_name" class="form-control mb-3" placeholder="Shelter Name" required>

                    <input type="text" name="location" class="form-control mb-3" placeholder="Location" required>

                    <input type="number" name="capacity" class="form-control mb-3" placeholder="Capacity" required>

                    <button class="btn btn-success w-100"> Save Shelter </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editShelterModal">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content rounded-4 border-0">

            <div class="modal-header border-0">

                <h5 class="fw-bold">
                    Edit Shelter
                </h5>

                <button class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <form id="editShelterForm">

                    <input type="hidden" name="id" id="editShelterId">

                    <input type="text" name="shelter_name" id="editShelterName" class="form-control mb-3" required>

                    <input type="text" name="location" id="editShelterLocation" class="form-control mb-3" required>

                    <input type="number" name="capacity" id="editShelterCapacity" class="form-control mb-3" required>

                    <input type="number" name="occupied" id="editShelterOccupied" class="form-control mb-3" required>

                    <button class="btn btn-success w-100">
                        Update Shelter
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>
<!-- ADD NEED MODAL -->
<div class="modal fade" id="addNeedModal" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content border-0 rounded-4">

            <div class="modal-header border-0 pb-0">

                <h5 class="fw-bold">
                    Add New Need
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body pt-2">

                <form id="addNeedForm">

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">
                                Need Name
                            </label>

                            <input type="text" name="need_name" class="form-control rounded-3" required>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">
                                Category
                            </label>

                            <select name="category" class="form-select rounded-3" required>

                                <option value="">
                                    Select Category
                                </option>
                                <?php foreach ($categories as $category): ?>

                                    <option value="<?= $category ?>">

                                        <?= ucfirst(str_replace('_', ' ', $category)) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Quantity
                            </label>
                            <input type="number" name="quantity" class="form-control rounded-3" min="1" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Priority
                            </label>

                            <select name="priority" class="form-select rounded-3" required>
                                <option value="">
                                    Select Priority
                                </option>

                                <?php foreach ($priorities as $priority): ?>

                                    <option value="<?= $priority ?>">

                                        <?= ucfirst($priority) ?>

                                    </option>

                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                Description
                            </label>
                            <textarea name="description" rows="4" class="form-control rounded-3"></textarea>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-0">
                <button class="btn btn-light border rounded-3 px-4" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button class="btn btn-success rounded-3 px-4" id="saveNeedBtn">
                    Save Need
                </button>
            </div>
        </div>
    </div>
</div>
<!-- EDIT NEED MODAL -->
<div class="modal fade" id="editNeedModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">
                    Edit Need
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body pt-2">

                <form id="editNeedForm">

                    <input type="hidden" name="id" id="editNeedId">

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">
                                Need Name
                            </label>

                            <input type="text" name="need_name" id="editNeedName" class="form-control rounded-3" required>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">
                                Category
                            </label>

                            <select name="category" id="editCategory" class="form-select rounded-3">
                                <?php foreach ($categories as $category): ?>

                                    <option value="<?= $category ?>">

                                        <?= ucfirst(str_replace('_', ' ', $category)) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">
                                Quantity
                            </label>

                            <input type="number" name="quantity" id="editQuantity" class="form-control rounded-3">

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">
                                Priority
                            </label>

                            <select name="priority" id="editPriority" class="form-select rounded-3">

                                <?php foreach ($priorities as $priority): ?>
                                    <option value="<?= $priority ?>">
                                        <?= ucfirst($priority) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label fw-semibold">
                                Status
                            </label>

                            <select name="status" id="editStatus" class="form-select rounded-3">

                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?= $status ?>">
                                        <?= ucfirst(str_replace('_', ' ', $status)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        </div>

                        <div class="col-12">

                            <label class="form-label fw-semibold">
                                Description
                            </label>

                            <textarea name="description" id="editDescription" rows="4" class="form-control rounded-3"></textarea>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-0">
                <button class="btn btn-light border rounded-3 px-4" data-bs-dismiss="modal">
                    Cancel
                </button>

                <button class="btn btn-success rounded-3 px-4" id="updateNeedBtn">
                    Update Need
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="addResourceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0">
                <h5 class="fw-bold">
                    Add Resource
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <form id="addResourceForm">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">
                                Resource Name
                            </label>
                            <input type="text" name="resource_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Category
                            </label>

                            <select name="category" class="form-select" required>

                                <?php foreach ($categories as $category): ?>

                                    <option value="<?= $category ?>">

                                        <?= ucwords(str_replace('_', ' ', $category)) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Address
                            </label>
                            <input type="text" name="address" class="form-control">
                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Contact Number
                            </label>
                            <input type="tel" name="contact_number" class="form-control" pattern="[0-9]+" inputmode="numeric" maxlength="15">

                        </div>
                        <div class="col-md-6">

                            <label class="form-label">
                                Opens At
                            </label>

                            <input type="time"
                                id="open_time"
                                class="form-control">

                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Closes At
                            </label>
                            <input type="time"
                                id="close_time"
                                class="form-control">

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Status
                            </label>

                            <select name="status" class="form-select">
                                <option value="open">
                                    Open
                                </option>

                                <option value="closed">
                                    Closed
                                </option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Notes
                            </label>
                            <textarea name="notes" rows="3" class="form-control"></textarea>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-0">
                <button class="btn btn-light border" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button class="btn btn-success" id="saveResourceBtn">
                    Save Resource
                </button>
            </div>

        </div>

    </div>

</div>

<div class="modal fade" id="editResourceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0">
                <h5 class="fw-bold">
                    Edit Resource
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <form id="editResourceForm">

                    <div class="row g-3">
                        <input type="hidden" name="resource_id" id="edit_resource_id">
                        <div class="col-md-6">
                            <label class="form-label">
                                Resource Name
                            </label>
                            <input type="text" name="resource_name" id="edit_resource_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Category
                            </label>

                            <select name="category" class="form-select" id="edit_category" required>

                                <?php foreach ($categories as $category): ?>

                                    <option value="<?= $category ?>">

                                        <?= ucwords(str_replace('_', ' ', $category)) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Address
                            </label>
                            <input type="text" id="edit_address" name="address" class="form-control">
                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Contact Number
                            </label>
                            <input type="tel" id="edit_contact" name="contact_number" class="form-control" pattern="[0-9]+" inputmode="numeric" maxlength="15">
                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Opens At
                            </label>

                            <input type="time" id="edit_open_time" class="form-control">

                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Closes At
                            </label>
                            <input type="time" id="edit_close_time" class="form-control">

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Status
                            </label>

                            <select id="edit_status" name="status" class="form-select">
                                <option value="open">
                                    Open
                                </option>

                                <option value="closed">
                                    Closed
                                </option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Notes
                            </label>
                            <textarea name="notes" rows="3" id="edit_notes" class="form-control"></textarea>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-0">
                <button class="btn btn-light border" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button class="btn btn-success" id="updateResourceBtn">
                    Save Resource
                </button>
            </div>

        </div>

    </div>

</div>

<div class="modal fade" id="addDonationModal" tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content border-0 rounded-4">

            <div class="modal-header border-0">

                <h5 class="fw-bold">
                    Add Donation
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <form id="addDonationForm">

                    <div class="mb-3">

                        <label class="form-label">
                            Donation Type
                        </label>

                        <select name="donation_type" class="form-select" required>
                            <?php foreach ($donationTypes as $type): ?>
                                <option value="<?= $type ?>">
                                    <?= ucwords(str_replace('_', ' ', $type)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Total Amount
                        </label>

                        <input type="number" name="total_amount" class="form-control" required>
                    </div>
                </form>

            </div>

            <div class="modal-footer border-0">

                <button class="btn btn-light border" data-bs-dismiss="modal">
                    Cancel
                </button>

                <button class="btn btn-success" id="saveDonationBtn">
                    Save Donation
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editDonationModal" tabindex="-1">

    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0">
                <h5 class="fw-bold">
                    Edit Donation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <form id="editDonationForm">

                    <input type="hidden" name="id" id="edit_donation_id">

                    <div class="mb-3">

                        <label class="form-label">
                            Donation Type
                        </label>

                        <select name="donation_type" id="edit_donation_type" class="form-select">
                            <?php foreach ($donationTypes as $type): ?>
                                <option value="<?= $type ?>">
                                    <?= ucwords(str_replace('_', ' ', $type)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Total Amount
                        </label>
                        <input type="number" name="total_amount" id="edit_total_amount" class="form-control">
                    </div>
                </form>

            </div>

            <div class="modal-footer border-0">

                <button class="btn btn-light border" data-bs-dismiss="modal">
                    Cancel
                </button>

                <button class="btn btn-success" id="updateDonationBtn">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<body>


    <?php include('includes/sidebar.php'); ?>
    <?php include('includes/nav.php'); ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row g-3 mb-4">
                <div class="col">
                    <div class="card border-0 shadow-sm p-3 rounded-4" style="background-color: #fff;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background-color: #2d5a27 ; color: #e8f5e9;">
                                <i class="fa fa-home fs-6"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size: 0.8rem; color: #2d5a27;">Total Shelters</h6>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h3 class="fw-bold mb-0" style="color: #020e00; font-size: 1.2rem;"><?= $municipality->totalShelters($org_id) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card border-0 shadow-sm p-3 rounded-4" style="background-color: #fff;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 42px; height: 42px; background-color: #a52a2a; color: white;">
                                <i class="fa fa-users fs-6"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size: 0.8rem; color: #a52a2a;">Total Capacity</h6>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <h3 class="fw-bold mb-0" style="color: #000000; font-size: 1.2rem;"><?= $totalOccupied ?>/<?= $totalCapacity ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card border-0 shadow-sm p-3 rounded-4">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width:42px;height:42px;background:#fff4e6;color:#ff922b;">
                                <i class="fa-solid fa-list-check"></i>
                            </div>

                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size:0.8rem;color:#ff922b;">
                                    Active Needs
                                </h6>
                            </div>
                        </div>

                        <h3 class="fw-bold mb-0" style="font-size:1.2rem;">
                            <?= $activeNeeds ?>
                        </h3>
                    </div>
                </div>

                <div class="col">
                    <div class="card border-0 shadow-sm p-3 rounded-4">

                        <div class="d-flex align-items-center mb-2">

                            <div class="icon-shape rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width:42px;height:42px;background:#ebfbee;color:#2b8a3e;">

                                <i class="fa-solid fa-box-open"></i>

                            </div>

                            <div>
                                <h6 class="mb-0 fw-bold"
                                    style="font-size:0.8rem;color:#2b8a3e;">

                                    Open Resources

                                </h6>
                            </div>

                        </div>

                        <h3 class="fw-bold mb-0"
                            style="font-size:1.2rem;">

                            <?= $openResources ?>

                        </h3>

                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm p-4 rounded-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center">
                                <h5 class="fw-bold mb-0">Shelter Overview</h5>
                            </div>
                            <button class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#addShelterModal">
                                + Add Shelter
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table id="shelterTable" class="table align-middle">
                                <thead class="text-muted small">
                                    <tr>
                                        <th class="fw-semibold">Shelter Name</th>
                                        <th class="fw-semibold">Location</th>

                                        <th class="fw-semibold">Occupancy</th>

                                        <th class="text-center fw-semibold">Status</th>
                                        <th class="text-center fw-semibold">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php foreach ($shelters as $shelter): ?>

                                        <tr class="border-bottom">

                                            <td class="">
                                                <?= $municipality->e($shelter['shelter_name']) ?>
                                            </td>

                                            <td>
                                                <?= $municipality->e($shelter['location']) ?>
                                            </td>

                                            <td>
                                                <span>
                                                    <?= $shelter['occupied'] ?> / <?= $shelter['capacity'] ?>
                                                </span>
                                            </td>

                                            <td class="text-center">

                                                <?php if ($shelter['status'] == 'open'): ?>

                                                    <span class="text-success">
                                                        Open
                                                    </span>

                                                <?php elseif ($shelter['status'] == 'near_full'): ?>

                                                    <span class="text-warning">
                                                        Near Full
                                                    </span>

                                                <?php else: ?>

                                                    <span class="text-danger">
                                                        Full
                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                            <td class="text-center action-buttons">

                                                <button
                                                    class="btn btn-sm btn-light rounded-circle me-1 editShelterBtn"

                                                    data-id="<?= $shelter['id'] ?>"

                                                    data-name="<?= $municipality->e($shelter['shelter_name']) ?>"

                                                    data-location="<?= $municipality->e($shelter['location']) ?>"

                                                    data-capacity="<?= $shelter['capacity'] ?>"

                                                    data-occupied="<?= $shelter['occupied'] ?>"

                                                    data-bs-toggle="modal"

                                                    data-bs-target="#editShelterModal">

                                                    <i class="fa-solid fa-pen-to-square text-secondary"></i>

                                                </button>

                                                <button class="btn btn-sm btn-light rounded-circle deleteShelterBtn" data-id="<?= $shelter['id'] ?>">
                                                    <i class="fa-solid fa-trash-can text-danger"></i>
                                                </button>
                                            </td>
                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 rounded-4 h-100" style="background-color: #fff;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="fw-bold mb-0" style="color: #1a3317;">
                                    Top Needs
                                </h6>
                            </div>
                            <button class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#addNeedModal">
                                + Add Need
                            </button>
                        </div>

                        <!-- TABLE -->
                        <div class="table-responsive mt-3">
                            <table id="needsTable" class="table align-middle" style="width:100%; font-size:0.84rem;">

                                <thead class="border-bottom">

                                    <tr class="text-muted small">
                                        <th class="fw-semibold">Need</th>
                                        <th class="fw-semibold text-center">
                                            Details
                                        </th>
                                        <th class="fw-semibold text-center">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($needs as $need): ?>

                                        <tr class="border-bottom">
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php if ($need['priority'] == 'high'): ?>
                                                        <span data-bs-toggle="tooltip" title="High Priority">
                                                            <i class="fa-solid fa-circle-exclamation me-1" style="font-size:0.75rem; color:#dc3545;"></i>
                                                        </span>

                                                    <?php elseif ($need['priority'] == 'medium'): ?>
                                                        <span data-bs-toggle="tooltip" title="Medium Priority">
                                                            <i class="fa-solid fa-clock me-1" style="font-size:0.75rem; color:#f59f00;"></i>
                                                        </span>

                                                    <?php else: ?>

                                                        <span data-bs-toggle="tooltip" title="Low Priority">
                                                            <i class="fa-solid fa-check me-1" style="font-size:0.75rem; color:#2b8a3e;"></i>
                                                        </span>

                                                    <?php endif; ?>

                                                    <div class="text-dark">

                                                        <?= $municipality->e($need['need_name']) ?>

                                                    </div>

                                                </div>

                                            </td>

                                            <td>
                                                <button style="background:#eef6ee;" class="btn btn-sm btn-light rounded-circle me-1" data-bs-toggle="modal" data-bs-target="#needModal<?= $need['id'] ?>">
                                                    <i class="fa-solid fa-eye" style="color:#4f6f52;"></i>
                                                </button>
                                            </td>
                                            <td class="text-center" style="white-space: nowrap;">

                                                <button
                                                    class="btn btn-sm btn-light rounded-circle editNeedBtn"
                                                    data-id="<?= $need['id'] ?>"
                                                    data-name="<?= $municipality->e($need['need_name']) ?>"
                                                    data-category="<?= $need['category'] ?>"
                                                    data-quantity="<?= $need['quantity'] ?>"
                                                    data-priority="<?= $need['priority'] ?>"
                                                    data-status="<?= $need['status'] ?>"
                                                    data-description="<?= $municipality->e($need['description']) ?>">

                                                    <i class="fa-solid fa-pen-to-square text-secondary"></i>

                                                </button>

                                                <button
                                                    class="btn btn-sm btn-light rounded-circle deleteNeedBtn"
                                                    data-id="<?= $need['id'] ?>">

                                                    <i class="fa-solid fa-trash-can text-danger"></i>

                                                </button>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>
            </div>

            <div class="row g-4 mt-2">

                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                            <div>
                                <h4 class="fw-bold mb-0 text-dark">Emergency Resources</h4>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="dropdown">

                                    <button class="btn btn-light border-0 rounded-pill px-3 dropdown-toggle shadow-sm" data-bs-toggle="dropdown">

                                        <i class="fa-solid fa-filter me-2 text-success"></i>
                                        Filter

                                    </button>

                                    <ul class="dropdown-menu shadow border-0 rounded-3">

                                        <li>
                                            <a class="dropdown-item filter-btn" href="#" data-category="all">
                                                All Categories
                                            </a>
                                        </li>

                                        <?php foreach ($categories as $category): ?>
                                            <li>
                                                <a class="dropdown-item filter-btn" href="#" data-category="<?= $category ?>">
                                                    <?= ucwords(str_replace('_', ' ', $category)) ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>

                                    </ul>

                                </div>
                                <button class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#addResourceModal">
                                    + Add Resource
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="resourceTable">
                                <thead class="bg-light">
                                    <tr class="text-muted small ">
                                        <th class="fw-semibold">Resource</th>
                                        <th class="fw-semibold">Category</th>
                                        <th class=" fw-semibold">Status</th>
                                        <th class="text-center fw-semibold">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="border-top-0">

                                    <?php foreach ($resources as $resource): ?>

                                        <tr>
                                            <td class="ps-3">
                                                <div class="text-dark">
                                                    <?= $municipality->e($resource['resource_name']) ?>
                                                </div>
                                            </td>
                                            <td data-category="<?= $resource['category'] ?>">
                                                <?= ucwords(str_replace('_', ' ', $resource['category'])) ?>
                                            </td>
                                            <td>
                                                <?php if ($resource['status'] == 'open'): ?>
                                                    <span class="badge rounded-pill px-3 text-success">
                                                        Open
                                                    </span>

                                                <?php else: ?>
                                                    <span class="badge rounded-pill px-3 text-danger">
                                                        Closed
                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                            <td class="text-center">

                                                <button style="background:#eef6ee;"
                                                    class="btn btn-sm btn-light rounded-circle me-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#resourceModal<?= $resource['resource_id'] ?>">

                                                    <i class="fa-solid fa-eye " style="color:#4f6f52;"></i>

                                                </button>

                                                <button
                                                    class="btn btn-sm btn-light rounded-circle me-1 editResourceBtn"

                                                    data-id="<?= $resource['resource_id'] ?>"

                                                    data-name="<?= $municipality->e($resource['resource_name']) ?>"

                                                    data-category="<?= $resource['category'] ?>"

                                                    data-address="<?= $municipality->e($resource['address']) ?>"

                                                    data-contact="<?= $municipality->e($resource['contact_number']) ?>"

                                                    data-opening_hours="<?= $municipality->e($resource['opening_hours']) ?>"

                                                    data-status="<?= $resource['status'] ?>"

                                                    data-notes="<?= $municipality->e($resource['notes']) ?>"

                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editResourceModal">

                                                    <i class="fa-solid fa-pen-to-square text-secondary"></i>

                                                </button>

                                                <button class="btn btn-sm btn-light rounded-circle deleteResourceBtn" data-id="<?= $resource['resource_id'] ?>">
                                                    <i class="fa-solid fa-trash-can text-danger"></i>
                                                </button>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-4 rounded-4 h-100" style="background-color: #fff;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0" style="color: #1a3317;">Donations</h6>
                            <button class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#addDonationModal">
                                + Add Donation
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table id="donationsTable" class="table table-borderless align-middle" style="width:100%; font-size: 0.85rem;">
                                <thead class="text-muted small ">
                                    <tr>
                                        <th class=" fw-semibold ">Category Name</th>
                                        <th class=" fw-semibold text-center">Qty</th>
                                        <th class=" fw-semibold text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php foreach ($donations as $donation): ?>

                                        <tr class="border-bottom">

                                            <td>
                                                <span class="">
                                                    <?= $municipality->e($donation['donation_type']) ?>
                                                </span>
                                            </td>

                                            <td class="text-center ">
                                                <?= $donation['total_amount'] ?>
                                            </td>

                                            <td class="text-center">

                                                <button
                                                    class="btn btn-sm btn-light rounded-circle me-1 editDonationBtn"

                                                    data-id="<?= $donation['id'] ?>"

                                                    data-type="<?= $municipality->e($donation['donation_type']) ?>"

                                                    data-amount="<?= $donation['total_amount'] ?>"

                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editDonationModal">

                                                    <i class="fa-solid fa-pen-to-square text-secondary"></i>

                                                </button>

                                                <button
                                                    class="btn btn-sm btn-light rounded-circle deleteDonationBtn"

                                                    data-id="<?= $donation['id'] ?>">

                                                    <i class="fa-solid fa-trash-can text-danger"></i>

                                                </button>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RESOURCE DETAILS MODAL -->
            <?php foreach ($resources as $resource): ?>

                <div class="modal fade" id="resourceModal<?= $resource['resource_id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-md">
                        <div class="modal-content resource-modal border-0">
                            <div class="modal-header resource-modal-header border-0">
                                <div class="d-flex align-items-center">
                                    <div class="resource-main-icon">
                                        <?php
                                        $icon = "fa-box";

                                        switch ($resource['category']) {

                                            case 'fuel_station':
                                                $icon = "fa-gas-pump";
                                                break;

                                            case 'hospital':
                                                $icon = "fa-hospital";
                                                break;

                                            case 'supermarket':
                                                $icon = "fa-cart-shopping";
                                                break;

                                            case 'bakery':
                                                $icon = "fa-bread-slice";
                                                break;

                                            case 'water_station':
                                                $icon = "fa-droplet";
                                                break;

                                            case 'pharmacy':
                                                $icon = "fa-prescription-bottle-medical";
                                                break;
                                        }
                                        ?>

                                        <i class="fa-solid <?= $icon ?>"></i>

                                    </div>

                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <h4 class="fw-bold mb-0">
                                                <?= $municipality->e($resource['resource_name']) ?>
                                            </h4>
                                        </div>

                                    </div>

                                </div>

                                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal">
                                </button>

                            </div>

                            <div class="modal-body pt-1">

                                <div class="row g-3">

                                    <div class="col-12">
                                        <div class="resource-card">
                                            <div class="resource-card-icon">
                                                <i class="fa-solid fa-location-dot"></i>
                                            </div>

                                            <div class="resource-card-content">

                                                <div class="resource-card-top">

                                                    <small class="resource-label">
                                                        ADDRESS
                                                    </small>

                                                </div>

                                                <div class="resource-view">

                                                    <?= $municipality->e($resource['address']) ?>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                    <div class="col-12">
                                        <div class="resource-card">
                                            <div class="resource-card-icon">
                                                <i class="fa-solid fa-phone"></i>
                                            </div>
                                            <div class="resource-card-content">
                                                <div class="resource-card-top">
                                                    <small class="resource-label">
                                                        CONTACT NUMBER
                                                    </small>
                                                </div>

                                                <div class="resource-view">
                                                    <?= $municipality->e($resource['contact_number']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="resource-card">
                                            <div class="resource-card-icon">
                                                <i class="fa-solid fa-clock"></i>
                                            </div>

                                            <div class="resource-card-content">
                                                <div class="resource-card-top">
                                                    <small class="resource-label">
                                                        OPENING HOURS
                                                    </small>
                                                </div>

                                                <div class="resource-view">
                                                    <?= $municipality->e($resource['opening_hours']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="resource-card align-items-start">
                                            <div class="resource-card-icon">
                                                <i class="fa-solid fa-note-sticky"></i>
                                            </div>
                                            <div class="resource-card-content">
                                                <div class="resource-card-top">
                                                    <small class="resource-label">
                                                        NOTES
                                                    </small>
                                                </div>

                                                <div class="resource-view">
                                                    <?= $municipality->e($resource['notes']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-light border rounded-3 px-4 py-2 fw-semibold" data-bs-dismiss="modal">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>



        </div>
        <?php include('includes/footer.php'); ?>
    </div>
    <?php include('includes/script.php'); ?>
    <?php
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        echo "<script>
        Swal.fire({
            icon: '{$flash['icon']}',
            title: '{$flash['title']}',
            text: '{$flash['text']}',
            timer: {$flash['timer']},
            showConfirmButton: " . ($flash['showConfirmButton'] ? 'true' : 'false') . ",
            timerProgressBar: true
        }).then(() => {
            window.location.href = '{$flash['redirect']}';
        });
    </script>";
        unset($_SESSION['flash']);
    }
    ?>

    <script>
        document.querySelectorAll(".resource-edit-btn").forEach(btn => {

            btn.addEventListener("click", function() {

                const card = this.closest(".resource-card");

                const view = card.querySelector(".resource-view");

                const input = card.querySelector(".resource-input");

                if (!input) return;

                view.classList.toggle("d-none");

                input.classList.toggle("d-none");

                if (!input.classList.contains("d-none")) {
                    input.focus();
                }

            });

        });


        document.querySelectorAll('.filter-btn').forEach(button => {

            button.addEventListener('click', function(e) {

                e.preventDefault();

                let category = this.dataset.category;

                let rows = document.querySelectorAll('#resourceTable tbody tr');

                rows.forEach(row => {

                    let rowCategory = row.children[1]
                        .dataset.category;

                    if (
                        category === 'all' ||
                        rowCategory === category
                    ) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }

                });

            });

        });

        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');

        tooltipTriggerList.forEach(tooltipTriggerEl => {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
        $("#addShelterForm").submit(function(e) {

            e.preventDefault();

            $.ajax({

                url: "actions/add_shelter.php",

                type: "POST",

                data: $(this).serialize(),

                dataType: "json",

                success: function(response) {

                    if (response.status == "success") {

                        $("#addShelterModal").modal("hide");

                        Swal.fire({
                            icon: "success",
                            title: "Shelter Added",
                            timer: 1500,
                            showConfirmButton: false
                        });

                        window.location.reload();

                    } else {

                        Swal.fire({
                            icon: "error",
                            title: response.message
                        });

                    }

                }

            });

        });
        $(document).on("click", ".editShelterBtn", function() {

            $("#editShelterId").val(
                $(this).data("id")
            );

            $("#editShelterName").val(
                $(this).data("name")
            );

            $("#editShelterLocation").val(
                $(this).data("location")
            );

            $("#editShelterCapacity").val(
                $(this).data("capacity")
            );

            $("#editShelterOccupied").val(
                $(this).data("occupied")
            );

        });
        $("#editShelterForm").submit(function(e) {

            e.preventDefault();

            $.ajax({

                url: "actions/update_shelter.php",

                type: "POST",

                data: $(this).serialize(),

                dataType: "json",

                success: function(response) {

                    if (response.status == "success") {

                        $("#editShelterModal").modal("hide");

                        Swal.fire({
                            icon: "success",
                            title: "Shelter Updated",
                            timer: 1500,
                            showConfirmButton: false
                        });

                        window.location.reload();

                    } else {

                        Swal.fire({
                            icon: "error",
                            title: response.message
                        });

                    }

                }

            });

        });
        $(document).on("click", ".deleteShelterBtn", function() {

            let id = $(this).data("id");

            Swal.fire({

                title: "Delete Shelter?",

                text: "This action cannot be undone",

                icon: "warning",

                showCancelButton: true,

                confirmButtonColor: "#198754",

                cancelButtonColor: "#dc3545",

                confirmButtonText: "Yes Delete"

            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({

                        url: "actions/delete_shelter.php",

                        type: "POST",

                        dataType: "json",

                        data: {
                            id: id
                        },

                        success: function(response) {

                            if (response.status == "success") {

                                Swal.fire({
                                    icon: "success",
                                    title: "Deleted Successfully",
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                window.location.reload();

                            } else {

                                Swal.fire({
                                    icon: "error",
                                    title: response.message
                                });

                            }

                        }

                    });

                }

            });

        });
        $(document).on("click", ".deleteNeedBtn", function() {

            let id = $(this).data("id");

            Swal.fire({

                title: "Delete Need?",
                text: "This action cannot be undone",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#198754",
                cancelButtonColor: "#dc3545",
                confirmButtonText: "Delete"

            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({

                        url: "actions/delete_need.php",

                        type: "POST",

                        dataType: "json",

                        data: {
                            id: id
                        },

                        success: function(response) {

                            if (response.status == "success") {

                                Swal.fire({

                                    icon: "success",
                                    title: "Deleted Successfully",
                                    timer: 1500,
                                    showConfirmButton: false

                                });

                                window.location.reload();

                            } else {

                                Swal.fire({
                                    icon: "error",
                                    title: "Error",
                                    text: response.message
                                });

                            }

                        },

                        error: function() {

                            Swal.fire({
                                icon: "error",
                                title: "Server Error"
                            });

                        }

                    });

                }

            });

        });
 
        $("#saveNeedBtn").click(function() {

            $.ajax({

                url: "actions/add_need.php",

                type: "POST",

                dataType: "json",

                data: $("#addNeedForm").serialize(),

                success: function(response) {

                    if (response.status == "success") {

                        Swal.fire({

                            icon: "success",

                            title: "Need Added Successfully",

                            timer: 1800,

                            showConfirmButton: false

                        });

                        $("#addNeedModal").modal("hide");

                        $("#addNeedForm")[0].reset();

                        window.location.reload();

                    } else {

                        Swal.fire({

                            icon: "error",

                            title: "Error",

                            text: response.message

                        });

                    }

                },

                error: function() {

                    Swal.fire({

                        icon: "error",

                        title: "Server Error"

                    });

                }

            });

        });

        $(document).on("click", ".editNeedBtn", function() {

            $("#editNeedId").val($(this).data("id"));

            $("#editNeedName").val($(this).data("name"));

            $("#editCategory").val($(this).data("category"));

            $("#editQuantity").val($(this).data("quantity"));

            $("#editPriority").val($(this).data("priority"));

            $("#editStatus").val($(this).data("status"));

            $("#editDescription").val($(this).data("description"));

            $("#editNeedModal").modal("show");

        });

        $("#updateNeedBtn").click(function() {

            $.ajax({

                url: "actions/update_need.php",

                type: "POST",

                dataType: "json",

                data: $("#editNeedForm").serialize(),

                success: function(response) {

                    if (response.status == "success") {

                        Swal.fire({

                            icon: "success",

                            title: "Updated Successfully",

                            timer: 1800,

                            showConfirmButton: false

                        });

                        $("#editNeedModal").modal("hide");

                        window.location.reload();

                    } else {

                        Swal.fire({

                            icon: "error",

                            title: "Error",

                            text: response.message

                        });

                    }

                },

                error: function() {

                    Swal.fire({

                        icon: "error",

                        title: "Server Error"

                    });

                }

            });

        });
      

        $(document).on("click", ".editResourceBtn", function() {

            $("#edit_resource_id").val($(this).data("id"));

            $("#edit_resource_name").val($(this).data("name"));

            $("#edit_category").val($(this).data("category"));

            $("#edit_address").val($(this).data("address"));

            $("#edit_contact_number").val($(this).data("contact"));

            $("#edit_opening_hours").val($(this).data("hours"));

            $("#edit_status").val($(this).data("status"));

            $("#edit_notes").val($(this).data("notes"));

        });

        $("#saveResourceBtn").click(function() {

            let opening_hours =

                $("#open_time").val() + " - " + $("#close_time").val();
                 let formData = $("#addResourceForm").serialize() + "&opening_hours=" + encodeURIComponent(opening_hours);

            $.ajax({

                url: "actions/add_resource.php",
                type: "POST",

                data: formData,
                dataType: "json",

                success: function(response) {

                    if (response.status == "success") {

                        $("#addResourceModal").modal("hide");

                        Swal.fire({

                            icon: "success",

                            title: "Success",

                            text: response.message,

                            timer: 1800,

                            showConfirmButton: false

                        });

                        window.location.reload();

                    } else {

                        Swal.fire({

                            icon: "error",

                            title: "Error",

                            text: response.message

                        });

                    }

                }

            });

        });


        $(document).on("click", ".editResourceBtn", function() {

            $("#edit_resource_id").val(
                $(this).data("id")
            );

            $("#edit_resource_name").val(
                $(this).data("name")
            );

            $("#edit_category").val(
                $(this).data("category")
            );

            $("#edit_address").val(
                $(this).data("address")
            );

            $("#edit_contact").val(
                $(this).data("contact")
            );

            $("#edit_status").val(
                $(this).data("status")
            );

            $("#edit_notes").val(
                $(this).data("notes")
            );
            let hours = $(this).data("opening_hours");

            if (hours && hours.includes(" - ")) {

                let parts = hours.split(" - ");

                $("#edit_open_time").val(parts[0]);

                $("#edit_close_time").val(parts[1]);

            }
        });





        $("#updateResourceBtn").click(function() {

            let opening_hours =

                $("#edit_open_time").val() + " - " + $("#edit_close_time").val();

            let formData = $("#editResourceForm").serialize() + "&opening_hours=" + encodeURIComponent(opening_hours);
            $.ajax({

                url: "actions/update_resource.php",

                type: "POST",

                data: formData,

                dataType: "json",

                success: function(response) {

                    if (response.status == "success") {

                        $("#editResourceModal").modal("hide");

                        Swal.fire({

                            icon: "success",

                            title: "Updated",

                            text: response.message,

                            timer: 1800,

                            showConfirmButton: false

                        });

                        window.location.reload();

                    } else {

                        Swal.fire({

                            icon: "error",

                            title: "Error",

                            text: response.message

                        });

                    }

                }

            });

        });

        $(document).on("click", ".deleteResourceBtn", function() {
            let id = $(this).data("id");
            Swal.fire({
                title: "Delete Resource?",
                text: "This action cannot be undone",
                icon: "warning",
                showCancelButton: true,

                confirmButtonColor: "#dc3545",

                confirmButtonText: "Delete"

            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({

                        url: "actions/delete_resource.php",

                        type: "POST",

                        data: {
                            resource_id: id
                        },

                        dataType: "json",

                        success: function(response) {

                            if (response.status == "success") {

                                Swal.fire({

                                    icon: "success",

                                    title: "Deleted",

                                    text: response.message,

                                    timer: 1800,

                                    showConfirmButton: false

                                });

                                window.location.reload();

                            } else {

                                Swal.fire({

                                    icon: "error",

                                    title: "Error",

                                    text: response.message

                                });

                            }

                        }

                    });

                }

            });

        });

        $(document).on("click", ".editDonationBtn", function() {

            $("#edit_donation_id").val(
                $(this).data("id")
            );

            $("#edit_donation_type").val(
                $(this).data("type")
            );

            $("#edit_total_amount").val(
                $(this).data("amount")
            );

        });

        $("#saveDonationBtn").click(function() {

            $.ajax({

                url: "actions/add_donation.php",

                type: "POST",

                data: $("#addDonationForm").serialize(),

                dataType: "json",

                success: function(response) {

                    if (response.status == "success") {

                        $("#addDonationModal").modal("hide");

                        Swal.fire({

                            icon: "success",

                            title: "Success",

                            text: response.message,

                            timer: 1800,

                            showConfirmButton: false

                        });

                        window.location.reload();

                    } else {

                        Swal.fire({

                            icon: "error",

                            title: "Error",

                            text: response.message

                        });

                    }

                }

            });

        });

        $("#updateDonationBtn").click(function() {

            $.ajax({

                url: "actions/update_donation.php",

                type: "POST",

                data: $("#editDonationForm").serialize(),

                dataType: "json",

                success: function(response) {

                    if (response.status == "success") {

                        $("#editDonationModal").modal("hide");

                        Swal.fire({

                            icon: "success",

                            title: "Updated",

                            text: response.message,

                            timer: 1800,

                            showConfirmButton: false

                        });

                        window.location.reload();

                    } else {

                        Swal.fire({

                            icon: "error",

                            title: "Error",

                            text: response.message

                        });

                    }

                }

            });

        });

        $(document).on("click", ".deleteDonationBtn", function() {

            let id = $(this).data("id");

            Swal.fire({

                title: "Delete Donation?",

                text: "This action cannot be undone",

                icon: "warning",

                showCancelButton: true,

                confirmButtonColor: "#198754",

                cancelButtonColor: "#d33",

                confirmButtonText: "Delete"

            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({

                        url: "actions/delete_donation.php",

                        type: "POST",

                        data: {
                            id: id
                        },

                        dataType: "json",

                        success: function(response) {

                            if (response.status == "success") {

                                Swal.fire({

                                    icon: "success",

                                    title: "Deleted",

                                    text: response.message,

                                    timer: 1800,

                                    showConfirmButton: false

                                });

                                window.location.reload();

                            } else {

                                Swal.fire({

                                    icon: "error",

                                    title: "Error",

                                    text: response.message

                                });

                            }

                        }

                    });

                }

            });

        });
    </script>
</body>

</html>
