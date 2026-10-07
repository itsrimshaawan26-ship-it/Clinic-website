<?php
include "config.php";

$result = mysqli_query(
    $conn,
    "SELECT token_no FROM current_token WHERE id = 1"
);

if (!$result) {
    die("Database Query Error: " . mysqli_error($conn));
}

$row = mysqli_fetch_assoc($result);

$currentToken = $row ? $row['token_no'] : 0;
?>



<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Live Queue |Dr. ZAIN RAZA
    </title>

    <!-- Main CSS -->

    
    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >
     <link rel="stylesheet" href="./css/style.css?v=5">
     <link rel="stylesheet" href="./css/tablet.css?v=5">
     <link rel="stylesheet" href="./css/mobile.css?v=5">
</head>


<body>


<!-- =====================================================
     PREMIUM NAVBAR
===================================================== -->

<header class="navbar">
     <!-- MOBILE MENU ICON -->
    <button class="mobile-menu" id="mobileMenu">
        <i class="fa-solid fa-bars"></i>
    </button>
    <!-- BRAND -->
    <div class="brand">

        <div class="brand-logo">
            <img src="images/logo.png" alt="Dr. Zain Raza Logo">
        </div>

        <div class="brand-text">
            <h2>Dr. ZAIN RAZA</h2>
            <p>Consultant Physician</p>
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

       <a href="queue.php" class="active">
            Live Queue
        </a>

        <a href="booking.php">
            Book Token
        </a>

        <a href="contact.html">
            Contact Us
        </a>

    </nav>
     
       
<!-- NAV BUTTONS -->
<div class="nav-buttons">

   

    <!-- CALL -->
    <a href="tel:+923000000000" class="call-button">
        <i class="fa-solid fa-phone"></i>
    </a>

    <!-- BOOK TOKEN -->
    <a href="booking.php" class="nav-book">
        <i class="fa-regular fa-calendar"></i>
        <span>Book Your Token</span>
    </a>

</div>

</header>

<!-- =====================================
     LIVE QUEUE
===================================== -->

<main class="queue-page">


    <!-- Page heading -->

    <section class="queue-heading">


        <h1>

            Live Queue Status

        </h1>


        <div class="queue-heading-line">

            <span></span>

            <i class="fa-solid fa-heart-pulse"></i>

            <span></span>

        </div>


        <p>

            Real-time updates on today's patient queue

        </p>


    </section>



    <!-- Queue statistics -->

    <section class="queue-stats">


        <!-- Current token -->

        <div class="queue-stat-card current-token-card">


            <div class="queue-stat-icon">

                <i class="fa-solid fa-users"></i>

            </div>


            <div>

                <p>

                    Current Token

                </p>


                <h2 id="currentToken">
            <?php echo $currentToken; ?>
            </h2>

                <span>

                    Now Serving

                </span>

            </div>


        </div>



        <!-- Patients ahead -->

        <div class="queue-stat-card">


            <div class="queue-stat-icon green-icon">

                <i class="fa-solid fa-user-group"></i>

            </div>


            <div>

                <p>

                    Your Turn

                </p>


                <h2 id="patientsAhead">

                    --

                </h2>


                <span>

                    Patients Ahead

                </span>

            </div>


        </div>



        <!-- Waiting time -->

        <div class="queue-stat-card">


            <div class="queue-stat-icon purple-icon">

                <i class="fa-regular fa-clock"></i>

            </div>


            <div>

                <p>

                    Estimated Waiting

                </p>


                <h2 id="waitingTime">

                    --

                </h2>


                <span>

                    Minutes

                </span>

            </div>


        </div>



        <!-- Total tokens -->

        <div class="queue-stat-card">


            <div class="queue-stat-icon orange-icon">

                <i class="fa-regular fa-calendar"></i>

            </div>


            <div>

                <p>

                    Total Tokens Today

                </p>


                <h2 id="totalTokens">

                          0

                        </h2>


                <span>

                    Patients

                </span>

            </div>


        </div>


    </section>



    <!-- Token checker -->

    <section class="queue-check-section">


        <!-- Left -->

        <div class="token-check-card">


            <h2>

                Check Your Token Status

            </h2>


            <p>

                Enter your token number to check your position in the queue.

            </p>


            <div class="token-input-box">


                <i class="fa-regular fa-user"></i>


                <input
                    type="number"
                    id="tokenInput"
                    placeholder="Enter Your Token Number"
                    min="1"
                >


            </div>


            <button
                id="checkTokenButton"
                type="button"
            >

                Check My Status

            </button>


            <div
                class="queue-message"
                id="queueMessage"
            >

                <i class="fa-solid fa-circle-info"></i>


                <p>

                    Enter your token number and click
                    <strong>
                        Check My Status
                    </strong>
                    to view your position and estimated waiting time.

                </p>

            </div>


        </div>



        <!-- Right -->

        <div class="queue-result-card">


            <h2>

                Your Queue Status

            </h2>


            <div
                class="queue-result"
                id="queueResult"
            >


                <div class="queue-result-icon">

                    <i class="fa-regular fa-clipboard"></i>

                </div>


                <h3>

                    Enter your token number to check

                </h3>


                <p>

                    Your live queue status will appear here.

                </p>


            </div>


        </div>


    </section>



    <!-- Benefits -->

    <section class="queue-benefits">


        <div class="queue-benefit">


            <i class="fa-solid fa-shield-heart"></i>


            <div>

                <h3>

                    Real-time Updates

                </h3>


                <p>

                    Get live queue information

                </p>

            </div>


        </div>


        <div class="queue-benefit">


            <i class="fa-solid fa-users"></i>


            <div>

                <h3>

                    Save Your Time

                </h3>


                <p>

                    No need to wait in line

                </p>

            </div>


        </div>


        <div class="queue-benefit">


            <i class="fa-regular fa-clock"></i>


            <div>

                <h3>

                    Better Experience

                </h3>


                <p>

                    Plan your visit easily

                </p>

            </div>


        </div>


        <div class="queue-benefit">


            <i class="fa-solid fa-heart"></i>


            <div>

                <h3>

                    Your Health, Our Priority

                </h3>


                <p>

                    We care for you

                </p>

            </div>


        </div>


    </section>


