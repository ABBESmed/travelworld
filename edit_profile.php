<?php

// Start the session so this page knows which user is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Protect the page from visitors who are not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}


// Connect to the database
require_once __DIR__ . "/config/database.php";


// Get the logged-in user's ID
$user_id = (int) $_SESSION["user_id"];


// Prepare messages
$errors = [];
$success_message = "";


// Retrieve the user's current information
$sql = "
    SELECT
        full_name,
        email,
        profile_picture
    FROM users
    WHERE id = ?
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result(
    $stmt,
    $full_name,
    $email,
    $profile_picture
);

$user_found = mysqli_stmt_fetch($stmt);

mysqli_stmt_close($stmt);


// If the user cannot be found, return to login
if (!$user_found) {
    header("Location: login.php");
    exit;
}


// Process the form only when it is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get the submitted information
    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");

    $new_password = $_POST["new_password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    /*
    $_FILES does not contain the image directly.

    It contains information about the uploaded file:
    name
    tmp_name
    error
    size
    */
    $picture_file = $_FILES["profile_picture"] ?? null;


    // Validate the full name
    if ($full_name === "") {
        $errors[] = "Full name is required.";
    }


    // Validate the email
    if ($email === "") {

        $errors[] = "Email is required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = "Email format is invalid.";
    }


    /*
    Validate the optional password change.

    If the user leaves both password fields empty,
    the current password stays unchanged.
    */
    if ($new_password !== "" || $confirm_password !== "") {

        if ($new_password === "") {
            $errors[] = "New password is required.";
        }

        if ($confirm_password === "") {
            $errors[] = "Password confirmation is required.";
        }

        if (
            $new_password !== ""
            && strlen($new_password) < 8
        ) {
            $errors[] =
                "New password must contain at least 8 characters.";
        }

        if (
            $new_password !== ""
            && $confirm_password !== ""
            && $new_password !== $confirm_password
        ) {
            $errors[] = "Passwords do not match.";
        }
    }


    /*
    Validate the profile picture only when
    the user selected a new file.
    */
    $picture_type = null;

    if (
        $picture_file !== null
        && $picture_file["error"] !== UPLOAD_ERR_NO_FILE
    ) {

        // Check that PHP received the file successfully
        if ($picture_file["error"] !== UPLOAD_ERR_OK) {

            $errors[] =
                "An error occurred while uploading the profile picture.";

        } else {

            // Image formats accepted by the website
            $allowed_types = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];


            // Detect the real file type
            $picture_type =
                mime_content_type($picture_file["tmp_name"]);


            // Check the image type
            if (!in_array($picture_type, $allowed_types, true)) {

                $errors[] =
                    "Only JPG, PNG, and WEBP pictures are allowed.";
            }


            // Maximum size: 2 MB
            if ($picture_file["size"] > 2 * 1024 * 1024) {

                $errors[] =
                    "Profile picture must not exceed 2 MB.";
            }
        }
    }


    // Check whether another account already uses the email
    if (empty($errors)) {

        $sql = "
            SELECT id
            FROM users
            WHERE email = ?
            AND id != ?
        ";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $email,
            $user_id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_bind_result(
            $stmt,
            $existing_user_id
        );


        if (mysqli_stmt_fetch($stmt)) {

            $errors[] =
                "Email is already used by another account.";
        }

        mysqli_stmt_close($stmt);
    }


    /*
    Save the new profile picture only when
    all validation has passed.
    */
    $new_picture_name = null;

    if (
        empty($errors)
        && $picture_file !== null
        && $picture_file["error"] === UPLOAD_ERR_OK
    ) {

        /*
        Convert the MIME type into a normal extension.
        */
        $extensions = [
            "image/jpeg" => "jpg",
            "image/png" => "png",
            "image/webp" => "webp"
        ];

        $extension = $extensions[$picture_type];


        // Create a unique filename
        $new_picture_name =
            "user_"
            . $user_id
            . "_"
            . time()
            . "."
            . $extension;


        // Create the complete destination
        $picture_destination =
            __DIR__
            . "/uploads/profiles/"
            . $new_picture_name;


        // Move the image from PHP's temporary folder
        if (
            !move_uploaded_file(
                $picture_file["tmp_name"],
                $picture_destination
            )
        ) {

            $errors[] =
                "The profile picture could not be saved.";
        }
    }


    // Update the profile when there are no errors
    if (empty($errors)) {

        // Update the full name and email
        $sql = "
            UPDATE users
            SET full_name = ?, email = ?
            WHERE id = ?
        ";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ssi",
            $full_name,
            $email,
            $user_id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);


        // Update the picture only when a new picture was uploaded
        if ($new_picture_name !== null) {

            $sql = "
                UPDATE users
                SET profile_picture = ?
                WHERE id = ?
            ";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $new_picture_name,
                $user_id
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);


            // Keep the PHP variable synchronized
            $profile_picture = $new_picture_name;
        }


        // Update the password only when a new password was entered
        if ($new_password !== "") {

            // Hash the new password before saving it
            $password_hash = password_hash(
                $new_password,
                PASSWORD_DEFAULT
            );


            /*
            Your users table uses the column
            called "password".
            */
            $sql = "
                UPDATE users
                SET password = ?
                WHERE id = ?
            ";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $password_hash,
                $user_id
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);
        }


        // Keep the session synchronized with the database
        $_SESSION["full_name"] = $full_name;
        $_SESSION["email"] = $email;


        // Success message
        $success_message =
            "Profile updated successfully.";
    }
}


