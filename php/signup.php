<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ['status' => '', 'message' => ''];

$servername = "localhost";
$username = "root";
$password = "";
$database = "college_club";

// DB connection
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    $response['status'] = 'error';
    $response['message'] = "Database connection failed";
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Check required fields
    if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
        $response['status'] = 'error';
        $response['message'] = "All fields are required";
        echo json_encode($response);
        exit;
    }

    // Validate email format and domain
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/@gmail.com$/', $email)) {
    $response['status'] = 'error';
    $response['message'] = "Email must be a valid @gmail.com address";
    echo json_encode($response);
    exit;
}


    // Match passwords
    if ($password !== $confirm_password) {
        $response['status'] = 'error';
        $response['message'] = "Passwords do not match";
        echo json_encode($response);
        exit;
    }

    // Strong password validation
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number    = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);

    if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
        $response['status'] = 'error';
        $response['message'] = "Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.";
        echo json_encode($response);
        exit;
    }

    // Check if email already exists
    $check_email_query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_email_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $response['status'] = 'error';
        $response['message'] = "Email already exists";
        echo json_encode($response);
        exit;
    }

    // Save user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $insert_query = "INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("sss", $fullname, $email, $hashed_password);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = "You've signed up successfully!";
    } else {
        $response['status'] = 'error';
        $response['message'] = "Failed to signup";
    }

    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>








