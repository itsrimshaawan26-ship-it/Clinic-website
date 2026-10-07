<?php
session_start();

/*
|--------------------------------------------------------------------------
| SAMPLE CONSULTATION HISTORY
|--------------------------------------------------------------------------
| Replace this sample array with your database query later.
*/

$history = [

    [
        'date' => 'Today',
        'date_value' => '2026-10-01',
        'total' => 20,
        'categories' => [
            'Health Checkup' => 6,
            'General Consultation' => 5,
            'Diabetes Management' => 4,
            'Blood Pressure' => 3,
            'Other' => 2
        ]
    ],

    [
        'date' => 'Sep 30, 2026',
        'date_value' => '2026-09-30',
        'total' => 18,
        'categories' => [
            'Health Checkup' => 5,
            'General Consultation' => 4,
            'Diabetes Management' => 5,
            'Blood Pressure' => 2,
            'Other' => 2
        ]
    ],

    [
        'date' => 'Sep 29, 2026',
        'date_value' => '2026-09-29',
        'total' => 15,
        'categories' => [
            'Health Checkup' => 4,
            'General Consultation' => 5,
            'Diabetes Management' => 3,
            'Blood Pressure' => 2,
            'Other' => 1
        ]
    ],

    [
        'date' => 'Sep 28, 2026',
        'date_value' => '2026-09-28',
        'total' => 17,
        'categories' => [
            'Health Checkup' => 5,
            'General Consultation' => 4,
            'Diabetes Management' => 4,
            'Blood Pressure' => 2,
            'Other' => 2
        ]
    ],

    [
        'date' => 'Sep 27, 2026',
        'date_value' => '2026-09-27',
        'total' => 12,
        'categories' => [
            'Health Checkup' => 3,
            'General Consultation' => 4,
            'Diabetes Management' => 2,
            'Blood Pressure' => 2,
            'Other' => 1
        ]
    ]

];

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Consultation History</title>

<style>

/* =========================================================
   RESET
========================================================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f4f7fb;
    color: #17243d;
}

/* =========================================================
   MAIN LAYOUT
========================================================= */

.admin-layout {
    display: flex;
    min-height: 100vh;
}

/* =========================================================
   SIDEBAR
========================================================= */

.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;

    width: 260px;

    padding: 32px 18px;

    background:
        linear-gradient(
            180deg,
            #17479b,
            #2859d8
        );

    color: white;
}

.logo-area {
    display: flex;
    align-items: center;

    gap: 12px;

    padding: 10px 12px;

    margin-bottom: 45px;
}

.logo-icon {
    width: 46px;
    height: 46px;

    border-radius: 13px;

    background: white;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 24px;
}

.logo-text {
    font-size: 20px;
    font-weight: 700;
}

.menu-title {
    font-size: 11px;
    letter-spacing: 1px;

    opacity: .65;

    padding-left: 13px;

    margin-bottom: 14px;
}

.menu {
    list-style: none;
}

.menu li {
    margin-bottom: 6px;
}

.menu a {
    display: flex;
    align-items: center;

    gap: 13px;

    padding: 13px 14px;

    border-radius: 12px;

    color: white;

    text-decoration: none;

    font-size: 14px;
    font-weight: 600;

    transition: .2s;
}

.menu a:hover {
    background: rgba(255,255,255,.12);
}

.menu a.active {
    background: rgba(255,255,255,.18);
}

.menu-icon {
    width: 25px;
    text-align: center;
    font-size: 19px;
}

.logout {
    margin-top: 15px;

    padding-top: 15px;

    border-top:
        1px solid
        rgba(255,255,255,.18);
}

/* =========================================================
   MAIN CONTENT
========================================================= */

.main-content {
    margin-left: 260px;

    width:
        calc(100% - 260px);

    padding: 35px 38px;
}

/* =========================================================
   HEADER
========================================================= */

.page-header {
    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 28px;
}

.page-title h1 {
    font-size: 32px;

    margin-bottom: 6px;

    color: #14213d;
}

.page-title p {
    font-size: 14px;

    color: #728099;
}

