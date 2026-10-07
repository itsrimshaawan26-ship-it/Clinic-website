<?php
session_start();

/*
|--------------------------------------------------------------------------
| GET SELECTED DATE
|--------------------------------------------------------------------------
*/

$selectedDate = $_GET['date'] ?? date('Y-m-d');


/*
|--------------------------------------------------------------------------
| FORMAT DATE
|--------------------------------------------------------------------------
*/

$dateObject = DateTime::createFromFormat('Y-m-d', $selectedDate);

if ($dateObject) {
    $displayDate = $dateObject->format('d M Y');
} else {
    $displayDate = date('d M Y');
}


/*
|--------------------------------------------------------------------------
| SAMPLE CONSULTATION DATA
|--------------------------------------------------------------------------
| Later these records can be replaced with your database query.
|--------------------------------------------------------------------------
*/

$consultations = [

    '2026-10-01' => [

        [
            'token' => '001',
            'name' => 'Ali Khan',
            'category' => 'Health Checkup',
            'start' => '10:05 AM',
            'end' => '10:17 AM',
            'duration' => '12 min'
        ],

        [
            'token' => '002',
            'name' => 'Ahmed Raza',
            'category' => 'Diabetes Management',
            'start' => '10:20 AM',
            'end' => '10:35 AM',
            'duration' => '15 min'
        ],

        [
            'token' => '003',
            'name' => 'Sara Ahmed',
            'category' => 'Blood Pressure',
            'start' => '10:40 AM',
            'end' => '10:51 AM',
            'duration' => '11 min'
        ],

        [
            'token' => '004',
            'name' => 'Muhammad Usman',
            'category' => 'General Consultation',
            'start' => '11:00 AM',
            'end' => '11:13 AM',
            'duration' => '13 min'
        ],

        [
            'token' => '005',
            'name' => 'Ayesha Malik',
            'category' => 'Health Checkup',
            'start' => '11:20 AM',
            'end' => '11:31 AM',
            'duration' => '11 min'
        ]

    ],

    '2026-09-30' => [

        [
            'token' => '001',
            'name' => 'Imran Khan',
            'category' => 'Health Checkup',
            'start' => '09:10 AM',
            'end' => '09:22 AM',
            'duration' => '12 min'
        ],

        [
            'token' => '002',
            'name' => 'Naveed Ahmed',
            'category' => 'General Consultation',
            'start' => '09:30 AM',
            'end' => '09:45 AM',
            'duration' => '15 min'
        ],

        [
            'token' => '003',
            'name' => 'Fatima',
            'category' => 'Diabetes Management',
            'start' => '09:50 AM',
            'end' => '10:06 AM',
            'duration' => '16 min'
        ]

    ]

];


/*
|--------------------------------------------------------------------------
| GET RECORDS FOR SELECTED DATE
|--------------------------------------------------------------------------
*/

$records = $consultations[$selectedDate] ?? [];

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>
    Consultation Details - <?php echo htmlspecialchars($displayDate); ?>
</title>


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
   LAYOUT
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

    background:
        rgba(255,255,255,.12);

}


.menu a.active {

    background:
        rgba(255,255,255,.18);

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
   MAIN
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

    margin-bottom: 25px;

}


.page-title h1 {

    font-size: 32px;

    color: #14213d;

    margin-bottom: 6px;

}


.page-title p {

    color: #728099;

    font-size: 14px;

}


.back-btn {

    text-decoration: none;

    background: white;

    color: #285bd5;

    padding: 11px 18px;

    border-radius: 10px;

    font-size: 13px;

    font-weight: 600;

    border:
        1px solid
        #e3e8f1;

}


/* =========================================================
   DATE SUMMARY
========================================================= */

.date-summary {

    background: white;

    border:
        1px solid
        #e4e9f1;

    border-radius: 14px;

    padding: 14px 18px;

    margin-bottom: 15px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    box-shadow:
        0 6px 20px
        rgba(25,55,100,.05);

}


.date-left {

    display: flex;

    align-items: center;

    gap: 12px;

}


.calendar {

    width: 40px;
    height: 40px;

    border-radius: 10px;

    background: #edf4ff;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 20px;

}


.date-label {

    font-size: 11px;

    color: #7a879c;

    margin-bottom: 3px;

}


.date-value {

    font-size: 17px;

    font-weight: 700;

    color: #17243d;

}


.patient-count {

    background: #edf4ff;

    color: #285bd5;

    padding: 7px 12px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 700;

}


/* =========================================================
   TABLE CARD
========================================================= */

.details-card {

    background: white;

    border:
        1px solid
        #e4e9f1;

    border-radius: 15px;

    overflow: hidden;

    box-shadow:
        0 8px 25px
        rgba(25,55,100,.06);

}


/* =========================================================
   TABLE
========================================================= */

.details-table {

    width: 100%;

    border-collapse: collapse;

}


.details-table thead {

    background: #f6f8fc;

}


.details-table th {

    padding: 13px 15px;

    text-align: left;

    font-size: 11px;

    text-transform: uppercase;

    letter-spacing: .4px;

    color: #65738b;

    border-bottom:
        1px solid
        #e5eaf1;

}


