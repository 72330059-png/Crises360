<?php
$orgType     = $_SESSION['type']         ?? '';
$orgName     = $_SESSION['org_name']     ?? '';
$orgLocation = $_SESSION['org_location'] ?? '';
$orgId       = (int)($_SESSION['org_id'] ?? 0);
$typeLabel = $orgType === 'police'        ? 'Police Unit'
  : ($orgType === 'hospital'     ? 'Hospital'
    : ($orgType === 'municipality' ? 'Municipality' : ucfirst($orgType)));

//  pending missions count (police only) 
$pendingCount = 0;
if ($orgType === 'police' && $orgId) {
  require_once('class/DAL.class.php');
  $dalN = new DAL();
  $row = $dalN->getRowSafe(
    "SELECT COUNT(*) as cnt
         FROM police_missions pm
         JOIN police_units pu ON pu.current_mission_id = pm.mission_id
         WHERE pu.organization_id = ? AND pm.status = 'sent'",
    [$orgId]
  );
  $sentCount = (int)($row['cnt'] ?? 0);

  $canceledRow = $dalN->getRowSafe(
    "SELECT COUNT(*) as cnt FROM notifications
         WHERE type = 'mission_canceled'
         AND target_org_id = ?
         AND is_read = 0",
    [$orgId]
  );
  $canceledCount = (int)($canceledRow['cnt'] ?? 0);

  $pendingCount = $sentCount + $canceledCount; // total for badge
}

//  active mission (police only) 
$activeMissionName  = '';
$activeIncidentName = '';
if ($orgType === 'police' && $orgId) {
  $dal2 = new DAL();
  $missionRow = $dal2->getRowSafe(
    "SELECT pm.title, i.incident_name
         FROM police_missions pm
         JOIN police_units pu ON pu.current_mission_id = pm.mission_id
         LEFT JOIN incidents i ON i.id = pu.incident_id
         WHERE pu.organization_id = ? AND pm.status = 'active'
         ORDER BY pm.created_at DESC LIMIT 1",
    [$orgId]
  );
  if ($missionRow) {
    $activeMissionName  = $missionRow['title']         ?? '';
    $activeIncidentName = $missionRow['incident_name'] ?? '';
  }
}

//  municipality notifications 
$needNotifCount = 0;
$needNotifs     = [];
if ($orgType === 'municipality' && $orgId) {
  $dalN = new DAL();
  $row  = $dalN->getRowSafe(
    "SELECT COUNT(*) as cnt FROM notifications WHERE target_org_id = ? AND is_read = 0",
    [$orgId]
  );
  $needNotifCount = (int)($row['cnt'] ?? 0);
  $needNotifs     = $dalN->getdata(
    "SELECT * FROM notifications WHERE target_org_id = ? AND is_read = 0 ORDER BY created_at DESC",
    [$orgId]
  );
}

//  hospital notifications 
$hospitalNotifCount = 0;
$hospitalNotifs     = [];
if ($orgType === 'hospital' && $orgId) {
  require_once('class/hospital.class.php');
  $navHospital        = new hospital_dashboard();
  $hospitalNotifCount = $navHospital->getHospitalNotifCount($orgId);
  $hospitalNotifs     = $navHospital->getHospitalNotifications($orgId);
}
?>

