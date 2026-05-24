<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['events'])) {
    $event = $_POST['events'];

    // DB connection
    $conn = new mysqli("localhost", "root", "", "college_club");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $eventSafe = $conn->real_escape_string($event);

    // Fetch distinct sub-events for the selected event
    $subEventQuery = "SELECT DISTINCT sub_events FROM events WHERE event_name = '$eventSafe'";
    $subEventResult = $conn->query($subEventQuery);

    if ($subEventResult && $subEventResult->num_rows > 0) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $eventSafe . '_registrations.csv"');

        $output = fopen('php://output', 'w');

        // CSV column headers
        fputcsv($output, ['Sub Event', 'Name', 'Email', 'Phone', 'Register No']);

        // Iterate through each sub-event
        while ($subEventRow = $subEventResult->fetch_assoc()) {
            $subEvent = $subEventRow['sub_events'];

            // Fetch registrations for the current sub-event
            $registrationQuery = "SELECT name, email, phoneno AS phone, register_no FROM events 
                                  WHERE event_name = '$eventSafe' AND sub_events LIKE '%$subEvent%'";
            $registrationResult = $conn->query($registrationQuery);

            if ($registrationResult && $registrationResult->num_rows > 0) {
                // Add a row for the sub-event name
                fputcsv($output, [$subEvent, '', '', '', '']);

                // Add registrations under the sub-event
                while ($row = $registrationResult->fetch_assoc()) {
                    fputcsv($output, ['', $row['name'], $row['email'], $row['phone'], $row['register_no']]);
                }
            } else {
                // Add a row indicating no registrations for this sub-event
                fputcsv($output, [$subEvent, 'No registrations found', '', '', '']);
            }
        }

        fclose($output);
        $conn->close();
        exit;
    } else {
        echo "No sub-events found for this event.";
    }
} else {
    echo "Invalid request.";
}
?>
