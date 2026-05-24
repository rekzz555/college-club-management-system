<?php
// Run this once to insert admin (optional file: create_admin.php)
$hash = password_hash("admin123", PASSWORD_DEFAULT);

$conn = new mysqli("localhost", "root", "", "college_club");
$conn->query("INSERT INTO admins (email, password) VALUES ('admin@example.com', '$hash')");
echo "Admin added!";
?>
