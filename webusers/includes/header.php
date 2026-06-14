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
<!-- iocn-->
<!-- <link rel="icon" type="image/png" sizes="32x32" href="/uploads/logo3.png"> -->
<!-- <link rel="apple-touch-icon" sizes="180x180" href="/uploads/logo3.png">     -->


        <link rel="icon" type="image/png" sizes="32x32" href="/uploads/logonew.png">
<link rel="apple-touch-icon" sizes="180x180" href="/uploads/logonew.png">

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
        padding-top: 90px;
        transition: margin-left 0.25s ease;
    }

    .main-content .container-fluid {
        padding-top: 0;
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
<style>
.top-nav {
    position: absolute;    
    top: 10px;
    left: 240px;            
    right: 10px;            
    height: 80px;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 30px;
    z-index: 1000;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin: 0;              
}
.swal2-container{
    z-index: 10000000 !important;
}
    .crm-nav-left {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .crm-nav-right {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .crm-divider {
        width: 1px;
        height: 25px;
        background-color: #e2e8f0;
        margin: 0 5px;
    }

    .crm-toggle-btn {
        background: none;
        border: none;
        font-size: 18px;
        color: #8392ab;
        cursor: pointer;
        padding: 5px;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        background: #1e3a5f;
        color: #fff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        flex-shrink: 0;
    }

    .crm-user-dropdown {
        position: relative;
    }

    .dropdown-menu {
        position: absolute;
        top: 50px;
        right: 0;
        width: 180px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .1);
        display: none;
        padding: 10px 0;
        z-index: 9999;
        border: 1px solid #eee;
    }

    .dropdown-menu.show {
        display: block !important;
    }

    .dropdown-menu li a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 16px;
        font-size: 13px;
        color: #334155;
        text-decoration: none;
        transition: background .15s;
    }

    .dropdown-menu li a:hover {
        background: #f8f9fa;
    }

    .admin-arrow {
        transition: transform .3s;
    }

    .admin-arrow.rotate {
        transform: rotate(180deg);
    }

    .notif-wrapper {
        position: relative;
    }

    .notif-bell {
        position: relative;
        cursor: pointer;
        font-size: 18px;
        color: #1e3a5f;
        padding: 8px;
    }

    .notif-badge {
        position: absolute;
        top: 0;
        right: 0;
        background: #e74c3c;
        color: white;
        border-radius: 50%;
        font-size: 10px;
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }

    .notif-dropdown {
        position: absolute;
        right: 0;
        top: 45px;
        width: 340px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, .12);
        z-index: 9999;
        max-height: 420px;
        overflow-y: auto;
    }

    .notif-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 18px;
        border-bottom: 1px solid #f0f0f0;
        font-weight: 600;
        font-size: 14px;
        color: #0f2238;
    }

    .notif-count {
        font-size: 12px;
        color: #9aa7b8;
        font-weight: 400;
    }

    .notif-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        border-bottom: 1px solid #f8f9fa;
        transition: .2s;
    }

    .notif-item:hover {
        background: #f9fbfd;
    }

    .notif-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .notif-text {
        flex-grow: 1;
    }

    .notif-text p {
        font-size: 13px;
        color: #2d3748;
        margin: 0 0 3px;
        line-height: 1.4;
    }

    .notif-text span {
        font-size: 11px;
        color: #9aa7b8;
    }

    .notif-seen-btn {
        border: none;
        background: #eef2f7;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        cursor: pointer;
        color: #1e3a5f;
        font-size: 11px;
        flex-shrink: 0;
        transition: .2s;
    }

    .notif-seen-btn:hover {
        background: #1e3a5f;
        color: white;
    }

    .notif-empty {
        padding: 30px;
        text-align: center;
        color: #9aa7b8;
    }

    .notif-empty i {
        font-size: 30px;
        margin-bottom: 8px;
        color: #c5d0dc;
        display: block;
    }

    .notif-empty p {
        font-size: 13px;
        margin: 0;
    }

    .crm-modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .45);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(3px);
    }

    .crm-modal-content {
        background: #fff;
        border-radius: 18px;
        padding: 30px;
        width: 420px;
        max-width: 92vw;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .2);
        position: relative;
    }

    .crm-modal-content h2 {
        font-size: 18px;
        font-weight: 700;
        color: #1b2559;
        margin-bottom: 20px;
    }

    .crm-modal-content label {
        font-size: 12px;
        font-weight: 600;
        color: #8392ab;
        text-transform: uppercase;
        letter-spacing: .4px;
        display: block;
        margin-bottom: 5px;
        margin-top: 12px;
    }

    .close-modal {
        position: absolute;
        top: 16px;
        right: 20px;
        font-size: 20px;
        cursor: pointer;
        color: #8392ab;
        background: none;
        border: none;
    }

    .crm-btn {
        width: 100%;
        margin-top: 18px;
        padding: 10px;
        background: #1e3a5f;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s;
    }

    .crm-btn:hover {
        background: #162d4a;
    }
</style>

<style>
@media (max-width: 1100px) {
    .sidebar { display: none; }
    .top-nav { left: 10px; right: 10px; }
    .main-content { margin-left: 0; }
    .police-layout { grid-template-columns: 1fr; }
}


@media (max-width: 767px) {
    .top-nav { left: 8px; right: 8px; top: 8px; height: 60px; padding: 0 12px; }
    .main-content { padding-top: 78px; padding-left: 8px; padding-right: 8px; }

    .crm-nav-left { min-width: 0; overflow: hidden; flex: 1; }
    .crm-nav-left div > div { display: none; }
    .crm-nav-left span { font-size: 13px !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; }

    .user-details .user-name { display: none !important; }
    .user-details .user-role { font-size: 12px; }

    .col-md-2, .col-md-3, .col-md-4,
    .col-md-6, .col-md-8,
    .col-lg-4, .col-lg-8 {
        width: 100% !important;
        flex: 0 0 100% !important;
        max-width: 100% !important;
    }

    .card.p-4 { padding: 12px !important; }
    .card.p-3 { padding: 10px !important; }

    .map-toolbar { flex-wrap: wrap; gap: 6px; }
    #map { height: 340px; }
    .side-panel { grid-template-columns: 1fr 1fr; }
}

@media (max-width: 1024px) {
    .col-lg-4, .col-lg-8 {
        width: 100% !important;
        flex: 0 0 100% !important;
        max-width: 100% !important;
    }
}

@media (max-width: 600px) {
    .police-nav-sub { display: none !important; }
    .crm-nav-right .user-details { display: none; }
    .crm-nav-left { max-width: 180px; overflow: hidden; }
    .crm-nav-left span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; }
}

@media (max-width: 480px) {
    .col-md-2 {
        width: 50% !important;
        flex: 0 0 50% !important;
        max-width: 50% !important;
    }

    .side-panel { grid-template-columns: 1fr; }
    #map { height: 280px; }
    .card.p-4 { padding: 10px !important; }
    .action-buttons .btn { padding: 4px 6px; font-size: 11px; }
}
</style>