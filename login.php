<?php
session_start(); // A session lets PHP remember that the user logged in while they move between pages. it help to remember the user

// Now we connect login.php to the database.
require_once __DIR__ ."/config/database.php";

// Now we create an empty array for login errors, $errors is the list where PHP will store those messages.
$errors = [];

// Now we create a variable to store the email entered in the login form
$email = "";

// Now we create a variable for the password entered in the login form
$password = "";

// Now we tell PHP to process the login form only when the user submits it

if ($_SERVER["REQUEST_METHOD"] === "POST"){
    $email = trim($_POST["email"] ?? ""); // ?? "" Use an empty string if the email was not submitted.

    // get the submitted password
    $password = $_POST["password"] ?? "";

    // now we validate the login email
    if ($email === "") {
        $errors[] = "Email is required.";
    }

    // Now we check whether the password field is empty
    if ($password === ""){
        $errors[] = "Password is required.";
    }

    // if the email and password fields contain values
    if (empty($errors)) {
        // now we create an sql instruction that searches for user by email 
        $sql = "SELECT id, full_name, email, password FROM users WHERE email = ?";

        // now we prepare that SQL query safely before we attach the user’s email
        $stmt = mysqli_prepare($conn, $sql);

        // Now we attach the submitted email to the ? placeholder
        mysqli_stmt_bind_param($stmt, "s", $email);

        // Now we execute the prepared login query
        mysqli_stmt_execute($stmt);

        // When MySQL returns a user, place each returned column into its matching PHP variable. with this the variables are connected with each column (PHP connects each column to an empty variable)
        mysqli_stmt_bind_result($stmt, $user_id, $user_full_name, $user_email, $stored_password_hash);


        // mysqli_stmt_fetch() fills those boxes When PHP executes it copies the row values into the connected variables
        // Because email is unique in your database, this login query should find either one row : user exists , no row : user does not exist that why we use if
        if (mysqli_stmt_fetch($stmt)){
            // we check whether the password entered by the user matches the protected password stored in MySQL
            if (password_verify($password, $stored_password_hash)){
                session_regenerate_id(true); // After a successful login, this function creates a new session identifier, true mean delete the old session identifier we use it to protects the login against session fixation attacks

                // Now we store the logged-in user’s ID inside the session $_SESSION is associative array used to remember information between pages
                $_SESSION["user_id"] = $user_id;
                $_SESSION["full_name"] = $user_full_name;
                $_SESSION["email"] = $user_email;

                // Now we close the login SELECT statement because we already retrieved the user information
                mysqli_stmt_close($stmt);

                // now we use header to redirect the user to index.php (homepage)
                header("Location: index.php");
                exit; //  Stop executing login.php immediately
            }else{
                // Email exists, but password is wrong
                $errors[] = "Email or password is incorrect.";
            }
        }else{
            // Email does not exist, We deliberately do not reveal whether an email is registered. That prevents someone from testing many email addresses to discover user accounts
            $errors[] = "Email or password is incorrect.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TravelWorld</title>
</head>
<body>
    <main>
        <h1>Login</h1>

        <p>Sign in to your TravelWorld account.</p>

        <?php if(!empty($errors)){ ?>
                <div class="errors">
                    <?php foreach($errors as $error) { ?>
                        <p> <?php echo htmlspecialchars($error) ?></p>
                    <?php  } ?>
                </div>
       <?php } ?>

        <!-- create the login form -->

        <form action="" method="POST">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email) ?>" required> <!-- Keeps the email visible after a failed login -->

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>

        <p>Don't have an account? <a href="register.php">Create one</a></p>
    </main>
</body>
</html>