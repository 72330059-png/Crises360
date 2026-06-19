<?php
date_default_timezone_set('Asia/Beirut');
session_start();
require('class/DAL.class.php');

$dal = new DAL();

$token = $_GET['token'] ?? '';

$sql = "SELECT *
        FROM organizations
        WHERE reset_token = ?
        AND reset_token_expiry >= NOW()
        LIMIT 1";

$user = $dal->getRowSafe($sql, [$token]);

if (!$user) {

    die("Invalid or expired token");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $updateSql = "UPDATE organizations
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = '👁️';
            } else {
                input.type = 'password';
                icon.textContent = '👁';
            }
        }
    </script>
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