.dashboard-btn {
    text-decoration: none;

    background: white;

    color: #285bd5;

    padding: 11px 18px;

    border-radius: 10px;

    font-size: 13px;

    font-weight: 600;

    border: 1px solid #e3e8f1;

    box-shadow:
        0 4px 14px
        rgba(20,50,100,.05);
}

/* =========================================================
   TABLE CARD
========================================================= */

.history-card {
    background: white;

    border:
        1px solid
        #e4e9f1;

    border-radius: 16px;

    box-shadow:
        0 8px 25px
        rgba(25,55,100,.06);

    overflow: hidden;
}

/* =========================================================
   TABLE
========================================================= */

.history-table {
    width: 100%;

    border-collapse: collapse;

    table-layout: fixed;
}

/* Header */

.history-table thead {
    background: #f6f8fc;
}

.history-table th {
    text-align: left;

    padding: 13px 17px;

    font-size: 12px;

    text-transform: uppercase;

    letter-spacing: .5px;

    color: #64728a;

    border-bottom:
        1px solid
        #e5eaf1;
}

.history-table th:first-child {
    width: 18%;
}

.history-table th:nth-child(2) {
    width: 62%;
}

.history-table th:last-child {
    width: 20%;

    text-align: center;
}

/* Body */

.history-table td {
    padding: 13px 17px;

    border-bottom:
        1px solid
        #edf0f5;

    vertical-align: middle;
}

.history-table tbody tr {
    transition: .15s;
}

.history-table tbody tr:hover {
    background: #fafcff;
}

.history-table tbody tr:last-child td {
    border-bottom: none;
}

/* =========================================================
   DATE
========================================================= */

.date-box {
    display: flex;
    align-items: center;

    gap: 10px;
}

.date-icon {
    width: 34px;
    height: 34px;

    flex-shrink: 0;

    border-radius: 9px;

    background: #edf4ff;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 17px;
}

.date-main {
    font-size: 14px;

    font-weight: 700;

    color: #1c2a43;
}

.today-label {
    display: inline-block;

    margin-top: 3px;

    padding: 2px 7px;

    border-radius: 20px;

    background: #dcf8e8;

    color: #168447;

    font-size: 9px;

    font-weight: 700;
}

/* =========================================================
   TOTAL PATIENTS
========================================================= */

.total-number {
    font-size: 15px;

    font-weight: 700;

    color: #205bd3;

    margin-bottom: 5px;
}

.category-list {
    display: flex;

    flex-wrap: wrap;

    gap: 4px 15px;
}

.category-item {
    font-size: 11px;

    color: #697790;

    white-space: nowrap;
}

.category-item strong {
    color: #273651;
}

/* =========================================================
   VIEW DETAILS BUTTON
========================================================= */

.view-btn {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 6px;

    padding: 9px 13px;

    border-radius: 9px;

    background: #2860d9;

    color: white;

    text-decoration: none;

    font-size: 11px;

    font-weight: 600;

    transition: .2s;
}

.view-btn:hover {
    background: #174fc5;

    transform: translateY(-1px);
}

/* =========================================================
   FOOTER INFO
========================================================= */

.table-footer {
    padding: 11px 17px;

    background: #fafbfe;

    border-top:
        1px solid
        #edf0f5;

    color: #78859a;

    font-size: 11px;
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1000px) {

    .sidebar {
        width: 220px;
    }

    .main-content {
        margin-left: 220px;

        width:
            calc(100% - 220px);

        padding: 25px;
    }

    .category-list {
        gap: 3px 10px;
    }

}

/* =========================================================
   TABLET
========================================================= */

@media (max-width: 800px) {

    .sidebar {
        width: 190px;
    }

    .main-content {
        margin-left: 190px;

        width:
            calc(100% - 190px);

        padding: 20px;
    }

    .page-title h1 {
        font-size: 26px;
    }

    .history-table th,
    .history-table td {
        padding: 10px;
    }

    .category-item {
        font-size: 10px;
    }

}

