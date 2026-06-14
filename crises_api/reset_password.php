<?php
date_default_timezone_set('Asia/Beirut');
include 'db.php';

$token = trim($_GET['token'] ?? '');

// ── Validate token ────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT id, full_name
    FROM members
    WHERE reset_token = ?
    AND reset_token_expiry >= NOW()
");
$stmt->bind_param("s", $token);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    die("
    <div style='font-family:sans-serif;text-align:center;margin-top:80px'>
        <h2 style='color:#c0392b'>Link Expired</h2>
        <p>This reset link is invalid or has expired.</p>
        <p>Please request a new one from the app.</p>
    </div>
    ");
}

// ── Handle form submission ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = trim($_POST['password'] ?? '');
    $confirm     = trim($_POST['confirm']  ?? '');

    if (strlen($newPassword) < 6) {
        $error = "Password must be at least 6 characters.";
    } else if ($newPassword !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = $conn->prepare("
            UPDATE members
            SET password = ?, reset_token = NULL, reset_token_expiry = NULL
            WHERE id = ?
        ");
        $update->bind_param("si", $hashed, $row['id']);
        $update->execute();

        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Password Updated',
            text: 'You can now log in with your new password.',
            confirmButtonColor: '#2d5a27'
        }).then(() => { window.close(); });
        </script>
        ";
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password — Crises App</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:Poppins,sans-serif; }
        body { height:100vh; display:flex; justify-content:center;
               align-items:center; background:#f4f7fb; }
        .card { width:420px; background:white; padding:40px;
                border-radius:20px; box-shadow:0 10px 30px rgba(0,0,0,0.1); }
        h2 { margin-bottom:6px; color:#1e293b; }
        p  { color:#64748b; margin-bottom:24px; font-size:14px; }
        .name { color:#2d5a27; font-weight:600; }
        input { width:100%; padding:14px; border-radius:10px;
                border:1px solid #ddd; margin-bottom:14px; font-size:14px; }
        input:focus { outline:none; border-color:#2d5a27; }
        .error { color:#c0392b; font-size:13px; margin-bottom:12px; }
        button { width:100%; padding:14px; border:none; background:#2d5a27;
                 color:white; border-radius:10px; cursor:pointer; font-size:15px;
                 font-family:Poppins,sans-serif; }
        button:hover { background:#245022; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Reset Password</h2>
        <p>Hello <span class="name"><?= htmlspecialchars($row['full_name']) ?></span>,
           enter your new password below.</p>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="password" name="password"
                   placeholder="New password (min 6 characters)" required>
            <input type="password" name="confirm"
                   placeholder="Confirm new password" required>
            <button type="submit">Update Password</button>
        </form>
    </div>
</body>
</html>