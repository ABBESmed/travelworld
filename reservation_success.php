<?php

session_start();

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

// Retrieve the reservation and make sure it belongs to the logged-in user
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

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $reservation_id,
    $user_id
);

mysqli_stmt_execute($stmt);

$reservation_result = mysqli_stmt_get_result($stmt);

$reservation = mysqli_fetch_assoc($reservation_result);

mysqli_stmt_close($stmt);

// Redirect when the reservation does not exist
if (!$reservation) {
    header("Location: flights.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Confirmed - TravelWorld</title>
</head>

<body>

    <main>

        <h1>Reservation Confirmed</h1>

        <p>
            <strong>Booking reference:</strong>

            <?= htmlspecialchars(
                $reservation["booking_reference"]
            ) ?>
        </p>

        <p>
            <strong>Flight:</strong>

            <?= htmlspecialchars(
                $reservation["airline_name"]
                . " - "
                . $reservation["flight_number"]
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
                    strtotime($reservation["departure_datetime"])
                )
            ) ?>
        </p>


        <p>
            <strong>Arrival:</strong>

            <?= htmlspecialchars(
                date(
                    "d/m/Y H:i",
                    strtotime($reservation["arrival_datetime"])
                )
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
            <strong>Date of birth:</strong>

            <?= htmlspecialchars(
                date(
                    "d/m/Y",
                    strtotime($reservation["date_of_birth"])
                )
            ) ?>
        </p>

        <p>
            <strong>Nationality:</strong>

            <?= htmlspecialchars(
                $reservation["nationality"]
            ) ?>
        </p>

        <p>
            <strong>Document number:</strong>

            <?= htmlspecialchars(
                $reservation["document_number"]
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

            <a href="flights.php">
                Search another flight
            </a>

            </main>

    </body>

</html>