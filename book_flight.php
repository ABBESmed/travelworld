<?php

session_start();

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

$stmt = mysqli_prepare($conn, $sql);

// Now we connect the flight ID to the ? placeholder
mysqli_stmt_bind_param(
    $stmt,
    "i",
    $flight_id
);

mysqli_stmt_execute($stmt);

// Now we retrieve the result returned by MySQL
$flight_result = mysqli_stmt_get_result($stmt);


// takes one row and converts it into a PHP associative array
$flight = mysqli_fetch_assoc($flight_result);

mysqli_stmt_close($stmt);

// Redirect when the flight does not exist or cannot be booked
if (!$flight) {
    header("Location: flights.php");
    exit;
}

// Now we prepare the variables for the passenger form (When the page first opens, the passenger form is empty)
// Prepare the passenger form values
$first_name = "";
$last_name = "";
$date_of_birth = "";
$nationality = "";
$document_number = "";

// Prepare an array for validation errors
$errors = [];


if ($_SERVER["REQUEST_METHOD"] === "POST") {

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


    if (empty($errors)) {

// Create a unique reference for this reservation
$booking_reference = strtoupper(uniqid("TW"));

// Get the logged-in user's ID
$user_id = (int) $_SESSION["user_id"];

// Use the selected flight's price as the reservation total
$total_price = (float) $flight["price"];


// we prepare the reservation INSERT query
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


// Now we prepare the reservation INSERT query
$stmt = mysqli_prepare($conn, $sql);


// Now we connect the four PHP values to the four ? placeholders
mysqli_stmt_bind_param(
    $stmt,
    "siid",
    $booking_reference,
    $user_id,
    $flight_id,
    $total_price
);

mysqli_stmt_execute($stmt);

// Now retrieve the ID of the reservation that MySQL just created
$reservation_id = mysqli_insert_id($conn);

mysqli_stmt_close($stmt);

// Now we prepare the SQL query that saves the passenger
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

// we connect the passenger values to the six ? placeholders
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

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

// we Prepare the query that removes one available seat
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

mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);


// Now we send the user to the confirmation page after the reservation is completed
header(
    "Location: reservation_success.php?reservation_id="
    . $reservation_id
);

exit;
}
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Flight - TravelWorld</title>
</head>

<body>

    <main>

        <h1>Book Flight</h1>

        <h2>
            <?= htmlspecialchars(
                $flight["airline_name"]
                . " - "
                . $flight["flight_number"]
            ) ?>
        </h2>

        <p>
            <strong>Route:</strong>

            <?= htmlspecialchars(
                $flight["departure_city"]
                . " → "
                . $flight["arrival_city"]
            ) ?>
        </p>

        <p>
            <strong>Price:</strong>

            <?= number_format(
                (float) $flight["price"],
                2,
                ",",
                " "
            ) ?> €
        </p>

        <p>
            <strong>Departure:</strong>

            <?= htmlspecialchars(
                date(
                    "d/m/Y H:i",
                    strtotime($flight["departure_datetime"])
                )
            ) ?>
        </p>

        <p>
            <strong>Arrival:</strong>

            <?= htmlspecialchars(
                date(
                    "d/m/Y H:i",
                    strtotime($flight["arrival_datetime"])
                )
            ) ?>
        </p>

        <?php if (!empty($errors)) { ?>

            <?php foreach ($errors as $error) { ?>

                <p><?= htmlspecialchars($error) ?></p>

            <?php } ?>

        <?php } ?>


        <form method="POST">

    <label for="first_name">First name</label>

        <input
            type="text"
            id="first_name"
            name="first_name"
            value="<?= htmlspecialchars($first_name) ?>"
            required
        >

    <label for="last_name">Last name</label>

        <input
            type="text"
            id="last_name"
            name="last_name"
            value="<?= htmlspecialchars($last_name) ?>"
            required
        >

    <label for="date_of_birth">Date of birth</label>

        <input
            type="date"
            id="date_of_birth"
            name="date_of_birth"
            value="<?= htmlspecialchars($date_of_birth) ?>"
            required
        >

    <label for="nationality">Nationality</label>

        <input
            type="text"
            id="nationality"
            name="nationality"
            value="<?= htmlspecialchars($nationality) ?>"
            required
        >

    <label for="document_number">Document number</label>

        <input
            type="text"
            id="document_number"
            name="document_number"
            value="<?= htmlspecialchars($document_number) ?>"
            required
        >


        <button type="submit">
            Confirm reservation
        </button>

        </form>
    </main>

</body>

</html>