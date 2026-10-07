
<?php

include "config.php";

header("Content-Type: application/json");


/* =====================================
   GET TOKEN
===================================== */

$token = filter_input(
    INPUT_GET,
    "token",
    FILTER_VALIDATE_INT
);

if ($token === false || $token === null || $token < 1) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid token."
    ]);

    exit;
}


/* =====================================
   GET CURRENT TOKEN
===================================== */

$currentQuery = mysqli_query(
    $conn,
    "SELECT token_no
     FROM current_token
     WHERE id = 1
     LIMIT 1"
);

$currentRow = mysqli_fetch_assoc($currentQuery);

$currentToken = $currentRow
    ? (int) $currentRow["token_no"]
    : 0;


/* =====================================
   CHECK PATIENT TOKEN
===================================== */

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, token_no, patient_name, status
     FROM patients
     WHERE token_no = ?
     AND booking_date = CURDATE()
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $token
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$patient = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$patient) {

    echo json_encode([
        "success" => false,
        "message" => "Token Not Found"
    ]);

    mysqli_close($conn);

    exit;
}


/* =====================================
   TOKEN ALREADY PASSED
===================================== */

if ($token <= $currentToken) {

    echo json_encode([
        "success" => true,
        "token" => (int) $patient["token_no"],
        "status" => "passed",
        "patientsAhead" => 0,
        "waitingTime" => 0,
        "currentToken" => $currentToken
    ]);

    mysqli_close($conn);

    exit;
}


/* =====================================
   COUNT ACTUAL PATIENTS AHEAD
===================================== */

$countStmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS patients_ahead
     FROM patients
     WHERE booking_date = CURDATE()
     AND token_no > ?
     AND token_no < ?
     AND status NOT IN ('Completed', 'Cancelled')"
);

mysqli_stmt_bind_param(
    $countStmt,
    "ii",
    $currentToken,
    $token
);

mysqli_stmt_execute($countStmt);

$countResult = mysqli_stmt_get_result($countStmt);

$countRow = mysqli_fetch_assoc($countResult);

$patientsAhead = (int) $countRow["patients_ahead"];

mysqli_stmt_close($countStmt);


/* =====================================
   YOUR TURN
===================================== */

if ($patientsAhead === 0) {

    echo json_encode([
        "success" => true,
        "token" => (int) $patient["token_no"],
        "status" => "your_turn",
        "patientsAhead" => 0,
        "waitingTime" => 0,
        "currentToken" => $currentToken
    ]);

    mysqli_close($conn);

    exit;
}


/* =====================================
   ESTIMATED WAITING TIME
   5 MINUTES PER PATIENT
===================================== */

$waitingTime = $patientsAhead * 5;


/* =====================================
   WAITING
===================================== */

echo json_encode([
    "success" => true,
    "token" => (int) $patient["token_no"],
    "status" => "waiting",
    "patientsAhead" => $patientsAhead,
    "waitingTime" => $waitingTime,
    "currentToken" => $currentToken
]);


mysqli_close($conn);

?>

