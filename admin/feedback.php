<?php

include 'db.php'; // Database connection

// Handle delete request
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    $deleteQuery = "DELETE FROM feedback WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $deleteId);

    if ($stmt->execute()) {
        echo "<script>alert('Feedback deleted successfully!'); window.location.href='feedback.php';</script>";
    } else {
        echo "<script>alert('Error deleting feedback.');</script>";
    }
    $stmt->close();
}

// Handle delete all request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all'])) {
    $deleteAllQuery = "DELETE FROM feedback";
    if ($conn->query($deleteAllQuery)) {
        echo "<script>
            alert('All feedback deleted successfully!');
            window.location.href = 'feedback.php';
        </script>";
    } else {
        error_log("Error in DELETE ALL query: " . $conn->error); // Log the error
        echo "<script>alert('Error deleting all feedback: " . $conn->error . "');</script>";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin - Feedback</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0f2f5;
      margin: 0;
      padding: 20px;
    }
    h2 {
      color: #333;
      text-align: center;
      margin-bottom: 30px;
    }
    .feedback-container {
      max-width: 900px;
      margin: auto;
      background: #fff;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      padding: 15px;
      border-bottom: 1px solid #ccc;
      text-align: left;
    }
    th {
      background: #007BFF;
      color: #fff;
    }
    tr:hover {
      background: #f1f1f1;
    }
    .button-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    .back-btn {
      padding: 9.5px 20px;
      background-color: #555;
      color: #fff;
      text-decoration: none;
      border-radius: 5px;
      font-size: 16px;
      transition: background-color 0.3s ease, color 0.3s ease;
    }
    .back-btn:hover {
      background-color: #007bff;
      color: #fff;
    }
    .delete-btn {
      color: #fff;
      background-color: #dc3545;
      padding: 5px 10px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .delete-btn:hover {
      background-color: #c82333;
    }
  </style>
</head>
<body>
<div class="button-container">
    <!-- Back button -->
    <a class="back-btn" href="http://localhost/college_club/admin/dashboard.html">&larr; Back</a>
</div>

<div class="feedback-container">
  <h2>Student Feedback/Suggestion</h2>

  <!-- Delete All Button -->
  <form method="post" style="margin-bottom: 20px;">
      <input type="hidden" name="delete_all" value="1">
      <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete all feedback?')">🗑️ Delete All</button>
  </form>

  <?php
  // Fetch feedback grouped by event_id
  $query = "SELECT id, event_id, name, email, message, submitted_at 
            FROM feedback 
            ORDER BY event_id, submitted_at DESC";
  $result = mysqli_query($conn, $query);

  if (mysqli_num_rows($result) > 0) {
    $currentEventId = null;

    while ($row = mysqli_fetch_assoc($result)) {
      // Check if a new event starts
      if ($currentEventId !== $row['event_id']) {
        // Close the previous table if it exists
        if ($currentEventId !== null) {
          echo "</table>";
        }

        // Display the event ID as a header
        $currentEventId = $row['event_id'];
        echo "<h3>Event ID: " . htmlspecialchars($currentEventId) . "</h3>";
        echo "<table>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Feedback</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>";
      }

      // Display the feedback row
      echo "<tr>
              <td>" . htmlspecialchars($row['name']) . "</td>
              <td>" . htmlspecialchars($row['email']) . "</td>
              <td>" . nl2br(htmlspecialchars($row['message'])) . "</td>
              <td>" . $row['submitted_at'] . "</td>
              <td>
                <a href='feedback.php?delete_id=" . $row['id'] . "' 
                   class='delete-btn' 
                   onclick='return confirm(\"Are you sure you want to delete this feedback?\")'>Delete</a>
              </td>
            </tr>";
    }

    // Close the last table
    echo "</table>";
  } else {
    echo "<p>No feedback yet.</p>";
  }
  ?>
</div>
</body>
</html>
