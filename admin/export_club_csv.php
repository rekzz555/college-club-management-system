<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['club'])) {
    $club = $_POST['club'];

    // DB connection
    $conn = new mysqli("localhost", "root", "", "college_club");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $clubSafe = $conn->real_escape_string($club);
    $sql = "SELECT name, email, phone, register_no, subcategory, experience FROM clubs WHERE club = '$clubSafe'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $clubSafe . '_registrations.csv"');

        $output = fopen('php://output', 'w');

        // CSV column headers
        fputcsv($output, ['Name', 'Email', 'Phone', 'Register No', 'Subcategory', 'Experience']);

        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    } else {
        echo "No data found for $club.";
    }

    $conn->close();
} else {
    echo "Invalid request.";
}
?>
