<?php
// session_start();
session_start();
// session_regenerate_id(true);
// echo "<pre>";
// print_r($_SESSION);
// echo "</pre>";
// exit;
require('class/DAL.class.php');
$dal = new DAL();

/* Always correct path */
// $baseURL = "/violet-crm-system/crm/";
$baseURL = "http://localhost/violet-crm-system/crm/";

if (isset($_SESSION['id']) && isset($_SESSION['role'])) {

    if ($_SESSION['role'] === 'admin') {
        header("Location: {$baseURL}admin_dashboard.php");
        exit;
    }

    if ($_SESSION['role'] === 'manager') {
        header("Location: {$baseURL}manager_dashboard.php");
        exit;
    }

    header("Location: {$baseURL}sales_dashboard.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $sql = "SELECT id, name, password, role FROM users WHERE email = '$email'";
    $result = $dal->getdata($sql);

    if ($result && count($result) > 0) {
        $user = $result[0];

        if (password_verify($password, $user['password'])) {

            // Save session values
            session_regenerate_id(true); // regenerate *after* checking credentials
            $_SESSION['id']    = $user['id'];
            $_SESSION['name']  = $user['name'];
            $_SESSION['email'] = $email;
            $_SESSION['role']  = $user['role'];
            $_SESSION['logged_in'] = true;


            // Update online status
            $now = date("Y-m-d H:i:s");
            $dal->execute("UPDATE users SET ustatus='online', last_activity='$now' WHERE id='{$user['id']}'");

            // Redirect by role
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
    <title>CRM Login</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #0A2A43, #1F4E79);
            font-family: Arial, sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-card {
            background: #ffffff10;
            padding: 40px 35px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
            box-shadow: 0 0 20px #00000040;
            width: 350px;
        }
        .login-card h3 {
            color: #F5F5F5;
            text-align: center;
            margin-bottom: 25px;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            background: #ffffff20;
            border: 1px solid #ffffff40;
            color: white;
            margin-bottom: 15px;
            border-radius: 6px;
        }
        .form-control::placeholder {
            color: #cccccc;
        }
        .btn-login {
            width: 100%;
            padding: 10px;
            background-color: #F2C94C;
            border: none;
            color: #0A2A43;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn-login:hover {
            background-color: #e0b442;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h3>CRM Login</h3>

        <!-- POST to the SAME PAGE so the URL NEVER breaks -->
        <form action="login.php" method="POST">
            <input type="text" name="email" class="form-control" placeholder="Email">
            <input type="password" name="password" class="form-control" placeholder="Password">
            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
// Flash message
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
