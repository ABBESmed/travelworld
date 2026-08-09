<?php

// Load the database connection
require_once __DIR__ . "/config/database.php";

// Create empty variables
$full_name = "";
$email = "";
$password = "";
$confirm_password = "";

$errors = [];
$success_message = "";


// Run this code only when the registration form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get the information sent by the form
    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";


    // Check the full name
    if ($full_name === "") {
        $errors[] = "Full name is required.";
    }


    // Check the email
    if ($email === "") {

        $errors[] = "Email is required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = "Please enter a valid email address.";
    }


    // Check the password
    if ($password === "") {

        $errors[] = "Password is required.";

    } elseif (strlen($password) < 8) {

        $errors[] = "Password must contain at least 8 characters.";
    }


    // Check the password confirmation
    if ($confirm_password === "") {

        $errors[] = "Please confirm your password.";

    } elseif ($password !== $confirm_password) {

        $errors[] = "Passwords do not match.";
    }


    // Check whether the email already exists
    if (empty($errors)) {

        $sql = "
            SELECT id
            FROM users
            WHERE email = ?
        ";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $email
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_bind_result(
            $stmt,
            $existing_user_id
        );


        if (mysqli_stmt_fetch($stmt)) {

            $errors[] =
                "An account already exists with this email.";
        }

        mysqli_stmt_close($stmt);
    }


    // Create the account only when there are no errors
    if (empty($errors)) {

        // Hash the password before saving it
        $hashed_password = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        // Save the new user
        $sql = "
            INSERT INTO users (
                full_name,
                email,
                password
            )
            VALUES (?, ?, ?)
        ";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "sss",
            $full_name,
            $email,
            $hashed_password
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);


        // Show success message
        $success_message =
            "Your account has been created successfully.";


        // Empty the form
        $full_name = "";
        $email = "";
        $password = "";
        $confirm_password = "";
    }
}


// Load the shared website header
require_once __DIR__ . "/includes/header.php";

?>

<main>

    <!-- Registration page -->
    <section class="register-page">

        <div class="register-container">

            <!-- Registration title -->
            <div class="register-title">

                <p>Join us</p>

                <h1>Create your account</h1>

                <span>
                    Enter your information to create
                    a TravelWorld account.
                </span>

            </div>


            <!-- Success message -->
            <?php if ($success_message !== "") { ?>

                <div class="register-success">

                    <p>
                        <?php
                        echo htmlspecialchars(
                            $success_message
                        );
                        ?>
                    </p>

                </div>

            <?php } ?>


            <!-- Validation errors -->
            <?php if (!empty($errors)) { ?>

                <div class="register-errors">

                    <?php foreach ($errors as $error) { ?>

                        <p>
                            <?php
                            echo htmlspecialchars($error);
                            ?>
                        </p>

                    <?php } ?>

                </div>

            <?php } ?>


            <!-- Registration form -->
            <form
                method="POST"
                class="register-form"
            >

                <!-- Full name -->
                <div class="register-form-group">

                    <label for="full_name">
                        Full name
                    </label>

                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        value="<?php
                        echo htmlspecialchars($full_name);
                        ?>"
                        required
                    >

                </div>


                <!-- Email -->
                <div class="register-form-group">

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php
                        echo htmlspecialchars($email);
                        ?>"
                        required
                    >

                </div>


                <!-- Password -->
                <div class="register-form-group">

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


                <!-- Confirm password -->
                <div class="register-form-group">

                    <label for="confirm_password">
                        Confirm password
                    </label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        required
                    >

                </div>


                <!-- Create account button -->
                <button
                    type="submit"
                    class="register-submit-button"
                >
                    Create account
                </button>

            </form>


            <p class="register-login-link">

                Already have an account?

                <a href="login.php">
                    Login
                </a>

            </p>

        </div>

    </section>

</main>

<?php

// Load the shared website footer
require_once __DIR__ . "/includes/footer.php";

?>