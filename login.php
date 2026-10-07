
<?php
session_start();

include "config.php";

if (isset($_POST['login'])) {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Get admin from database
    $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $admin = $result->fetch_assoc();

        // Verify hashed password
        if (password_verify($password, $admin['password'])) {

            session_regenerate_id(true);

            $_SESSION['admin'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            header("Location: admin.php");
            exit();

        } else {

            $error = "Invalid Username or Password";

        }

    } else {

        $error = "Invalid Username or Password";

    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Login</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, sans-serif;
}

body{
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:
    radial-gradient(circle at top left,#4facfe 0%,transparent 30%),
    radial-gradient(circle at bottom right,#00f2fe 0%,transparent 30%),
    linear-gradient(135deg,#0f172a,#1e3a8a,#2563eb);
    overflow:hidden;
    position:relative;
}

.login-box{
    width:400px;
    background:#fff;
    padding:40px;
    border-radius:20px;
    box-shadow:0 15px 40px rgba(0,0,0,0.15);
}

.logo{
    text-align:center;
    margin-bottom:25px;
}

.logo h1{
    color:#0d6efd;
    margin-bottom:5px;
}

.logo p{
    color:#666;
}

.input-group{
    margin-bottom:18px;
}

.input-group input{
    width:100%;
    padding:14px;
    border:1px solid #ddd;
    border-radius:10px;
    font-size:15px;
}

.input-group input:focus{
    outline:none;
    border-color:#0d6efd;
}

.login-btn{
    width:100%;
    padding:14px;
    border:none;
    border-radius:10px;
    background:#0d6efd;
    color:white;
    font-size:16px;
    cursor:pointer;
    transition:.3s;
}

.login-btn:hover{
    background:#0b5ed7;
}

.error{
    background:#ffe5e5;
    color:red;
    padding:10px;
    border-radius:8px;
    margin-bottom:15px;
    text-align:center;
}

</style>

</head>

<body>

<div class="login-box">

    <div class="logo">
        <h1>Dr. Zain Raza</h1>
        <p>Admin Panel Login</p>
    </div>

    <?php
    if(isset($error)){
        echo "<div class='error'>" . htmlspecialchars($error) . "</div>";
    }
    ?>

    <form method="POST">

        <div class="input-group">
            <input
                type="text"
                name="username"
                placeholder="Username"
                required
                autocomplete="username"
            >
        </div>

        <div class="input-group">
            <input
                type="password"
                name="password"
                placeholder="Password"
                required
                autocomplete="current-password"
            >
        </div>

        <button
            type="submit"
            name="login"
            class="login-btn"
        >
            Login
        </button>

    </form>

</div>

</body>
</html>