<nav class="top-nav">

  <!-- LEFT -->
  <div class="crm-nav-left">
    <div style="display: flex; flex-direction: column; justify-content: center; gap: 4px;">

      <?php if ($orgType === 'police'): ?>
        <span style="font-size: 16px; font-weight: 700; color: #1e293b; letter-spacing: -0.3px;">
          <?= htmlspecialchars($orgName) ?>
        </span>
        <div style="font-size: 12px; color: #64748b; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 2px;">
          <span style="color: #94a3b8;"><?= htmlspecialchars(ucfirst($orgLocation ?? '')) ?></span>
          <?php if ($activeIncidentName): ?>
            <span style="color: #cbd5e1; font-weight: 300;">|</span>
            <span style="color: #475569;">Incident: <strong style="color: #dc2626; font-weight: 600;"><?= htmlspecialchars($activeIncidentName) ?></strong></span>
          <?php endif; ?>
          <?php if ($activeMissionName): ?>
            <span style="color: #cbd5e1; font-weight: 300;">|</span>
            <span style="display: inline-flex; align-items: center; gap: 5px; background: #eff6ff; color: #1d4ed8; padding: 2px 8px; border-radius: 6px; font-weight: 600; font-size: 11px;">
              <span style="color: #3b82f6; font-size: 8px;">●</span> <?= htmlspecialchars($activeMissionName) ?>
            </span>
          <?php else: ?>
            <span style="color: #cbd5e1; font-weight: 300;">|</span>
            <span style="background: #f1f5f9; padding: 2px 8px; border-radius: 6px; color: #64748b; font-weight: 500; font-size: 11px;">No Active Mission</span>
          <?php endif; ?>
        </div>

      <?php elseif ($orgType === 'hospital'): ?>
        <span style="font-size: 16px; font-weight: 700; color: #1e293b; letter-spacing: -0.3px;">
          Emergency Portal
        </span>
        <div style="font-size: 12px; color: #64748b; display: flex; align-items: center; gap: 8px; margin-top: 2px;">
          <span style="font-weight: 600; color: #475569;"><?= htmlspecialchars($orgName) ?></span>
          <span style="color: #cbd5e1; font-weight: 300;">|</span>
          <span style="color: #94a3b8;"><?= htmlspecialchars(ucfirst($orgLocation)) ?></span>
        </div>

      <?php elseif ($orgType === 'municipality'): ?>
        <span style="font-size: 16px; font-weight: 700; color: #1e293b; letter-spacing: -0.3px;">
          Municipality Portal
        </span>
        <div style="font-size: 12px; color: #64748b; display: flex; align-items: center; gap: 8px; margin-top: 2px;">
          <span style="font-weight: 600; color: #475569;"><?= htmlspecialchars($orgName) ?></span>
          <span style="color: #cbd5e1; font-weight: 300;">|</span>
          <span style="color: #94a3b8;"><?= htmlspecialchars(ucfirst($orgLocation)) ?></span>
        </div>

      <?php else: ?>
        <span style="font-size: 16px; font-weight: 700; color: #1e293b;">
          🏢 <?= htmlspecialchars($orgName) ?>
        </span>
        <span style="font-size: 12px; color: #94a3b8; margin-top: 2px;"><?= htmlspecialchars(ucfirst($orgLocation)) ?></span>
      <?php endif; ?>

    </div>
  </div>

  <!-- RIGHT -->
  <div class="crm-nav-right">
    <div class="crm-icon-stack" style="display:flex;gap:18px;margin-right:10px;color:#8392ab;align-items:center;">
      <div class="notif-wrapper">
        <div class="notif-bell" onclick="<?php if ($orgType === 'police'): ?>openMissionNotif()<?php elseif ($orgType === 'municipality'): ?>openMuniNotif()<?php elseif ($orgType === 'hospital'): ?>openHospitalNotif()<?php endif; ?>">
          <i class="fas fa-bell"></i>
          <?php if ($orgType === 'police' && $pendingCount > 0): ?>
            <span class="notif-badge"><?= $pendingCount ?></span>
          <?php elseif ($orgType === 'municipality' && $needNotifCount > 0): ?>
            <span class="notif-badge"><?= $needNotifCount ?></span>
          <?php elseif ($orgType === 'hospital' && $hospitalNotifCount > 0): ?>
            <span class="notif-badge"><?= $hospitalNotifCount ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="crm-divider"></div>

    <div class="crm-user-dropdown" id="userDropdownTrigger"
      style="display:flex;align-items:center;gap:12px;cursor:pointer;">
      <div class="user-details" style="text-align:right;">
        <span class="user-name" id="navAdminName"
          style="display:block;font-weight:600;font-size:14px;color:#252f40;">
          <?= htmlspecialchars($orgName) ?>
        </span>
        <span class="user-role" style="font-size:12px;color:#8392ab;"><?= $typeLabel ?></span>
      </div>
      <i class="fa fa-chevron-down admin-arrow" style="font-size:10px;color:#8392ab;"></i>
      <ul class="dropdown-menu" id="navDropdownMenu">
        <li><a href="#" id="openProfile"><i class="fa fa-user"></i> My Profile</a></li>
        <li><a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </div>
  </div>

