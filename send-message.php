<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $subject = trim($_POST["subject"]);
    $message = trim($_POST["message"]);


    include "config.php";


    // Save message in database
    $stmt = $conn->prepare(
        "INSERT INTO messages (name, phone, email, subject, message)
         VALUES (?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "sssss",
        $name,
        $phone,
        $email,
        $subject,
        $message
    );


    if ($stmt->execute()) {

        echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Message Sent</title>

            <style>
                body {
                    margin: 0;
                    padding: 80px 20px;
                    font-family: Arial, sans-serif;
                    background: #f5fbfd;
                    text-align: center;
                }

                .success-box {
                    max-width: 500px;
                    margin: auto;
                    padding: 35px;
                    background: white;
                    border-radius: 18px;
                    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
                }

                h2 {
                    color: #0874c8;
                    margin-bottom: 12px;
                }

                p {
                    color: #555;
                }

                a {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 12px 24px;
                    background: #0874c8;
                    color: white;
                    text-decoration: none;
                    border-radius: 7px;
                }
            </style>
        </head>

        <body>

            <div class='success-box'>
                <h2>Message Sent Successfully</h2>
                <p>Thank you! We will contact you soon.</p>

                <a href='contact.php'>
                    Back to Contact Page
                </a>
            </div>

        </body>
        </html>
        ";

    } else {

        echo "Something went wrong. Please try again.";

    }


    $stmt->close();
    $conn->close();


} else {

    header("Location: contact.php");
    exit();

}

?>