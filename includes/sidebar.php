<div id="crm-sidebar" class="crm-sidebar collapsed">

    <div class="sidebar-logo">
        <img class="logo-full" src="uploads/logo222.png" alt="Crisis 360 Logo">
        <img class="logo-icon" src="uploads/logo3.png">
    </div>
    <div class="sidebar-menu">

        <ul>
            <!-- MAIN -->
            <li>

                <div class="menu-section clickable">
                    <i class="fa fa-layer-group"></i>
                    <span class="title">MAIN</span>
                    <span class="arrow">▾</span>
                </div>


                <ul class="submenu">

                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php') ? 'active' : ''; ?>">
                        <a href="admin_dashboard.php" title="dashboard">
                            <i class="fa fa-home"></i>
                            <span class="title">Dashboard</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li>
                <div class="menu-section clickable">
                    <i class="fa fa-triangle-exclamation"></i>
                    <span class="title"> CRISIS</span>
                    <span class="arrow">▾</span>
                </div>

                <ul class="submenu">
                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'incidents.php') ? 'active' : ''; ?>"><a href="incidents.php" title="incidents"><i class="fa fa-exclamation-triangle"></i><span class="title">Incidents</span></a></li>
                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'alerts.php') ? 'active' : ''; ?>"><a href="alerts.php" title="alerts"><i class="fa fa-bell"></i><span class="title">Alerts</span></a></li>
                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'reports.php') ? 'active' : ''; ?>"><a href="reports.php" title="reports"><i class="fa fa-file-alt"></i><span class="title">Reports</span></a></li>
                </ul>
            </li>


            <!-- RESOURCES -->
            <li>
                <div class="menu-section clickable">
                    <i class="fa fa-hospital"></i>
                    <span class="title">RESOURCES </span>
                    <span class="arrow">▾</span>
                </div>
                <ul class="submenu">
                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'hospitals.php') ? 'active' : ''; ?>">
                        <a href="hospitals.php" title="hospitals">
                            <i class="fa fa-hospital"></i>
                            <span class="title">Hospitals</span>
                        </a>
                    </li>

                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'shelters.php') ? 'active' : ''; ?>">
                        <a href="shelters.php" title="shelters">
                            <i class="fa fa-house-user"></i>
                            <span class="title">Shelters</span>
                        </a>
                    </li>

                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'police.php') ? 'active' : ''; ?>">
                        <a href="police.php" title="police">
                            <i class="fa fa-shield-alt"></i>
                            <span class="title">Police</span>
                        </a>
                    </li>
                    <!-- <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'teams.php') ? 'active' : ''; ?>">
                        <a href="teams.php" title="teams">
                            <i class="fa fa-users-cog"></i>
                            <span class="title">Response Teams</span>
                        </a>
                    </li> -->
                </ul>
            </li>


            <!-- OPERATIONS -->
            <li>
                <div class="menu-section clickable">
                    <i class="fa fa-users"></i>
                    <span class="title">OPERATIONS </span>
                    <span class="arrow">▾</span>
                </div>


                <ul class="submenu">
    

                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'maps.php') ? 'active' : ''; ?>">
                        <a href="maps.php" title="maps">
                            <i class="fa fa-map-marked-alt"></i>
                            <span class="title">Maps</span>
                        </a>
                    </li>

                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'news.php') ? 'active' : ''; ?>">
                        <a href="news.php" title=" news">
                            <i class="fa-solid fa-newspaper"></i>
                            <span class="title">News</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!-- SYSTEM -->
            <li>
                <div class="menu-section clickable">
                    <i class="fa fa-cog"></i>
                    <span class="title">SYSTEM </span>
                    <span class="arrow">▾</span>
                </div>
                <ul class="submenu">
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active' : ''; ?>">
                            <a href="users.php" title="users">
                                <i class="fa fa-user"></i>
                                <span class="title">Users</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'settingss.php') ? 'active' : ''; ?>">
                        <a href="settings.php" title="settings">
                            <i class="fa fa-cog"></i>
                            <span class="title">Settings</span>
                        </a>
                    </li>
                </ul>
            </li>

        </ul>

    </div>
</div>