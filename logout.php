<?php
session_start();

// Now we empty all information stored inside the session This line replaces the session data with an empty array
// Forget all information about the logged-in user
$_SESSION = [];

// Now we destroy the session itself deletes the session from the server
session_destroy();

// Now we redirect the user to the login page after logout
header("Location: login.php");

// Now we stop logout.php immediately after the redirect
exit;
