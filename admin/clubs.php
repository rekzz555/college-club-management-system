<!-- admin_clubs.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Clubs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            padding: 20px;
        }
        .button-container {
            display: flex;
            justify-content: space-between; /* Align buttons to opposite sides */
            align-items: center;
            margin-bottom: 20px;
        }
        .back-button {
            padding: 10px 20px;
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
        .club-card {
            background: white;
            padding: 20px;
            margin: 15px 0;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        .club-card:hover {
            transform: scale(1.02);
        }
        .club-link {
            text-decoration: none;
            color: #333;
            font-weight: bold;
            font-size: 18px;
        }
    </style>
</head>
<body>

    <a class="back-button" href="http://localhost/college_club/admin/dashboard.html">&larr; Back </a>
    <h2>📋 Club Registrations</h2>

    <?php
    $clubs = ['Dance Club', 'Music Club', 'Film Club', 'Art Club', 'Sports Club', 'Commerce Club', 'Writers Club'];
    foreach ($clubs as $club) {
        $encodedClub = urlencode($club);
        echo "
        <div class='club-card'>
            <a class='club-link' href='http://localhost/college_club/admin/club.php?club=$encodedClub'>$club</a>
        </div>";
    }
    ?>

</body>
</html>


