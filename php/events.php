<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

// DB config
$host = "localhost";
$user = "root";
$password = "";
$database = "college_club";

// Connect to database
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$register_no = $_POST['register_no'] ?? '';
$phoneno = $_POST['phoneno'] ?? '';
$event_name = $_POST['event'] ?? '';
$sub_events = $_POST['selectedSubEvents'] ?? '';

// Validation
if (empty($name) || empty($email) || empty($register_no) || empty($phoneno)) {
    echo "All required fields must be filled.";
    $conn->close();
    exit;
}

// Check for existing registration for the same sub-event under the same event
$checkSql = "SELECT id FROM events WHERE email = ? AND event_name = ? AND sub_events = ?";
$checkStmt = $conn->prepare($checkSql);
if ($checkStmt) {
    $checkStmt->bind_param("sss", $email, $event_name, $sub_events);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo "You have already registered for the sub-event '$sub_events' under the event '$event_name'.";
        $checkStmt->close();
        $conn->close();
        exit;
    }
    $checkStmt->close();
}

// Insert registration
$insertSql = "INSERT INTO events (name, email, register_no, phoneno, event_name, sub_events)
              VALUES (?, ?, ?, ?, ?, ?)";
$insertStmt = $conn->prepare($insertSql);

if ($insertStmt) {
    $insertStmt->bind_param("ssssss", $name, $email, $register_no, $phoneno, $event_name, $sub_events);

    if ($insertStmt->execute()) {
        // Send confirmation email
        $mail = new PHPMailer(true);

        try {
            // SMTP setup
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'Enter your email'; // Replace with your Gmail
            $mail->Password = 'Enter your password'; // Replace with your App password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Email content
            $mail->setFrom('Enter your email', 'College Events Team');
            $mail->addAddress($email, $name);
            $mail->Subject = "Confirmation: Registered for $event_name - $sub_events";
            $mail->Body = "
                <p>Dear $name,</p>
                <p>You have successfully registered for <strong>'$event_name'</strong> under the sub-event <strong>'$sub_events'</strong>.</p>
                <p><strong>Registration Details:</strong></p>
                <ul>
                    <li><strong>Name:</strong> $name</li>
                    <li><strong>Email:</strong> $email</li>
                    <li><strong>Register Number:</strong> $register_no</li>
                    <li><strong>Phone Number:</strong> $phoneno</li>
                    <li><strong>Event Name:</strong> $event_name</li>
                    <li><strong>Sub-Event:</strong> $sub_events</li>
                </ul>
                <p>Thank you for registering! We look forward to your participation.</p>
                <p>Best regards,<br>$event_name Team</p>
            ";
            $mail->isHTML(true); // Set email format to HTML

            if ($mail->send()) {
                echo "Registration successful!";
            } else {
                echo "Registration successful, but email could not be sent.";
            }
        } catch (Exception $e) {
            echo "Registration successful, but email could not be sent. Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Failed to register. Please try again.";
    }

    $insertStmt->close();
} else {
    echo "Failed to prepare the statement.";
}

$conn->close();
?>
