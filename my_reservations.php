<?php

// Start the session so PHP knows which user is logged in
session_start();

// Connect this page to the database
require_once __DIR__ . "/config/database.php";

// Only logged-in users can view reservations
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Retrieve the logged-in user's ID
$user_id = (int) $_SESSION["user_id"];

/*
Retrieve all reservations belonging to the logged-in user.

We connect:
reservations → flights
flights → departure airport
flights → arrival airport
reservations → passengers
*/
$sql = "
    SELECT
        r.id,
        r.booking_reference,
        r.total_price,
        r.status,
        r.created_at,

        f.flight_number,
        f.airline_name,
        f.departure_datetime,
        f.arrival_datetime,

        departure_airport.city AS departure_city,
        arrival_airport.city AS arrival_city,

        p.first_name,
        p.last_name

    FROM reservations AS r

    INNER JOIN flights AS f
        ON r.flight_id = f.id

    INNER JOIN airports AS departure_airport
        ON f.departure_airport_id = departure_airport.id

    INNER JOIN airports AS arrival_airport
        ON f.arrival_airport_id = arrival_airport.id

    INNER JOIN passengers AS p
        ON p.reservation_id = r.id

    WHERE r.user_id = ?

    ORDER BY r.created_at DESC
";

// Prepare the SQL query
$stmt = mysqli_prepare($conn, $sql);

// Connect the logged-in user's ID to the question mark
mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

// Execute the reservation search
mysqli_stmt_execute($stmt);

// Retrieve the MySQL result
$reservations_result = mysqli_stmt_get_result($stmt);

// Convert the result into a normal PHP associative array
$reservations = mysqli_fetch_all(
    $reservations_result,
    MYSQLI_ASSOC
);

// Close the prepared statement
mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Reservations - TravelWorld</title>
</head>

<body>

    <main>

        <h1>My Reservations</h1>

        <?php if (empty($reservations)) { ?>

            <p>
                You do not have any reservations yet.
            </p>

            <a href="flights.php">
                Search for a flight
            </a>

        <?php } else { ?>

            <?php foreach ($reservations as $reservation) { ?>

                <article>

                    <h2>
                        <?= htmlspecialchars(
                            $reservation["airline_name"]
                            . " - "
                            . $reservation["flight_number"]
                        ) ?>
                    </h2>

                    <p>
                        <strong>Booking reference:</strong>

                        <?= htmlspecialchars(
                            $reservation["booking_reference"]
                        ) ?>
                    </p>

                    <p>
                        <strong>Passenger:</strong>

                        <?= htmlspecialchars(
                            $reservation["first_name"]
                            . " "
                            . $reservation["last_name"]
                        ) ?>
                    </p>

                    <p>
                        <strong>Route:</strong>

                        <?= htmlspecialchars(
                            $reservation["departure_city"]
                            . " → "
                            . $reservation["arrival_city"]
                        ) ?>
                    </p>

                    <p>
                        <strong>Departure:</strong>

                        <?= htmlspecialchars(
                            date(
                                "d/m/Y H:i",
                                strtotime(
                                    $reservation["departure_datetime"]
                                )
                            )
                        ) ?>
                    </p>

                    <p>
                        <strong>Arrival:</strong>

                        <?= htmlspecialchars(
                            date(
                                "d/m/Y H:i",
                                strtotime(
                                    $reservation["arrival_datetime"]
                                )
                            )
                        ) ?>
                    </p>

                    <p>
                        <strong>Total price:</strong>

                        <?= number_format(
                            (float) $reservation["total_price"],
                            2,
                            ",",
                            " "
                        ) ?> €
                    </p>

                    <p>
                        <strong>Status:</strong>

                        <?= htmlspecialchars(
                            ucfirst($reservation["status"])
                        ) ?>
                    </p>

                    <p>
                        <strong>Reserved on:</strong>

                        <?= htmlspecialchars(
                            date(
                                "d/m/Y H:i",
                                strtotime(
                                    $reservation["created_at"]
                                )
                            )
                        ) ?>
                    </p>

                    <a
                        href="reservation_success.php?reservation_id=<?= (int) $reservation["id"] ?>"
                    >
                        View reservation details
                    </a>

                </article>

                <hr>

            <?php } ?>

            <a href="flights.php">
                Search another flight
            </a>

        <?php } ?>

    </main>

</body>

</html>