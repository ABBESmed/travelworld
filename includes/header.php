<?php
/*
activate the session so the website can remember the logged-in user, 
while avoiding starting the session more than once
*/

// session_status() Returns the current session status, PHP_SESSION_NONE No session has been started yet
// session_start() remembers the logged-in user
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelWorld</title>
    <!-- connect the css file to the website -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- main website header -->
    <header class="header">
        <div class="container">

        <!-- website logo -->
        <a href="index.php"><img class="logo" src="assets/images/logo.svg" alt="TravelWorld logo"></a>
            
            <!-- Navigation menu -->
            <nav class="nav">
                <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <a href="flights.php">Flights</a>
                <a href="contact.php">Contact</a>

                <?php if (isset($_SESSION["user_id"])) { ?>

                    <!-- show these links when the user is logged in -->
                     <a href="my_reservations.php">My reservations</a>
                     <a href="profile.php">Profile</a>
                     <a href="logout.php">Logout</a>

                <?php } else { ?>
                    
                    <!-- show these links when the user is not logged in -->
                     <a href="login.php">Login</a>
                     <a href="register.php" class="register-button">Register</a>

                <?php } ?>
            </nav>
        </div>
    </header>
