<?php

// Start the session so we know which user is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Connect to the database
require_once __DIR__ . "/config/database.php";


// Redirect visitors who are not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}


// Retrieve the selected flight ID from the URL
$flight_id = $_GET["flight_id"] ?? "";


// Reject a missing or invalid flight ID
if ($flight_id === "" || !ctype_digit($flight_id)) {
    header("Location: flights.php");
    exit;
}


// Convert the flight ID from text into an integer
$flight_id = (int) $flight_id;


// Retrieve the selected flight and its airport cities
$sql = "
    SELECT
        f.id,
        f.flight_number,
        f.airline_name,
        f.departure_datetime,
        f.arrival_datetime,
        f.price,
        f.available_seats,
        departure_airport.city AS departure_city,
        arrival_airport.city AS arrival_city

    FROM flights AS f

    INNER JOIN airports AS departure_airport
        ON f.departure_airport_id = departure_airport.id

    INNER JOIN airports AS arrival_airport
        ON f.arrival_airport_id = arrival_airport.id

    WHERE f.id = ?
        AND f.status = 'scheduled'
        AND f.available_seats > 0
";


// Prepare the query
$stmt = mysqli_prepare($conn, $sql);


// Connect the flight ID to the ? placeholder
mysqli_stmt_bind_param(
    $stmt,
    "i",
    $flight_id
);


// Execute the query
mysqli_stmt_execute($stmt);


// Retrieve the result returned by MySQL
$flight_result = mysqli_stmt_get_result($stmt);


// Convert the flight row into a PHP associative array
$flight = mysqli_fetch_assoc($flight_result);


// Close the prepared statement
mysqli_stmt_close($stmt);


// Redirect when the flight does not exist or cannot be booked
if (!$flight) {
    header("Location: flights.php");
    exit;
}


// Prepare the passenger form values
$first_name = "";
$last_name = "";
$date_of_birth = "";
$nationality = "";
$document_number = "";


// Prepare an array for validation errors
$errors = [];


// Process the booking form only when it is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get the passenger information
    $first_name = trim($_POST["first_name"] ?? "");
    $last_name = trim($_POST["last_name"] ?? "");
    $date_of_birth = $_POST["date_of_birth"] ?? "";
    $nationality = trim($_POST["nationality"] ?? "");
    $document_number = trim($_POST["document_number"] ?? "");


    // Validate the passenger's first name
    if ($first_name === "") {
        $errors[] = "First name is required.";
    }


    // Validate the passenger's last name
    if ($last_name === "") {
        $errors[] = "Last name is required.";
    }


    // Validate the passenger's date of birth
    if ($date_of_birth === "") {
        $errors[] = "Date of birth is required.";
    }


    // Validate the passenger's nationality
    if ($nationality === "") {
        $errors[] = "Nationality is required.";
    }


    // Validate the passenger's document number
    if ($document_number === "") {
        $errors[] = "Document number is required.";
    }


    // Continue only when there are no errors
    if (empty($errors)) {

        // Create a unique reference for this reservation
        $booking_reference = strtoupper(
            uniqid("TW")
        );


        // Get the logged-in user's ID
        $user_id = (int) $_SESSION["user_id"];


        // Use the selected flight's price as the reservation total
        $total_price = (float) $flight["price"];


        // Prepare the reservation INSERT query
        $sql = "
            INSERT INTO reservations (
                booking_reference,
                user_id,
                flight_id,
                passenger_count,
                total_price,
                status
            )
            VALUES (?, ?, ?, 1, ?, 'confirmed')
        ";


        $stmt = mysqli_prepare($conn, $sql);


        /*
        Connect the four PHP values
        to the four ? placeholders.

        s = booking reference
        i = user ID
        i = flight ID
        d = total price
        */
        mysqli_stmt_bind_param(
            $stmt,
            "siid",
            $booking_reference,
            $user_id,
            $flight_id,
            $total_price
        );


        // Save the reservation
        mysqli_stmt_execute($stmt);


        // Retrieve the reservation ID MySQL just created
        $reservation_id = mysqli_insert_id($conn);


        mysqli_stmt_close($stmt);


        // Prepare the query that saves the passenger
        $sql = "
            INSERT INTO passengers (
                reservation_id,
                first_name,
                last_name,
                date_of_birth,
                nationality,
                document_number
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ";


        $stmt = mysqli_prepare($conn, $sql);


        // Connect the passenger values to the placeholders
        mysqli_stmt_bind_param(
            $stmt,
            "isssss",
            $reservation_id,
            $first_name,
            $last_name,
            $date_of_birth,
            $nationality,
            $document_number
        );


        // Save the passenger
        mysqli_stmt_execute($stmt);


        mysqli_stmt_close($stmt);


        // Prepare the query that removes one available seat
        $sql = "
            UPDATE flights
            SET available_seats = available_seats - 1
            WHERE id = ?
        ";


        $stmt = mysqli_prepare($conn, $sql);


        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $flight_id
        );


        // Update the number of seats
        mysqli_stmt_execute($stmt);


        mysqli_stmt_close($stmt);


        // Send the user to the confirmation page
        header(
            "Location: reservation_success.php?reservation_id="
            . $reservation_id
        );

        exit;
    }
}


