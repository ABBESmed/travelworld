<?php

// Start the session so we can remember the logged-in user
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load the database connection
require_once __DIR__ . "/config/database.php";

// Create empty variables
$email = "";
$errors = [];


// Run the login code only when the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get the information sent by the form
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";


    // Check the email
    if ($email === "") {

        $errors[] = "Email is required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = "Please enter a valid email address.";
    }


    // Check the password
    if ($password === "") {
        $errors[] = "Password is required.";
    }


    // Continue only when there are no errors
    if (empty($errors)) {

        // Find the user using the submitted email
        $sql = "
            SELECT
                id,
                full_name,
                email,
                password
            FROM users
            WHERE email = ?
        ";

        // Prepare the SQL query
        $stmt = mysqli_prepare($conn, $sql);

        // Send the email to the query
        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $email
        );

        // Execute the query
        mysqli_stmt_execute($stmt);

        // Get the result
        $result = mysqli_stmt_get_result($stmt);

        // Convert the result into a PHP array
        $user = mysqli_fetch_assoc($result);

        // Close the prepared statement
        mysqli_stmt_close($stmt);


        /*
        Check that:

        1. The user exists
        2. The entered password matches the hashed password
           stored in the password column
        */
        if (
            $user
            && password_verify(
                $password,
                $user["password"]
            )
        ) {

            // Save user information in the session
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["email"] = $user["email"];

            // Send the user to the homepage
            header("Location: index.php");
            exit;

        } else {

            $errors[] = "Incorrect email or password.";
        }
    }
}


// Load the shared website header
require_once __DIR__ . "/includes/header.php";

?>

<main>

    <!-- Login page -->
    <section class="login-page">

        <div class="login-container">

            <!-- Login title -->
            <div class="login-title">

                <p>Welcome back</p>

                <h1>Login to TravelWorld</h1>

                <span>
                    Sign in to your TravelWorld account.
                </span>

            </div>


            <!-- Display login errors -->
            <?php if (!empty($errors)) { ?>

                <div class="login-errors">

                    <?php foreach ($errors as $error) { ?>

                        <p>
                            <?php echo htmlspecialchars($error); ?>
                        </p>

                    <?php } ?>

                </div>

            <?php } ?>


            <!-- Login form -->
            <form
                method="POST"
                class="login-form"
            >

                <!-- Email -->
                <div class="login-form-group">

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo htmlspecialchars($email); ?>"
                        required
                    >

                </div>


                <!-- Password -->
                <div class="login-form-group">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                    >

                </div>


                <!-- Login button -->
                <button
                    type="submit"
                    class="login-button"
                >
                    Login
                </button>

            </form>


            <!-- Registration link -->
            <p class="login-register-link">

                Don't have an account?

                <a href="register.php">
                    Create one
                </a>

            </p>

        </div>

    </section>

</main>

<?php

// Load the shared website footer
require_once __DIR__ . "/includes/footer.php";

?>