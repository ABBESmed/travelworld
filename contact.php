<?php

// Start the session so we can recognize a logged-in user
session_start();

// Connect this page to the database
require_once __DIR__ . "/config/database.php";

// Use the logged-in user's information when available
$full_name = $_SESSION["full_name"] ?? "";
$email = $_SESSION["email"] ?? "";

// Prepare the other form values
$subject = "";
$message = "";

// Prepare messages
$errors = [];
$success_message = "";

// Check whether the contact form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Retrieve and clean the values sent by the form
    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $message = trim($_POST["message"] ?? "");

    // Validate the full name
    if ($full_name === "") {
        $errors[] = "Full name is required.";
    }

    // Validate the email
    if ($email === "") {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email address is invalid.";
    }

    // Validate the subject
    if ($subject === "") {
        $errors[] = "Subject is required.";
    }

    // Validate the message
    if ($message === "") {
        $errors[] = "Message is required.";
    }

    // Save the message only when there are no errors
    if (empty($errors)) {

        $sql = "
            INSERT INTO contact_messages (
                full_name,
                email,
                subject,
                message
            )
            VALUES (?, ?, ?, ?)
        ";

        // Prepare the INSERT query
        $stmt = mysqli_prepare($conn, $sql);

        // Connect the form values to the four question marks
        mysqli_stmt_bind_param(
            $stmt,
            "ssss",
            $full_name,
            $email,
            $subject,
            $message
        );

        // Save the contact message
        mysqli_stmt_execute($stmt);

        // Close the prepared statement
        mysqli_stmt_close($stmt);

        // Display a success message
        $success_message = "Your message has been sent successfully.";

        // Empty only the subject and message fields
        $subject = "";
        $message = "";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Contact - TravelWorld</title>
</head>

<body>

    <main>

        <h1>Contact Us</h1>

        <?php if ($success_message !== "") { ?>

            <p>
                <?= htmlspecialchars($success_message) ?>
            </p>

        <?php } ?>

        <?php if (!empty($errors)) { ?>

            <?php foreach ($errors as $error) { ?>

                <p>
                    <?= htmlspecialchars($error) ?>
                </p>

            <?php } ?>

        <?php } ?>

        <form method="POST">

            <div>

                <label for="full_name">
                    Full name
                </label>

                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    value="<?= htmlspecialchars($full_name) ?>"
                    required
                >

            </div>

            <div>

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($email) ?>"
                    required
                >

            </div>

            <div>

                <label for="subject">
                    Subject
                </label>

                <input
                    type="text"
                    id="subject"
                    name="subject"
                    value="<?= htmlspecialchars($subject) ?>"
                    required
                >

            </div>

            <div>

                <label for="message">
                    Message
                </label>

                <textarea
                    id="message"
                    name="message"
                    rows="6"
                    required
                ><?= htmlspecialchars($message) ?></textarea>

            </div>

            <button type="submit">
                Send message
            </button>

        </form>

        <p>
            <a href="index.php">
                Return to home page
            </a>
        </p>

    </main>

</body>

</html>