</nav>


<!-- ══════════ MUNICIPALITY NOTIF OVERLAY ══════════ -->
<?php if ($orgType === 'municipality'): ?>
  <div id="muniNotifOverlay" style="display:none;position:fixed;inset:0;background:rgba(10,22,40,0.45);z-index:999999;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
    <div style="background:#fff;border-radius:18px;padding:24px;width:440px;max-width:92vw;max-height:85vh;overflow-y:auto;box-shadow:0 20px 60px rgba(10,22,40,0.25);">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
        <div>
          <div style="font-size:16px;font-weight:800;color:#0f172a;">🔔 Notifications</div>
          <div style="font-size:12px;color:#94a3b8;margin-top:2px;">Need status updates</div>
        </div>
        <button onclick="closeMuniNotif()" style="width:30px;height:30px;border-radius:8px;border:none;background:#f1f5f9;cursor:pointer;font-size:14px;color:#475569;">✕</button>
      </div>
      <div id="muniNotifList">
        <?php if (empty($needNotifs)): ?>
          <div style="text-align:center;padding:30px 0;color:#94a3b8;">
            <div style="font-size:32px;margin-bottom:10px;">✅</div>
            <div style="font-size:13px;">No new notifications</div>
          </div>
        <?php else: ?>
          <?php foreach ($needNotifs as $notif): ?>
            <div id="muni-notif-<?= $notif['id'] ?>" style="border:1.5px solid #e2e8f0;border-radius:14px;padding:16px;margin-bottom:12px;">
              <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                <div style="flex:1;">
                  <div style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:6px;"><?= htmlspecialchars($notif['message']) ?></div>
                  <div style="font-size:11px;color:#94a3b8;"><?= date('M d, h:i A', strtotime($notif['created_at'])) ?></div>
                </div>
                <button onclick="markMuniNotifRead(<?= $notif['id'] ?>)" style="padding:7px 14px;border-radius:9px;border:1.5px solid #e2e8f0;background:#fff;color:#64748b;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">✓ Mark Read</button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endif; ?>


