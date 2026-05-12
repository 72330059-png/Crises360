<div id="crm-sidebar" class="crm-sidebar collapsed">
    <ul>


        <!-- // if (isset($_SESSION['admin_login']) && $_SESSION['admin_login'] === true): -->
        <?php if (isset($_SESSION['role'])): ?>
            <?php

            // ADMIN DASHBOARD
            if ($_SESSION['role'] === 'admin'): ?>

                <li>
                    <a href="admin_dashboard.php">
                        <i class="fa fa-home"></i>
                        <span class="title">Dashboard</span>
                    </a>
                </li>

            <?php
            // MANAGER DASHBOARD
            elseif ($_SESSION['role'] === 'manager'): ?>

                <li>
                    <a href="manager_dashboard.php">
                        <i class="fa fa-home"></i>
                        <span class="title">Dashboard</span>
                    </a>
                    
                </li>

            <?php
            // SALES DASHBOARD
            else: ?>

                <li>
                    <a href="sales_dashboard.php">
                        <i class="fa fa-home"></i>
                        <span class="title">Dashboard</span>
                    </a>
                </li>

            <?php endif; ?>

        <?php endif; ?>

        <!-- ONLY ADMIN SEES USERS -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>

            <li>
                <a href="users.php">
                    <i class="fa fa-user-plus"></i>
                    <span class="title">Users</span>
                </a>
            </li>

        <?php endif; ?>



        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <li>
                <a href="manage_users.php">
                    <i class="fa fa-user"></i>
                    <span class="title">Managing</span>
                </a>
            </li>

        <?php endif; ?>



        <li>
            <a href="contacts.php">
                <i class="fa fa-address-book"></i>
                <span class="title">Contacts</span>
            </a>
        </li>




        <li>
            <a href="pipeline.php">
                <i class="fa fa-columns"></i>
                <span class="title">pipeline </span>
            </a>
        </li>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>

            <li>
                <a href="leads.php">
                    <i class="fa fa-users"></i>
                    <span class="title">Leads</span>
                </a>
            </li>
        <?php endif; ?>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'sales'): ?>
            <li>
                <a href="lead_sales.php">
                    <i class="fa fa-users"></i>
                    <span class="title">Leads</span>
                </a>
            </li>
        <?php endif; ?>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'manager'): ?>
            <li>
                <a href="lead_manager.php">
                    <i class="fa fa-users"></i>
                    <span class="title">Leads</span>
                </a>
            </li>
        <?php endif; ?>
        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'manager')): ?>

            <li>
                <a href="deals.php">
                    <i class="fa fa-briefcase"></i>
                    <span class="title">Deals</span>
                </a>
            </li>
        <?php endif; ?>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'sales'): ?>
            <li>
                <a href="deals_sales.php">
                    <i class="fa fa-briefcase"></i>
                    <span class="title">Deals</span>
                </a>
            </li>
        <?php endif; ?>

        <li>
            <a href="customers.php">
                <i class="fa fa-user-group"></i>
                <span class="title">Customers</span>
            </a>
        </li>




        <li>
            <a href="meeting.php">
                <!-- <i class="fa fa-address-book"></i> -->
                <!-- <i class="fas fa-calendar-alt"></i> -->
                <!-- <i class="bi bi-calendar-event"></i> -->
                <!-- <i class="fas fa-calendar-check"></i> -->
                <i class="fas fa-clock"></i>



                <span class="title">meetings</span>
            </a>
        </li>


        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'manager')): ?>
            <li>
                <a href="tasks.php">
                    <i class="fa fa-tasks"></i>
                    <span class="title">Tasks</span>
                </a>
            </li>
        <?php endif; ?>




        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'sales'): ?>
            <li>
                <a href="tasks_sales.php">
                    <i class="fa fa-tasks"></i>
                    <span class="title">Tasks</span>
                </a>
            </li>
        <?php endif; ?>




         <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>

            <li>
                <a href="report.php">
                   <i class="fas fa-chart-bar"></i>

                    <span class="title">Users</span>
                </a>
            </li>

        <?php endif; ?>

    </ul>
</div>