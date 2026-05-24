<?php
session_start(); // ✅ Start session at the top
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$database = "college_club";

// Connect to DB
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    if (empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
        exit;
    }

    // Validate email format and restrict to @gmail.com
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/@gmail\.com$/', $email)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format. Please use a @gmail.com email."]);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare failed"]);
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Email not found"]);
    } else {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {
            $_SESSION['email'] = $user['email']; // ✅ Set session email
            echo json_encode(["status" => "success", "message" => "Login successful!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Incorrect password!"]);
        }
    }

    $stmt->close();
}

$conn->close();
