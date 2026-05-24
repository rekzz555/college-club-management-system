<?php
$conn = mysqli_connect("localhost", "root", "", "college_club");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$selectedEvent = isset($_GET['events']) ? $_GET['events'] : null;
$alertMessage = ""; // Variable to store alert messages

if (isset($_GET['delete_id'])) {
    // Securely extract the delete_id parameter
    $deleteId = $conn->real_escape_string($_GET['delete_id']);
    
    // SQL DELETE query to remove the specific row
    $deleteQuery = "DELETE FROM events WHERE id = ?";
    
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $deleteId);
    
    if ($stmt->execute()) {
        $alertMessage = "Entry deleted successfully!"; // Show success message
    } else {
        $alertMessage = "Error deleting entry: " . $conn->error; // Show the error
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_entry'])) {
    $sub_event = $conn->real_escape_string($_POST['sub_event']);
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $register_no = $conn->real_escape_string($_POST['register_no']);

    // Insert the new entry into the events table
    $insertQuery = "INSERT INTO events (event_name, sub_events, name, email, phoneno, register_no) 
                    VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ssssss", $selectedEvent, $sub_event, $name, $email, $phone, $register_no);

    if ($stmt->execute()) {
        $alertMessage = "Entry added successfully!";
    } else {
        $alertMessage = "Error adding entry: " . $conn->error;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all'])) {
    if ($selectedEvent) {
        $deleteAllQuery = "DELETE FROM events WHERE event_name = ?";
        $stmt = $conn->prepare($deleteAllQuery);
        $stmt->bind_param("s", $selectedEvent);

        if ($stmt->execute()) {
            $alertMessage = "All registrations for \"$selectedEvent\" have been deleted successfully!";
        } else {
            $alertMessage = "Error deleting registrations: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - Events</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background-color: #f9f9f9;
        }
        h2 {
            margin-bottom: 20px;
        }
        .event-box {
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 20px;
            background-color: #fff;
            margin-bottom: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: 0.3s ease;
        }
        .event-box:hover {
            background-color: #f0f8ff;
        }
        .button-container {
            display: flex;
            justify-content: space-between; /* Align buttons to opposite sides */
            align-items: center;
            margin-bottom: 20px; /* Add space below the buttons */
            margin-top: -30px; /* Move the buttons further up */
        }
        .back-button {
            padding: 9.5px 20px;
            background-color: #555;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
            margin-left: -20px; /* Move the Back button slightly to the left */
        }
        .back-button:hover {
            background-color: #007bff;
            color: #fff;
        }
        .add-btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .add-btn:hover {
            background-color: #0056b3;
        }
        .delete-all-btn {
            padding: 10px 20px;
            background-color: #dc3545;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .delete-all-btn:hover {
            background-color: #c82333;
        }
        .export-btn {
            padding: 10px 20px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .export-btn:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 30px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        .no-data {
            font-style: italic;
            color: #666;
            margin-bottom: 20px;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
            font-size: 16px;
        }
        .input-box {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 200px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .input-box:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        select.input-box {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 220px; /* Slightly wider for dropdown */
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        select.input-box:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
    </style>
</head>
<body>

<div class="button-container">
    <!-- Back button -->
    <a class="back-button" href="http://localhost/college_club/admin/dashboard.html">&larr; Back</a>

    <?php if ($selectedEvent): ?>
        <!-- Export button -->
        <form method="post" action="http://localhost/college_club/admin/export_events_csv.php" style="margin: 0; display: inline;">
            <input type="hidden" name="event" value="<?php echo htmlspecialchars($selectedEvent); ?>">
            <button type="submit" class="export-btn">📁 Export to CSV</button>
        </form>
    <?php endif; ?>
</div>

<?php if (!empty($alertMessage)): ?>
    <div class="alert" id="alert-box">
        <?php echo htmlspecialchars($alertMessage); ?>
    </div>
    <script>
        // Hide the alert box after 3 seconds
        setTimeout(function() {
            var alertBox = document.getElementById('alert-box');
            if (alertBox) {
                alertBox.style.display = 'none';
            }
        }, 3000); // 3000 milliseconds = 3 seconds
    </script>
<?php endif; ?>

<?php if ($selectedEvent): ?>
    <h2>Details for "<?php echo htmlspecialchars($selectedEvent); ?>"</h2>

    <?php
    // Fetch event_id and club_id for the selected event
    $eventDetailsQuery = "SELECT event_id, club_id FROM event_details WHERE event_name = ?";
    $stmt = $conn->prepare($eventDetailsQuery);
    $stmt->bind_param("s", $selectedEvent);
    $stmt->execute();
    $eventDetailsResult = $stmt->get_result();

    if ($eventDetailsRow = $eventDetailsResult->fetch_assoc()) {
        $eventId = htmlspecialchars($eventDetailsRow['event_id']);
        $clubId = htmlspecialchars($eventDetailsRow['club_id']);
        echo "<p><strong>Event ID:</strong> $eventId</p>";
        echo "<p><strong>Club ID:</strong> $clubId</p>";
    } else {
        echo "<p class='no-data'>Event details not found.</p>";
    }
    ?>

    <!-- Add New Entry Form -->
    <form method="post" style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
        
        </select>
        <input type="text" name="name" placeholder="Name" class="input-box" required>
        <input type="email" name="email" placeholder="Email" class="input-box" required>
        <input type="text" name="phone" placeholder="Phone No" class="input-box" required>
        <select name="sub_event" class="input-box" required>
            <option value="" disabled selected>Select Sub-Event</option>
            <?php
            // Fetch the sub_events string for the selected event
            $subEventQuery = "SELECT sub_events FROM event_details WHERE event_name = ?";
            $stmtSub = $conn->prepare($subEventQuery);
            $stmtSub->bind_param("s", $selectedEvent);
            $stmtSub->execute();
            $subEventResult = $stmtSub->get_result();
            $subEventsSet = [];
            while ($subEventRow = $subEventResult->fetch_assoc()) {
                // Split by comma and trim spaces
                $subEvents = array_map('trim', explode(',', $subEventRow['sub_events']));
                foreach ($subEvents as $subEvent) {
                    if ($subEvent !== "" && !in_array($subEvent, $subEventsSet)) {
                        $subEventsSet[] = $subEvent;
                    }
                }
            }
            // Output each unique sub-event as an option
            foreach ($subEventsSet as $subEventName) {
                echo "<option value=\"" . htmlspecialchars($subEventName) . "\">" . htmlspecialchars($subEventName) . "</option>";
            }
            ?>
        <input type="text" name="register_no" placeholder="Register No" class="input-box" required>
        <button type="submit" name="add_entry" class="add-btn">Add Entry</button>
    </form>

    <!-- Delete All Button -->
    <form method="post" style="margin-top: 10px;">
        <input type="hidden" name="delete_all" value="1">
        <button type="submit" class="delete-all-btn" onclick="return confirm('Are you sure you want to delete all registrations for this event?')">🗑️ Delete All</button>
    </form>

    <?php
    // Fetch registrations for the selected event grouped by sub_event
    $registrationsQuery = "SELECT sub_events, id, name, register_no, email, phoneno 
                           FROM events 
                           WHERE event_name = ? 
                           ORDER BY sub_events";
    $stmt = $conn->prepare($registrationsQuery);
    $stmt->bind_param("s", $selectedEvent);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $currentSubEvent = null;

        while ($row = $res->fetch_assoc()) {
            // Debugging: Log the current sub-event
            error_log("Processing sub_event: " . $row['sub_events']);

            // Check if a new sub-event starts
            if ($currentSubEvent !== $row['sub_events']) {
                // Close the previous table if it exists
                if ($currentSubEvent !== null) {
                    echo "</table>";
                }

                // Display the sub-event header
                $currentSubEvent = $row['sub_events'];
                echo "<h3>Sub-Event: " . htmlspecialchars($currentSubEvent) . "</h3>";
                echo "<table border='1' style='width: 100%; margin-bottom: 20px;'>
                        <tr>
                            <th>Name</th>
                            <th>Register No</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>";
            }

            // Display the registration row
            echo "<tr>
                    <td>" . htmlspecialchars($row['name']) . "</td>
                    <td>" . htmlspecialchars($row['register_no']) . "</td>
                    <td>" . htmlspecialchars($row['email']) . "</td>
                    <td>" . htmlspecialchars($row['phoneno']) . "</td>
                    <td>
                        <a href='events.php?events=" . urlencode($selectedEvent) . "&delete_id=" . $row['id'] . "' 
                           onclick='return confirm(\"Are you sure you want to delete this entry?\")'>Delete</a>
                    </td>
                  </tr>";
        }

        // Close the last table
        echo "</table>";
    } else {
        echo "<p class='no-data'>No registrations for this event.</p>";
    }
    ?>
<?php else: ?>
    <h2>All Events</h2>
    <?php
    $eventQuery = "SELECT DISTINCT event_name FROM event_details WHERE event_name IS NOT NULL AND event_name != ''";
    $eventResult = mysqli_query($conn, $eventQuery);

    if (!$eventResult) {
        die("Query failed: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($eventResult) > 0):
        while ($events = mysqli_fetch_assoc($eventResult)):
            $eventName = trim($events['event_name']); // Trim whitespace
            if (!empty($eventName)): // Check if the event name is not empty
    ?>
        <div class="event-box" onclick="location.href='http://localhost/college_club/admin/events.php?events=<?php echo urlencode($eventName); ?>'">
            <?php echo htmlspecialchars($eventName); ?>
        </div>
    <?php
            endif;
        endwhile;
    else:
        echo "<p>No events found in the database.</p>";
    endif;
    ?>
<?php endif; ?>

</body>
</html>
