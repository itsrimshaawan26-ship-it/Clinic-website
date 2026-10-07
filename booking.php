<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config.php";


/* =====================================
   BOOK TOKEN
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullName = trim($_POST['fullName'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $date     = trim($_POST['date'] ?? '');
    $purpose  = trim($_POST['purpose'] ?? '');


    /* =====================================
       BASIC VALIDATION
    ===================================== */

    if (
        $fullName === '' ||
        $phone === '' ||
        $date === '' ||
        $purpose === ''
    ) {
        die("Please fill all required fields.");
    }


    /* =====================================
       VALIDATE NAME
    ===================================== */

    if (
        mb_strlen($fullName) < 2 ||
        mb_strlen($fullName) > 100
    ) {
        die("Please enter a valid name.");
    }


    /* =====================================
       VALIDATE DATE
    ===================================== */

    $dateObject = DateTime::createFromFormat(
        'Y-m-d',
        $date
    );

    if (
        !$dateObject ||
        $dateObject->format('Y-m-d') !== $date
    ) {
        die("Please select a valid date.");
    }


    /* =====================================
       PREVENT PAST DATES
    ===================================== */

    $today = new DateTime('today');

    if ($dateObject < $today) {
        die("Past dates are not allowed.");
    }


    /* =====================================
       VALIDATE PHONE NUMBER
    ===================================== */

    $phoneClean = preg_replace(
        '/[\s\-]/',
        '',
        $phone
    );

    if (
        !preg_match(
            '/^(03\d{9}|\+923\d{9})$/',
            $phoneClean
        )
    ) {
        die("Please enter a valid Pakistani phone number.");
    }

    $phone = $phoneClean;


    /* =====================================
       VALIDATE EMAIL
    ===================================== */

    if (
        $email !== '' &&
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        die("Please enter a valid email address.");
    }


    /* =====================================
       VALIDATE PURPOSE
    ===================================== */

    $allowedPurposes = [
        "General Consultation",
        "Health Checkup",
        "Diabetes Management",
        "Blood Pressure Care"
    ];

    if (
        !in_array(
            $purpose,
            $allowedPurposes,
            true
        )
    ) {
        die("Please select a valid purpose.");
    }


    /* =====================================
       PREVENT DUPLICATE BOOKING
       Same phone + same date
    ===================================== */

    $duplicateStmt = mysqli_prepare(
        $conn,
        "SELECT id
         FROM patients
         WHERE phone = ?
         AND booking_date = ?
         LIMIT 1"
    );

    if (!$duplicateStmt) {
        die("Database error.");
    }

    mysqli_stmt_bind_param(
        $duplicateStmt,
        "ss",
        $phone,
        $date
    );

    if (!mysqli_stmt_execute($duplicateStmt)) {
        mysqli_stmt_close($duplicateStmt);
        die("Database error.");
    }

    $duplicateResult = mysqli_stmt_get_result(
        $duplicateStmt
    );

    if (
        mysqli_num_rows($duplicateResult) > 0
    ) {

        mysqli_stmt_close($duplicateStmt);

        echo "
        <script>
            alert('You already have a booking for this date.');
            window.location.href = 'booking.php';
        </script>
        ";

        exit;
    }

    mysqli_stmt_close($duplicateStmt);


    /* =====================================
       GENERATE TOKEN + INSERT PATIENT
===================================== */

    /*
     * Create a separate lock for each date.
     *
     * Example:
     * clinic_token_2026-09-13
     * clinic_token_2026-09-14
     *
     * This prevents two people from getting
     * the same token at the same time.
     */

    $lockName = "clinic_token_" . $date;


    /* =====================================
       GET DATE LOCK
    ===================================== */

    $lockStmt = mysqli_prepare(
        $conn,
        "SELECT GET_LOCK(?, 5)"
    );

    if (!$lockStmt) {
        die("Unable to generate token.");
    }

    mysqli_stmt_bind_param(
        $lockStmt,
        "s",
        $lockName
    );

    if (!mysqli_stmt_execute($lockStmt)) {
        mysqli_stmt_close($lockStmt);
        die("Unable to generate token.");
    }

    mysqli_stmt_bind_result(
        $lockStmt,
        $lockResult
    );

    mysqli_stmt_fetch($lockStmt);

    mysqli_stmt_close($lockStmt);


    /* =====================================
       CHECK LOCK
    ===================================== */

    if ((int)$lockResult !== 1) {
        die("Unable to generate token. Please try again.");
    }


    /* =====================================
       START TRANSACTION
    ===================================== */

    mysqli_begin_transaction($conn);


    try {

        /* =====================================
           GET NEXT TOKEN
        ===================================== */

        $tokenStmt = mysqli_prepare(
            $conn,
            "SELECT COALESCE(MAX(token_no), 0) + 1
             FROM patients
             WHERE booking_date = ?"
        );

        if (!$tokenStmt) {
            throw new Exception(
                "Unable to generate token."
            );
        }

        mysqli_stmt_bind_param(
            $tokenStmt,
            "s",
            $date
        );

        if (!mysqli_stmt_execute($tokenStmt)) {

            mysqli_stmt_close($tokenStmt);

            throw new Exception(
                "Unable to generate token."
            );
        }

        mysqli_stmt_bind_result(
            $tokenStmt,
            $token
        );

        mysqli_stmt_fetch($tokenStmt);

        mysqli_stmt_close($tokenStmt);

        $token = (int)$token;


        /* =====================================
           INSERT PATIENT
        ===================================== */

        $sql = "INSERT INTO patients
                (
                    token_no,
                    patient_name,
                    phone,
                    email,
                    purpose,
                    booking_date,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare(
            $conn,
            $sql
        );

        if (!$stmt) {
            throw new Exception(
                "Database error."
            );
        }

        $status = "Waiting";

        mysqli_stmt_bind_param(
            $stmt,
            "issssss",
            $token,
            $fullName,
            $phone,
            $email,
            $purpose,
            $date,
            $status
        );


        /* =====================================
           EXECUTE INSERT
        ===================================== */

        if (!mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);

            throw new Exception(
                "Unable to book token."
            );
        }

        mysqli_stmt_close($stmt);


        /* =====================================
           COMMIT TRANSACTION
        ===================================== */

        mysqli_commit($conn);


        /* =====================================
           RELEASE DATE LOCK
        ===================================== */

        $releaseStmt = mysqli_prepare(
            $conn,
            "SELECT RELEASE_LOCK(?)"
        );

        if ($releaseStmt) {

            mysqli_stmt_bind_param(
                $releaseStmt,
                "s",
                $lockName
            );

            mysqli_stmt_execute(
                $releaseStmt
            );

            mysqli_stmt_close(
                $releaseStmt
            );
        }


        /* =====================================
           BOOKING SUCCESS
        ===================================== */

        session_start();

        $_SESSION['booking_success'] = [
            'token'   => $token,
            'name'    => $fullName,
            'phone'   => $phone,
            'date'    => $date,
            'purpose' => $purpose
        ];


        /* =====================================
           REDIRECT TO CONFIRMATION
        ===================================== */

        header(
            "Location: confirmation.php"
        );

        exit;


    } catch (Exception $e) {


        /* =====================================
           ROLLBACK
        ===================================== */

        mysqli_rollback($conn);


        /* =====================================
           RELEASE DATE LOCK
        ===================================== */

        $releaseStmt = mysqli_prepare(
            $conn,
            "SELECT RELEASE_LOCK(?)"
        );

        if ($releaseStmt) {

            mysqli_stmt_bind_param(
                $releaseStmt,
                "s",
                $lockName
            );

            mysqli_stmt_execute(
                $releaseStmt
            );

            mysqli_stmt_close(
                $releaseStmt
            );
        }


        die(
            "Unable to book token. Please try again."
        );
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Book Your Token</title>


    <!-- CSS -->

    <link
        rel="stylesheet"
        href="./css/style.css?v=5"
    >

    <link
        rel="stylesheet"
        href="./css/test.css?v=5"
    >

    <link
        rel="stylesheet"
        href="./css/mobile.css?v=5"
    >


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

</head>


<body>


<!-- =====================================================
     PREMIUM NAVBAR
===================================================== -->

<header class="navbar">


    <!-- MOBILE MENU ICON -->

    <button
        class="mobile-menu"
        id="mobileMenu"
    >

        <i class="fa-solid fa-bars"></i>

    </button>


    <!-- BRAND -->

    <div class="brand">

        <div class="brand-logo">

            <img
                src="images/logo.png"
                alt="Dr. Zain Raza Logo"
            >

        </div>


        <div class="brand-text">

            <h2>
                Dr. ZAIN RAZA
            </h2>

            <p>
                Consultant Physician
            </p>

        </div>

    </div>


    <!-- NAVIGATION -->

    <nav class="navigation">

        <a href="index.html">
            Home
        </a>

        <a href="about.html">
            About Doctor
        </a>

        <a href="service.html">
            Services
        </a>

        <a href="index.html#how-it-works">
            How It Works
        </a>

        <a href="queue.php">
            Live Queue
        </a>

        <a
            href="booking.php"
            class="active"
        >
            Book Token
        </a>

        <a href="contact.html">
            Contact Us
        </a>

    </nav>


    <!-- NAV BUTTONS -->

    <div class="nav-buttons">


        <!-- CALL -->

        <a
            href="tel:+923000000000"
            class="call-button"
        >

            <i class="fa-solid fa-phone"></i>

        </a>


        <!-- BOOK TOKEN -->

        <a
            href="booking.php"
            class="nav-book"
        >

            <i class="fa-regular fa-calendar"></i>

            <span>
                Book Your Token
            </span>

        </a>

    </div>

</header>



<!-- ================= BOOKING SECTION ================= -->

<section class="hero">

    <div class="main-container">


        <!-- LEFT -->

        <div class="left-side">


            <div class="badge">

                <i class="fa-solid fa-shield-heart"></i>

                Easy Booking. Better Care.

            </div>


            <h1>

                Book Your Token

                <span>
                    Online in Minutes
                </span>

            </h1>


            <p class="description">

                Select your preferred date
                to book your token.
                We'll save your time and help you
                plan your visit better.

            </p>



            <!-- ================= FORM ================= -->

            <form
                action="booking.php"
                method="POST"
                class="booking-form"
            >


                <!-- FULL NAME -->

                <div class="form-group">

                    <label for="fullName">
                        Full Name
                    </label>

                    <div class="input-area">

                        <i class="fa-regular fa-user"></i>

                        <input
                            type="text"
                            id="fullName"
                            name="fullName"
                            placeholder="Enter your full name"
                            maxlength="100"
                            required
                        >

                    </div>

                </div>



                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">
                        Email (Optional)
                    </label>

                    <div class="input-area">

                        <i class="fa-regular fa-envelope"></i>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email"
                            maxlength="150"
                        >

                    </div>

                </div>



                <!-- PHONE -->

                <div class="form-group">

                    <label for="phone">
                        Phone Number
                    </label>

                    <div class="input-area">

                        <i class="fa-solid fa-phone"></i>

                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            placeholder="03XX XXXXXXX"
                            maxlength="15"
                            required
                        >

                    </div>

                </div>



                <!-- DATE -->

                <div class="form-group">

                    <label for="date">
                        Select Date
                    </label>

                    <div class="input-area">

                        <i class="fa-regular fa-calendar"></i>

                        <input
                            type="date"
                            id="date"
                            name="date"
                            min="<?php echo date('Y-m-d'); ?>"
                            required
                        >

                    </div>

                </div>



                <!-- PURPOSE -->

                <div class="form-group">

                    <label for="purpose">
                        Purpose of Visit
                    </label>

                    <div class="input-area">

                        <i class="fa-regular fa-clipboard"></i>

                        <select
                            id="purpose"
                            name="purpose"
                            required
                        >

                            <option value="">
                                Select Purpose
                            </option>

                            <option value="General Consultation">
                                General Consultation
                            </option>

                            <option value="Health Checkup">
                                Health Checkup
                            </option>

                            <option value="Diabetes Management">
                                Diabetes Management
                            </option>

                            <option value="Blood Pressure Care">
                                Blood Pressure Care
                            </option>

                        </select>

                    </div>

                </div>



                <!-- BUTTON -->

                <div class="button-box">

                    <button
                        type="submit"
                        name="submit"
                        id="nextButton"
                    >

                        Book Token

                        <i class="fa-solid fa-arrow-right"></i>

                    </button>

                </div>

            </form>

        </div>



        <!-- RIGHT -->

        <div class="right-side">


            <!-- SECURITY -->

            <div class="side-card security-card">

                <i class="fa-solid fa-shield-halved security-icon"></i>

                <div>

                    <h3>

                        Your Information
                        <br>
                        is Safe

                    </h3>

                    <p>

                        We value your privacy.
                        Your data is secure with us.

                    </p>

                </div>

            </div>



            <!-- WHY BOOK -->

            <div class="side-card why-card">

                <h3>
                    Why Book Online?
                </h3>

                <p>

                    <i class="fa-solid fa-circle-check"></i>

                    Avoid Long Queues

                </p>

                <p>

                    <i class="fa-solid fa-circle-check"></i>

                    Get Your Token Instantly

                </p>

                <p>

                    <i class="fa-solid fa-circle-check"></i>

                    Check Live Queue

                </p>

                <p>

                    <i class="fa-solid fa-circle-check"></i>

                    Better Experience

                </p>

            </div>



            <!-- HELP -->

            <div class="side-card help-card">

                <i class="fa-solid fa-headphones help-icon"></i>

                <div>

                    <h3>
                        Need Help?
                    </h3>

                    <p class="whatsapp">

                        <i class="fa-brands fa-whatsapp"></i>

                        Chat on WhatsApp

                    </p>

                    <p>
                        We're here to assist you
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>



<!-- ================= PROCESS ================= -->

<section class="process-section">

    <div class="process-box">


        <!-- STEP 1 -->

        <div class="process-item">

            <div class="process-icon">

                <i class="fa-regular fa-calendar"></i>

            </div>

            <div>

                <h4>
                    Choose Date
                </h4>

                <p>
                    Pick your preferred date
                </p>

            </div>

        </div>


        <i class="fa-solid fa-chevron-right arrow"></i>


        <!-- STEP 2 -->

        <div class="process-item">

            <div class="process-icon">

                <i class="fa-solid fa-ticket"></i>

            </div>

            <div>

                <h4>
                    Book Your Token
                </h4>

                <p>
                    Fill in your details quickly
                </p>

            </div>

        </div>


        <i class="fa-solid fa-chevron-right arrow"></i>


        <!-- STEP 3 -->

        <div class="process-item">

            <div class="process-icon">

                <i class="fa-regular fa-thumbs-up"></i>

            </div>

            <div>

                <h4>
                    Get Confirmation
                </h4>

                <p>
                    Receive your token instantly
                </p>

            </div>

        </div>


        <i class="fa-solid fa-chevron-right arrow"></i>


        <!-- STEP 4 -->

        <div class="process-item">

            <div class="process-icon">

                <i class="fa-solid fa-hospital"></i>

            </div>

            <div>

                <h4>
                    Visit the Clinic
                </h4>

                <p>
                    Check the live queue
                </p>

            </div>

        </div>

    </div>

</section>



<!-- ================= PREMIUM FOOTER ================= -->

<footer class="premium-footer">

    <div class="footer-container">


        <!-- BRAND -->

        <div class="footer-brand">

            <div class="footer-logo">

                <div class="footer-logo-icon">

                    <i class="fas fa-heartbeat"></i>

                </div>

                <div>

                    <h2>
                        Dr. ZAIN RAZA
                    </h2>

                    <span>
                        Consultant Physician
                    </span>

                </div>

            </div>


            <p class="footer-description">

                Trusted medical care with compassion,
                experience and a patient-first approach.

            </p>


            <div class="social-links">

                <a
                    href="#"
                    aria-label="Facebook"
                >

                    <i class="fab fa-facebook-f"></i>

                </a>

                <a
                    href="#"
                    aria-label="Instagram"
                >

                    <i class="fab fa-instagram"></i>

                </a>

                <a
                    href="#"
                    aria-label="YouTube"
                >

                    <i class="fab fa-youtube"></i>

                </a>

            </div>

        </div>



        <!-- QUICK LINKS -->

        <div class="footer-column">

            <h3>
                Quick Links
            </h3>

            <a href="index.html">
                Home
            </a>

            <a href="about.html">
                About Doctor
            </a>

            <a href="service.html">
                Services
            </a>

            <a href="booking.php">
                Book Token
            </a>

            <a href="contact.html">
                Contact
            </a>

        </div>



        <!-- SERVICES -->

        <div class="footer-column">

            <h3>
                Our Services
            </h3>

            <a href="service.html">
                General Consultation
            </a>

            <a href="service.html">
                Health Checkup
            </a>

            <a href="service.html">
                Diabetes Management
            </a>

            <a href="service.html">
                Blood Pressure Care
            </a>

            <a href="service.html">
                Medical Consultation
            </a>

        </div>



        <!-- CONTACT / HOURS -->

        <div class="footer-column footer-contact">

            <h3>
                Clinic Information
            </h3>


            <div class="footer-info">

                <i class="fas fa-map-marker-alt"></i>

                <div>

                    <strong>
                        Location
                    </strong>

                    <span>
                        Chakwal, Punjab, Pakistan
                    </span>

                </div>

            </div>


            <div class="footer-info">

                <i class="fas fa-clock"></i>

                <div>

                    <strong>
                        Opening Hours
                    </strong>

                    <span>
                        Mon - Sat: 09:00 AM - 09:00 PM
                    </span>

                    <span class="closed">
                        Sunday: Closed
                    </span>

                </div>

            </div>


            <a
                href="booking.php"
                class="footer-book-btn"
            >

                <i class="fas fa-calendar-check"></i>

                Book Your Token

            </a>

        </div>

    </div>



    <!-- FOOTER BOTTOM -->

    <div class="footer-bottom">

        <p>

            © 2026 Dr. ZAIN RAZA.
            All Rights Reserved.

        </p>


        <div class="footer-bottom-links">

            <a href="privacy.html">
                Privacy Policy
            </a>

            <a href="terms.html">
                Terms & Conditions
            </a>

        </div>

    </div>

</footer>



<script src="./javascript/script.js"></script>

</body>

</html>

