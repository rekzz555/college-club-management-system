<?php
// feedback.php

// DB connection
$servername = "localhost";
$username = "root";  // default for XAMPP
$password = "";      // default for XAMPP
$dbname = "college_club";

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $message = trim($_POST["message"]);

    // Validate fields
    if (!empty($name) && !empty($email)) {
        $stmt = $conn->prepare("INSERT INTO feedback (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);

        if ($stmt->execute()) {
            echo "<script>alert('Thank you for your feedback!'); window.location.href='index.html';</script>";
        } else {
            echo "<script>alert('Something went wrong. Please try again.'); window.history.back();</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Please fill all required fields.'); window.history.back();</script>";
    }
}

$conn->close();
?>
