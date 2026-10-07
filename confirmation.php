<?php

session_start();

/*
|--------------------------------------------------------------------------
| Check Booking Session
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['booking_success'])) {
    header("Location: booking.php");
    exit;
}

$booking = $_SESSION['booking_success'];

/*
|--------------------------------------------------------------------------
| Get Booking Data
|--------------------------------------------------------------------------
*/

$token   = $booking['token'] ?? '';
$name    = $booking['name'] ?? '';
$phone   = $booking['phone'] ?? '';
$date    = $booking['date'] ?? '';
$purpose = $booking['purpose'] ?? '';

/*
|--------------------------------------------------------------------------
| Clear Session
|--------------------------------------------------------------------------
*/

unset($_SESSION['booking_success']);

/*
|--------------------------------------------------------------------------
| Safe Output
|--------------------------------------------------------------------------
*/

$token   = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
$name    = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$phone   = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
$purpose = htmlspecialchars($purpose, ENT_QUOTES, 'UTF-8');

/*
|--------------------------------------------------------------------------
| Format Date
|--------------------------------------------------------------------------
*/

$formattedDate = date("d M Y", strtotime($date));

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Booking Confirmed | Dr. Zain Raza</title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f8fb;
            color: #222;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }

        .confirmation-container {
            width: 100%;
            max-width: 650px;
        }

        .confirmation-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);
        }

        .success-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;

            background: #e8f8ef;
            color: #22a06b;

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 38px;
        }

        h1 {
            font-size: 30px;
            margin-bottom: 10px;
            color: #173b57;
        }

        .subtitle {
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
        }

        .token-box {
            background: #f1f7fb;
            border: 2px dashed #2b7da8;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .token-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }

        .token-number {
            font-size: 52px;
            font-weight: bold;
            color: #176b87;
        }

        .details {
            text-align: left;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 13px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .detail-label {
            color: #777;
            font-weight: 600;
        }

        .detail-value {
            color: #222;
            text-align: right;
            font-weight: 500;
        }

        .status {
            display: inline-block;
            padding: 6px 13px;
            border-radius: 20px;
            background: #fff4d6;
            color: #9a6a00;
            font-size: 13px;
            font-weight: bold;
        }

        .buttons {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            text-decoration: none;
            padding: 13px 18px;
            border-radius: 9px;
            font-weight: bold;
            transition: 0.3s;
        }

        
         

            .btn-primary {
            background: #007bff;
            color: white;
        }

           .btn-primary:hover {
             background: #0056b3;
        }

        .btn-secondary {
            background: #eef3f6;
            color:  #0056b3;
        }

        .btn-secondary:hover {
            background: #dfe9ee;
        }

        .note {
            margin-top: 25px;
            font-size: 13px;
            color: #777;
            line-height: 1.6;
        }

        @media (max-width: 600px) {

            .confirmation-card {
                padding: 25px 18px;
            }

            h1 {
                font-size: 25px;
            }

            .token-number {
                font-size: 45px;
            }

            .detail-row {
                flex-direction: column;
                gap: 5px;
            }

            .detail-value {
                text-align: left;
            }

            .buttons {
                flex-direction: column;
            }

        }

    </style>

</head>

<body>

<div class="confirmation-container">

    <div class="confirmation-card">

        <div class="success-icon">
            <i class="fa-solid fa-check"></i>
        </div>

        <h1>Booking Confirmed!</h1>

        <p class="subtitle">
            Your appointment token has been successfully booked.
        </p>

        <div class="token-box">

            <div class="token-label">
                YOUR TOKEN NUMBER
            </div>

            <div class="token-number">
                #<?php echo $token; ?>
            </div>

        </div>

        <div class="details">

            <div class="detail-row">

                <span class="detail-label">
                    <i class="fa-solid fa-user"></i>
                    Patient Name
                </span>

                <span class="detail-value">
                    <?php echo $name; ?>
                </span>

            </div>

            <div class="detail-row">

                <span class="detail-label">
                    <i class="fa-solid fa-phone"></i>
                    Phone
                </span>

                <span class="detail-value">
                    <?php echo $phone; ?>
                </span>

            </div>

            <div class="detail-row">

                <span class="detail-label">
                    <i class="fa-solid fa-calendar"></i>
                    Date
                </span>

                <span class="detail-value">
                    <?php echo htmlspecialchars($formattedDate, ENT_QUOTES, 'UTF-8'); ?>
                </span>

            </div>

            <div class="detail-row">

                <span class="detail-label">
                    <i class="fa-solid fa-stethoscope"></i>
                    Purpose
                </span>

                <span class="detail-value">
                    <?php echo $purpose; ?>
                </span>

            </div>

            <div class="detail-row">

                <span class="detail-label">
                    <i class="fa-solid fa-clock"></i>
                    Status
                </span>

                <span class="detail-value">
                    <span class="status">
                        Waiting
                    </span>
                </span>

            </div>

        </div>

        <div class="buttons">

            <a href="queue.php" class="btn btn-primary">
                <i class="fa-solid fa-list-ol"></i>
                View Live Queue
            </a>

            <a href="booking.php" class="btn btn-secondary">
                <i class="fa-solid fa-plus"></i>
                Book Another
            </a>

        </div>

        <p class="note">
            Please keep your token number safe. You can use the Live Queue
            page to check the current token and your position in the queue.
        </p>

    </div>

</div>

</body>
</html>