.details-table td {

    padding: 13px 15px;

    font-size: 13px;

    border-bottom:
        1px solid
        #edf0f5;

    vertical-align: middle;

}


.details-table tbody tr:hover {

    background: #fafcff;

}


.details-table tbody tr:last-child td {

    border-bottom: none;

}


/* =========================================================
   TOKEN
========================================================= */

.token {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    width: 35px;

    height: 28px;

    background: #edf4ff;

    color: #285bd5;

    border-radius: 7px;

    font-weight: 700;

    font-size: 11px;

}


/* =========================================================
   PATIENT
========================================================= */

.patient-name {

    font-weight: 700;

    color: #1d2b45;

}


.category {

    display: inline-block;

    padding: 5px 9px;

    border-radius: 20px;

    background: #f0f5ff;

    color: #3c5e9d;

    font-size: 10px;

    font-weight: 600;

}


/* =========================================================
   TIME
========================================================= */

.time {

    color: #52617a;

    font-size: 12px;

}


.duration {

    font-weight: 700;

    color: #159447;

}


/* =========================================================
   EMPTY
========================================================= */

.no-records {

    text-align: center;

    padding: 55px 20px;

}


.no-records-icon {

    font-size: 40px;

    margin-bottom: 10px;

}


.no-records h3 {

    font-size: 18px;

    margin-bottom: 5px;

}


.no-records p {

    color: #78859a;

    font-size: 13px;

}


/* =========================================================
   FOOTER
========================================================= */

.table-footer {

    padding: 11px 15px;

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

@media (max-width: 850px) {

    .sidebar {

        width: 210px;

    }

    .main-content {

        margin-left: 210px;

        width:
            calc(100% - 210px);

        padding: 25px;

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


    .back-btn {

        padding: 9px 11px;

        font-size: 11px;

    }


    .date-summary {

        padding: 12px;

    }


    /*
    Prevent whole page horizontal scroll.
    Only table area can scroll if necessary.
    */

    .details-card {

        overflow-x: auto;

    }


    .details-table {

        min-width: 760px;

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
     MAIN CONTENT
===================================================== -->

<main class="main-content">


    <!-- HEADER -->

    <div class="page-header">


        <div class="page-title">

            <h1>
                Consultation Details
            </h1>

            <p>
                Complete consultation records for the selected date
            </p>

        </div>


        <a href="history.php"
           class="back-btn">

            ← Back to History

        </a>


    </div>



    <!-- DATE SUMMARY -->

    <div class="date-summary">


        <div class="date-left">


            <div class="calendar">
                📅
            </div>


            <div>

                <div class="date-label">
                    CONSULTATION DATE
                </div>

                <div class="date-value">

                    <?php
                    echo htmlspecialchars(
                        $displayDate
                    );
                    ?>

                </div>

            </div>


        </div>


        <div class="patient-count">

            <?php
            echo count($records);
            ?>

            Consultations

        </div>


    </div>



    <!-- DETAILS -->

    <div class="details-card">


        <?php if (!empty($records)): ?>


        <table class="details-table">


            <thead>

                <tr>

                    <th>
                        Token
                    </th>

                    <th>
                        Patient Name
                    </th>

                    <th>
                        Consultation Category
                    </th>

                    <th>
                        Start Time
                    </th>

                    <th>
                        End Time
                    </th>

                    <th>
                        Duration
                    </th>

                </tr>

            </thead>


            <tbody>


            <?php foreach ($records as $record): ?>


                <tr>


                    <td>

                        <span class="token">

                            <?php
                            echo htmlspecialchars(
                                $record['token']
                            );
                            ?>

                        </span>

                    </td>


                    <td>

                        <span class="patient-name">

                            <?php
                            echo htmlspecialchars(
                                $record['name']
                            );
                            ?>

                        </span>

                    </td>


                    <td>

                        <span class="category">

                            <?php
                            echo htmlspecialchars(
                                $record['category']
                            );
                            ?>

                        </span>

                    </td>


                    <td>

                        <span class="time">

                            <?php
                            echo htmlspecialchars(
                                $record['start']
                            );
                            ?>

                        </span>

                    </td>


                    <td>

                        <span class="time">

                            <?php
                            echo htmlspecialchars(
                                $record['end']
                            );
                            ?>

                        </span>

                    </td>


                    <td>

                        <span class="duration">

                            <?php
                            echo htmlspecialchars(
                                $record['duration']
                            );
                            ?>

                        </span>

                    </td>


                </tr>


            <?php endforeach; ?>


            </tbody>


        </table>


        <div class="table-footer">

            Showing
            <strong>
                <?php echo count($records); ?>
            </strong>
            completed consultations for
            <?php echo htmlspecialchars($displayDate); ?>.

        </div>


        <?php else: ?>


        <div class="no-records">

            <div class="no-records-icon">
                📋
            </div>

            <h3>
                No Consultation Records
            </h3>

            <p>
                No completed consultations were found for
                <?php echo htmlspecialchars($displayDate); ?>.
            </p>

        </div>


        <?php endif; ?>


    </div>


</main>


</div>


</body>

</html>