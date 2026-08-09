<?php

// Load the database connection
require_once __DIR__ . "/config/database.php";

// Create empty variables
$full_name = "";
$email = "";
$subject = "";
$message = "";

$errors = [];
$success_message = "";


// Run this code only when the contact form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get the information sent by the form
    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $message = trim($_POST["message"] ?? "");


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


    // Check the subject
    if ($subject === "") {
        $errors[] = "Subject is required.";
    }


    // Check the message
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

        // Prepare the SQL query
        $stmt = mysqli_prepare($conn, $sql);

        // ssss means the four values are strings
        mysqli_stmt_bind_param(
            $stmt,
            "ssss",
            $full_name,
            $email,
            $subject,
            $message
        );

        // Execute the query
        mysqli_stmt_execute($stmt);

        // Close the prepared statement
        mysqli_stmt_close($stmt);


        // Show a success message
        $success_message =
            "Your message has been sent successfully.";


        // Empty the form after successful submission
        $full_name = "";
        $email = "";
        $subject = "";
        $message = "";
    }
}


// Load the shared header
require_once __DIR__ . "/includes/header.php";

?>

<main>

    <!-- Contact page -->
    <section class="contact-page">

        <div class="contact-container">


            <!-- Contact page title -->
            <div class="contact-title">

                <p>Get in touch</p>

                <h1>Contact Us</h1>

                <span>
                    Have a question about TravelWorld?
                    Send us a message.
                </span>

            </div>


            <!-- Success message -->
            <?php if ($success_message !== "") { ?>

                <div class="contact-success">

                    <p>
                        <?php
                        echo htmlspecialchars($success_message);
                        ?>
                    </p>

                </div>

            <?php } ?>


            <!-- Validation errors -->
            <?php if (!empty($errors)) { ?>

                <div class="contact-errors">

                    <?php foreach ($errors as $error) { ?>

                        <p>
                            <?php
                            echo htmlspecialchars($error);
                            ?>
                        </p>

                    <?php } ?>

                </div>

            <?php } ?>


            <!-- Contact form -->
            <form
                method="POST"
                class="contact-form"
            >


                <!-- Full name -->
                <div class="contact-form-group">

                    <label for="full_name">
                        Full name
                    </label>

                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        value="<?php echo htmlspecialchars($full_name); ?>"
                        required
                    >

                </div>


                <!-- Email -->
                <div class="contact-form-group">

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


                <!-- Subject -->
                <div class="contact-form-group">

                    <label for="subject">
                        Subject
                    </label>

                    <input
                        type="text"
                        id="subject"
                        name="subject"
                        value="<?php echo htmlspecialchars($subject); ?>"
                        required
                    >

                </div>


                <!-- Message -->
                <div class="contact-form-group">

                    <label for="message">
                        Message
                    </label>

                    <textarea
                        id="message"
                        name="message"
                        rows="6"
                        required
                    ><?php echo htmlspecialchars($message); ?></textarea>

                </div>


                <!-- Submit button -->
                <button
                    type="submit"
                    class="contact-button"
                >
                    Send message
                </button>

            </form>

        </div>

    </section>

</main>

<?php

// Load the shared footer
require_once __DIR__ . "/includes/footer.php";

?>