<?php
session_start();
require('class/DAL.class.php');
$dal = new DAL();

// $baseURL = "http://localhost/senior/crises360/web/";
$baseURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/";

/* ALREADY LOGGED IN*/
if (isset($_SESSION['id']) && isset($_SESSION['role'])) {

    if ($_SESSION['role'] === 'admin') {
        header("Location: {$baseURL}admin_dashboard.php");
        exit;
    }

    if ($_SESSION['role'] === 'manager') {
        header("Location: {$baseURL}manager_dashboard.php");
        exit;
    }

    header("Location: {$baseURL}manager_dashboard.php");
    exit;
}

/*LOGIN PROCES */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // captcha befor all
    $recaptcha = $_POST['g-recaptcha-response'] ?? '';
    // $secret = 'secret';
    $secret = RECAPTCHA_SECRET;
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$recaptcha}");
    $result = json_decode($verify);

    if (!$result->success) {
        $_SESSION['flash'] = [
            'icon' => 'error',
            'title' => 'Bot Detected',
            'text'  => 'Please complete the CAPTCHA.',
            'redirect' => $baseURL . "login.php",
            'timer' => 2000,
            'showConfirmButton' => true
        ];
        header("Location: {$baseURL}login.php");
        exit;
    }
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $sql = "SELECT id, name, password, role FROM users WHERE email = '$email'";
    $result = $dal->getdata($sql);

    if ($result && count($result) > 0) {
        $user = $result[0];

        if (password_verify($password, $user['password'])) {

            session_regenerate_id(true);

            $_SESSION['id']    = $user['id'];
            $_SESSION['name']  = $user['name'];
            $_SESSION['email'] = $email;
            $_SESSION['role']  = $user['role'];
            $_SESSION['logged_in'] = true;

            $now = date("Y-m-d H:i:s");
            $dal->execute("UPDATE users SET ustatus='online', last_activity='$now' WHERE id='{$user['id']}'");

            if ($user['role'] === 'admin') {
                header("Location: {$baseURL}admin_dashboard.php");
                exit;
            }

            if ($user['role'] === 'manager') {
                header("Location: {$baseURL}manager_dashboard.php");
                exit;
            }

            header("Location: {$baseURL}sales_dashboard.php");
            exit;
        }

        // Wrong password
        $_SESSION['flash'] = [
            'icon' => 'error',
            'title' => 'Invalid Password',
            'text'  => 'Please try again.',
            'redirect' => $baseURL . "login.php",
            'timer' => 1500,
            'showConfirmButton' => true
        ];

        header("Location: {$baseURL}login.php");
        exit;
    }

    // Email not found
    $_SESSION['flash'] = [
        'icon' => 'error',
        'title' => 'Email Not Found',
        'text'  => 'Please try again.',
        'redirect' => $baseURL . "login.php",
        'timer' => 1500,
        'showConfirmButton' => true
    ];

    header("Location: {$baseURL}login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crisis360 Login</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Playfair+Display:wght@500;600;700&display=swap"
        rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(180deg, #eef2f7, #e3eaf3);
        }

        .g-recaptcha {
            transform: scale(0.77);
            transform-origin: left;
            margin-bottom: -10px;
        }

        .container {
            width: 1100px;
            height: 600px;
            display: flex;
            overflow: hidden;
            border-radius: 24px;
            background: linear-gradient(180deg, #f8fafc, #eef2f7);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
        }

        .left {
            width: 45%;
            position: relative;
            background: url('uploads/naqoura.jpg') center/cover no-repeat;
        }

        .left::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg,
                    rgba(0, 0, 0, 0.15) 0%,
                    rgba(0, 0, 0, 0.35) 100%);
        }

        .left-content {
            position: absolute;
            bottom: 50px;
            left: 40px;
            right: 40px;
            max-width: 300px;
            color: white;
            z-index: 2;
        }

        .left-content h1 {
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 40px;
            letter-spacing: -0.5px;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        }

        .left-content p {
            font-size: 15px;
            line-height: 1.7;
        }

        .right {
            width: 55%;
            padding: 60px 70px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .right h2 {
            font-family: 'Playfair Display', serif;
            font-size: 34px;
            font-weight: 600;
            color: #0f2238;
            line-height: 1.3;
            margin-bottom: 6px;
        }

        .subtitle {
            font-family: 'Poppins', sans-serif;
            font-size: 13.5px;
            color: #9aa7b8;
            line-height: 1.5;
            margin-bottom: 26px;
            max-width: 360px;
            margin-left: auto;
            margin-right: auto;
            letter-spacing: 0.2px;

        }

        .input-group:first-of-type {
            margin-top: 10px;
        }

        .input-group {
            margin-bottom: 22px;
        }

        .input-group label {
            font-size: 12px;
            color: #6b7c93;
            margin-bottom: 6px;
            display: block;
        }

        .input-group input {
            width: 100%;
            padding: 14px 18px;
            border-radius: 30px;
            background: #f9fbfd;
            border: 1px solid #e6edf5;
            font-size: 14px;
            transition: 0.2s;
        }

        .input-group input:focus {
            outline: none;
            border-color: #1e3a5f;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(30, 58, 95, 0.1);
        }

        button {
            margin-top: 18px;
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 30px;
            background: #efc863;
            color: #0f2238;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: 0.25s;
        }

        button:hover {
            background: #d4a52f;
            transform: translateY(-1px);
        }

        .forgot {
            margin-top: 18px;
            text-align: center;
        }

        .forgot a {
            font-size: 13px;
            color: #7a8ca3;
            text-decoration: none;
        }

        .forgot a:hover {
            color: #1e3a5f;
        }

        @media(max-width:1000px) {
            .container {
                flex-direction: column;
                height: auto;
                width: 90%;
            }

            .left {
                height: 250px;
                width: 100%;
            }

            .right {
                width: 100%;
                padding: 40px;
            }
        }
    </style>
</head>

<body>

    <div class="container">

        <div class="left">
            <div class="left-content">
            </div>
        </div>

        <div class="right">
            <div class="heading">
                <h2>Welcome back to Crisis360</h2>
                <p class="subtitle">Access and continue monitoring in real-time</p>
            </div>
            <form method="POST" action="login.php">
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <div style="position: relative;">
                        <input type="password" name="password" id="passwordInput" placeholder="Enter your password" required>
                        <span onclick="togglePassword()" id="eyeIcon"
                            style="position:absolute; right:18px; top:50%; transform:translateY(-50%); cursor:pointer; color:#9aa7b8; font-size:18px;">
                            👁
                        </span>
                    </div>
                </div>
                <div class="g-recaptcha" data-sitekey="6Le4yf0sAAAAAPlNlJwtppooy9g6LfyVhAeryz7z" style="margin-top:10px;"></div>
                <button>Login</button>
            </form>

            <div class="forgot">
                <a href="forgot_password.php">Forgot password?</a>
            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <?php include('includes/script.php'); ?>
    <?php
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        echo "
    <script>
        Swal.fire({
            icon: '{$flash['icon']}',
            title: '{$flash['title']}',
            text: '{$flash['text']}',
            timer: {$flash['timer']},
            showConfirmButton: " . ($flash['showConfirmButton'] ? 'true' : 'false') . ",
            timerProgressBar: true
        }).then(() => {
            window.location.href = '{$flash['redirect']}';
        });
    </script>
    ";
        unset($_SESSION['flash']);
    }
    ?>
</body>

</html>