<?php

/*
Activate the session so the website can remember
the logged-in user while avoiding starting
the session more than once.
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>TravelWorld</title>

    <!-- Connect the CSS file to the website -->
    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>

<body>

    <!-- Main website header -->
    <header class="header">

        <div class="container">

            <!-- Website logo -->
            <a href="index.php">

                <img
                    class="logo"
                    src="assets/images/logo.svg"
                    alt="TravelWorld logo"
                >

            </a>


            <!-- Burger button for mobile -->
            <button
                class="burger-menu"
                type="button"
                aria-label="Open navigation menu"
            >
                ☰
            </button>


            <!-- Navigation menu -->
            <nav class="nav">

                <a href="index.php">
                    Home
                </a>

                <a href="about.php">
                    About
                </a>

                <a href="flights.php">
                    Flights
                </a>

                <a href="contact.php">
                    Contact
                </a>


                <?php if (isset($_SESSION["user_id"])) { ?>

                    <a href="my_reservations.php">
                        My reservations
                    </a>

                    <a href="profile.php">
                        Profile
                    </a>

                    <a href="logout.php">
                        Logout
                    </a>

                <?php } else { ?>

                    <a href="login.php">
                        Login
                    </a>

                    <a
                        href="register.php"
                        class="register-button"
                    >
                        Register
                    </a>

                <?php } ?>

            </nav>

        </div>

    </header>