// Load the shared website header
require_once __DIR__ . "/includes/header.php";

?>

<main>

    <!-- Book flight page -->
    <section class="booking-page">

        <div class="booking-container">


            <!-- Page title -->
            <div class="booking-title">

                <p>Ready to travel?</p>

                <h1>Book Your Flight</h1>

            </div>


            <!-- Selected flight information -->
            <div class="booking-flight-card">

                <h2>
                    <?php
                    echo htmlspecialchars(
                        $flight["airline_name"]
                        . " - "
                        . $flight["flight_number"]
                    );
                    ?>
                </h2>


                <div class="booking-flight-details">

                    <p>
                        <strong>Route:</strong>

                        <?php
                        echo htmlspecialchars(
                            $flight["departure_city"]
                            . " → "
                            . $flight["arrival_city"]
                        );
                        ?>
                    </p>


                    <p>
                        <strong>Price:</strong>

                        <?php
                        echo number_format(
                            (float) $flight["price"],
                            2,
                            ",",
                            " "
                        );
                        ?>
                        €
                    </p>


                    <p>
                        <strong>Departure:</strong>

                        <?php
                        echo htmlspecialchars(
                            date(
                                "d/m/Y H:i",
                                strtotime(
                                    $flight["departure_datetime"]
                                )
                            )
                        );
                        ?>
                    </p>


                    <p>
                        <strong>Arrival:</strong>

                        <?php
                        echo htmlspecialchars(
                            date(
                                "d/m/Y H:i",
                                strtotime(
                                    $flight["arrival_datetime"]
                                )
                            )
                        );
                        ?>
                    </p>

                </div>

            </div>


            <!-- Validation errors -->
            <?php if (!empty($errors)) { ?>

                <div class="booking-errors">

                    <?php foreach ($errors as $error) { ?>

                        <p>
                            <?php
                            echo htmlspecialchars($error);
                            ?>
                        </p>

                    <?php } ?>

                </div>

            <?php } ?>


            <!-- Passenger form -->
            <form
                method="POST"
                class="booking-form"
            >

                <h2>Passenger information</h2>


                <!-- First name -->
                <div class="booking-form-group">

                    <label for="first_name">
                        First name
                    </label>

                    <input
                        type="text"
                        id="first_name"
                        name="first_name"
                        value="<?php
                        echo htmlspecialchars($first_name);
                        ?>"
                        required
                    >

                </div>


                <!-- Last name -->
                <div class="booking-form-group">

                    <label for="last_name">
                        Last name
                    </label>

                    <input
                        type="text"
                        id="last_name"
                        name="last_name"
                        value="<?php
                        echo htmlspecialchars($last_name);
                        ?>"
                        required
                    >

                </div>


                <!-- Date of birth -->
                <div class="booking-form-group">

                    <label for="date_of_birth">
                        Date of birth
                    </label>

                    <input
                        type="date"
                        id="date_of_birth"
                        name="date_of_birth"
                        value="<?php
                        echo htmlspecialchars($date_of_birth);
                        ?>"
                        required
                    >

                </div>


                <!-- Nationality -->
                <div class="booking-form-group">

                    <label for="nationality">
                        Nationality
                    </label>

                    <input
                        type="text"
                        id="nationality"
                        name="nationality"
                        value="<?php
                        echo htmlspecialchars($nationality);
                        ?>"
                        required
                    >

                </div>


                <!-- Document number -->
                <div class="booking-form-group">

                    <label for="document_number">
                        Document number
                    </label>

                    <input
                        type="text"
                        id="document_number"
                        name="document_number"
                        value="<?php
                        echo htmlspecialchars($document_number);
                        ?>"
                        required
                    >

                </div>


                <!-- Confirm booking button -->
                <button
                    type="submit"
                    class="booking-button"
                >
                    Confirm reservation
                </button>

            </form>

        </div>

    </section>

</main>

<?php

// Load the shared website footer
require_once __DIR__ . "/includes/footer.php";

?>