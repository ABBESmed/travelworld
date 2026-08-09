<?php

// Load the database connection
require_once __DIR__ . "/config/database.php";

// Create empty variables
$departure_airport_id = "";
$arrival_airport_id = "";
$errors = [];
$flights = [];


/*
Get all airports from the database.

We need them to create the departure
and arrival <select> options.
*/
$sql = "
    SELECT id, name, city, country
    FROM airports
    ORDER BY city ASC
";

$result = mysqli_query($conn, $sql);

// Convert the MySQL result into a normal PHP array
$airports = mysqli_fetch_all($result, MYSQLI_ASSOC);


/*
Only search for flights when the form
has been submitted with POST.
*/
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get the selected airport IDs from the form
    $departure_airport_id =
        $_POST["departure_airport_id"] ?? "";

    $arrival_airport_id =
        $_POST["arrival_airport_id"] ?? "";


    // Check that a departure airport was selected
    if ($departure_airport_id === "") {
        $errors[] = "Please choose a departure airport.";
    }


    // Check that an arrival airport was selected
    if ($arrival_airport_id === "") {
        $errors[] = "Please choose an arrival airport.";
    }


    // Departure and arrival cannot be the same airport
    if (
        $departure_airport_id !== ""
        && $arrival_airport_id !== ""
        && $departure_airport_id == $arrival_airport_id
    ) {
        $errors[] =
            "Departure and arrival airports must be different.";
    }


    /*
    Search the database only when
    there are no validation errors.
    */
    if (empty($errors)) {

        $sql = "
            SELECT
                f.id,
                f.flight_number,
                f.airline_name,
                f.departure_datetime,
                f.arrival_datetime,
                f.price,
                f.available_seats,

                departure_airport.city
                    AS departure_city,

                arrival_airport.city
                    AS arrival_city

            FROM flights AS f

            INNER JOIN airports AS departure_airport
                ON f.departure_airport_id =
                   departure_airport.id

            INNER JOIN airports AS arrival_airport
                ON f.arrival_airport_id =
                   arrival_airport.id

            WHERE f.departure_airport_id = ?
                AND f.arrival_airport_id = ?
                AND f.status = 'scheduled'
                AND f.available_seats > 0

            ORDER BY f.departure_datetime ASC
        ";


        // Prepare the SQL query
        $stmt = mysqli_prepare($conn, $sql);


        /*
        ii means we send two integers:

        first i  = departure airport ID
        second i = arrival airport ID
        */
        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $departure_airport_id,
            $arrival_airport_id
        );


        // Execute the prepared query
        mysqli_stmt_execute($stmt);


        // Get the result returned by MySQL
        $result = mysqli_stmt_get_result($stmt);


        // Convert the result into a normal PHP array
        $flights = mysqli_fetch_all(
            $result,
            MYSQLI_ASSOC
        );


        // Close the prepared statement
        mysqli_stmt_close($stmt);
    }
}


// Load the shared website header
require_once __DIR__ . "/includes/header.php";

?>

<main>

    <!-- Flights page -->
    <section class="flights-page">

        <div class="flights-container">

            <!-- Page title -->
            <div class="flights-title">

                <p>Find your journey</p>

                <h1>
                    Search available flights
                </h1>

            </div>


            <!-- Flight search form -->
            <form
                method="POST"
                class="flight-search-form"
            >

                <!-- Display validation errors -->
                <?php if (!empty($errors)) { ?>

                    <div class="flight-errors">

                        <?php foreach ($errors as $error) { ?>

                            <p>
                                <?php
                                echo htmlspecialchars($error);
                                ?>
                            </p>

                        <?php } ?>

                    </div>

                <?php } ?>


                <div class="flight-search-fields">

                    <!-- Departure airport -->
                    <div class="flight-form-group">

                        <label for="departure_airport_id">
                            Departure airport
                        </label>

                        <select
                            id="departure_airport_id"
                            name="departure_airport_id"
                            required
                        >

                            <option value="">
                                Choose a departure airport
                            </option>


                            <?php foreach ($airports as $airport) { ?>

                                <option
                                    value="<?php echo (int) $airport["id"]; ?>"

                                    <?php if (
                                        $departure_airport_id
                                        == $airport["id"]
                                    ) {
                                        echo "selected";
                                    } ?>
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $airport["city"]
                                        . " - "
                                        . $airport["name"]
                                        . " ("
                                        . $airport["country"]
                                        . ")"
                                    );
                                    ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>


                    <!-- Arrival airport -->
                    <div class="flight-form-group">

                        <label for="arrival_airport_id">
                            Arrival airport
                        </label>

                        <select
                            id="arrival_airport_id"
                            name="arrival_airport_id"
                            required
                        >

                            <option value="">
                                Choose an arrival airport
                            </option>


                            <?php foreach ($airports as $airport) { ?>

                                <option
                                    value="<?php echo (int) $airport["id"]; ?>"

                                    <?php if (
                                        $arrival_airport_id
                                        == $airport["id"]
                                    ) {
                                        echo "selected";
                                    } ?>
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $airport["city"]
                                        . " - "
                                        . $airport["name"]
                                        . " ("
                                        . $airport["country"]
                                        . ")"
                                    );
                                    ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>

                </div>


                <button
                    type="submit"
                    class="flight-search-button"
                >
                    Search flights
                </button>

            </form>


            <!-- Show results only after a successful search -->
            <?php if (
                $_SERVER["REQUEST_METHOD"] === "POST"
                && empty($errors)
            ) { ?>

                <section class="flight-results">

                    <h2>
                        Available Flights
                    </h2>


                    <!-- No flights found -->
                    <?php if (empty($flights)) { ?>

                        <p class="no-flights">
                            No flights found for this route.
                        </p>


                    <!-- Flights were found -->
                    <?php } else { ?>

                        <div class="flight-list">

                            <?php foreach ($flights as $flight) { ?>

                                <article class="flight-card">

                                    <h3>
                                        <?php
                                        echo htmlspecialchars(
                                            $flight["airline_name"]
                                            . " - "
                                            . $flight["flight_number"]
                                        );
                                        ?>
                                    </h3>


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
                                        <strong>
                                            Departure:
                                        </strong>

                                        <?php
                                        echo htmlspecialchars(
                                            date(
                                                "d/m/Y H:i",
                                                strtotime(
                                                    $flight[
                                                        "departure_datetime"
                                                    ]
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
                                                    $flight[
                                                        "arrival_datetime"
                                                    ]
                                                )
                                            )
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
                                        <strong>
                                            Available seats:
                                        </strong>

                                        <?php
                                        echo (int)
                                            $flight["available_seats"];
                                        ?>
                                    </p>


                                    <a
                                        class="book-flight-button"
                                        href="book_flight.php?flight_id=<?php
                                        echo (int) $flight["id"];
                                        ?>"
                                    >
                                        Book this flight
                                    </a>

                                </article>

                            <?php } ?>

                        </div>

                    <?php } ?>

                </section>

            <?php } ?>

        </div>

    </section>

</main>

<?php

// Load the shared website footer
require_once __DIR__ . "/includes/footer.php";

?>