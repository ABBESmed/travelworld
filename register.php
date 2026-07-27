<?php
// this page user fills the form and we check the information and protect the password and we add the user to the users table
// register.php must load the db connection cause <e will insert a new user into users table

// Before registering a user, connect this page to the database.
require_once __DIR__ . "/config/database.php";

// then register.php will be able to communicate with mysql

// prepare a place to store validation errors. [] an empty php array that store several values
$errors = [];
$success_message = ""; // Prepare an empty variable for the success message before processing the form.

$full_name = "";  // "" when the user enters his fullname PHP will replace the empty value
$email = "";
$password = "";
$confirm_password = "";

// we check if the submit the form 
if ($_SERVER["REQUEST_METHOD"] === "POST"){
    // Get the values from the submitted form if doesn't exist use an empty string ""
    $full_name = trim($_POST["full_name"] ?? "");  // trim remove outside spaces   ?? "" when exists use the submitted fullname otherwise use an empty string.
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    // now we begin validation

    if ($full_name === ""){
        $errors[] = "Full name is required.";
    }
    if ($email === ""){
        $errors[] = "Email is required.";
    }elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)){ // filter_var php function check whether this value has a valid format ,FILTER_VALIDATE_EMAIL php predefined cconstant the rule used to check it
        $errors[] = "Email format is invalid.";
    }
    if ($password === ""){
        $errors[] = "Password is required.";
    }elseif (strlen($password) < 8){  // strlen is get string length
        $errors[] = "Password must contain at least 8 characters.";
    }
    if ($confirm_password === ""){
        $errors[] = "Password confirmation is required.";
    }elseif ($password !== $confirm_password){
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)){ // empty determine whether a variable is empty
        // we check whether another account already uses this email
        // we create sql instruction and we store it inside the variable $sql, ? its a placeholder because it is safer and protect against SQL injection
        $sql = "SELECT id FROM users WHERE email = ?";
        // we prepare SQL query before executing it , and we stor it inside $stmt (statement)
        // mysqli_prepare() this preapare sql query safely it has two arguments $conn which database connection should use and $sql which sql query should be prepared
        $stmt = mysqli_prepare($conn, $sql);

        // now we attach the submitted email to the ? placeholder
        // with mysqli_stmt_bind_param() i can attach php variables to placeholder in a prepared sql statment need 3 arguments $stmt, "s" mean which data type we are adding string cause email is text, $email should be placed into the placeholder ?
        mysqli_stmt_bind_param($stmt, "s", $email);

        // now we can execute the prepared SQL query using mysqli_stmt_execute() function so we send it statment to mysql and run it
        mysqli_stmt_execute($stmt);

        // now we store the result produced by this prepared statment so we can check whether the email exists
        mysqli_stmt_store_result($stmt);

        // now we check how many users mysql found with that email
        if (mysqli_stmt_num_rows($stmt) > 0){
            $errors[] = "An account with this email already exists.";
        }

        // now we close the prepared statement because the email check query is finished
        mysqli_stmt_close($stmt);

        // this empty is for if PHP may discover a duplicate email and add a new error
        if (empty($errors)){
            // we use the password_hash to protect the password and transforms the real password into a protected value, PASSWORD_DEFAULT use php recommended protection algorithm
           $password_hash = password_hash($password, PASSWORD_DEFAULT);

           // now we create the sql instruction that will add the new user to the users table
           $sql = "INSERT INTO users(full_name, email, password) VALUES (?, ?, ?)";

           // now we prepare the $sql query safely before we attach the users information
           $stmt = mysqli_prepare($conn, $sql);

           // now we attach the 3 php variables to the 3 ? placeholders
           mysqli_stmt_bind_param($stmt, "sss", $full_name, $email, $password_hash);

           // now we execute the prepared INSERT query and check whether it succeeds
           if (mysqli_stmt_execute($stmt)){
            // inside this success block, store a success message
            $success_message = "Account created successfully.";

            // Now we need to empty the temporary php variables or the input field
            $full_name = "";
            $email = "";
            $password = "";
            $confirm_password = "";
           }else{
            $errors[] = "Account creation failed. Please try again.";
           }

           // Now the INSERT statement has finished, whether it succeeded or failed. Close that prepared statement
           mysqli_stmt_close($stmt);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - TravelWorld</title>
</head>
<body>
    <main>
        <h1>Create your account</h1>

        <p>Enter your information to create a TravelWorld account.</p>

        <?php
            // now we create the success message to appear only after an account is created
            if ($success_message !== ""){
                echo "<p>" . htmlspecialchars($success_message) . "<p>"; // htmlspecialchars this makes text safe to display inside html it convert special html characters
            }
        ?>

        <?php 
            // Now we need to read every error stored inside the $errors array we use foreach loop
            if (!empty($errors)){
                echo "<ul>";

                foreach ($errors as $error){
                    echo "<li>" . htmlspecialchars($error) . "</li>";
                }
                echo "</ul>";
            }
        ?>

        <!-- Now we start the registration form -->
        <!-- Send the form information to PHP using the POST request method -->
        <form method="POST" action="">
            <label for="full_name">Full name</label>
            <!-- id connect to the label for cause they are identical and name this what php uses to receive the submitted value -->
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); // Keep the correct information so the user only fixes the field containing the mistake.  ?>">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">

            <label for="password">Password</label>
            <input type="password" id="password" name="password">

            <label for="confirm_password">Confirm password</label>
            <input type="password" id="confirm_password" name="confirm_password">

            <button type="submit">Create account</button>
        </form>
    </main>
</body>
</html>