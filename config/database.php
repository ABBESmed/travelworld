<?php

// with this i Connect PHP to the travelworld_db MYSQL database

$db_host="127.0.0.1";  // this mean that mysql database is running on my computer
$db_user="root";  // connect to mysql using the username root 
$db_password=""; // connect to mysql without password
$db_name="travelworld_db"; // open the database named travelworld_db


/* here i will create a variable called $conn that will store the database connection

and i will do it using mysqli_connect() built-in php function and also this function need information

or arguments to connect to mysql*/

$conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);

// now i will check if the connection succeeded

if(!$conn){  // !$conn mean there is no valid db connection, so if the connection failed do this

    // here the function die() stops the php page, and mysqli_connect_error() its function returns the reason why mysql connection failed

    die("Database connection failed: " . mysqli_connect_error());
}

// here i will use mysqli_set_charset() so i can configures how text format travels through that connection

mysqli_set_charset($conn, "utf8mb4");

