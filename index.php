<?php

session_start();
// here index.php is the main entrance page of the website

/* 
i use require_once cause i need to load Another

PHP file (database.php) and executes it. once tell php to load that file one time

__DIR__ returns the folder where index.php is located and . joins text

together in php
*/

require_once __DIR__ . "/config/database.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>TravelWorld</h1>

    <?php if (isset($_SESSION["user_id"])) { ?>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION["full_name"]) ?> </p>
            <p><a href="profile.php">My profile</a></p>
            <p><a href="logout.php">Logout</a></p>
    <?php } else{ ?>
       <p><a href="login.php">Login</a></p>
       <p><a href="register.php">Create an account</a></p>
    <?php } ?>
</body>
</html>