</main>



<!-- =====================================
     FOOTER
===================================== -->

<footer class="queue-footer">


    <div class="queue-footer-content">


        <div class="queue-footer-help">


            <h3>

                Need Help?

            </h3>


            <p>

                Contact us on WhatsApp

            </p>

        </div>


        <a
            href="https://wa.me/923120000000"
            class="queue-whatsapp"
        >

            <i class="fa-brands fa-whatsapp"></i>

            0312-XXXXXXX

        </a>


        <a
         href="booking.php"
          class="queue-book-button">

            <i class="fa-regular fa-calendar"></i>

            Book Your Token

        </a>


        <div class="queue-socials">


            <p>

                Follow Us

            </p>


            <a href="#">

                <i class="fa-brands fa-facebook-f"></i>

            </a>


            <a href="#">

                <i class="fa-brands fa-whatsapp"></i>

            </a>


            <a href="#">

                <i class="fa-brands fa-instagram"></i>

            </a>


        </div>


    </div>


    <div class="queue-copyright">

        © 2026 Dr. ZAIN RAZA. All Rights Reserved.

    </div>


</footer>



<!-- JavaScript -->

<script>

const tokenInput = document.getElementById("tokenInput");
const checkButton = document.getElementById("checkTokenButton");
const queueResult = document.getElementById("queueResult");
const patientsAhead = document.getElementById("patientsAhead");
const waitingTime = document.getElementById("waitingTime");

let currentToken = <?php echo (int)$currentToken; ?>;
let checkedToken = null;

