<?php

session_start();

/* =====================================
   ADMIN SESSION SECURITY
===================================== */

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: login.php");
    exit();
}

include "config.php";


/* =====================================
   CHECK REQUEST METHOD
===================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin.php");
    exit();
}


/* =====================================
   CHECK CSRF TOKEN
===================================== */

$csrfToken = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $csrfToken)
) {
    die("Invalid security token.");
}


/* =====================================
   VALIDATE PATIENT ID
===================================== */

$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);

if ($id === false || $id === null || $id < 1) {
    header("Location: admin.php");
    exit();
}


/* =====================================
   DELETE PATIENT
===================================== */

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM patients WHERE id = ?"
);

if (!$stmt) {
    die("Database error.");
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);


/* =====================================
   REDIRECT
===================================== */

header("Location: admin.php");
exit();

?>