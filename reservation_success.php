<?php

// Start the session so PHP knows which user is logged in
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


// Retrieve the reservation ID from the URL
$reservation_id = $_GET["reservation_id"] ?? "";


// Reject a missing or invalid reservation ID
if (
    $reservation_id === ""
    || !ctype_digit($reservation_id)
) {
    header("Location: flights.php");
    exit;
}


// Convert the reservation ID into an integer
$reservation_id = (int) $reservation_id;


// Retrieve the logged-in user's ID
$user_id = (int) $_SESSION["user_id"];


/*
Retrieve the reservation and make sure
it belongs to the logged-in user.
*/
$sql = "
    SELECT
        r.booking_reference,
        r.total_price,
        r.status,

        f.flight_number,
        f.airline_name,
        f.departure_datetime,
        f.arrival_datetime,

        departure_airport.city AS departure_city,
        arrival_airport.city AS arrival_city,

        p.first_name,
        p.last_name,
        p.date_of_birth,
        p.nationality,
        p.document_number

    FROM reservations AS r

    INNER JOIN flights AS f
        ON r.flight_id = f.id

    INNER JOIN airports AS departure_airport
        ON f.departure_airport_id = departure_airport.id

    INNER JOIN airports AS arrival_airport
        ON f.arrival_airport_id = arrival_airport.id

    INNER JOIN passengers AS p
        ON p.reservation_id = r.id

    WHERE r.id = ?
        AND r.user_id = ?
";


// Prepare the SQL query
$stmt = mysqli_prepare($conn, $sql);


// Connect the reservation ID and user ID
// to the two ? placeholders
mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $reservation_id,
    $user_id
);


// Execute the query
mysqli_stmt_execute($stmt);


// Retrieve the MySQL result
$reservation_result = mysqli_stmt_get_result($stmt);


// Convert the result into a PHP associative array
$reservation = mysqli_fetch_assoc($reservation_result);


// Close the prepared statement
mysqli_stmt_close($stmt);


// Redirect when the reservation does not exist
if (!$reservation) {
    header("Location: flights.php");
    exit;
}


// Load the shared website header
require_once __DIR__ . "/includes/header.php";

?>

<main>

    <!-- Reservation confirmation page -->
    <section class="reservation-success-page">

        <div class="reservation-success-container">


            <!-- Confirmation title -->
            <div class="reservation-success-title">

                <div class="success-icon">
                    ✓
                </div>

                <p>Booking completed</p>

                <h1>Reservation Confirmed</h1>

                <span>
                    Your flight reservation has been successfully created.
                </span>

            </div>


            <!-- Reservation information -->
            <div class="reservation-success-card">

                <h2>
                    Reservation details
                </h2>


                <p>
                    <strong>
                        Booking reference:
                    </strong>

                    <?php
                    echo htmlspecialchars(
                        $reservation["booking_reference"]
                    );
                    ?>
                </p>


                <p>
                    <strong>
                        Flight:
                    </strong>

                    <?php
                    echo htmlspecialchars(
                        $reservation["airline_name"]
                        . " - "
                        . $reservation["flight_number"]
                    );
                    ?>
                </p>


                <p>
                    <strong>
                        Route:
                    </strong>

                    <?php
                    echo htmlspecialchars(
                        $reservation["departure_city"]
                        . " → "
                        . $reservation["arrival_city"]
                    );
                    ?>
                </p>


                <p>
                    <strong>
                        Departure:
                    </strong>

                    <?php
                    echo htmlspecialchars(
                        date(
                            "d/m/Y H:i",
                            strtotime(
                                $reservation["departure_datetime"]
                            )
                        )
                    );
                    ?>
                </p>


                <p>
                    <strong>
                        Arrival:
                    </strong>

                    <?php
                    echo htmlspecialchars(
                        date(
                            "d/m/Y H:i",
                            strtotime(
                                $reservation["arrival_datetime"]
                            )
                        )
                    );
                    ?>
                </p>


                <p>
                    <strong>
                        Passenger:
                    </strong>

                    <?php
                    echo htmlspecialchars(
                        $reservation["first_name"]
                        . " "
                        . $reservation["last_name"]
                    );
                    ?>
                </p>


                <p>
                    <strong>
                        Date of birth:
                    </strong>

                    <?php
                    echo htmlspecialchars(
                        date(
                            "d/m/Y",
                            strtotime(
                                $reservation["date_of_birth"]
                            )
                        )
                    );
                    ?>
                </p>


                <p>
                    <strong>
                        Nationality:
                    </strong>

                    <?php
                    echo htmlspecialchars(
                        $reservation["nationality"]
                    );
                    ?>
                </p>


                <p>
                    <strong>
                        Document number:
                    </strong>

                    <?php
                    echo htmlspecialchars(
                        $reservation["document_number"]
                    );
                    ?>
                </p>


                <p>
                    <strong>
                        Total price:
                    </strong>

                    <?php
                    echo number_format(
                        (float) $reservation["total_price"],
                        2,
                        ",",
                        " "
                    );
                    ?>
                    €
                </p>


                <p>
                    <strong>
                        Status:
                    </strong>

                    <span class="reservation-status">

                        <?php
                        echo htmlspecialchars(
                            ucfirst(
                                $reservation["status"]
                            )
                        );
                        ?>

                    </span>
                </p>


                <!-- Page actions -->
                <div class="reservation-success-actions">

                    <a
                        href="my_reservations.php"
                        class="reservation-view-button"
                    >
                        My reservations
                    </a>

                    <a
                        href="flights.php"
                        class="reservation-search-link"
                    >
                        Search another flight
                    </a>

                </div>

            </div>

        </div>

    </section>

</main>

<?php

// Load the shared website footer
require_once __DIR__ . "/includes/footer.php";

?>