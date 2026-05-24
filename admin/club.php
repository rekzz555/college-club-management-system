<!-- admin_club.php -->
<?php
$club = $_GET['club'] ?? '';

if (!$club) {
    echo "Invalid club.";
    exit;
}

// DB connection
$conn = new mysqli("localhost", "root", "", "college_club");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$clubSafe = $conn->real_escape_string($club);

// Fetch the club_id for the given club name
$clubIdQuery = "SELECT club_id FROM club_list WHERE club = '$clubSafe' LIMIT 1";
$clubIdResult = $conn->query($clubIdQuery);
$club_id = $clubIdResult->fetch_assoc()['club_id'];
if (!$club_id) {
    error_log("Error: club_id not found for club: " . $clubSafe);
    echo "<script>alert('Invalid club. Please check the club list.');</script>";
    exit;
}

// Fetch subcategories if applicable
$subcategories = [];
if ($club === 'Dance Club' || $club === 'Sports Club') {
    $subcatQuery = "SELECT DISTINCT subcategory FROM clubs WHERE club_id = '$club_id' AND subcategory IS NOT NULL AND subcategory != ''";
    $subcatResult = $conn->query($subcatQuery);
    while ($row = $subcatResult->fetch_assoc()) {
        $subcategories[] = $row['subcategory'];
    }
}

// Handle form submission for adding a new entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_entry'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $register_no = $conn->real_escape_string($_POST['register_no']);
    $subcategory = isset($_POST['subcategory']) ? $conn->real_escape_string($_POST['subcategory']) : null;
    $experience = isset($_POST['experience']) ? $conn->real_escape_string($_POST['experience']) : null;

    // Debugging: Log the form data
    error_log("Form Data: Name=$name, Email=$email, Phone=$phone, Register No=$register_no, Subcategory=$subcategory, Experience=$experience");

    // Insert the new entry using the club_id and club name
    $insertQuery = "INSERT INTO clubs (club_id, club, name, email, phone, register_no, subcategory, experience) 
                    VALUES ('$club_id', '$clubSafe', '$name', '$email', '$phone', '$register_no', '$subcategory', '$experience')";
    if ($conn->query($insertQuery)) {
        echo "<script>
            openModal('Entry added successfully!');
            setTimeout(function() {
                window.location.href = window.location.href;
            }, 1000); // Reload the page after 1 second
        </script>";
    } else {
        error_log("Error in INSERT query: " . $conn->error); // Log the error
        echo "<script>openModal('Error adding entry: " . $conn->error . "');</script>";
    }
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $deleteId = $conn->real_escape_string($_GET['delete_id']);
    $deleteQuery = "DELETE FROM clubs WHERE id = '$deleteId'";
    if ($conn->query($deleteQuery)) {
        echo "<script>openModal('Entry deleted successfully!');</script>";
    } else {
        echo "<script>openModal('Error deleting entry: " . $conn->error . "');</script>";
    }
}

// Handle delete all request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all'])) {
    $deleteAllQuery = "DELETE FROM clubs WHERE club_id = '$club_id'";
    if ($conn->query($deleteAllQuery)) {
        echo "<script>
            openModal('All entries deleted successfully!');
            setTimeout(function() {
                window.location.href = window.location.href;
            }, 1000); // Reload the page after 1 second
        </script>";
    } else {
        error_log("Error in DELETE ALL query: " . $conn->error); // Log the error
        echo "<script>openModal('Error deleting all entries: " . $conn->error . "');</script>";
    }
}

