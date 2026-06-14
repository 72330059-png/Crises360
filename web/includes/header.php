<?php
require_once('class/DAL.class.php');
$obj = new DAL();
$timeout = 5 * 60;

$obj->execute("
    UPDATE users 
    SET status='offline'
    WHERE TIMESTAMPDIFF(SECOND, last_activity, NOW()) > $timeout
");
?>


<!-- Bootstrap -->
<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> -->

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
<link rel="icon" type="image/png" sizes="32x32" href="../uploads/logo3.png">
<link rel="apple-touch-icon" sizes="180x180" href="/uploads/logo3.png">

<style>
    footer {
        text-align: center;
    }

    .badge.bg-danger {
        font-size: 0.7rem;
        vertical-align: middle;
    }

    .report-buttons {
        display: flex;
        gap: 20px;
        margin: 20px 0;
        flex-wrap: wrap;
        justify-content: flex-start;
    }

    .report-buttons button {
        background-color: #6c5ce7;
        color: white;
        border: none;
        padding: 12px 20px;
        font-size: 16px;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .report-buttons button:hover {
        background-color: #5a4bcf;
    }

    .report-container {
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        min-height: 300px;
        margin-bottom: 30px;
    }

    .report-container table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .report-container th,
    .report-container td {
        padding: 10px;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }

    .report-container th {
        background-color: #eee;
        font-weight: bold;
    }

    .report-container tr:hover {
        background-color: #f1f1f1;
    }



    body {
        background: #fff;
        font-family: Arial;
    }


    .star {
        cursor: pointer;
        font-size: 18px;
        opacity: 0.3;
    }

    .star.filled {
        opacity: 1;
    }

    .rating .star {
        cursor: pointer;
        font-size: 20px;
        opacity: 0.3 !important;
        transition: opacity 0.2s;
    }

    .rating .star.filled {
        opacity: 1 !important;
    }

    .rating .star {
        color: #f7c948;
    }

    .avatar-box {
        width: 55px;
        height: 55px;
        background: #c0392b;

        color: white;
        font-size: 28px;
        font-weight: bold;
        border-radius: 6px;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 15px;
    }

    .profile-popup {
        position: absolute;
        background: white;
        padding: 15px;
        width: 300px;
        display: none;
        z-index: 9999;
    }


    .popup-arrow {
        width: 0;
        height: 0;
        border-left: 10px solid transparent;
        border-right: 10px solid transparent;
        border-bottom: 10px solid white;
        position: absolute;
        top: -10px;
        left: 20px;
    }

    .close-btn {
        position: absolute;
        top: 8px;
        right: 10px;
        background: transparent;
        border: none;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        color: #444;
    }

    .close-btn:hover {
        color: #000;
    }

    .avatar-wrapper {
        position: relative;
        display: inline-block;
    }

    .crm-sidebar .menu-section:hover {
        background: rgba(255, 255, 255, 0.15);
        color: #fff;
    }

    .crm-sidebar .menu-section.active {
        background: rgba(255, 255, 255, 0.18);
        color: #fff;
    }

    .crm-sidebar .menu-section {
        padding: 20px 18px 10px 18px;

        color: rgba(255, 255, 255, 0.4);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        background: transparent !important;
        display: flex;
        align-items: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);

    }

    .crm-sidebar .menu-section .arrow,
    .crm-sidebar .menu-section i {
        display: none !important;
    }

    .arrow {
        float: right;
        font-size: 16px;
        opacity: 0.7;
        font-size: 14px;
        margin-left: 10px;
        transition: 0.3s;
    }


    .crm-sidebar .submenu li a {
        padding: 10px 20px;
        font-size: 14px;
        color: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.3s ease;
    }

    .crm-sidebar .submenu li a i {
        color: #ffffff !important;
        font-size: 16px;
        width: 20px;
        opacity: 0.7;
    }

    .crm-sidebar .submenu li a:hover,
    .crm-sidebar .submenu li.active a {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border-left: 3px solid var(--yellow);

    }

    .crm-sidebar .submenu li.active a i,
    .crm-sidebar .submenu li a:hover i {
        color: var(--yellow) !important;
        opacity: 1;
    }

    .crm-sidebar .submenu li {
        margin: 0;
        padding: 0;
    }

    .crm-sidebar .submenu {
        padding: 0;
        display: block;

    }

    ul {
        margin: 0;
        padding: 0;
    }

    li {
        list-style: none;
    }

    .crm-sidebar .submenu li a:hover {
        opacity: 1;
        transform: translateX(4px);
        background: rgba(255, 255, 255, 0.05);
    }

    .crm-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 90px;
        height: 100vh;

        background: var(--navy);
        color: white;
        transition: width 0.25s ease;
        z-index: 1000;
        display: flex;
        flex-direction: column;


    }

    .sidebar-menu {
        flex: 1;

        overflow-y: auto;

        padding-bottom: 20px;
    }

    .crm-sidebar i {
        font-size: 14px;
        color: var(--yellow);
        opacity: 0.85;
    }

    /* //////////////////////////////// */
    .logo-icon {
        display: none;
        width: 40px;
    }

    .logo-full {
        width: 90px;
    }

    .crm-sidebar.collapsed .logo-full {
        display: none;
    }

    .crm-sidebar.collapsed .logo-icon {
        display: block;
        width: 40px;
    }

    .sidebar-logo img:hover {
        opacity: 1;
        transform: scale(1.05);
    }


    .sidebar-logo {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        height: 90px;

        overflow: hidden;
        transition: all 0.3s ease;
    }


    .sidebar-logo img {
        height: 120px;
        width: 130px;

        object-fit: contain;
        transition: 0.3s;
    }


    .crm-sidebar.collapsed .sidebar-logo img {
        width: 45px !important;
        height: 45px !important;
    }

    .crm-sidebar.collapsed .sidebar-logo {
        height: 70px;
        padding: 10px 0;
    }

    .crm-sidebar.collapsed .logo-full {
        display: none;
    }

    .crm-sidebar.collapsed .logo-icon {
        display: block !important;
    }

    .crm-sidebar.collapsed .menu-section {
        padding: 0 !important;
        margin: 0 !important;
        height: 0 !important;
        overflow: hidden;
    }

    .crm-sidebar.collapsed .arrow {
        display: none;
    }

    .crm-sidebar.collapsed li a {
        justify-content: center;
        padding: 12px 0;
    }

    .crm-sidebar.collapsed i {
        font-size: 18px;
    }

    .crm-sidebar.collapsed .menu-section .title {
        display: none;
    }

    .menu-section i {
        display: none;
    }

    .crm-sidebar.collapsed .menu-section i {
        display: block;
        font-size: 18px;
    }


    .sidebar-menu::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar-menu::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar-menu::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
    }

    .sidebar-menu::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.4);
    }

    .crm-sidebar.collapsed .menu-section.active {
        background: transparent !important;
    }

    .crm-sidebar.collapsed .link-text {
        display: none !important;
    }

    .crm-sidebar.collapsed .submenu li a {
        justify-content: center;
        padding: 10px 0 !important;
        width: 100%;
        margin-bottom: 2px;
    }

    .crm-sidebar.collapsed .submenu li a i {
        margin: 0 !important;
        font-size: 20px;
    }

    .crm-sidebar.collapsed .submenu {
        display: block !important;
        height: auto !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .crm-sidebar.collapsed .submenu li a span,
    .crm-sidebar.collapsed .menu-section .title {
        display: none !important;
        height: 0;
        margin: 0;
        padding: 0;
    }

    :root {
        --yellow: #FFC107;
    }

    .crm-sidebar .submenu li.active a i {
        color: var(--yellow) !important;
        opacity: 1;
    }

    .crm-sidebar .submenu li.active a {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        border-left: 3px solid var(--yellow) !important;
    }

    .crm-sidebar.collapsed .submenu li.active a {
        border-left: 4px solid var(--yellow) !important;
    }

    .badge-notify {
        background-color: #ff3b30;
        color: white;
        font-size: 10px;
        font-weight: bold;
        padding: 2px 6px;
        border-radius: 10px;
        margin-left: auto;
        min-width: 18px;
        text-align: center;
        line-height: 1;
    }


    .crm-sidebar.collapsed .badge-notify {
        display: none;
    }

    .top-nav {
        position: fixed;
        top: 0;
        left: 80px;
        right: 0;
        height: 70px;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 30px;
        z-index: 2000;
        border-bottom: 1px solid #ebf0f5;
        transition: left 0.25s ease;
    }

    .crm-sidebar.collapsed~.top-nav {
        left: 70px;
    }

    .crm-sidebar.expanded~.top-nav {
        left: 165px;
    }

    .crm-nav-left {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .crm-search-container {
        position: relative;
        width: 300px;
    }

    .crm-search-container i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #8392ab;
    }

    .crm-search-container input {
        width: 100%;
        padding: 10px 15px 10px 40px;
        border-radius: 12px;
        border: 1px solid #d2d6da;
        background: #f8f9fa;
        font-size: 14px;
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

    .user-avatar {
        width: 40px;
        height: 40px;
        background: var(--navy);
        color: #fff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }

    .badge-notify-dot {
        position: absolute;
        top: -2px;
        right: -2px;
        width: 8px;
        height: 8px;
        background: #ea0606;
        border: 2px solid #fff;
        border-radius: 50%;
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
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        display: none;
        padding: 10px 0;
        z-index: 9999;
        border: 1px solid #eee;
    }

    .dropdown-menu.show {
        display: block !important;
    }

    .admin-arrow.rotate {
        transform: rotate(180deg);
        transition: 0.3s;
    }

    .notification-wrapper {
        position: relative;
        cursor: pointer;
        display: flex;
        align-items: center;
    }

    .badge-notify-num {
        position: absolute;
        top: -8px;
        right: -10px;
        background: #ea0606;
        color: white;
        font-size: 10px;
        font-weight: bold;
        min-width: 16px;
        height: 16px;
        padding: 0 4px;
        border-radius: 10px;
        border: 2px solid #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        line-height: 1;
    }

    .crm-icon-stack i:hover {
        color: var(--navy);
        transform: scale(1.1);
        transition: 0.2s;
    }

    .crm-icon-stack {
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 18px;
        color: #8392ab;
    }


    .main-content {
        padding: 20px 40px;
    }

    .crm-sidebar.collapsed~.main-content {
        margin-left: 70px;
    }

    .crm-sidebar.expanded~.main-content {
        margin-left: 165px;
    }

    body {
        background-color: #f7f9fc;
    }

    .dashboard-card {
        background: #ffffff;
        border: none;
        border-radius: 16px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        min-height: 110px;
        position: relative;
    }

    .card-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .card-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .card-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #1b2559;
        margin-bottom: 2px;
    }

    .card-value {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1b2559;
        line-height: 1;
    }

    .card-subtext {
        font-size: 0.75rem;
        color: #a3adc2;
        margin-top: 5px;
    }


    .filter-row-container {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        margin-bottom: 25px;
    }

    .search-container {
        position: relative;
        flex: 2;
        min-width: 200px;
        display: flex;
        align-items: center;
    }

    .search-container i {
        position: absolute;
        left: 15px;
        z-index: 10;
        color: #a3adc2;
        font-size: 0.9rem;
        pointer-events: none;
    }

    .search-container .filter-control {
        padding-left: 40px !important;
        width: 100%;
    }

    .filter-group-item {
        flex: 1;
        min-width: 140px;
    }

    .filter-control {
        width: 100%;
        border: 1px solid #e0e5f2;
        border-radius: 8px;
        color: #8f9bba;
        height: 42px;
        background-color: #fff;
    }

    .btn-add-navy {
        background-color: #111c44;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0 25px;
        font-weight: 600;
        height: 42px;
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
        flex-shrink: 0;
    }



    .btn-add-navy:hover {
        background-color: #1b2559;
        color: white;
    }

    .date-filter {
        width: 200px;
    }

    .table-container {
        background: #ffffff;
        border-radius: 15px;
        padding: 25px;
        margin-top: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .table {
        border-collapse: collapse;
        width: 100%;
    }

    .table thead th {
        border: none;
        color: #8f9bba;
        font-weight: 700;
        font-size: 12px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        padding: 15px 10px;
        border-bottom: 1px solid #f1f4f9;
    }

    .table tbody td {
        border: none;
        border-bottom: 1px solid #f1f4f9;
        padding: 18px 10px;
        font-size: 14px;
        color: #2b3674;
        vertical-align: middle;
    }

    .status-text {
        font-weight: 700;
    }

    .text-high {
        color: #ee5d50 !important;
        font-weight: 700;
    }

    .text-medium {
        color: #ffb547 !important;
        font-weight: 700;
    }

    .text-low {
        color: #3cbb7eff !important;
        font-weight: 700;
    }

    .text-in-progress {
        color: #eab308 !important;
        font-weight: 700;
    }

    .text-investigating {
        color: #190864ff !important;
        font-weight: 700;
    }

    .text-resolved {
        color:  #22c55e !important;
        font-weight: 700;
    }


    .dataTables_info {
        color: #a3adc2 !important;
        font-size: 13px;
        margin-top: 20px;
    }

    .dataTables_paginate {
        margin-top: 15px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: none !important;
        background: none !important;
        color: #a3adc2 !important;
        font-weight: 700 !important;
        border-radius: 8px !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #f4f7fe !important;
        color: #4318ff !important;
        border: none !important;
        box-shadow: none !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f4f7fe !important;
        color: #4318ff !important;
        border: none !important;
        box-shadow: none !important;
    }

    .page-link {
        border: none !important;
        background: transparent !important;
        color: #a3adc2 !important;
        font-size: 13px;
        padding: 8px 12px;
    }

    .page-item.active .page-link {
        background: #4318ff !important;
        color: white !important;
        border-radius: 8px;
    }

    .table-main-title {
        font-weight: 700;
        color: #1b2559;
        margin: 0;
    }

    .featured-news-card .news-img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 12px;
    }

    .featured-news-card .news-title {
        color: #1b2559;
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .featured-news-card .news-text {
        font-size: 13px;
        color: #a3aed0;
        line-height: 1.4;
    }

    .category-item {
        background-color: #f4f7fe;
        border-radius: 12px;
        padding: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: transform 0.2s;
    }

    .category-item i {
        font-size: 14px;
    }

    .category-item span {
        font-size: 13px;
        font-weight: 600;
    }

    .cat-weather {
        color: #0580cdff;
    }

    .cat-traffic {
        color: #1f0888ff;
    }

    .cat-safety {
        color: #ee5d50;
    }


    .cat-general {
        color: #ce7c01ff;
    }

    .cat-medical {
        color: #01b555ff;
    }

    .cat-infra {
        color: #7551ff;
    }


    .btn-read-more {
        background: #1b2559;
        color: white;
        border-radius: 10px;
        font-size: 12px;
        padding: 6px 16px;
        border: none;
    }


    .stat-card {
        padding: 15px;
        border-radius: 12px;
        text-align: left;
        background: #fff;
        border: 1px solid #eee;
    }

    .stat-card h6 {
        font-size: 13px;
        color: #777;
    }

    .stat-card h4 {
        font-weight: bold;
    }

    .card {
        border-radius: 12px;
        border: 1px solid #eee;
    }

    .table td,
    .table th {
        vertical-align: middle;
    }

    .page-header h2 {
        color: #1b2559;
        font-weight: 700;
        margin-bottom: 2px;
    }

    #addIncidentModal .modal-content {
        border-radius: 20px;
        border: none;
        padding: 10px;
    }

    #addIncidentModal .modal-header {
        border-bottom: none;
        padding-bottom: 0;
    }

    #addIncidentModal .modal-title {
        color: #1b2559;
        font-weight: 700;
    }

    #addIncidentModal .modal-body {
        padding-top: 10px;
    }

    #addIncidentModal .form-label {
        font-size: 14px;
        font-weight: 600;
        color: #1b2559;
        margin-bottom: 6px;
    }

    #addIncidentModal .form-control,
    #addIncidentModal .form-select {
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 14px;
    }

    #addIncidentModal textarea {
        resize: none;
    }

    #addIncidentModal .modal-footer {
        border-top: none;
        padding-top: 0;
    }

    #addIncidentModal .btn {
        border-radius: 12px;
        padding: 8px 18px;
        font-weight: 600;
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
        top: 0px;
        right: 0px;
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
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
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
        transition: 0.2s;
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
        margin: 0 0 3px 0;
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
        transition: 0.2s;
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
    }

    .notif-empty p {
        font-size: 13px;
        margin: 0;
    }
