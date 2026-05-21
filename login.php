<?php
session_start();
require('class/DAL.class.php');
$dal = new DAL();

$baseURL = "http://localhost/senior/usersweb/";

/* =========================
   ALREADY LOGGED IN
========================= */
if (isset($_SESSION['org_id']) && isset($_SESSION['type'])) {

    if ($_SESSION['type'] === 'hospital') {
        header("Location: {$baseURL}hospital_dashboard.php");
        exit;
    }

    if ($_SESSION['type'] === 'police') {
        header("Location: {$baseURL}police_dashboard.php");
        exit;
    }

    if ($_SESSION['type'] === 'municipality') {
        header("Location: {$baseURL}municipality_dashboard.php");
        exit;
    }
}

/* =========================
   LOGIN PROCESS
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic protection (simple)
    $email = $dal->escape($email);
    $sql = "SELECT id, name, password, type FROM organizations WHERE email = '$email'";
    $result = $dal->getdata($sql);

    if ($result && count($result) > 0) {

        $org = $result[0];

        if (password_verify($password, $org['password'])) {

            session_regenerate_id(true);

            $_SESSION['org_id']   = $org['id'];
            $_SESSION['org_name'] = $org['name'];
            $_SESSION['email']    = $email;
            $_SESSION['type']     = $org['type'];
            $_SESSION['logged_in'] = true;

            // ✅ ADD FLASH HERE
            $_SESSION['flash'] = [
                'icon' => 'success',
                'title' => 'Welcome!',
                'text'  => 'Login successful',
                'redirect' => $baseURL . $org['type'] . "_dashboard.php",
                'timer' => 1500,
                'showConfirmButton' => false
            ];
            // Redirect based on type
            if ($org['type'] === 'hospital') {
                header("Location: {$baseURL}hospital_dashboard.php");
                exit;
            }

            if ($org['type'] === 'police') {
                header("Location: {$baseURL}police_dashboard.php");
                exit;
            }

            if ($org['type'] === 'municipality') {
                header("Location: {$baseURL}municipality_dashboard.php");
                exit;
            }
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
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Mrs+Saint+Delafield&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Poppins', sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f0f2f5;
        }

        .container {
            width: 1000px;
            height: 650px;
            display: flex;
            overflow: hidden;
            border-radius: 30px;
            background: #ffffff;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }

        /* Left Side Image Panel */
        .left {
            width: 46%;
            position: relative;
            background: url('uploads/log2.jpeg') center/cover no-repeat;
        }

        .left-content {
            position: absolute;
            top: 60px;
            left: 40px;
            color: white;
            z-index: 2;
        }

        .left-content .script-title {
            font-family: 'Mrs Saint Delafield', cursive;
            font-size: 80px;
            line-height: 1;
            margin-bottom: -15px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }

        .right h2 {
            font-family: 'Playfair Display', serif;
            /* Uses the classy font you like */
            font-size: 38px;
            font-weight: 500;
            /* Medium weight is classier than thick Bold */
            color: #0f172a;
            letter-spacing: -0.5px;
            /* Slight squeeze for a modern look */
            margin-bottom: 12px;
        }

        .divider {
            width: 40px;
            height: 3px;
            background: #a3b18a;
            margin-bottom: 20px;
        }

        .left-content p {
            font-size: 18px;
            font-weight: 300;
            line-height: 1.5;
            letter-spacing: 0.5px;
            opacity: 0.95;
        }

        /* Right Side Form Panel */
        .right {
            width: 54%;
            padding: 60px 80px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .heading h2 {
            font-size: 42px;
            color: #0f172a;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #64748b;
            font-size: 15px;
            font-weight: 400;
            margin-bottom: 45px;
            /* More breathing room before the inputs */
            letter-spacing: 0.2px;
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #334155;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 18px;
            color: #94a3b8;
            font-size: 18px;
            opacity: 0.6;
        }

        .input-group input {
            width: 100%;
            padding: 16px 16px 16px 50px;
            border-radius: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: #386641;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(56, 102, 65, 0.1);
        }

        .forgot-link {
            text-align: right;
            margin-top: -10px;
            margin-bottom: 30px;
        }

        .forgot-link a {
            font-size: 13px;
            color: #386641;
            text-decoration: none;
            font-weight: 500;
        }

        button {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            background: #2d5a27;
            color: white;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #1e3d1a;
        }

        /* Footer Script */
        .footer-script {
            text-align: center;
            margin-top: 20px;
        }

        .footer-script p {
            font-family: 'Mrs Saint Delafield', cursive;
            font-size: 45px;
            color: #386641;
            display: inline-block;
            position: relative;
        }

        .footer-script p::after {
            content: "";
            position: absolute;
            bottom: 5px;
            left: 20%;
            width: 60%;
            height: 1px;
            background: #386641;
            opacity: 0.6;
        }

        @media(max-width:1000px) {
            .container {
                flex-direction: column;
                height: auto;
                width: 95%;
            }

            .left {
                width: 100%;
                height: 200px;
            }

            .right {
                width: 100%;
                padding: 40px 30px;
            }

            .left-content .script-title {
                font-size: 60px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <!-- Left Side -->
        <div class="left">
            <div class="left-content">
                <div class="script-title">South</div>
                <div class="divider"></div>
                <p>In unity, we heal.<br>In service, we rise.</p>
            </div>
        </div>

        <!-- Right Side -->
        <div class="right">
            <div>
                <div class="heading">
                    <h2>Welcome Back</h2>
                    <p class="subtitle">Access your organization dashboard</p>
                </div>

                <form method="POST" action="login.php">
                    <div class="input-group">
                        <label>Email</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" name="email" placeholder="Enter your email" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Password</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" name="password" placeholder="Enter your password" required>
                        </div>
                    </div>

                    <div class="forgot-link">
                        <a href="#">Forgot password?</a>
                    </div>

                    <button type="submit">Login</button>
                </form>
            </div>

            <div class="footer-script">
                <p>For Lebanon. For our future.</p>
            </div>
        </div>
    </div>

    <!-- SweetAlert & Session Logic kept exactly the same -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        echo "<script>
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
        </script>";
        unset($_SESSION['flash']);
    }
    ?>
</body>

</html>