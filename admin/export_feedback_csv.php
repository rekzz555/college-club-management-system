<?php
// filepath: c:\xampp\htdocs\college_club\admin\export_feedback_csv.php

include 'db.php'; // Include database connection

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=feedback.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Name', 'Email', 'Feedback', 'Date']); // CSV headers

$query = "SELECT name, email, message, submitted_at FROM feedback ORDER BY submitted_at DESC";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [$row['name'], $row['email'], $row['message'], $row['submitted_at']]);
    }
}

fclose($output);
exit;
?>