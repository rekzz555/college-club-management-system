<?php
session_start();

// Set header to return JSON
header('Content-Type: application/json');

// Database connection
$conn = new mysqli("localhost", "root", "", "college_club");

// Check connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed!"]);
    exit;
}

// Validate login credentials
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Example: Check credentials in the database
    $query = "SELECT * FROM admins WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_email'] = $admin['email'];

            echo json_encode(['status' => 'success', 'message' => 'Login successful']);
            exit;
        }
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
    exit;
}
?>

