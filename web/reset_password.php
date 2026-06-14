<?php
date_default_timezone_set('Asia/Beirut');
session_start();
require('class/DAL.class.php');

$dal = new DAL();

$token = $_GET['token'] ?? '';

$sql = "SELECT *
        FROM users
        WHERE reset_token = ?
        AND reset_token_expiry >= NOW()
        LIMIT 1";

$user = $dal->getRowSafe($sql, [$token]);

if (!$user) {

    die("Invalid or expired token");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $updateSql = "UPDATE users
                  SET password=?,
                      reset_token=NULL,
                      reset_token_expiry=NULL
                  WHERE id=?";

    $dal->executeSafe($updateSql, [
        $password,
        $user['id']
    ]);
    $updated = true; 

}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Reset Password</title>

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

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            width: 100%;
            padding-right: 45px;
            height: 55px;
        }

        #eyeIcon {
            position: absolute;
            right: 15px;
            top: 40%;
            transform: translateY(-50%);

            display: flex;
            align-items: center;
            justify-content: center;

            height: 100%;
            cursor: pointer;
            color: #9aa7b8;
            font-size: 18px;
        }
    </style>

</head>

<body>

    <div class="card">

        <h2>Reset Password</h2>

        <p>
            Enter your new password below.
        </p>

        <form method="POST">

            <div class="input-group">
                <div class="password-wrapper">
                    <input type="password"
                        name="password"
                        id="passwordInput"
                        placeholder="Enter your password"
                        required>

                    <span onclick="togglePassword()" id="eyeIcon">👁</span>
                </div>
            </div>

            <button type="submit">
                Update Password
            </button>

        </form>

    </div>
    <?php include('includes/script.php'); ?>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <?php if (isset($updated)): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Password Updated',
                text: 'You can now login.',
                confirmButtonColor: '#2d5a27'
            }).then(() => {
                window.location = 'login.php';
            });
        </script>
    <?php endif; ?>
</body>

</html>