// Load the shared website header
require_once __DIR__ . "/includes/header.php";

?>

<main>

    <!-- Edit profile page -->
    <section class="edit-profile-page">

        <div class="edit-profile-container">


            <!-- Page title -->
            <div class="edit-profile-title">

                <p>My account</p>

                <h1>Edit Profile</h1>

                <span>
                    Update your personal information
                    and profile picture.
                </span>

            </div>


            <!-- Success message -->
            <?php if ($success_message !== "") { ?>

                <div class="edit-profile-success">

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

                <div class="edit-profile-errors">

                    <?php foreach ($errors as $error) { ?>

                        <p>
                            <?php
                            echo htmlspecialchars($error);
                            ?>
                        </p>

                    <?php } ?>

                </div>

            <?php } ?>


            <!-- Update form -->
            <form
                method="POST"
                enctype="multipart/form-data"
                class="edit-profile-form"
            >


                <!-- Current profile picture -->
                <?php if (!empty($profile_picture)) { ?>

                    <div class="edit-current-picture">

                        <img
                            src="uploads/profiles/<?php
                            echo htmlspecialchars(
                                $profile_picture
                            );
                            ?>"
                            alt="Current profile picture"
                        >

                    </div>

                <?php } ?>


                <!-- Full name -->
                <div class="edit-profile-form-group">

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
                <div class="edit-profile-form-group">

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


                <!-- New password -->
                <div class="edit-profile-form-group">

                    <label for="new_password">
                        New password
                    </label>

                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                    >

                </div>


                <!-- Confirm password -->
                <div class="edit-profile-form-group">

                    <label for="confirm_password">
                        Confirm new password
                    </label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                    >

                </div>


                <p class="edit-password-help">
                    Leave the password fields empty
                    to keep your current password.
                </p>


                <!-- Profile picture -->
                <div class="edit-profile-form-group">

                    <label for="profile_picture">
                        Profile picture
                    </label>

                    <input
                        type="file"
                        id="profile_picture"
                        name="profile_picture"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                </div>


                <p class="edit-picture-help">
                    Leave this field empty to keep
                    your current profile picture.
                </p>


                <!-- Form buttons -->
                <div class="edit-profile-actions">

                    <button
                        type="submit"
                        class="edit-profile-submit"
                    >
                        Update profile
                    </button>

                    <a
                        href="profile.php"
                        class="edit-profile-back"
                    >
                        Back to my profile
                    </a>

                </div>

            </form>

        </div>

    </section>

</main>

<?php

// Load the shared website footer
require_once __DIR__ . "/includes/footer.php";

?>