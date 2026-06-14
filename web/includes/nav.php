<?php
require_once('class/DAL.class.php');
$dalNotif        = new DAL();
$unreadCount     = $dalNotif->countUnread();
$notifications   = $dalNotif->getUnreadNotifications();
?>

<style>
 
 
@keyframes dropIn {
    from { opacity:0; transform:translateY(-6px); }
    to   { opacity:1; transform:translateY(0); }
}

@keyframes modalIn {
    from { opacity:0; transform:scale(.95); }
    to   { opacity:1; transform:scale(1); }
}
 
@media (max-width: 768px) {
    .top-nav {
        padding: 0 14px;
        height: 58px;
        gap: 8px;
    }
 
    /* Hide user name/role text, show only avatar */
    .user-details,
    .admin-arrow {
        display: none !important;
    }
 
    .crm-user-dropdown {
        padding: 4px;
        gap: 0;
    }
 
    /* Breadcrumb shorter */
    .bc-current { max-width: 110px; font-size: 13px; }
 
    /* Notification dropdown goes left on mobile so it doesn't overflow */
    .notif-dropdown {
        right: auto;
        left: 50%;
        transform: translateX(-50%);
        width: 290px;
    }
 
    /* Dropdown menu doesn't overflow right edge */
    .dropdown-menu {
        right: 0;
        min-width: 150px;
    }
 
    .crm-divider { display: none; }
 
    .crm-modal-content {
        padding: 24px 18px;
        border-radius: 16px;
    }
}
 
@media (max-width: 400px) {
    .bc-current { max-width: 80px; font-size: 12px; }
    .notif-dropdown { width: 260px; }
}
</style>
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

    <!-- <div class="crm-nav-center">
        <div class="crm-search-container">
            <i class="fa fa-search"></i>
            <input type="text" placeholder="Type here to search...">
        </div>
    </div> -->

    <div class="crm-nav-right">
        <div class="crm-icon-stack" style="display: flex; gap: 18px; margin-right: 10px; color: #8392ab;">
            <!-- <i class="fa fa-cog" style="cursor: pointer;"></i>//style="position: relative; cursor: pointer;" -->
            <div class="notif-wrapper">
                <div class="notif-bell" onclick="toggleNotifDropdown()">
                    <i class="fas fa-bell"></i>
                    <?php if ($unreadCount > 0): ?>
                        <span class="notif-badge"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </div>

                <div class="notif-dropdown" id="notifDropdown" style="display:none;">
                    <div class="notif-header">
                        <span>Notifications</span>
                        <span class="notif-count"><?= $unreadCount ?> unread</span>
                    </div>

                    <?php if (empty($notifications)): ?>
                        <div class="notif-empty">
                            <i class="fas fa-check-circle"></i>
                            <p>No new notifications</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $notif):
                            $icon  = $notif['type'] === 'mission' ? 'fa-shield-alt' : 'fa-box';
                            $color = $notif['type'] === 'mission' ? '#1e3a5f' : '#e67e22';
                            $bg    = $notif['type'] === 'mission' ? '#eef2f7' : '#fef5ec';
                        ?>

                            <div class="notif-item" id="notif-<?= $notif['id'] ?>">
                                <div class="notif-icon" style="background:<?= $bg ?>">
                                    <i class="fas <?= $icon ?>" style="color:<?= $color ?>"></i>
                                </div>
                                <div class="notif-text"
                                    style="<?= $notif['type'] === 'need' ? 'cursor:pointer;' : '' ?>"
                                    onclick="<?= $notif['type'] === 'need' ? 'goToNeeds(' . $notif['id'] . ')' : '' ?>">
                                    <p><?= htmlspecialchars($notif['message']) ?></p>
                                    <span><?= date('M d, h:i A', strtotime($notif['created_at'])) ?></span>
                                </div>
                                <button onclick="markRead(<?= $notif['id'] ?>)" class="notif-seen-btn" title="Mark as read">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
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

        // Replace the old pollAdminNotifCount function with this one
        // inside $(document).ready(function() { ... })

        var lastNotifCount = <?= (int)$unreadCount ?>; // start from PHP-rendered count

        function renderAdminNotifList(notifications) {
            var typeIcons = {
                mission: {
                    icon: 'fa-shield-alt',
                    color: '#1e3a5f',
                    bg: '#eef2f7'
                },
                need: {
                    icon: 'fa-box',
                    color: '#e67e22',
                    bg: '#fef5ec'
                }
            };

            if (!notifications || notifications.length === 0) {
                return '<div class="notif-empty"><i class="fas fa-check-circle"></i><p>No new notifications</p></div>';
            }

            var html = '';
            notifications.forEach(function(notif) {
                var t = typeIcons[notif.type] || typeIcons['need'];
                var date = new Date(notif.created_at.replace(' ', 'T'));
                var label = date.toLocaleString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit'
                });
                var clickStyle = notif.type === 'need' ? 'cursor:pointer;' : '';
                var clickAttr = notif.type === 'need' ? 'onclick="goToNeeds(' + notif.id + ')"' : '';

                html += '<div class="notif-item" id="notif-' + notif.id + '">';
                html += '  <div class="notif-icon" style="background:' + t.bg + '">';
                html += '    <i class="fas ' + t.icon + '" style="color:' + t.color + '"></i>';
                html += '  </div>';
                html += '  <div class="notif-text" style="' + clickStyle + '" ' + clickAttr + '>';
                html += '    <p>' + $('<div>').text(notif.message).html() + '</p>';
                html += '    <span>' + label + '</span>';
                html += '  </div>';
                html += '  <button onclick="markRead(' + notif.id + ')" class="notif-seen-btn" title="Mark as read">';
                html += '    <i class="fas fa-check"></i>';
                html += '  </button>';
                html += '</div>';
            });
            return html;
        }

        function pollAdminNotifCount() {
            $.get('actions/get_admin_notifications.php', function(res) {
                if (!res || typeof res.count === 'undefined') return;

                var count = parseInt(res.count);
                var badge = $('.notif-badge');

                // --- update badge ---
                if (count > 0) {
                    if (badge.length) badge.text(count);
                    else $('.notif-bell').append('<span class="notif-badge">' + count + '</span>');
                    $('.notif-count').text(count + ' unread');
                } else {
                    badge.remove();
                    $('.notif-count').text('0 unread');
                }

                // --- refresh list only if count changed (avoid flicker while open) ---
                if (count !== lastNotifCount) {
                    lastNotifCount = count;
                    var dropdownBody = $('#notifDropdown').find('.notif-item, .notif-empty');
                    dropdownBody.remove(); // clear old items
                    $('#notifDropdown').append(renderAdminNotifList(res.notifications));
                }

            }, 'json');
        }

        setInterval(pollAdminNotifCount, 10000);

    });
</script>