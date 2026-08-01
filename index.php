<?php

// Here index.php is the main entrance page of the website

/* 
I use require_once because I need to load another
PHP file (database.php) and execute it.

once tells PHP to load that file only one time.

__DIR__ returns the folder where index.php is located
and . joins text together in PHP.
*/

require_once __DIR__ . "/config/database.php";

// Load the header.
// header.php starts the session and opens the HTML page.
require_once __DIR__ . "/includes/header.php";

?>

<!-- Main content of the homepage -->
<main>

    <h1>TravelWorld</h1>

    <?php if (isset($_SESSION["user_id"])) { ?>

        <p>
            Welcome,
            <?php echo htmlspecialchars($_SESSION["full_name"]); ?>
        </p>

        <p>
            <a href="profile.php">My profile</a>
        </p>

        <p>
            <a href="logout.php">Logout</a>
        </p>

    <?php } else { ?>

        <p>
            <a href="login.php">Login</a>
        </p>

        <p>
            <a href="register.php">Create an account</a>
        </p>

    <?php } ?>

</main>

<?php require_once __DIR__ . "/includes/footer.php"; ?>