$sql = "SELECT id, name, email, phone, register_no, subcategory, experience 
        FROM clubs 
        WHERE club_id = '$club_id'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - <?php echo htmlspecialchars($club); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f9f9f9;
        }
        h2 {
            margin-bottom: 10px;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .back-button {
            padding: 9.5px 20px;
            background-color: #555;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .back-button:hover {
            background-color: #007bff;
            color: #fff;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #f0f0f0;
        }
        tr:hover {
            background: #f9f9f9;
        }
        form input, form button {
            margin: 5px 0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        form button {
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        form button:hover {
            background-color: #0056b3;
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 10px;
            text-align: center;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        }
        .input-box {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            color: #333;
            width: 180px;
            transition: border-color 0.3s ease;
            background: #fff;
        }

        .input-box:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.2);
        }

        .input-box,
        select.input-box,
        form input,
        form select {
            color: #333 ;
        }

        select.input-box {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: #fff;
            color: #333;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            width: 180px;
            height: 38px; /* Match input height */
            transition: border-color 0.3s ease;
            box-sizing: border-box;
            line-height: 1.2;
            vertical-align: middle;
        }

        /* Optional: Custom dropdown arrow for select */
        select.input-box {
            background-image: url("data:image/svg+xml;utf8,<svg fill='gray' height='16' viewBox='0 0 20 20' width='16' xmlns='http://www.w3.org/2000/svg'><path d='M7.293 7.293a1 1 0 011.414 0L10 8.586l1.293-1.293a1 1 0 111.414 1.414l-2 2a1 1 0 01-1.414 0l-2-2a1 1 0 010-1.414z'/></svg>");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px 16px;
            padding-right: 35px; /* space for arrow */
        }

        select.input-box:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.2);
        }
    </style>
   
</head>
<body>

    <div class="button-container">
        <!-- Back button -->
        <a class="back-button" href="http://localhost/college_club/admin/clubs.php">&larr; Back</a>

        <!-- Export button -->
        <form method="post" action="http://localhost/college_club/admin/export_club_csv.php" style="margin: 0; display: inline;">
            <input type="hidden" name="club" value="<?php echo htmlspecialchars($club); ?>">
            <button type="submit" class="export-btn">📁 Export to CSV</button>
        </form>
    </div>

    <h2><?php echo htmlspecialchars($club); ?> Registrations <small style="color: #888;"> (<?php echo htmlspecialchars($club_id); ?>)</small> </h2>

    <!-- Add Entry Form -->
   <form method="post" style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
    <input type="text" name="name" placeholder="Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="phone" placeholder="Phone No" required>
    <input type="text" name="register_no" placeholder="Register No" required>

    <?php if ($club === 'Dance Club' || $club === 'Sports Club' || $club === 'Music Club') : ?>
        <select name="subcategory" class="input-box" required>
            <option value="" disabled selected>Select Subcategory</option>
            <?php
            // Fetch subcategories from club_list for the current club
            $subcatOptions = [];
            $subcatListQuery = "SELECT subcategory FROM club_list WHERE club_id = '$club_id' AND subcategory IS NOT NULL AND subcategory != ''";
            $subcatListResult = $conn->query($subcatListQuery);
            // Break each subcategory by comma if needed
            $subcatOptions = [];
            while ($row = $subcatListResult->fetch_assoc()) {
                $subcats = explode(',', $row['subcategory']);
                foreach ($subcats as $subcat) {
                    $trimmed = trim($subcat);
                    if ($trimmed !== '' && !in_array($trimmed, $subcatOptions)) {
                        $subcatOptions[] = $trimmed;
                    }
                }
            }
            while ($row = $subcatListResult->fetch_assoc()) {
                $subcatOptions[] = $row['subcategory'];
            }
            foreach ($subcatOptions as $subcat):
            ?>
                <option value="<?= htmlspecialchars($subcat) ?>"><?= htmlspecialchars($subcat) ?></option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>
    

    <input type="text" name="experience" placeholder="Experience">
    <button type="submit" name="add_entry" class="add-btn">Add Entry</button>
</form>


    <!-- Delete All Button -->
    <form method="post" style="margin-top: 10px; margin-left: 1200px">
        <input type="hidden" name="delete_all" value="1">
        <button type="submit" class="delete-all-btn" onclick="return confirm('Are you sure you want to delete all entries for this club?')">🗑️ Delete All</button>
    </form>

    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Register No</th>
            <?php if ($club === 'Dance Club' || $club === 'Sports Club' || $club === 'Music Club' ): ?>
                <th>Subcategory</th>
            <?php endif; ?>
            <th>Experience</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td><?= htmlspecialchars($row['register_no']) ?></td>
            <?php if ($club === 'Dance Club' || $club === 'Sports Club'|| $club === 'Music Club' ): ?>
                <td><?= htmlspecialchars($row['subcategory']) ?></td>
            <?php endif; ?>
            <td><?= htmlspecialchars($row['experience']) ?></td>
            <td>
                <a href="club.php?club=<?= urlencode($club) ?>&delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this entry?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <!-- Alert Modal -->
    <div id="alertModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <p id="modalMessage"></p>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>

