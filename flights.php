<?php

// remembers the logged-in user
session_start();

// connects it to my database

require_once __DIR__ . "/config/database.php";

// retrieve all airports for the search form using this sql query

$sql = "SELECT id, name, city, country FROM airports ORDER BY city ASC";

// execute the sql query

$airports_result = mysqli_query($conn, $sql);

// convert the mysql result into a normal php array so we can use it in the dropdown form menu

$airports = mysqli_fetch_all($airports_result, MYSQLI_ASSOC);

// store the airports selected by the user
$departure_airport_id = "";
$arrival_airport_id = "";

// store the flights found by the search
$flights = [];

// store the validation errors
$errors = [];

// Did the user submit the form?
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // retrieve the two airports selected by the user , Use an empty string when the value was not sent

    $departure_airport_id = $_POST["departure_airport_id"] ?? "";
    $arrival_airport_id = $_POST["arrival_airport_id"] ?? "";


    // validate the selected airports
    if ($departure_airport_id === "") {
        $errors[] = "Departure airport is required.";
    }

    if ($arrival_airport_id === "") {
        $errors[] = "Arrival airport is required.";
    }

    // prevent selecting the same airport twice

    if ($departure_airport_id !== "" && $arrival_airport_id !== "" && $departure_airport_id === $arrival_airport_id) {
        $errors[] = "Departure and arrival airports must be different.";
    }

    // Now we search the database only when there are no validation errors

    if (empty($errors)) {
// This searches for every available flight that: leaves from the selected airport and arrives at the selected airport


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

            WHERE f.departure_airport_id = ?
                AND f.arrival_airport_id = ?
                AND f.status = 'scheduled'
                AND f.available_seats > 0

            ORDER BY f.departure_datetime ASC
        ";

        // Now we prepare the SQL query
        $stmt = mysqli_prepare($conn, $sql);

        // This will fills the two ? placeholders
        mysqli_stmt_bind_param($stmt, "ii", $departure_airport_id, $arrival_airport_id);

        // we execute the prepared search (Run the flight search now using the two selected airport IDs)
        mysqli_stmt_execute($stmt);

        // Now we retrieve the rows found by MySQL
        $flights_result = mysqli_stmt_get_result($stmt);

        // Now we convert the MySQL result into a normal PHP array
        $flights = mysqli_fetch_all($flights_result, MYSQLI_ASSOC);

        // Now we close the prepared statement
        mysqli_stmt_close($stmt);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flights - TravelWorld</title>
</head>
<body>
    <main>
        <h1>Search Flights</h1>

        <form method="POST">


        <?php if (!empty($errors)) { ?>

            <?php foreach ($errors as $error) { ?>

                <p><?php echo htmlspecialchars($error) ?></p>

            <?php } ?>

        <?php } ?>

            <label for="departure_airport_id">Departure airport</label>
            
            <select id="departure_airport_id" name="departure_airport_id">
                <option value="">Choose a departure airport</option>
                    <?php foreach ($airports as $airport) { ?>
                        <option value="<?php echo (int) $airport["id"] ?>"
                            <?php if ($departure_airport_id == $airport["id"]) {
                                echo "selected";
                            } ?>
                        >

                        <?= htmlspecialchars(
                            $airport["city"]
                            . " - "
                            . $airport["name"]
                            . " ("
                            . $airport["country"]
                            . ")"
                        ) ?>
                        </option>
                    <?php } ?>
            </select>


            <label for="arrival_airport_id">Arrival airport</label>

            <select id="arrival_airport_id" name="arrival_airport_id" required>
                <option value="">Choose an arrival airport</option>


                <?php foreach ($airports as $airport) { ?>

                    <option
                        value="<?php echo (int) $airport["id"] ?>"
                        <?php if (
                            $arrival_airport_id == $airport["id"]
                        ) {
                            echo "selected";
                        } ?>
                    >
                        <?php echo htmlspecialchars(
                            $airport["city"]
                            . " - "
                            . $airport["name"]
                            . " ("
                            . $airport["country"]
                            . ")"
                        ) ?>
                    </option>

                <?php } ?>

            </select>

            <button type="submit">
                Search flights
            </button>
        </form>

        <?php if (
            $_SERVER["REQUEST_METHOD"] === "POST"
            && empty($errors)
        ) { ?>

            <h2>Available Flights</h2>

                <?php if (empty($flights)) { ?>

                    <p>No flights found for this route.</p>

                <?php } else { ?>
                    <?php foreach ($flights as $flight) { ?>
                        <article>

                            <h3>
                                <?= htmlspecialchars(
                                    $flight["airline_name"]
                                    . " - "
                                    . $flight["flight_number"]
                                ) ?>
                            </h3>

                            <p>
                                <strong>Route:</strong>

                                <?= htmlspecialchars(
                                    $flight["departure_city"]
                                    . " → "
                                    . $flight["arrival_city"]
                                ) ?>
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
                                <strong>Available seats:</strong>

                                <?= (int) $flight["available_seats"] ?>
                            </p>

                            <a href="book_flight.php?flight_id=<?= (int) $flight["id"] ?>">
                                Book this flight
                            </a>
                                <?php } ?>
                </article>
            <?php } ?>
        <?php } ?>
    </main>
</body>
</html>