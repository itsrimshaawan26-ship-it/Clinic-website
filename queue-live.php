<?php

include "config.php";


/* =====================================
   RESET QUEUE FOR NEW DAY
===================================== */

mysqli_query(
    $conn,
    "UPDATE current_token
     SET token_no = 0,
         queue_date = CURDATE()
     WHERE id = 1
     AND queue_date <> CURDATE()"
);


/* =====================================
   CURRENT TOKEN
===================================== */

$currentResult = mysqli_query(
    $conn,
    "SELECT token_no
     FROM current_token
     WHERE id = 1"
);

$currentRow = mysqli_fetch_assoc($currentResult);

$currentToken = (int) ($currentRow['token_no'] ?? 0);


/* =====================================
   TOTAL TOKENS TODAY
===================================== */

$totalResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM patients
     WHERE booking_date = CURDATE()"
);

$totalRow = mysqli_fetch_assoc($totalResult);

$totalTokens = (int) ($totalRow['total'] ?? 0);


/* =====================================
   SEND JSON DATA
===================================== */

echo json_encode([
    "success" => true,
    "token" => $currentToken,
    "totalTokens" => $totalTokens
]);


mysqli_close($conn);

?>