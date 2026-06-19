<?php
date_default_timezone_set('Asia/Beirut');
// session_start();
session_start();

require('class/DAL.class.php');
require 'vendor/autoload.php';
require 'send_gmail_oauth.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$dal = new DAL();

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = trim($_POST['email']);

    $sql = "SELECT * FROM users WHERE email=?";
    $user = $dal->getRowSafe($sql, [$email]);

    if ($user) {

        $token = bin2hex(random_bytes(32));

        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $updateSql = "UPDATE users
                      SET reset_token=?,
                          reset_token_expiry=?
                      WHERE id=?";

        $dal->executeSafe($updateSql, [
            $token,
            $expiry,
            $user['id']
        ]);

        // $resetLink = "http://localhost/senior/crises360/web/reset_password.php?token=$token";
        $resetLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/reset_password.php?token=$token";

       $htmlBody = "
    <div style='font-family:Poppins,sans-serif;padding:20px'>
        <h2 style='color:#2d5a27'>Reset Your Password</h2>
        <p>Click the button below to reset your password.</p>
        <a href='$resetLink'
           style='background:#2d5a27;color:white;padding:12px 20px;
                  text-decoration:none;border-radius:8px;display:inline-block'>
            Reset Password
        </a>
        <p style='margin-top:20px'>This link expires in 1 hour.</p>
    </div>
";

$result = sendGmailOAuth($email, 'Reset Password - Crisis360', $htmlBody);

if ($result['success']) {
    $message = "success";
} else {
    $message = $result['error'];
    echo "<pre>" . $result['error'] . "</pre>";
}
    } else {

        $message = "Email not found";
        echo "EMAIL NOT FOUND IN DB"; // ADD THIS

    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Forgot Password</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Poppins, sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f4f7fb;
        }

        .card {
            width: 420px;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-bottom: 10px;
            color: #1e293b;
        }

        p {
            color: #64748b;
            margin-bottom: 25px;
        }

        input {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }

        button {
            width: 100%;
            padding: 14px;
            border: none;
            background: #2d5a27;
            color: white;
            border-radius: 10px;
            cursor: pointer;
            font-size: 15px;
        }
        /* Responsive Design */

@media (max-width: 768px) {

    body {
        padding: 20px;
    }

    .card {
        width: 100%;
        max-width: 500px;
        padding: 30px;
    }

    h2 {
        font-size: 24px;
    }

    p {
        font-size: 14px;
        line-height: 1.6;
    }

    input,
    button {
        font-size: 14px;
        padding: 13px;
    }

    .password-wrapper input {
        height: 50px;
    }
}

@media (max-width: 480px) {

    body {
        padding: 15px;
    }

    .card {
        width: 100%;
        padding: 25px 20px;
        border-radius: 15px;
    }

    h2 {
        font-size: 22px;
        text-align: center;
    }

    p {
        font-size: 13px;
        text-align: center;
    }

    input,
    button {
        padding: 12px;
        font-size: 14px;
    }

    .password-wrapper input {
        padding-right: 45px;
        height: 48px;
    }

    #eyeIcon {
        right: 12px;
        font-size: 16px;
    }
}

@media (max-width: 320px) {

    .card {
        padding: 20px 15px;
    }

    h2 {
        font-size: 20px;
    }

    p {
        font-size: 12px;
    }

    input,
    button {
        font-size: 13px;
    }
}
    </style>

</head>

<body>

    <div class="card">

        <h2>Forgot Password</h2>

        <p>
            Enter your email to receive a reset link.
        </p>

        <form method="POST">

            <input type="email"
                name="email"
                placeholder="Enter your email"
                required>

            <button type="submit">
                Send Reset Link
            </button>

        </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if ($message == "success"): ?>

        <script>
            Swal.fire({
                icon: 'success',
                title: 'Email Sent',
                text: 'Reset link sent successfully.',
                confirmButtonColor: '#2d5a27'
            }).then(() => {
                window.location.href = 'login.php';
            });
        </script>

    <?php endif; ?>

</body>

</html>