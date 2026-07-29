<?php
// flights.php may need to know whether the visitor is logged in
session_start();

// Now we connect flights.php to the database
require_once __DIR__ . "/config/database.php";

// Now write the SQL query that retrieves all airports for the flight-search form
$sql = "SELECT id, name, city, country FROM airports ORDER BY city ASC";


// execute the airport query (Send the SQL query to MySQL using the database connection)
$airports_result = mysqli_query($conn, $sql);

// convert the MySQL result into a normal PHP array, $airports_result (contains the rows returned by MySQL), mysqli_fetch_all(...) (takes all those rows), MYSQLI_ASSOC(means each row becomes an associative array using the column names) 
$airports = mysqli_fetch_all($airports_result, MYSQLI_ASSOC);


// Now prepare a variable for the departure airport selected in the search form
$departure_airport_id = "";

// Now prepare a variable for the arrival airport selected in the form
$arrival_airport_id = "";

// Now we prepare a variable for the travel date selected in the form
$departure_date = "";

// Now we prepare an empty array for the flights found by the search
$flights = [];

// an array for error messages
$errors = [];

// Now we check whether the flight-search form was submitted

if ($_SERVER["REQUEST_METHOD"] === "POST"){

// we retrieve the selected departure and arrival airports and dates, and we use an empty string when no date or no aiports was sent
    $departure_airport_id = $_POST["departure_airport_id"] ?? "";
    $arrival_airport_id = $_POST["arrival_airport_id"] ?? "";
    $departure_date = $_POST["departure_date"] ?? "";


// validate
    if ($departure_airport_id === "") {
        $errors[] = "Departure airport is required.";
    }

    if ($arrival_airport_id === "") {
        $errors[] = "Arrival airport is required.";
    }

    if ($departure_date === "") {
    $errors[] = "Departure date is required.";
    }


// Now we prevent the user from choosing the same airport for departure and arrival
    if (
    $departure_airport_id !== ""
    && $arrival_airport_id !== ""
    && $departure_airport_id === $arrival_airport_id
    ) {
    $errors[] = "Departure and arrival airports must be different.";
    }

// Now we search the database only when the form contains no validation errors
    if (empty($errors)){
        $sql = "
        SELECT 
            f.id,
            f.flight_number,
            f.airline_name,
            f.departure_datetime,
            f.arrival_datetime,
            f.price,
            f.available_seats,

            /* departure_airport = nickname of the airports table
               .city = retrieve the city column
               AS departure_city = return it with the name departure_city */

            departure_airport.city AS departure_city,

            departure_airport.name AS departure_airport_name,

            arrival_airport.city AS arrival_city,

            arrival_airport.name AS arrival_airport_name

            FROM flights AS f

            INNER JOIN airports AS departure_airport

            ON f.departure_airport_id = departure_airport.id

            INNER JOIN airports AS arrival_airport

            ON f.arrival_airport_id = arrival_airport.id

            WHERE f.departure_airport_id = ?

            AND f.arrival_airport_id = ?

            AND DATE(f.departure_datetime) = ?

            AND f.status = 'scheduled'

            AND f.available_seats > 0

            ORDER BY f.departure_datetime ASC
        ";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "iis",
            $departure_airport_id,
            $arrival_airport_id,
            $departure_date
        );

        mysqli_stmt_execute($stmt);

        $flights_result = mysqli_stmt_get_result($stmt);

        // Now convert the MySQL result into a normal PHP array

        $flights = mysqli_fetch_all($flights_result, MYSQLI_ASSOC);

        mysqli_stmt_close($stmt);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Flights - TravelWorld</title>
</head>
<body>
    <main>
        <h1>Search Flights</h1>

        <?php if (!empty($errors)) { ?>
            <?php foreach ($errors as $error) { ?>
                <p><?php echo htmlspecialchars($error) ?></p>
            <?php } ?>
        <?php } ?>

        <form method="POST">
            <label for="departure_airport_id">Departure airport</label>
            <select id="departure_airport_id" name="departure_airport_id" required>
                <option value="">Choose a departure airport</option>

                <?php foreach ($airports as $airport) { ?>
                    <!-- Take the airport ID and convert it into an integer -->
                    <option value="<?= (int) $airport["id"] ?>">
                        <?= htmlspecialchars(
                            $airport["city"] . " - " .
                            $airport["name"] . " (" .
                            $airport["country"] . ")"
                        ) ?>
                    </option>
                <?php } ?>
            </select>


            <label for="arrival_airport_id">Arrival airport</label>
            <select id="arrival_airport_id" name="arrival_airport_id" required>
                <option value="">Choose an arrival airport</option>
                    <?php foreach ($airports as $airport) { ?>
                        <option value="<?= (int) $airport["id"] ?>">
                            <?= htmlspecialchars(
                                $airport["city"] . " - " .
                                $airport["name"] . " (" .
                                $airport["country"] . ")"
                            ) ?>
                        </option>
                    <?php } ?>
            </select>


             <label for="departure_date">
                    Departure date
                </label>

                <input
                    type="date"
                    id="departure_date"
                    name="departure_date"
                    value="<?= htmlspecialchars($departure_date) ?>"
                    required
                >

                <button type="submit">
                Search flights
                </button>
        </form>
        


        <!-- Display the search result only after submitting a valid form -->
        <?php if (
            $_SERVER["REQUEST_METHOD"] === "POST"
            && empty($errors)
        ) { ?>

            <section>

                <h2>Available Flights</h2>

                <?php if (empty($flights)) { ?>

                    <p>
                        No flights are available for the selected route and date.
                    </p>

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
                                <strong>From:</strong>

                                <?= htmlspecialchars(
                                    $flight["departure_city"]
                                    . " - "
                                    . $flight["departure_airport_name"]
                                ) ?>
                            </p>

                            <p>
                                <strong>To:</strong>

                                <?= htmlspecialchars(
                                    $flight["arrival_city"]
                                    . " - "
                                    . $flight["arrival_airport_name"]
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

                        </article>

                    <?php } ?>

                <?php } ?>

            </section>

        <?php } ?>

    </main>
</body>
</html>