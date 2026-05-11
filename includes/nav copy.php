<div class="top-nav">
    <button id="toggleSidebar" class="crm-toggle-btn">
        <i class="fa fa-bars"></i>
    </button>

    <div class="crm-nav-right">
        <span class="crm-username" id="navAdminName">
            <?php
            if (isset($_SESSION['id']) && isset($_SESSION['role'])) {
                echo htmlspecialchars($_SESSION['name']);
            } else {
                echo "Guest";
            }
            ?>


        </span>
        <div class="crm-user-dropdown">


            <i class="fa fa-chevron-down admin-arrow"></i>


            <ul class="dropdown-menu">

                <li><a href="#" id="openProfile">My Profile</a></li>


                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</div>

<div id="profileModal" class="crm-modal">
    <div class="crm-modal-content">
        <span class="close-modal">&times;</span>

        <h2>My Profile</h2>

        <form id="profileForm">
            <label>Name</label>
            <input type="text" name="name" id="profileName" class="form-control" required>

            <label>Email</label>
            <input type="email" name="email" id="profileEmail" class="form-control" required>


            <label>Password</label>
            <input type="password" placeholder="Enter new password" name="profilePassword" id="profilePassword" class="form-control">

            <label>Confirm your password </label>
            <input type="password" placeholder="••••••••" name="passconfirmed" id="passconfirmed">

            <button type="submit" class="crm-btn">Save Changes</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {

        // 🌸 When "My Profile" button is clicked
        $('#openProfile').on('click', function(e) {
            e.preventDefault();

            // show the custom modal
            $('#profileModal').css('display', 'flex');

            // fetch current admin info
            $.get('actions/get_admin_profile.php', function(res) {
                if (res.status === 'success') {
                    $('#profileName').val(res.data.name);
                    $('#profileEmail').val(res.data.email);

                    $('#profilePassword').val('');
                    console.log("AJAX response:", res);
                    console.log("Name:", res.data.name);
                    console.log($("#profileName"));
                    console.log($("#profileName").length);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.message || 'Failed to load profile information.'
                    });
                }
            }, 'json');
        });

        $('.close-modal').on('click', function() {
            $('#profileModal').css('display', 'none');
        });



        $('#profileForm').on('submit', function(e) {
            e.preventDefault();
            // --- PASSWORD MATCH CHECK ---
            const pass = $('#profilePassword').val();
            const confirm = $('#passconfirmed').val();

            if (pass !== "" && pass !== confirm) {
                Swal.fire({
                    icon: 'error',
                    title: 'Password Mismatch',
                    text: 'Passwords do not match!'
                });
                return; // stop form submit
            }

            const formData = $(this).serialize(); ////takes all the inputs inside your <form> and converts them into a URL-encoded string that can be sent easily with AJAX.

            $.post('actions/update_admin_profile.php', formData, function(res) {
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // close the modal
                        // $('#profileModal').modal('hide');
                        $('#profileModal').css('display', 'none');

                        // instantly update admin name in navbar
                        const newName = $('#profileName').val();
                        $('#navAdminName').text(newName);
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.message || 'Failed to update profile.'
                    });
                }
            }, 'json').fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Unable to connect to the server.'
                });
            });
        });

    });
</script>