<!-- ══════════ HOSPITAL NOTIF OVERLAY ══════════ -->
<?php if ($orgType === 'hospital'): ?>
  <div id="hospitalNotifOverlay" style="display:none;position:fixed;inset:0;background:rgba(10,22,40,0.45);z-index:999999;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
    <div style="background:#fff;border-radius:18px;padding:24px;width:480px;max-width:92vw;max-height:85vh;overflow-y:auto;box-shadow:0 20px 60px rgba(10,22,40,0.25);">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
        <div>
          <div style="font-size:16px;font-weight:800;color:#0f172a;">🔔 Hospital Notifications</div>
          <div style="font-size:12px;color:#94a3b8;margin-top:2px;">Transfer requests &amp; updates</div>
        </div>
        <button onclick="closeHospitalNotif()" style="width:30px;height:30px;border-radius:8px;border:none;background:#f1f5f9;cursor:pointer;font-size:14px;color:#475569;">✕</button>
      </div>
      <div id="hospitalNotifList">
        <?php if (empty($hospitalNotifs)): ?>
          <div style="text-align:center;padding:30px 0;color:#94a3b8;">
            <div style="font-size:32px;margin-bottom:10px;">✅</div>
            <div style="font-size:13px;">No new notifications</div>
          </div>
        <?php else: ?>
          <?php foreach ($hospitalNotifs as $notif): ?>
            <div id="hospital-notif-<?= $notif['id'] ?>" style="border:1.5px solid #e2e8f0;border-radius:14px;padding:16px;margin-bottom:12px;">
              <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                <div style="flex:1;">
                  <div style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:6px;"><?= htmlspecialchars($notif['message']) ?></div>
                  <div style="font-size:11px;color:#94a3b8;"><?= date('M d, h:i A', strtotime($notif['created_at'])) ?></div>
                  <?php if ($notif['type'] === 'transfer_request'): ?>
                    <div style="display:flex;gap:8px;margin-top:10px;">
                      <button onclick="respondTransfer(<?= $notif['transfer_id'] ?>, 'accepted', <?= $notif['id'] ?>)"
                        style="padding:6px 16px;border-radius:8px;border:none;background:#16a34a;color:#fff;font-size:12px;font-weight:700;cursor:pointer;">
                        ✓ Accept
                      </button>
                      <button onclick="respondTransfer(<?= $notif['transfer_id'] ?>, 'rejected', <?= $notif['id'] ?>)"
                        style="padding:6px 16px;border-radius:8px;border:none;background:#dc2626;color:#fff;font-size:12px;font-weight:700;cursor:pointer;">
                        ✕ Reject
                      </button>
                    </div>
                  <?php endif; ?>
                </div>
                <?php if ($notif['type'] !== 'transfer_request'): ?>
                  <button onclick="markHospitalNotifRead(<?= $notif['id'] ?>)"
                    style="padding:7px 14px;border-radius:9px;border:1.5px solid #e2e8f0;background:#fff;color:#64748b;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">
                    ✓ Mark Read
                  </button>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endif; ?>


