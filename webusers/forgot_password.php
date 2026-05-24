<?php
date_default_timezone_set('Asia/Beirut');
// session_start();
session_start();

require('class/DAL.class.php');
require 'vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dal = new DAL();

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = trim($_POST['email']);

    $sql = "SELECT * FROM organizations WHERE email=?";
    $user = $dal->getRowSafe($sql, [$email]);

    if ($user) {

        $token = bin2hex(random_bytes(32));

        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $updateSql = "UPDATE organizations
                      SET reset_token=?,
                          reset_token_expiry=?
                      WHERE id=?";

        $dal->executeSafe($updateSql, [
            $token,
            $expiry,
            $user['id']
        ]);

        $resetLink = "http://localhost/senior/crises360/webusers/reset_password.php?token=$token";

        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();

            $mail->Host = 'smtp.gmail.com';

            $mail->SMTPAuth = true;

            $mail->Username = 'mourtadadouaa@gmail.com';

            $mail->Password = 'qlmx yszj qhqs izpz';

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

            $mail->Port = 587;

            $mail->setFrom('mourtadadouaa@gmail.com', 'Crisis360');

            $mail->addAddress($email);

            $mail->isHTML(true);

            $mail->Subject = 'Reset Password - Crisis360';

            $mail->Body = "
                <div style='font-family:Poppins,sans-serif;padding:20px'>
                    <h2 style='color:#2d5a27'>Reset Your Password</h2>

                    <p>
                        Click the button below to reset your password.
                    </p>

                    <a href='$resetLink'
                       style='background:#2d5a27;
                              color:white;
                              padding:12px 20px;
                              text-decoration:none;
                              border-radius:8px;
                              display:inline-block'>
                        Reset Password
                    </a>

                    <p style='margin-top:20px'>
                        This link expires in 1 hour.
                    </p>
                </div>
            ";

            $mail->send();

            $message = "success";
        } catch (Exception $e) {

            $message = $mail->ErrorInfo;
        }
    } else {

        $message = "Email not found";
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
            });
        </script>

    <?php endif; ?>

</body>

</html>