/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 650px) {

    .admin-layout {
        display: block;
    }

    .sidebar {
        position: relative;

        width: 100%;

        min-height: auto;

        padding: 15px;
    }

    .logo-area {
        margin-bottom: 15px;
    }

    .menu-title {
        display: none;
    }

    .menu {
        display: flex;

        flex-wrap: wrap;

        gap: 5px;
    }

    .menu li {
        margin: 0;
    }

    .menu a {
        padding: 9px 10px;

        font-size: 12px;
    }

    .logout {
        margin-top: 0;

        padding-top: 0;

        border-top: none;
    }

    .main-content {
        margin-left: 0;

        width: 100%;

        padding: 18px 12px;
    }

    .page-header {
        align-items: flex-start;

        gap: 10px;
    }

    .page-title h1 {
        font-size: 24px;
    }

    .page-title p {
        font-size: 12px;
    }

    .dashboard-btn {
        font-size: 11px;

        padding: 9px 11px;
    }

    /*
    -------------------------------------------------------
    Mobile table becomes scrollable horizontally
    WITHOUT creating page-wide side scroll.
    -------------------------------------------------------
    */

    .history-card {
        overflow-x: auto;
    }

    .history-table {
        min-width: 650px;
    }

}

</style>

</head>

<body>


<div class="admin-layout">


<!-- =====================================================
     SIDEBAR
===================================================== -->

<aside class="sidebar">

    <div class="logo-area">

        <div class="logo-icon">
            🏥
        </div>

        <div class="logo-text">
            Clinic Admin
        </div>

    </div>


    <div class="menu-title">
        MAIN MENU
    </div>


    <ul class="menu">

        <li>

            <a href="admin.php">

                <span class="menu-icon">
                    🏠
                </span>

                Dashboard

            </a>

        </li>


        <li>

            <a href="history.php"
               class="active">

                <span class="menu-icon">
                    📋
                </span>

                Consultation History

            </a>

        </li>


        <li class="logout">

            <a href="logout.php">

                <span class="menu-icon">
                    🚪
                </span>

                Logout

            </a>

        </li>

    </ul>

</aside>


<!-- =====================================================
     MAIN
===================================================== -->

<main class="main-content">


    <!-- HEADER -->

    <div class="page-header">

        <div class="page-title">

            <h1>
                Consultation History
            </h1>

            <p>
                Daily patient consultation records
            </p>

        </div>


        <a href="admin.php"
           class="dashboard-btn">

            ← Dashboard

        </a>

    </div>


    <!-- =================================================
         TABLE
    ================================================= -->

    <div class="history-card">

        <table class="history-table">


            <thead>

                <tr>

                    <th>
                        Date
                    </th>

                    <th>
                        Total Patients
                    </th>

                    <th>
                        View Details
                    </th>

                </tr>

            </thead>


            <tbody>


            <?php foreach ($history as $index => $day): ?>

                <tr>


                    <!-- DATE -->

                    <td>

                        <div class="date-box">

                            <div class="date-icon">
                                📅
                            </div>

                            <div>

                                <div class="date-main">

                                    <?php
                                    echo htmlspecialchars(
                                        $day['date']
                                    );
                                    ?>

                                </div>


                                <?php if ($index === 0): ?>

                                    <span class="today-label">
                                        TODAY
                                    </span>

                                <?php endif; ?>

                            </div>

                        </div>

                    </td>


                    <!-- PATIENTS -->

                    <td>

                        <div class="total-number">

                            <?php
                            echo $day['total'];
                            ?>
                            Patients

                        </div>


                        <div class="category-list">


                            <?php foreach (
                                $day['categories']
                                as $category => $count
                            ): ?>

                                <span class="category-item">

                                    <?php
                                    echo htmlspecialchars(
                                        $category
                                    );
                                    ?>:

                                    <strong>
                                        <?php
                                        echo $count;
                                        ?>
                                    </strong>

                                </span>

                            <?php endforeach; ?>


                        </div>

                    </td>


                    <!-- BUTTON -->

                    <td style="text-align:center;">

                        <a
                            href="consultation-details.php?date=<?php echo urlencode($day['date_value']); ?>"
                            class="view-btn"
                        >

                            View Details
                            →

                        </a>

                    </td>


                </tr>

            <?php endforeach; ?>


            </tbody>

        </table>


        <div class="table-footer">

            Showing daily consultation history.
            Click <strong>View Details</strong> to see complete patient records.

        </div>

    </div>


</main>

</div>


</body>

</html>