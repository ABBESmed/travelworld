<?php

// Start the session so this page knows which user is logged in.
session_start();

// Protect the page from visitors who are not logged in.
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// Connect to the database.
require_once __DIR__ . "/config/database.php";

// Get the logged-in user's ID.
$user_id = $_SESSION["user_id"];

// Prepare messages.
$errors = [];
$success_message = "";

// Retrieve the user's current information.
$sql = "SELECT full_name, email, profile_picture FROM users WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $user_id);

mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result(
    $stmt,
    $full_name,
    $email,
    $profile_picture
);

mysqli_stmt_fetch($stmt);

mysqli_stmt_close($stmt);

// Process the form only when it is submitted.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");

    $new_password = $_POST["new_password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    // Get the uploaded file information, When no file information exists, store null
    $picture_file = $_FILES["profile_picture"] ?? null; // $picture_file does not contain the image itself. it contains information about image such as: name, tmp_name, error = 0, size






    // Validate the full name.
    if ($full_name === "") {
        $errors[] = "Full name is required.";
    }

    // Validate the email.
    if ($email === "") {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email format is invalid.";
    }

    // Validate the optional password change.
    if ($new_password !== "" || $confirm_password !== "") {
        if ($new_password === "") {
            $errors[] = "New password is required.";
        }

        if ($confirm_password === "") {
            $errors[] = "Password confirmation is required.";
        }

        if ($new_password !== "" && strlen($new_password) < 8) {
            $errors[] = "New password must contain at least 8 characters.";
        }

        if (
            $new_password !== ""
            && $confirm_password !== ""
            && $new_password !== $confirm_password
        ) {
            $errors[] = "Passwords do not match.";
        }
    }

    // now we check whether PHP received information about the input from the file input (Does $picture_file contain file-upload information? true PHP receives an array and entre the block)
    if ($picture_file !== null) {

        // here we see if the file arrive successfully ( for successful upload 0 === 0), $picture_file is an associative array containing file information $picture_file["error"] = 0, UPLOAD_ERR_OK = 0 (built-in PHP constant)
        if ($picture_file["error"] === UPLOAD_ERR_OK) {

            // List the image types (formats) accepted by our website
                $allowed_types = [
                    "image/jpeg",
                    "image/png",
                    "image/webp"
                ];

                // now we check the real type of the uploaded file
                // $picture_file["tmp_name"] is the temporary location where PHP placed the uploaded file (C:\xampp\tmp\php123.tmp)
                // mime_content_type(...) opens that temporary file and checks what it really is
                $picture_type = mime_content_type($picture_file["tmp_name"]);  // Examine the uploaded file and remember its real file type

                // Now we check whether the detected picture type exists inside the allowed list
                if (!in_array($picture_type, $allowed_types, true)) {

                    // Add an error when the file is not JPG, PNG, or WEBP
                    $errors[] = "Only JPG, PNG, and WEBP pictures are allowed.";
                }

                // Now we check that the picture is not larger than 2 MB
                if ($picture_file["size"] > 2 * 1024 * 1024) {

                    // Add an error when the picture is too large
                    $errors[] = "Profile picture must not exceed 2 MB.";
                }

                // Continue only when the form and picture contain no errors
                if (empty($errors)) {

                    // Convert "image/png" into "png", "image/jpeg" into "jpeg", etc.
                    $extension = str_replace("image/", "", $picture_type);

                    // Now we create a unique filename for the new profile picture, time() is a built-in PHP function that returns the current Unix timestamp
                    $new_picture_name = "user_" . $user_id . "_" . time() . "." . $extension;

                    // Now we create the complete location where the picture will be saved
                    $picture_destination = __DIR__ . "/uploads/profiles/" . $new_picture_name;

                    // Now we actually move the picture from PHP’s temporary folder into uploads/profiles/
                    if (move_uploaded_file($picture_file["tmp_name"], $picture_destination)) {

                        // We save the new picture filename in the logged-in user's database row
                        $sql = "UPDATE users SET profile_picture = ? WHERE id = ?";

                        // Now we prepare that SQL query safely (prepares the instruction before we insert the real filename and user ID)
                        $stmt = mysqli_prepare($conn, $sql);

                        // Now we connect the real filename and user ID to the two ? placeholders
                        mysqli_stmt_bind_param($stmt, "si", $new_picture_name, $user_id);

                        // Now we execute the database update (actually this line sends the instruction to MySQL)
                        mysqli_stmt_execute($stmt);

                        // Now close the prepared statement after MySQL finishes updating the picture filename
                        mysqli_stmt_close($stmt);

                        // Now we keep the PHP variable synchronized with the new filename
                        $profile_picture = $new_picture_name;
                    }else {
                        $errors[] = "The profile picture could not be saved.";
                    }
                }
        }

        // Now we handle this situation: The user selected a file, but PHP could not upload it successfully
        elseif ($picture_file["error"] !== UPLOAD_ERR_NO_FILE){
            $errors[] = "An error occurred while uploading the profile picture.";
        }
    }
    



    // Check whether another account already uses the email.
    if (empty($errors)) {
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";

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
            $errors[] = "Email is already used by another account.";
        }

        mysqli_stmt_close($stmt);
    }

    // Update the profile when there are no errors.
    if (empty($errors)) {
        $sql = "UPDATE users SET full_name = ?, email = ? WHERE id = ?";

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

        // Update the password only when a new password was entered.
        if ($new_password !== "") {
            $password_hash = password_hash(
                $new_password,
                PASSWORD_DEFAULT
            );

            $sql = "UPDATE users SET password = ? WHERE id = ?";

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

        // Keep the session synchronized with the database.
        $_SESSION["full_name"] = $full_name;
        $_SESSION["email"] = $email;

        $success_message = "Profile updated successfully.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - TravelWorld</title>
</head>
<body>
    <main>
        <h1>Edit Profile</h1>

        <!--Does the success message contain text? $success_message = ""; So the condition is false, and nothing appears-->
        <?php if ($success_message !== "") { ?>
            <p><?php echo htmlspecialchars($success_message) ?></p>
        <?php } ?>

        <!-- Now prepare the page to display validation errors -->
         <?php if (!empty($errors)) { ?>
            <div class="errors">
                <?php foreach($errors as $error) { ?>
                    <p><?php echo htmlspecialchars($error) ?></p>
                <?php } ?>
            </div>
         <?php } ?>

         <!-- Now we create the update form -->
          <!-- enctype="multipart/form-data" allows the browser to send the actual profile-picture file to php -->
          <form action="" method="POST" enctype="multipart/form-data">
            <label for="full_name">Full name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name) ?>" required>

            <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email) ?>" required>

            <label for="new_password">New password</label>
                <input type="password" id="password" name="new_password">

            <label for="confirm_password">Confirm new password</label>
                <input type="password" id="confirm_password" name="confirm_password">

            <label for="profile_picture">Profile picture</label>
                <input type="file" id="profile_picture" name="profile_picture" accept=".jpg,.jpeg,.png,.webp">

            <p>Leave this field empty to keep your current profile picture.</p>

                <button type="submit">Update profile</button>
          </form>

          <p><a href="profile.php">Back to my profile</a></p>
    </main>
</body>
</html>