async function checkTokenStatus(token, showError = true) {
    try {
        const response = await fetch("queue-check.php?token=" + encodeURIComponent(token) + "&time=" + Date.now());
        const data = await response.json();

        if (!data.success) {
            if (showError) {
                queueResult.innerHTML = `<div class="queue-result-icon warning-result">
                <i class="fa-solid fa-circle-exclamation">
                </i>
                </div>
                <h3>Token Not Found</h3>
                <p>This token has not been booked today. Please enter a valid token number.</p>`;
            }
            patientsAhead.textContent = "--";
            waitingTime.textContent = "--";
            return;
        }

        if (typeof data.currentToken !== "undefined") {
            currentToken = Number(data.currentToken);
            document.getElementById("currentToken").textContent = currentToken;
        }

        if (data.status === "passed") {
            patientsAhead.textContent = "0"; waitingTime.textContent = "0";
            queueResult.innerHTML = `<div class="queue-result-icon passed-result"><i class="fa-solid fa-clock"></i></div><h3>Your Token Has Passed</h3><p>Your token has already been called. Please contact the clinic staff for assistance.</p>`;
            return;
        }

        if (data.status === "your_turn") {
            patientsAhead.textContent = "0"; waitingTime.textContent = "0";
            queueResult.innerHTML = `<div class="queue-result-icon turn-result"><i class="fa-solid fa-bell"></i></div><h3>It Is Your Turn!</h3><p>Your token is currently being served. Please proceed to the clinic.</p><div class="queue-result-details"><div class="queue-result-detail"><span>Your Token</span><strong>#${token}</strong></div><div class="queue-result-detail"><span>Status</span><strong>Now Serving</strong></div></div>`;
            return;
        }

        const peopleAhead = Number(data.patientsAhead || 0);
        const waiting = Number(data.waitingTime !== undefined ? data.waitingTime : peopleAhead * 5);
        patientsAhead.textContent = peopleAhead;
        waitingTime.textContent = waiting;

        queueResult.innerHTML = `<div class="queue-result-icon success-result">
        <i class="fa-solid fa-circle-check"></i></div>
        <h3>Your Queue Status</h3><p>Your token has been found successfully. Please keep checking the live queue for updates.</p>
        <div class="queue-result-details"><div class="queue-result-detail">
        <span>Your Token</span>
        <strong>#${token}</strong>
        </div><div class="queue-result-detail">
        <span>Patients Ahead</span><strong>${peopleAhead}</strong></div>
        <div class="queue-result-detail">
        <span>Estimated Wait</span>
        <strong>${waiting} min</strong></div>
        <div class="queue-result-detail">
        <span>Current Token</span><strong>#${currentToken}</strong>
        </div>
         </div>`;

    } catch (error) {
        console.error("Token verification failed:", error);
        if (showError) {
            queueResult.innerHTML = `<div class="queue-result-icon warning-result">
            <i class="fa-solid fa-triangle-exclamation">
            </i>
            </div><h3>Something Went Wrong</h3><p>Unable to verify your token. Please try again.</p>`;
            patientsAhead.textContent = "--"; waitingTime.textContent = "--";
        }
    }
}

async function updateLiveQueue() {
    try {
        const response = await fetch("queue-live.php?time=" + Date.now());
        const data = await response.json();
        if (data.success) {
            currentToken = Number(data.token);
            document.getElementById("currentToken").textContent = currentToken;
            document.getElementById("totalTokens").textContent = data.totalTokens;
            if (checkedToken !== null) await checkTokenStatus(checkedToken, false);
        }
    } catch (error) {
        console.error("Live queue update failed:", error);
    }
}

updateLiveQueue();
setInterval(updateLiveQueue, 3000);

checkButton.addEventListener("click", async function () {
    const enteredValue = tokenInput.value.trim();
    if (enteredValue === "") {
        checkedToken = null;
        queueResult.innerHTML = `<div class="queue-result-icon warning-result">
        <i class="fa-solid fa-circle-exclamation"></i>
        </div>
        <h3>Enter Your Token Number</h3>
        <p>Please enter a valid token number to check your queue status.</p>`;
        patientsAhead.textContent = "--"; waitingTime.textContent = "--"; return;
    }
    const token = Number(enteredValue);
    if (token < 1 || !Number.isInteger(token)) {
        checkedToken = null;
        queueResult.innerHTML = `<div class="queue-result-icon warning-result">
        <i class="fa-solid fa-circle-exclamation">
        </i></div><h3>Invalid Token</h3><p>Please enter a valid token number.</p>`;
        patientsAhead.textContent = "--"; waitingTime.textContent = "--"; return;
    }
    checkedToken = token;
    await checkTokenStatus(token, true);
});

tokenInput.addEventListener("keydown", function(event) { if (event.key === "Enter") checkButton.click(); });

</script>

 <script src="./javascript/script.js"></script>
</body>

</html>