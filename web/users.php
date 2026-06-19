<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<?php
require_once('class/DAL.class.php');
require_once('class/users.class.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$index = new users;
$indexx = $index->getallusers();
$roles = $index->getEnumValues("users", "role");

?>

<?php include('includes/header.php'); ?>



<div class="modal fade" id="edituserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="updateUserForm">

                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="id" id="edit_id">

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" id="edit_name" name="name" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" id="edit_email" name="email" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" id="edit_role" name="role">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role ?>"><?= $role ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Password (leave blank to keep old one)</label>
                        <input type="password" id="edit_pass" name="pass" class="form-control">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>

            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>
            <div class="modal-body">
                <form id="addForm" >
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1" style="width: 150px;">Name</span>
                        </div>
                        <input type="text" class="form-control" placeholder="name" aria-label="user" name="name" aria-describedby="basic-addon1" size="20" required>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1" style="width: 150px;">Email</span>
                        </div>
                        <input type="text" class="form-control" placeholder="email" aria-label="user" name="email" aria-describedby="basic-addon1" size="20" required>
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1" style="width: 150px;">Password</span>
                        </div>
                        <input type="password" class="form-control" placeholder="password" aria-label="user" name="pass" aria-describedby="basic-addon1" size="20" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>

                        <select class="form-select" name="role">
                            <option value="">Select role</option>

                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role ?>"><?= $role ?></option>
                            <?php endforeach; ?>
                        </select>

                    </div>


                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                        <button type="submit" class="btn btn-danger" value="upload-image">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<body>
    <?php include('includes/sidebar.php'); ?>


    <?php include('includes/nav.php'); ?>

    <div class="main-content">

        <h1 class="mt-4 page-title"></i> Users</h1>

        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item "><a href="admin_dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active ">users</li>
        </ol>
        <div align="right" class="mb-3">
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fa fa-plus"></i> Add User
            </button>


        </div>

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="datatablesSimple" class="table table-striped table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3">NAME</th>
                                <th class="py-3">status</th>
                                <th class="py-3">EMAIL</th>
                                <th class="py-3">ROLE</th>
                                <th class="text-center pe-4 py-3">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($indexx as $ind): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?php echo htmlspecialchars($ind['name']) ?></td>
                                    <td><?php echo htmlspecialchars($ind['ustatus']) ?></td>
                                    <td><?php echo htmlspecialchars($ind['email']) ?></td>
                                    <td><?php echo htmlspecialchars($ind['role']) ?></td>
                                    <td class="text-center pe-4">
                                        <a href="#" class="editUserBtn me-2"
                                            data-id="<?= $ind['id'] ?>"
                                            data-name="<?= $ind['name'] ?>"
                                            data-email="<?= $ind['email'] ?>"
                                            data-role="<?= $ind['role'] ?>">
                                            <i class="fas fa-edit text-success"></i>
                                        </a>
                                        <a data-id="<?php echo $ind['id'] ?>" class="delete" style="cursor: pointer;">
                                            <i class="fa fa-trash text-danger"></i>
                                        </a>
                                        <a href="https://mail.google.com/mail/?view=cm&to=<?= urlencode($ind['email']) ?>"
                                            target="_blank">
                                            <i class="fas fa-envelope text-primary"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/script.php'); ?>
    <script>
        $(document).on('click', '.editUserBtn', function() {

            $('#edit_id').val($(this).data('id'));
            $('#edit_name').val($(this).data('name'));
            $('#edit_email').val($(this).data('email'));
            $('#edit_role').val($(this).data('role'));
            $('#edit_pass').val('');

            let modal = new bootstrap.Modal(document.getElementById('edituserModal'));
            modal.show();
        });

        $('#updateUserForm').submit(function(e) {
            e.preventDefault();

            $.post('actions/update_user.php', $(this).serialize(), function(res) {

                if (res.status === 'success') {
                    bootstrap.Modal.getInstance(document.getElementById('edituserModal')).hide();
                    Swal.fire('Updated!', res.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', res.message, 'error');
                }

            }, 'json');
        });
        $('#addForm').submit(function(e) {

    e.preventDefault();

    $.ajax({
        url: 'actions/add_users.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',

        success: function(res) {

            if (res.status === 'success') {

                bootstrap.Modal.getInstance(
                    document.getElementById('addModal')
                ).hide();

                Swal.fire(
                    'Added!',
                    res.message,
                    'success'
                ).then(() => {
                    location.reload();
                });

            } else {

                Swal.fire(
                    'Error!',
                    res.message,
                    'error'
                );

            }
        }
    });

});
    </script>


</body>

</html>