<!-- ══════════ PROFILE MODAL ══════════ -->
<div id="profileModal" class="crm-modal">
  <div class="crm-modal-content">
    <span class="close-modal">&times;</span>
    <h2>My Profile</h2>
    <form id="profileForm">
      <label>Name</label>
      <input type="text" name="name" id="profileName" class="form-control" required>
      <label>Email</label>
      <input type="email" name="email" id="profileEmail" class="form-control" required>
      <?php if ($orgType === 'hospital'): ?>
        <label>Phone</label>
        <input type="text" name="phone" id="profilePhone" class="form-control">
      <?php endif; ?>
      <label>Password</label>
      <input type="password" placeholder="Enter new password" name="profilePassword" id="profilePassword" class="form-control">
      <label>Confirm your password</label>
      <input type="password" placeholder="••••••••" name="passconfirmed" id="passconfirmed" class="form-control">
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
      $.get('actions/get_org_profile.php', function(res) {
        if (res.status === 'success') {
          $('#profileName').val(res.data.name);
          $('#profileEmail').val(res.data.email);
          if ($('#profilePhone').length) {
            $('#profilePhone').val(res.data.phone ?? '');
          }
          $('#profilePassword').val('');
          $('#passconfirmed').val('');
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: res.message || 'Failed to load profile.'
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
      if (pass !== '' && pass !== confirm) {
        Swal.fire({
          icon: 'error',
          title: 'Password Mismatch',
          text: 'Passwords do not match!'
        });
        return;
      }
      $.post('actions/update_org_profile.php', $(this).serialize(), function(res) {
        if (res.status === 'success') {
          $('#profileModal').css('display', 'none');
          $('#navAdminName').text($('#profileName').val());
          Swal.fire({
              icon: 'success',
              title: 'Updated!',
              text: res.message,
              timer: 1500,
              showConfirmButton: false
            })
            .then(() => {
              window.location.reload();
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

    $('#userDropdownTrigger').on('click', function(e) {
      e.stopPropagation();
      $('#navDropdownMenu').toggleClass('show');
    });
    $(document).on('click', function() {
      $('#navDropdownMenu').removeClass('show');
    });



    var lastOrgNotifCount = <?= $orgType === 'police' ? $pendingCount : ($orgType === 'municipality' ? $needNotifCount : $hospitalNotifCount) ?>;
    var orgType = '<?= $orgType ?>';

    function updateOrgBadge(count) {
      var badge = $('.notif-badge');
      if (count > 0) {
        if (badge.length) badge.text(count);
        else $('.notif-bell').append('<span class="notif-badge">' + count + '</span>');
      } else {
        badge.remove();
      }
    }

    function emptyHtml(msg) {
      return '<div style="text-align:center;padding:30px 0;color:#94a3b8;">' +
        '<div style="font-size:32px;margin-bottom:10px;">✅</div>' +
        '<div style="font-size:13px;">' + msg + '</div></div>';
    }

    function esc(str) {
      return $('<div>').text(str || '').html();
    }

    function fmtDate(str) {
      var d = new Date(str.replace(' ', 'T'));
      return d.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit'
      });
    }

    function renderPoliceNotifs(sentMissions, canceledNotifs) {
      var html = '';
      sentMissions.forEach(function(m) {
        var priBg = m.priority === 'High' ? '#fdecea' : (m.priority === 'Medium' ? '#fffbeb' : '#e8f5e9');
        var priColor = m.priority === 'High' ? '#e53935' : (m.priority === 'Medium' ? '#b45309' : '#2e7d32');
        html += '<div style="border:1.5px solid #e2e8f0;border-radius:14px;padding:16px;margin-bottom:12px;">';
        html += '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">';
        html += '<span style="font-size:13px;font-weight:700;color:#0f172a;">📋 ' + esc(m.title) + '</span>';
        html += '<span style="padding:3px 10px;border-radius:6px;font-size:11px;font-weight:700;background:' + priBg + ';color:' + priColor + ';">' + esc(m.priority) + '</span></div>';
        if (m.incident_name) html += '<div style="font-size:11px;color:#475569;margin-bottom:8px;"><span style="color:#94a3b8;">Incident:</span> <span style="font-weight:600;">' + esc(m.incident_name) + '</span></div>';
        if (m.description) html += '<div style="font-size:12px;color:#475569;background:#f8fafc;border-radius:8px;padding:9px 11px;margin-bottom:12px;line-height:1.5;">' + esc(m.description) + '</div>';
        html += '<div style="display:flex;gap:8px;">';
        html += '<button onclick="respondMission(' + m.mission_id + ',\'accept\')" style="flex:1;padding:9px;border-radius:9px;border:none;background:#2e7d32;color:#fff;font-size:13px;font-weight:700;cursor:pointer;">✔ Accept</button>';
        html += '<button onclick="respondMission(' + m.mission_id + ',\'reject\')" style="flex:1;padding:9px;border-radius:9px;border:1.5px solid #e2e8f0;background:#fff;color:#64748b;font-size:13px;font-weight:700;cursor:pointer;">✕ Reject</button>';
        html += '</div></div>';
      });
      canceledNotifs.forEach(function(n) {
        html += '<div style="border:1.5px solid #fde8e8;border-radius:14px;padding:16px;margin-bottom:12px;background:#fff5f5;">';
        html += '<div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;"><span style="font-size:22px;">❌</span>';
        html += '<div><div style="font-size:13px;font-weight:700;color:#e53935;">Mission Canceled</div>';
        html += '<div style="font-size:11px;color:#94a3b8;">' + fmtDate(n.created_at) + '</div></div></div>';
        html += '<div style="font-size:12px;color:#475569;background:#fef2f2;border-radius:8px;padding:9px 11px;">' + esc(n.message) + '</div>';
        html += '<button onclick="respondMission(' + n.id + ',\'read\')" style="margin-top:10px;width:100%;padding:8px;border-radius:9px;border:1.5px solid #fecaca;background:#fff;color:#e53935;font-size:12px;font-weight:600;cursor:pointer;">✓ Dismiss</button>';
        html += '</div>';
      });
      return html || emptyHtml('No pending missions');
    }

    function renderMuniNotifs(notifications) {
      if (!notifications || !notifications.length) return emptyHtml('No new notifications');
      var html = '';
      notifications.forEach(function(n) {
        html += '<div id="muni-notif-' + n.id + '" style="border:1.5px solid #e2e8f0;border-radius:14px;padding:16px;margin-bottom:12px;">';
        html += '<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">';
        html += '<div style="flex:1;"><div style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:6px;">' + esc(n.message) + '</div>';
        html += '<div style="font-size:11px;color:#94a3b8;">' + fmtDate(n.created_at) + '</div></div>';
        html += '<button onclick="markMuniNotifRead(' + n.id + ')" style="padding:7px 14px;border-radius:9px;border:1.5px solid #e2e8f0;background:#fff;color:#64748b;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">✓ Mark Read</button>';
        html += '</div></div>';
      });
      return html;
    }

    function renderHospitalNotifs(notifications) {
      if (!notifications || !notifications.length) return emptyHtml('No new notifications');
      var html = '';
      notifications.forEach(function(n) {
        html += '<div id="hospital-notif-' + n.id + '" style="border:1.5px solid #e2e8f0;border-radius:14px;padding:16px;margin-bottom:12px;">';
        html += '<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">';
        html += '<div style="flex:1;"><div style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:6px;">' + esc(n.message) + '</div>';
        html += '<div style="font-size:11px;color:#94a3b8;">' + fmtDate(n.created_at) + '</div>';
        if (n.type === 'transfer_request') {
          html += '<div style="display:flex;gap:8px;margin-top:10px;">';
          html += '<button onclick="respondTransfer(' + n.transfer_id + ',\'accepted\',' + n.id + ')" style="padding:6px 16px;border-radius:8px;border:none;background:#16a34a;color:#fff;font-size:12px;font-weight:700;cursor:pointer;">✓ Accept</button>';
          html += '<button onclick="respondTransfer(' + n.transfer_id + ',\'rejected\',' + n.id + ')" style="padding:6px 16px;border-radius:8px;border:none;background:#dc2626;color:#fff;font-size:12px;font-weight:700;cursor:pointer;">✕ Reject</button>';
          html += '</div>';
        }
        html += '</div>';
        if (n.type !== 'transfer_request') {
          html += '<button onclick="markHospitalNotifRead(' + n.id + ')" style="padding:7px 14px;border-radius:9px;border:1.5px solid #e2e8f0;background:#fff;color:#64748b;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">✓ Mark Read</button>';
        }
        html += '</div></div>';
      });
      return html;
    }

    function pollOrgNotifCount() {
      var endpoint = orgType === 'police' ? 'actions/get_police_notifications.php' :
        orgType === 'municipality' ? 'actions/get_muni_notifications.php' :
        orgType === 'hospital' ? 'actions/get_hospital_notifications.php' :
        null;
      if (!endpoint) return;

      $.get(endpoint, function(res) {
        if (!res || typeof res.count === 'undefined') return;
        var count = parseInt(res.count);

        updateOrgBadge(count);

        if (count === lastOrgNotifCount) return; 
        lastOrgNotifCount = count;

        if (orgType === 'police') {
          var card = document.querySelector('#missionNotifOverlay > div');
          if (card) {
            while (card.children.length > 1) card.removeChild(card.lastChild);
            var tmp = document.createElement('div');
            tmp.innerHTML = renderPoliceNotifs(res.sentMissions || [], res.canceledNotifs || []);
            while (tmp.firstChild) card.appendChild(tmp.firstChild);
          }
        } else if (orgType === 'municipality') {
          var ml = document.getElementById('muniNotifList');
          if (ml) ml.innerHTML = renderMuniNotifs(res.notifications || []);
        } else if (orgType === 'hospital') {
          var hl = document.getElementById('hospitalNotifList');
          if (hl) hl.innerHTML = renderHospitalNotifs(res.notifications || []);
        }

      }, 'json');
    }

    setInterval(pollOrgNotifCount, 10000);
  });
</script>


<!-- ══════════ MUNICIPALITY SCRIPTS ══════════ -->
<?php if ($orgType === 'municipality'): ?>
  <script>
    function openMuniNotif() {
      document.getElementById('muniNotifOverlay').style.display = 'flex';
    }

    function closeMuniNotif() {
      document.getElementById('muniNotifOverlay').style.display = 'none';
    }
    document.addEventListener('click', function(e) {
      var o = document.getElementById('muniNotifOverlay');
      if (o && e.target === o) closeMuniNotif();
    });

    function markMuniNotifRead(id) {
      $.post('actions/mark_notifs_read.php', {
        id: id
      }, function(res) {
        if (res.status === 'success') {
          location.reload();
          var el = document.getElementById('muni-notif-' + id);
          if (el) el.remove();
          var badge = document.querySelector('.notif-badge');
          if (badge) {
            var c = parseInt(badge.textContent) - 1;
            if (c <= 0) badge.remove();
            else badge.textContent = c;
          }
          var list = document.getElementById('muniNotifList');
          if (list && list.children.length === 0) list.innerHTML = '<div style="text-align:center;padding:30px 0;color:#94a3b8;"><div style="font-size:32px;margin-bottom:10px;">✅</div><div style="font-size:13px;">No new notifications</div></div>';
        }
      }, 'json');
    }
  </script>
<?php endif; ?>


<!-- ══════════ HOSPITAL SCRIPTS ══════════ -->
<?php if ($orgType === 'hospital'): ?>
  <script>
    function openHospitalNotif() {
      document.getElementById('hospitalNotifOverlay').style.display = 'flex';
    }

    function closeHospitalNotif() {
      document.getElementById('hospitalNotifOverlay').style.display = 'none';
    }
    document.addEventListener('click', function(e) {
      var o = document.getElementById('hospitalNotifOverlay');
      if (o && e.target === o) closeHospitalNotif();
    });

    function updateHospitalBadge() {
      var badge = document.querySelector('.notif-badge');
      if (badge) {
        var c = parseInt(badge.textContent) - 1;
        if (c <= 0) badge.remove();
        else badge.textContent = c;
      }
    }

    function checkHospitalNotifEmpty() {
      var list = document.getElementById('hospitalNotifList');
      if (list && list.children.length === 0)
        list.innerHTML = '<div style="text-align:center;padding:30px 0;color:#94a3b8;"><div style="font-size:32px;margin-bottom:10px;">✅</div><div style="font-size:13px;">No new notifications</div></div>';
    }

    function markHospitalNotifRead(id) {
      $.post('actions/mark_hospital_notif_read.php', {
        id: id
      }, function(res) {
        if (res.status === 'success') {
          location.reload();
          var el = document.getElementById('hospital-notif-' + id);
          if (el) el.remove();
          updateHospitalBadge();
          checkHospitalNotifEmpty();
        }
      }, 'json');
    }

    function respondTransfer(transferId, action, notifId) {
      var label = action === 'accepted' ? 'Accept' : 'Reject';
      Swal.fire({
        title: label + ' Transfer?',
        text: 'Are you sure you want to ' + action + ' this transfer request?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: action === 'accepted' ? '#16a34a' : '#dc2626',
        confirmButtonText: 'Yes, ' + label
      }).then(function(result) {
        if (result.isConfirmed) {
          $.post('actions/respond_transfer.php', {
            transfer_id: transferId,
            action: action,
            notif_id: notifId
          }, function(res) {
            if (res.success) {

              document.getElementById('hospitalNotifOverlay').style.display = 'none';

              Swal.fire({
                icon: 'success',
                title: action === 'accepted' ? 'Transfer Accepted!' : 'Transfer Rejected!',
                text: action === 'accepted' ?
                  'The transfer request was accepted successfully.' : 'The transfer request was rejected successfully.',
                allowOutsideClick: false,
                timer: 1500,
                showConfirmButton: false
              }).then(() => {
                window.location.reload();
              });

            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: res.message || 'Something went wrong.'
              });
            }
          }, 'json');
        }
      });
    }
  </script>
<?php endif; ?>