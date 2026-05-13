<?php
require_once('class/DAL.class.php');
$obj = new DAL();

?>

<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Custom CSS -->
<link rel="stylesheet" href="assets/css/style.css">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/main.min.css' rel='stylesheet' />

<style>
    body {
        background-color: #f7f9fc;
    }


    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        width: 230px;
        height: 100vh;
        padding: 15px;
        background: #f5f7fb;
    }

    .sidebar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: top;
        border-radius: 20px;
    }



    .main-content {
        margin-left: 230px;
    }

    .main-content .container-fluid {
        padding-top: 0;
    }

    .topbar {
        position: sticky;

        margin-left: 230px;
        margin-right: 20px;
        margin-top: 15px;

        height: 80px;
        background: white;
        border-radius: 16px;
        margin-bottom: 10px;
        padding: 15px 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;

        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        z-index: 10;
    }

    .main-content .row:first-child {
        margin-top: 0 !important;
    }

    .badge-soft-success {
        background-color: #e6f4ea;
        color: #1e7e34;
        border: 1px solid #c3e6cb;
    }

    .badge-soft-secondary {
        background-color: #f8f9fa;
        color: #6c757d;
        border: 1px solid #dee2e6;
    }

    .badge-soft-warning {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }

    .custom-table thead th {
        font-size: 0.8rem;
        text-transform: uppercase;
        color: #adb5bd;
        background-color: #fafbfc;
        border-top: none;
        padding: 12px 8px;
    }

    .list-group-item {
        font-size: 0.95rem;
        color: #495057;
    }

    .avatar-stack {
        display: flex;
        align-items: center;
    }

    .stack-item {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 2px solid white;
        margin-left: -8px;
        object-fit: cover;
    }

    .stack-item:first-child {
        margin-left: 0;
    }

    .stack-more {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #f1f3f5;
        color: #6c757d;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: -8px;
        border: 2px solid white;
        font-weight: bold;
    }

    .text-success {
        color: #28a745 !important;
    }

    .title-icon-wrapper {
        width: 45px;
        height: 45px;
        background-color: #e6f4ea;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .summary-card {
        background-color: #f8f9fa;
        border: 1px solid #f1f1f1;
        border-radius: 12px;
    }

    .icon-box {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .bg-blue-light {
        background-color: #e7f0ff;
    }

    .bg-red-light {
        background-color: #ffe7e7;
    }

    .bg-green-light {
        background-color: #e7ffed;
    }

    .bg-orange-light {
        background-color: #fff4e7;
    }

    .report-container {
        background-color: #fdfdfd;
    }

    .btn-success {
        background-color: #1a8754;
        border: none;
        border-radius: 8px;
    }

    .action-btn {
        border: none;
        border-radius: 12px;
        transition: all 0.2s ease;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        opacity: 0.8;
    }

    .bg-soft-danger {
        background-color: #fff1f1;
    }

    .bg-soft-primary {
        background-color: #f0f7ff;
    }

    .bg-soft-success {
        background-color: #f0fff4;
    }

    .bg-soft-purple {
        background-color: #f8f0ff;
    }

    .text-purple {
        color: #8e44ad;
    }

    .bg-soft-purple {
        background-color: #f4ebff;
    }

    .text-muted {
        color: #adb5bd !important;
    }

    .dropdown-toggle.cursor-pointer {
        text-decoration: none;
        color: inherit;
    }

    .dropdown-toggle::after {
        display: inline-block;
        margin-left: 0.5em;
        vertical-align: middle;
        content: "";
        border-top: 0.3em solid;
        border-right: 0.3em solid transparent;
        border-bottom: 0;
        border-left: 0.3em solid transparent;
        opacity: 0.5;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .border-bottom:hover {
        background-color: #f9f9f9;
        transition: 0.2s;
    }

    .dropdown-menu {
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        font-size: 0.85rem;
    }

    .transfer-item {
        background-color: #ffffff;
        transition: all 0.2s ease;
        border-color: #f1f1f1 !important;
    }

    .transfer-item:hover {
        background-color: #fcfcfc;
        border-color: #e0e0e0 !important;
    }

    .icon-box-sm {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    .badge-soft-orange {
        background-color: #fff4e7;
        color: #fd7e14;
        border: none;
        font-weight: 600;
    }

    .badge-soft-success {
        background-color: #e7ffed;
        color: #198754;
        border: none;
        font-weight: 600;
    }

    .bg-blue-light {
        background-color: #eff6ff;
    }

    table.dataTable.no-footer {
        border-bottom: none !important;
    }

    table.dataTable thead .sorting:before,
    table.dataTable thead .sorting:after,
    table.dataTable thead .sorting_asc:before,
    table.dataTable thead .sorting_asc:after {
        display: none !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: 1px solid #dee2e6 !important;
        background: white !important;
        border-radius: 4px !important;
        margin: 0 2px !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #f8f9fa !important;
        color: #2d5a27 !important;
        font-weight: bold !important;
    }

    @media (min-width: 992px) {
        .custom-table-container {
            overflow-x: visible !important;
        }

        #shelterTable th,
        #shelterTable td {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }
    }

    #shelterTable {
        border-collapse: collapse !important;
    }

    #shelterTable tbody tr {
        border-bottom: 1px solid #f0f0f0 !important;
    }

    .dataTables_wrapper .dataTables_table.no-footer {
        border-bottom: none !important;
    }
</style>