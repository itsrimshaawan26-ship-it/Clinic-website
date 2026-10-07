<?php

session_start();

/* =====================================
   ADMIN SESSION SECURITY
===================================== */

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: login.php");
    exit();
}

/* =====================================
   PREVENT BROWSER CACHE
===================================== */

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/* =====================================
   DATABASE CONNECTION
===================================== */

include "config.php";

/* =====================================
   CSRF TOKEN
===================================== */

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$search = "";
$selectedDate = $_GET['date'] ?? date('Y-m-d');


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
   FINISH CONSULTATION
===================================== */

if (isset($_POST['finish_consultation'])) {

    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die("Invalid CSRF Token");
    }

    $currentResult = mysqli_query($conn, "SELECT token_no FROM current_token WHERE id = 1");
    $currentRow = mysqli_fetch_assoc($currentResult);
    $currentToken = (int) ($currentRow['token_no'] ?? 0);

    if ($currentToken > 0) {
        $finishStmt = mysqli_prepare($conn, "UPDATE patients
            SET status = 'Completed',
                consultation_end = NOW(),
                consultation_duration = TIMESTAMPDIFF(SECOND, consultation_start, NOW())
            WHERE token_no = ?
            AND booking_date = CURDATE()
            AND status = 'Serving'");
        mysqli_stmt_bind_param($finishStmt, "i", $currentToken);
        mysqli_stmt_execute($finishStmt);
        mysqli_stmt_close($finishStmt);
    }

    header("Location: admin.php");
    exit();
}

/* =====================================
   NEXT PATIENT
===================================== */

if (isset($_POST['next'])) {

    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        die("Invalid CSRF Token");
    }

    /* ---------------------------------
       Get current token
    --------------------------------- */

    $currentResult = mysqli_query(
        $conn,
        "SELECT token_no
         FROM current_token
         WHERE id = 1"
    );

    $currentRow = mysqli_fetch_assoc($currentResult);

    $currentToken = (int) ($currentRow['token_no'] ?? 0);


    /* ---------------------------------
       Find next actual patient
    --------------------------------- */

    $nextStmt = mysqli_prepare(
        $conn,
        "SELECT token_no
         FROM patients
         WHERE booking_date = CURDATE()
         AND token_no > ?
         ORDER BY token_no ASC
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $nextStmt,
        "i",
        $currentToken
    );

    mysqli_stmt_execute($nextStmt);

    $nextResult = mysqli_stmt_get_result($nextStmt);

    $nextRow = mysqli_fetch_assoc($nextResult);

    mysqli_stmt_close($nextStmt);


    /* ---------------------------------
       Next patient exists
    --------------------------------- */

    if ($nextRow) {

        $nextToken = (int) $nextRow['token_no'];


        /* ---------------------------------
           Previous patient → Completed
        --------------------------------- */

        if ($currentToken > 0) {

            $completeStmt = mysqli_prepare(
                $conn,
                "UPDATE patients
                 SET status = 'Completed',
                     consultation_end = NOW(),
                     consultation_duration = TIMESTAMPDIFF(SECOND, consultation_start, NOW())
                 WHERE token_no = ?
                 AND booking_date = CURDATE()"
            );

            mysqli_stmt_bind_param(
                $completeStmt,
                "i",
                $currentToken
            );

            mysqli_stmt_execute($completeStmt);

            mysqli_stmt_close($completeStmt);
        }


        /* ---------------------------------
           Next patient → Serving
        --------------------------------- */

        $serveStmt = mysqli_prepare(
            $conn,
            "UPDATE patients
             SET status = 'Serving',
                 consultation_start = NOW(),
                 consultation_end = NULL,
                 consultation_duration = NULL
             WHERE token_no = ?
             AND booking_date = CURDATE()"
        );

        mysqli_stmt_bind_param(
            $serveStmt,
            "i",
            $nextToken
        );

        mysqli_stmt_execute($serveStmt);

        mysqli_stmt_close($serveStmt);


        /* ---------------------------------
           Update Current Token
        --------------------------------- */

        $updateTokenStmt = mysqli_prepare(
            $conn,
            "UPDATE current_token
             SET token_no = ?,
                 queue_date = CURDATE()
             WHERE id = 1"
        );

        mysqli_stmt_bind_param(
            $updateTokenStmt,
            "i",
            $nextToken
        );

        mysqli_stmt_execute($updateTokenStmt);

        mysqli_stmt_close($updateTokenStmt);
    }


    /* ---------------------------------
       Prevent Form Resubmission
    --------------------------------- */

    header("Location: admin.php");
    exit();
}


