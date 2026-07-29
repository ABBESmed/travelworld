<?php

// The profile page must know which user is logged in so we use session_start()
session_start();

// Does the session contain a logged-in user ID?
if (!isset($_SESSION["user_id"])) {
    // When no user is logged in, send them to the login page
    header("Location: login.php");
    // now we stop profile.php after the redirect using exit
    exit;
}

// Now we connect profile.php to the database
require_once __DIR__ . "/config/database.php";

// we Remember the ID of the user whose profile we need to retrieve
$user_id = $_SESSION["user_id"];


// Now we write the SQL query that will find the logged-in user
$sql = "SELECT full_name, email, profile_picture, created_at FROM users WHERE id = ?";

// Now we prepare the SQL query safely
$stmt = mysqli_prepare($conn, $sql);

// Now we connect the real user ID to the ? placeholder i mean inteeger
mysqli_stmt_bind_param($stmt, "i", $user_id);

// Now we execute the prepared profile query (sends the query to MySQL)
mysqli_stmt_execute($stmt);

// Now we connect the columns returned by MySQL to PHP variables
mysqli_stmt_bind_result($stmt, $full_name, $email, $profile_picture, $created_at);

// Now we fetch the user row from MySQL and place its values into the variables
mysqli_stmt_fetch($stmt);

// Now we close the prepared profile query because the user information has already been retrieved
mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - TravelWorld</title>
</head>
<body>
    <main>
        <h1>My Profile</h1>
        <p>
            <strong>Full name:</strong>
            <?php echo htmlspecialchars($full_name) ?>
        </p>

        <p>
            <strong>Email:</strong>
            <?php echo htmlspecialchars($email) ?>
        </p>

        <p>
            <strong>Member since:</strong>
            <?php echo htmlspecialchars($created_at) ?>
        </p>

        <?php if (!empty($profile_picture)) { ?>
            <img src="uploads/profiles/<?php echo htmlspecialchars($profile_picture) ?>" alt="Profile picture" width="150">
        <?php } else{ ?>
            <p>No profile picture uploaded.</p>
        <?php } ?>

        <p><a href="edit_profile.php">Edit profile</a></p>
        <p><a href="index.php">Back to homepage</a></p>
    </main>
</body>
</html>