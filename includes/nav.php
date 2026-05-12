<nav class="top-nav">
    <div class="crm-nav-left">
        <button id="toggleSidebar" class="crm-toggle-btn">
            <i class="fa fa-bars"></i>
        </button>
        <div class="crm-breadcrumb">
            <!-- <span class="bc-folder">Pages</span> -->
            <i class="fa fa-chevron-right" style="font-size: 10px; color: #ccc; margin: 0 5px;"></i>
            <span class="bc-current" id="dynamicPageTitle"><?php echo $page_title ?? 'Dashboard'; ?></span>
        </div>
    </div>

    <div class="crm-nav-center">
        <div class="crm-search-container">
            <i class="fa fa-search"></i>
            <input type="text" placeholder="Type here to search...">
        </div>
    </div>

    <div class="crm-nav-right">
        <div class="crm-icon-stack" style="display: flex; gap: 18px; margin-right: 10px; color: #8392ab;">
            <!-- <i class="fa fa-cog" style="cursor: pointer;"></i> -->
            <div class="notification-wrapper" style="position: relative; cursor: pointer;">
                <i class="fa fa-bell"></i>
                <!-- <span class="badge-notify-dot"></span> -->
                 <span class="badge-notify-num">3</span>
            </div>
        </div>
        <div class="crm-divider"></div>

        <div class="crm-user-dropdown" id="userDropdownTrigger" style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
        
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
            </div>
            <div class="user-details" style="text-align: right;">
                <span class="user-name" id="navAdminName" style="display: block; font-weight: 600; font-size: 14px; color: #252f40;">
                    <?php echo htmlspecialchars($_SESSION['name']); ?>
                </span>
                <span class="user-role" style="font-size: 12px; color: #8392ab;">
                    <?php echo ucfirst($_SESSION['role']); ?>
                </span>
            </div>
            <i class="fa fa-chevron-down admin-arrow" style="font-size: 10px; color: #8392ab;"></i>

            <ul class="dropdown-menu" id="navDropdownMenu">
                <li><a href="#" id="openProfile"><i class="fa fa-user"></i> My Profile</a></li>
                <li><a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

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

        $('#openProfile').on('click', function(e) {
            e.preventDefault();

            $('#profileModal').css('display', 'flex');
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
            const pass = $('#profilePassword').val();
            const confirm = $('#passconfirmed').val();

            if (pass !== "" && pass !== confirm) {
                Swal.fire({
                    icon: 'error',
                    title: 'Password Mismatch',
                    text: 'Passwords do not match!'
                });
                return; 
            }

            const formData = $(this).serialize(); 

            $.post('actions/update_admin_profile.php', formData, function(res) {
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        
                        $('#profileModal').css('display', 'none');
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