/* =====================================
   GET CURRENT TOKEN
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
   SEARCH PATIENTS
===================================== */

if (isset($_GET['search'])) {

    $search = trim($_GET['search']);

    $stmt = mysqli_prepare(
        $conn,
        "SELECT *
         FROM patients
         WHERE booking_date = ?
         AND (
             CAST(token_no AS CHAR) LIKE ?
             OR phone LIKE ?
             OR patient_name LIKE ?
         )
         ORDER BY token_no ASC"
    );

    $searchValue = "%" . $search . "%";

    mysqli_stmt_bind_param(
        $stmt,
        "ssss",
        $selectedDate,
        $searchValue,
        $searchValue,
        $searchValue
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

} else {

    $result = mysqli_query(
        $conn,
        "SELECT *
         FROM patients
         WHERE booking_date = CURDATE()
         ORDER BY token_no ASC"
    );
}


/* =====================================
   TOTAL PATIENTS
===================================== */

$countStmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM patients
     WHERE booking_date = ?"
);

mysqli_stmt_bind_param(
    $countStmt,
    "s",
    $selectedDate
);

mysqli_stmt_execute($countStmt);

$countResult = mysqli_stmt_get_result($countStmt);

$countRow = mysqli_fetch_assoc($countResult);

$totalPatients = (int) ($countRow['total'] ?? 0);

mysqli_stmt_close($countStmt);


/* =====================================
   CONSULTATION CATEGORY GROUPING
   purpose = consultation category
===================================== */

$groupingStmt = mysqli_prepare(
    $conn,
    "SELECT
        purpose,
        COUNT(*) AS total
     FROM patients
     WHERE booking_date = ?
     GROUP BY purpose
     ORDER BY total DESC, purpose ASC"
);

mysqli_stmt_bind_param(
    $groupingStmt,
    "s",
    $selectedDate
);

mysqli_stmt_execute($groupingStmt);

$groupingResult = mysqli_stmt_get_result($groupingStmt);

$groupedConsultations = [];

while ($groupRow = mysqli_fetch_assoc($groupingResult)) {

    $categoryName = trim($groupRow['purpose'] ?? '');

    if ($categoryName === '') {
        $categoryName = 'Other';
    }

    $groupedConsultations[] = [
        'category' => $categoryName,
        'total' => (int)$groupRow['total']
    ];
}

mysqli_stmt_close($groupingStmt);



/* =====================================
   CONTACT MESSAGES
===================================== */

$messagesResult = mysqli_query(
    $conn,
    "SELECT *
     FROM messages
     ORDER BY created_at DESC"
);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>

   <style>