</style>
<style>
    
@media (max-width: 768px) {
 
    /* Force sidebar to always be collapsed — never expands */
    #crm-sidebar {
        width: 70px !important;
        pointer-events: auto;
    }
 
    /* Override any JS-added .expanded class on small screens */
    #crm-sidebar.expanded {
        width: 70px !important;
    }
 
    /* Keep all the collapsed visual rules active */
    #crm-sidebar .logo-full       { display: none !important; }
    #crm-sidebar .logo-icon       { display: block !important; width: 36px !important; }
    #crm-sidebar .sidebar-logo    { height: 64px !important; padding: 10px 0 !important; }
    #crm-sidebar .sidebar-logo img{ width: 36px !important; height: 36px !important; }
 
    #crm-sidebar .menu-section    { padding: 0 !important; margin: 0 !important; height: 0 !important; overflow: hidden !important; }
    #crm-sidebar .menu-section .title { display: none !important; }
    #crm-sidebar .arrow           { display: none !important; }
 
    #crm-sidebar .submenu         { display: block !important; height: auto !important; opacity: 1 !important; visibility: visible !important; }
    #crm-sidebar .submenu li a    { justify-content: center !important; padding: 12px 0 !important; }
    #crm-sidebar .submenu li a span{ display: none !important; }
    #crm-sidebar .submenu li a i  { font-size: 20px !important; margin: 0 !important; }
    #crm-sidebar .badge-notify    { display: none !important; }
 
    /* Nav and content always use collapsed offset */
    .top-nav {
        left: 70px !important;
    }
 
    .main-content {
        margin-left: 70px !important;
        padding: 16px !important;
    }
 
    /* Hide the hamburger toggle button — not needed on small screens */
    #toggleSidebar {
        display: none !important;
    }
}
</style>