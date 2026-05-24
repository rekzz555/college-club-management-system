<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "college_club";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form data
$name        = $_POST['name'] ?? '';
$email       = $_POST['email'] ?? '';
$phone       = $_POST['phone'] ?? '';
$registerNo  = $_POST['registerNo'] ?? '';
$experience  = $_POST['experience'] ?? null;
$subcategory = $_POST['options'] ?? '';
$club        = $_POST['club'] ?? '';
$club_id     = $_POST['club_id'] ?? '';

// ✅ Phone validation
if (!preg_match('/^\d{10}$/', $phone)) {
    echo "Invalid phone number.";
    $conn->close();
    exit;
}

// ✅ Check if already registered for this club + subcategory
$checkSql = "SELECT id FROM clubs WHERE email = ? AND club = ? AND subcategory = ? AND club_id = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("ssss", $email, $club, $subcategory, $club_id);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    echo "already_registered: You have already registered for the $club" . (!empty($subcategory) ? " - $subcategory" : "") . ".";
    $checkStmt->close();
    $conn->close();
    exit;
}
$checkStmt->close();

// ✅ Insert data
$sql = "INSERT INTO clubs (name, email, phone, register_no, subcategory, experience, club, club_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ssssssss", $name, $email, $phone, $registerNo, $subcategory, $experience, $club, $club_id);

    if ($stmt->execute()) {
        // ✅ Send confirmation email
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'Enter your email'; // Replace with your Gmail
            $mail->Password   = 'Enter your password'; // Replace with your App password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('Enter your email', 'Club Team');
            $mail->addAddress($email, $name);

            $mail->isHTML(true);
            $mail->Subject = 'Club Registration Confirmation';

            $whatsappLinks = [
                'Dance Club' => 'Enter the whatsapp link',
                'Music Club' => 'Enter the whatsapp link',
                'Sports Club' => 'Enter the whatsapp link',
                'Film Club'  => 'Enter the whatsapp link',
                'Art Club'   => 'Enter the whatsapp link'
            ];
            
            $whatsappLink = $whatsappLinks[$club] ?? '';

            $mailBody = "Hi <b>$name</b>,<br><br>";

            if (!empty($subcategory)) {
                $mailBody .= "You have successfully registered for the <b>$subcategory</b> category under <b>$club</b>.<br><br>";
            } else {
                $mailBody .= "You have successfully registered for the <b>$club</b>.<br><br>";
            }

            $mailBody .= "Click here <a href='$whatsappLink' target='_blank'>
            <span style='color: #25D366; font-weight: bold;'>to join</span></a> the official WhatsApp group.<br><br>";

            $mailBody .= "Regards,<br><b>" . htmlspecialchars($club) . " Team</b>";
            $mail->Body = $mailBody;

            if ($mail->send()) {
                echo "success: You have registered for $club" . (!empty($subcategory) ? " - $subcategory" : "") . ".";
            } else {
                echo "mail_error: {$mail->ErrorInfo}";
            }
        } catch (Exception $e) {
            echo "mail_error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Registration Failed! DB Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Error preparing statement: " . $conn->error;
}

$conn->close();
?>