*{box-sizing:border-box}
html,body{width:100%;max-width:100%;overflow-x:hidden}
:root{--primary:#2563eb;--primary2:#4f46e5;--cyan:#06b6d4;--dark:#172554;--muted:#64748b;--bg:#eef5ff;--border:#dbe5f1;--success:#16a34a;--warning:#f59e0b;--pink:#ec4899;--purple:#7c3aed}
body{margin:0;font-family:Inter,'Segoe UI',Arial,sans-serif;background:linear-gradient(135deg,#eef6ff 0%,#f5f3ff 48%,#ecfeff 100%);color:#1e293b}
.topbar{height:76px;background:linear-gradient(90deg,#ffffff 0%,#f0f7ff 55%,#f5f3ff 100%);border-bottom:1px solid #d8e4f3;display:flex;align-items:center;justify-content:space-between;padding:0 34px;position:sticky;top:0;z-index:100;box-shadow:0 5px 22px rgba(37,99,235,.10)}
.topbar h1{margin:0;color:#172554;font-size:22px;font-weight:800}
.menu-wrapper{position:relative}.menu-btn{border:0;background:linear-gradient(135deg,#dbeafe,#ede9fe);color:#1d4ed8;padding:12px 18px;border-radius:12px;font-size:14px;font-weight:800;cursor:pointer;box-shadow:0 4px 12px rgba(37,99,235,.10)}.menu-btn:hover{background:linear-gradient(135deg,#bfdbfe,#ddd6fe);transform:translateY(-1px)}
.menu-dropdown{display:none;position:absolute;right:0;top:48px;width:225px;background:#fff;border:1px solid #dbe5f1;border-radius:14px;padding:8px;box-shadow:0 18px 45px rgba(30,64,175,.18);z-index:1000}.menu-dropdown a{display:block;padding:12px 14px;text-decoration:none;color:#334155;border-radius:9px;font-weight:700;font-size:14px}.menu-dropdown a:hover{background:#eff6ff;color:#1d4ed8}.menu-wrapper:hover .menu-dropdown{display:block}
.page{width:100%;max-width:1450px;margin:auto;padding:30px 34px 50px;overflow:hidden}.page-heading{display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:22px}.page-heading h2{margin:0;color:#172554;font-size:28px;font-weight:850}.page-heading p{margin:6px 0 0;color:#5b6f8f;font-size:14px}.date-label{background:linear-gradient(135deg,#ffffff,#eef6ff);border:1px solid #d7e4f4;padding:10px 14px;border-radius:12px;color:#334155;font-size:13px;font-weight:700;box-shadow:0 4px 12px rgba(37,99,235,.07)}
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-bottom:24px}.card{background:#fff;border:1px solid #dbe5f1;border-radius:18px;padding:20px 22px;box-shadow:0 8px 24px rgba(37,99,235,.09);display:flex;align-items:center;gap:16px;transition:.2s;position:relative;overflow:hidden}.card:after{content:'';position:absolute;right:-25px;top:-35px;width:90px;height:90px;border-radius:50%;background:rgba(255,255,255,.20)}.card:hover{transform:translateY(-3px);box-shadow:0 14px 30px rgba(37,99,235,.14)}.card h3{margin:0 0 5px;color:#526887;font-size:12px;text-transform:uppercase;letter-spacing:.7px}.card h1{margin:0;color:#172554;font-size:29px}.card-icon{width:50px;height:50px;flex:0 0 50px;border-radius:14px;display:grid;place-items:center;font-size:21px}
.stats .card:nth-child(1){background:linear-gradient(135deg,#ffffff 0%,#eff6ff 100%);border-left:5px solid #2563eb}.stats .card:nth-child(1) .card-icon{background:linear-gradient(135deg,#bfdbfe,#dbeafe);color:#1d4ed8}
.stats .card:nth-child(2){background:linear-gradient(135deg,#ffffff 0%,#ecfeff 100%);border-left:5px solid #06b6d4}.stats .card:nth-child(2) .card-icon{background:linear-gradient(135deg,#a5f3fc,#cffafe);color:#0891b2}
.stats .card:nth-child(3){background:linear-gradient(135deg,#ffffff 0%,#f5f3ff 100%);border-left:5px solid #7c3aed}.stats .card:nth-child(3) .card-icon{background:linear-gradient(135deg,#ddd6fe,#ede9fe);color:#7c3aed}.stats .card:nth-child(3) h1{color:#16a34a}
.control-panel{background:linear-gradient(135deg,#ffffff,#f5f8ff);border:1px solid #dbe5f1;border-radius:18px;padding:20px;margin-bottom:24px;box-shadow:0 8px 24px rgba(37,99,235,.08)}.consultation-controls{display:flex;justify-content:center;gap:12px;flex-wrap:wrap}.btn{border:0;color:#fff;padding:13px 26px;border-radius:11px;font-size:14px;font-weight:800;cursor:pointer;background:linear-gradient(135deg,#2563eb,#4f46e5);box-shadow:0 7px 16px rgba(37,99,235,.25);transition:.2s}.btn:hover{transform:translateY(-2px);box-shadow:0 10px 20px rgba(37,99,235,.30)}.finish-btn{background:linear-gradient(135deg,#16a34a,#059669);box-shadow:0 7px 16px rgba(22,163,74,.23)}.finish-btn:hover{background:linear-gradient(135deg,#15803d,#047857)}.finish-btn:disabled{opacity:.45;cursor:not-allowed;transform:none}
.section{background:#fff;border:1px solid #dbe5f1;border-radius:18px;margin-bottom:28px;box-shadow:0 8px 25px rgba(37,99,235,.08);overflow:hidden}.section-header{padding:19px 22px;background:linear-gradient(90deg,#eff6ff,#f5f3ff,#ecfeff);border-bottom:1px solid #dbe5f1;display:flex;justify-content:space-between;align-items:center}.section-header h2{margin:0;color:#172554;font-size:18px;font-weight:850}.section-header span{color:#5b6f8f;font-size:12px;font-weight:600}
.search-box{padding:17px 22px;background:linear-gradient(135deg,#f8fbff,#f7f5ff);border-bottom:1px solid #dbe5f1}.search-box form{display:grid;grid-template-columns:180px minmax(0,1fr) 100px;gap:10px}.search-box input{width:100%;min-width:0;padding:12px 13px;border:1px solid #cbd8ea;border-radius:10px;font-size:14px;background:#fff;outline:none}.search-box input:focus{border-color:#7aa7f7;box-shadow:0 0 0 3px #dbeafe}.search-box button{background:linear-gradient(135deg,#2563eb,#4f46e5);color:#fff;border:0;padding:12px 20px;border-radius:10px;cursor:pointer;font-weight:800;box-shadow:0 5px 13px rgba(37,99,235,.20)}
.table-wrap{width:100%;overflow:hidden}table{width:100%;max-width:100%;border-collapse:collapse;table-layout:fixed}th{background:linear-gradient(135deg,#2563eb,#4f46e5);color:#fff;padding:13px 9px;font-size:10px;text-transform:uppercase;letter-spacing:.35px;text-align:center;border-bottom:1px solid #1d4ed8;white-space:normal;word-break:break-word}td{padding:13px 8px;text-align:center;border-bottom:1px solid #edf2f7;font-size:12px;white-space:normal;overflow-wrap:anywhere;word-break:break-word}tbody tr:nth-child(even){background:#f8fbff}tbody tr:hover{background:#eef6ff}
.status-badge{display:inline-flex;padding:5px 9px;border-radius:999px;font-size:10px;font-weight:800}.status-waiting{background:#fef3c7;color:#92400e}.status-serving{background:#dcfce7;color:#166534;box-shadow:0 0 0 2px #bbf7d0}.status-completed{background:#e0e7ff;color:#3730a3}.delete-btn{background:linear-gradient(135deg,#fee2e2,#fecaca);color:#b91c1c;border:0;padding:7px 10px;border-radius:8px;font-size:11px;font-weight:800;cursor:pointer}.delete-btn:hover{background:#fecaca}.start-time{font-weight:800;color:#2563eb}.end-time{font-weight:800;color:#16a34a}.end-time-live{color:#f59e0b;font-weight:800}.consultation-timer{font-weight:900;font-size:12px}.consultation-timer.live{color:#2563eb;background:#eff6ff;padding:4px 7px;border-radius:6px}.consultation-timer.completed{color:#16a34a;background:#ecfdf5;padding:4px 7px;border-radius:6px}.message-cell{max-width:300px;white-space:normal!important;line-height:1.45;text-align:left!important}
@media(max-width:1100px){.page{padding-left:20px;padding-right:20px}.stats{grid-template-columns:repeat(3,minmax(0,1fr))}.card{padding:17px}.card h1{font-size:25px}th{font-size:9px}td{font-size:11px;padding:10px 5px}}
@media(max-width:900px){.stats{grid-template-columns:1fr}.page{padding:22px 18px}.topbar{padding:0 18px}.page-heading{align-items:flex-start;flex-direction:column;gap:12px}.search-box form{grid-template-columns:1fr}.search-box button{width:100%}.table-wrap{overflow:hidden}table{font-size:11px}.section-header{gap:10px}.section-header span{display:none}}
@media(max-width:600px){.topbar{height:68px}.topbar h1{font-size:16px}.menu-btn{padding:10px 13px}.page{padding:18px 10px}.page-heading h2{font-size:22px}.page-heading p{font-size:13px}.date-label{width:100%;text-align:left}.card{padding:16px}.card h1{font-size:24px}.btn{width:100%}.consultation-controls{flex-direction:column}.section{border-radius:14px}.section-header{padding:15px 16px}.section-header h2{font-size:16px}.search-box{padding:14px}.search-box form{grid-template-columns:1fr}.table-wrap{width:100%;overflow:hidden}table{width:100%;min-width:0;table-layout:fixed}th{font-size:8px;padding:9px 3px}td{font-size:9px;padding:9px 3px}.status-badge{font-size:8px;padding:4px 6px}.delete-btn{font-size:9px;padding:6px 7px}.consultation-timer{font-size:9px}}
@media(max-width:430px){.topbar h1{font-size:14px}.menu-btn{font-size:12px;padding:9px 11px}.page-heading h2{font-size:20px}.card-icon{width:44px;height:44px;flex-basis:44px}.card h3{font-size:10px}.card h1{font-size:22px}th{font-size:7px}td{font-size:8px}.start-time,.end-time,.end-time-live{font-size:8px}}
</style>
</head>

<body>

<div class="topbar">

    <h1>🏥 Clinic Admin Dashboard</h1>

    <div class="menu-wrapper">

        <button class="menu-btn">
            ☰ Menu
        </button>

        <div class="menu-dropdown">

            <a href="admin.php">
                🏠 Dashboard
            </a>

            <a href="history.php">
                📋 Consultation History
            </a>

            <a href="logout.php">
                🚪 Logout
            </a>

        </div>

    </div>

</div>

<main class="page">
<div class="page-heading">
<div><h2>Dashboard Overview</h2><p>Manage patient queue, consultations and daily clinic activity.</p></div>
<div class="date-label">📅 <?php echo date('d M Y'); ?></div>
</div>
<div class="stats">

    <div class="card"><div class="card-icon">🎫</div><div><h3>Current Token</h3>
        <h1><?php echo $currentToken; ?></h1></div></div>

    <div class="card"><div class="card-icon">👥</div><div>
        <h3>Total Patients</h3>
       <h1><?php echo $totalPatients; ?></h1></div></div>

    <div class="card"><div class="card-icon">●</div><div>
        <h3>Status</h3>
        <h1>Live</h1></div></div>

</div>

<div class="control-panel"><form method="POST" class="consultation-controls">

        <input type="hidden" name="csrf_token"
               value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

        <button type="submit" name="next" class="btn">Next Patient</button>

        <button type="submit" name="finish_consultation" class="btn finish-btn"
                <?php echo ($currentToken <= 0 ? 'disabled' : ''); ?>
                onclick="return confirm('Finish the current consultation?');">
            Finish Consultation
        </button>

    </form></div>
<section class="section"><div class="section-header"><h2>Patient Bookings</h2><span>Daily patient queue & consultation status</span></div>
<div class="search-box">
    <form method="GET">

        <input
            type="date"
            name="date"
            value="<?php echo htmlspecialchars($selectedDate); ?>"
        >

        <input
            type="text"
            name="search"
            placeholder="Search by Token, Name or Phone"
            value="<?php echo htmlspecialchars($search); ?>"
        >

        <button type="submit">
            Search
        </button>

    </form>
</div>
<div class="table-wrap"><table>

<tr>
    <th>Token No</th>
    <th>Patient Name</th>
    <th>Phone</th>
    <th>Purpose</th>
    <th>Date</th>
    <th>Booked At</th>
    <th>Status</th>
    <th>Start Time</th>
    <th>End Time</th>
    <th>Consultation Time</th>
    <th>Action</th>
</tr>

<?php
while($row = mysqli_fetch_assoc($result)){
?>

<tr>
    <td><?php echo (int)$row['token_no']; ?></td>

<td>
    <?php echo htmlspecialchars($row['patient_name'], ENT_QUOTES, 'UTF-8'); ?>
</td>

<td>
    <?php echo htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8'); ?>
</td>

<td>
    <?php echo htmlspecialchars($row['purpose'], ENT_QUOTES, 'UTF-8'); ?>
</td>

<td>
    <?php echo htmlspecialchars($row['booking_date'], ENT_QUOTES, 'UTF-8'); ?>
</td>

<td>
    <?php echo htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8'); ?>
</td>

<td>
    <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
        <?php echo htmlspecialchars($row['status']); ?>
    </span>
</td>

<td>
    <?php if (!empty($row['consultation_start'])): ?>
        <span class="start-time"><?php echo date('h:i:s A', strtotime($row['consultation_start'])); ?></span>
    <?php else: ?>--<?php endif; ?>
</td>

<td>
    <?php if (!empty($row['consultation_end'])): ?>
        <span class="end-time"><?php echo date('h:i:s A', strtotime($row['consultation_end'])); ?></span>
    <?php elseif ($row['status'] === 'Serving'): ?>
        <span class="end-time-live">Running...</span>
    <?php else: ?>--<?php endif; ?>
</td>

<td>
    <?php if ($row['status'] === 'Serving' && !empty($row['consultation_start'])): ?>
        <span class="consultation-timer live" data-start="<?php echo strtotime($row['consultation_start']); ?>">00:00:00</span>
    <?php elseif (isset($row['consultation_duration']) && $row['consultation_duration'] !== null): ?>
        <?php $duration=(int)$row['consultation_duration']; $hours=floor($duration/3600); $minutes=floor(($duration%3600)/60); $seconds=$duration%60; ?>
        <span class="consultation-timer completed"><?php echo sprintf('%02d:%02d:%02d',$hours,$minutes,$seconds); ?></span>
    <?php else: ?>--:--:--<?php endif; ?>
</td>

<td>
    <form method="POST" action="delete.php" style="display:inline;"
          onsubmit="return confirm('Are you sure you want to delete this booking?');">

        <input type="hidden" name="id"
               value="<?php echo (int)$row['id']; ?>">

        <input type="hidden" name="csrf_token"
               value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

        <button type="submit" class="delete-btn">
            Delete
        </button>

    </form>
</td>
</tr>

<?php } ?>

</table></div></div></section>
<!-- =====================================
     CONTACT MESSAGES
===================================== -->

<section class="section"><div class="section-header"><h2>Contact Messages</h2><span>Messages received from patients</span></div><div class="table-wrap"><table>

<tr>
    <th>Name</th>
    <th>Phone</th>
    <th>Email</th>
    <th>Subject</th>
    <th>Message</th>
    <th>Received At</th>
</tr>

<?php
if (mysqli_num_rows($messagesResult) > 0) {

    while ($messageRow = mysqli_fetch_assoc($messagesResult)) {
?>

<tr>

    <td>
        <?php echo htmlspecialchars($messageRow['name'], ENT_QUOTES, 'UTF-8'); ?>
    </td>

    <td>
        <?php echo htmlspecialchars($messageRow['phone'], ENT_QUOTES, 'UTF-8'); ?>
    </td>

    <td>
        <?php echo htmlspecialchars($messageRow['email'], ENT_QUOTES, 'UTF-8'); ?>
    </td>

    <td>
        <?php echo htmlspecialchars($messageRow['subject'], ENT_QUOTES, 'UTF-8'); ?>
    </td>

    <td style="max-width:300px;">
        <?php echo htmlspecialchars($messageRow['message'], ENT_QUOTES, 'UTF-8'); ?>
    </td>

    <td>
        <?php echo htmlspecialchars($messageRow['created_at'], ENT_QUOTES, 'UTF-8'); ?>
    </td>

</tr>

<?php
    }

} else {
?>

<tr>
    <td colspan="6">
        No messages found.
    </td>
</tr>

<?php } ?>

</table></div></section></main>

<script>
function updateConsultationTimers(){
    const timers=document.querySelectorAll('.consultation-timer.live');
    const now=Math.floor(Date.now()/1000);
    timers.forEach(function(timer){
        const start=parseInt(timer.dataset.start,10);
        if(!start)return;
        let elapsed=now-start;
        if(elapsed<0)elapsed=0;
        const hours=Math.floor(elapsed/3600);
        const minutes=Math.floor((elapsed%3600)/60);
        const seconds=elapsed%60;
        timer.textContent=String(hours).padStart(2,'0')+':'+String(minutes).padStart(2,'0')+':'+String(seconds).padStart(2,'0');
    });
}
updateConsultationTimers();
setInterval(updateConsultationTimers,1000);
</script>
</body>
</html>