<?php

require_once('class/DAL.class.php');
require_once('class/task.class.php');

// if (!isset($_SESSION['admin_login'])) {
//     header("Location: login.php");
//     exit();
// }
$username = isset($_SESSION['name']) ? $_SESSION['name'] : '';
// var_dump($username);
// exit;

$taskObj   = new task();
$tasks     = $taskObj->getstatusdone($username);
?>
<style>
    .date-filters {
        display: flex;
        gap: 20px;
        font-weight: 500;
        color: #666;
        border-bottom: 1px solid #e3e3e3;
        padding-bottom: 8px;
    }

    .date-filter {
        cursor: pointer;
        padding-bottom: 6px;
        transition: 0.3s;
    }

    .date-filter.active {
        color: #0d6efd;
        border-bottom: 3px solid #0d6efd;
    }

    .date-filter.overdue.active {
        color: #dc3545;
        border-bottom-color: #dc3545;
    }


    .date-filters,
    h4,
    #tasksTableContainer {
        margin-left: -100px !important;
        /* margin-top: -20px !important; */
    }
</style>



<body>

    <div class="main-content p-4 ">



        <div class="tab-content">

            <!-- TASKS TAB -->
            <div class="tab-pane fade show active" id="tasks" role="tabpanel" aria-labelledby="tasks-tab">

                <h4 class="mb-3"> All Tasks</h4>


                <div class="d-flex justify-content-between align-items-center mb-3">

                    <!-- LEFT: TABS -->
                    <div class="date-filters d-flex gap-3">
                        <span class="date-filter active" data-filter="all">All</span>
                        <span class="date-filter" data-filter="today">Today</span>
                        <span class="date-filter" data-filter="tomorrow">Tomorrow</span>
                        <span class="date-filter" data-filter="this_week">This Week</span>
                        <span class="date-filter" data-filter="next_week">Next Week</span>
                        <span class="date-filter overdue" data-filter="overdue">Overdue</span>
                    </div>

                    <!-- RIGHT: CALENDAR -->
                    <input type="date" id="dateFilter"
                        class="form-control form-control-sm"
                        style="width:130px">
                </div>


                <div id="tasksTableContainer">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>task</th>
                                <th>due date</th>
                                <th>status</th>
                                <th>type</th>
                                <th>name</th>


                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($tasks)): ?>
                                <?php foreach ($tasks as $task): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($task['title']) ?>

                                            <?php if ($task['priority'] == 1): ?>
                                                <span class="badge bg-danger ms-2">Priority</span>
                                            <?php endif; ?>
                                        </td>

                                        <td><?= htmlspecialchars($task['due_date']) ?></td>
                                        <td>
                                            <?php
                                            $statuses = ['pending', 'done', 'cancelled'];
                                            $currentStatus = strtolower(trim($task['status'] ?? 'pending'));

                                            // reorder: put current status first
                                            $statuses = array_unique(array_merge([$currentStatus], $statuses));
                                            ?>

                                            <select class="form-select form-select-sm update-status" data-id="<?= $task['id'] ?>">
                                                <?php foreach ($statuses as $s): ?>
                                                    <option value="<?= $s ?>" <?= ($s == $currentStatus ? 'selected' : '') ?>>
                                                        <?= ucfirst($s) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>


                                        </td>
                                        <td><?= htmlspecialchars($task['related_type']) ?></td>
                                        <td><?= htmlspecialchars($task['related_name']) ?></td>

                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No tasks found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div id="noTasksMessage" class="text-center text-muted py-4" style="display:none;">
                    No tasks found
                </div>
            </div>





        </div>
    </div>
</body>
<!-- <script>
    alert(1)
</script> -->