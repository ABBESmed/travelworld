<?php

// Start the session so we can know which user is connected
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Protect the page.
// If the user is not logged in, send them to the login page.
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}


// Load the database connection
require_once __DIR__ . "/config/database.php";


// Get the connected user's ID from the session
$user_id = (int) $_SESSION["user_id"];


// Get the user's information from the database
$sql = "
    SELECT
        full_name,
        email,
        profile_picture,
        created_at
    FROM users
    WHERE id = ?
";


// Prepare the SQL query
$stmt = mysqli_prepare($conn, $sql);


// Send the user ID to the prepared query
mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);


// Execute the query
mysqli_stmt_execute($stmt);


// Get the result
$result = mysqli_stmt_get_result($stmt);


// Convert the result into a PHP array
$user = mysqli_fetch_assoc($result);


// Close the prepared statement
mysqli_stmt_close($stmt);


// If the user cannot be found, send them back to login
if (!$user) {
    header("Location: login.php");
    exit;
}


// Put the user's information into simple variables
$full_name = $user["full_name"];
$email = $user["email"];
$profile_picture = $user["profile_picture"];
$created_at = $user["created_at"];


// Load the shared website header
require_once __DIR__ . "/includes/header.php";

?>

<main>

    <!-- Profile page -->
    <section class="profile-page">

        <div class="profile-container">


            <!-- Profile title -->
            <div class="profile-title">

                <p>My account</p>

                <h1>My Profile</h1>

            </div>


            <!-- Profile card -->
            <div class="profile-card">


                <!-- Profile picture -->
                <div class="profile-picture">

                    <?php if (!empty($profile_picture)) { ?>

                        <img
                            src="uploads/profiles/<?php
                            echo htmlspecialchars($profile_picture);
                            ?>"
                            alt="Profile picture"
                        >

                    <?php } else { ?>

                        <div class="no-profile-picture">
                            No photo
                        </div>

                    <?php } ?>

                </div>


                <!-- Profile information -->
                <div class="profile-info">

                    <h2>
                        <?php
                        echo htmlspecialchars($full_name);
                        ?>
                    </h2>


                    <p>
                        <strong>Email:</strong>

                        <?php
                        echo htmlspecialchars($email);
                        ?>
                    </p>


                    <p>
                        <strong>Member since:</strong>

                        <?php
                        echo htmlspecialchars(
                            date(
                                "d/m/Y",
                                strtotime($created_at)
                            )
                        );
                        ?>
                    </p>


                    <!-- Profile actions -->
                    <div class="profile-actions">

                        <a
                            href="edit_profile.php"
                            class="edit-profile-button"
                        >
                            Edit profile
                        </a>

                        <a
                            href="index.php"
                            class="profile-home-link"
                        >
                            Back to homepage
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </section>

</main>

<?php

// Load the shared website footer
require_once __DIR__ . "/includes/footer.php";

?>