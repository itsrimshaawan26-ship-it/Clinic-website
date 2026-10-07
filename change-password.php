<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include "config.php";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($newPassword !== $confirmPassword) {

        $error = "New passwords do not match.";

    } elseif (strlen($newPassword) < 8) {

        $error = "New password must be at least 8 characters.";

    } elseif ($currentPassword === $newPassword) {

        $error = "New password must be different from current password.";

    } else {

        $adminId = $_SESSION['admin_id'];

        $stmt = $conn->prepare(
            "SELECT password FROM admins WHERE id = ? LIMIT 1"
        );

        $stmt->bind_param("i", $adminId);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $admin = $result->fetch_assoc();

            if (password_verify($currentPassword, $admin['password'])) {

                $newHashedPassword = password_hash(
                    $newPassword,
                    PASSWORD_DEFAULT
                );

                $update = $conn->prepare(
                    "UPDATE admins SET password = ? WHERE id = ?"
                );

                $update->bind_param(
                    "si",
                    $newHashedPassword,
                    $adminId
                );

                if ($update->execute()) {

                    $message = "Password changed successfully.";

                } else {

                    $error = "Something went wrong. Please try again.";
                }

                $update->close();

            } else {

                $error = "Current password is incorrect.";
            }

        } else {

            $error = "Admin account not found.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Change Password | Admin</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    min-height: 100vh;

    display: flex;
    justify-content: center;
    align-items: center;

    background:
    radial-gradient(
        circle at top left,
        #4facfe 0%,
        transparent 30%
    ),
    radial-gradient(
        circle at bottom right,
        #00f2fe 0%,
        transparent 30%
    ),
    linear-gradient(
        135deg,
        #0f172a,
        #1e3a8a,
        #2563eb
    );

    overflow: hidden;
}

.password-box {

    width: 420px;

    background: white;

    padding: 35px;

    border-radius: 20px;

    box-shadow:
        0 15px 40px rgba(0,0,0,0.15);
}

h1 {

    text-align: center;

    color: #0d6efd;

    margin-bottom: 8px;
}

.subtitle {

    text-align: center;

    color: #666;

    margin-bottom: 25px;
}

.input-group {

    margin-bottom: 18px;
}

.input-group label {

    display: block;

    margin-bottom: 7px;

    font-weight: bold;

    color: #333;
}

.input-group input {

    width: 100%;

    padding: 13px;

    border: 1px solid #ddd;

    border-radius: 10px;

    font-size: 15px;

    background: #fff;
}

.input-group input:focus {

    outline: none;

    border-color: #0d6efd;

    box-shadow: 0 0 0 3px rgba(13,110,253,0.10);
}

.btn {

    width: 100%;

    padding: 13px;

    border: none;

    border-radius: 10px;

    background: #0d6efd;

    color: white;

    font-size: 16px;

    cursor: pointer;

    transition: 0.3s;
}

.btn:hover {

    background: #0b5ed7;
}

.success {

    background: #d1e7dd;

    color: #0f5132;

    padding: 10px;

    border-radius: 8px;

    margin-bottom: 15px;

    text-align: center;
}

.error {

    background: #ffe5e5;

    color: #b00020;

    padding: 10px;

    border-radius: 8px;

    margin-bottom: 15px;

    text-align: center;
}

.back {

    display: block;

    text-align: center;

    margin-top: 18px;

    text-decoration: none;

    color: #0d6efd;
}

.back:hover {

    text-decoration: underline;
}

</style>

</head>

<body>

<div class="password-box">

    <h1>Change Password</h1>

    <p class="subtitle">
        Admin Account Security
    </p>

    <?php if ($message): ?>

        <div class="success">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <?php if ($error): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <form method="POST" autocomplete="off">

        <div class="input-group">

            <label>Current Password</label>

            <input
                type="password"
                name="current_password"
                required
                autocomplete="current-password"
                placeholder="Enter current password"
            >

        </div>


        <div class="input-group">

            <label>New Password</label>

            <input
                type="password"
                name="new_password"
                required
                minlength="8"
                autocomplete="new-password"
                placeholder="Enter new password"
            >

        </div>


        <div class="input-group">

            <label>Confirm New Password</label>

            <input
                type="password"
                name="confirm_password"
                required
                minlength="8"
                autocomplete="new-password"
                placeholder="Confirm new password"
            >

        </div>


        <button
            type="submit"
            class="btn"
        >
            Change Password
        </button>

    </form>


    <a
        href="admin.php"
        class="back"
    >
        ← Back to Dashboard
    </a>

</div>

</body>

</html>