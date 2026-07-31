<?php

// remembers the logged-in user
session_start();

// connects it to my database

require_once __DIR__ . "/config/database.php";

// retrieve all airports for the search form using this sql query

$sql = "SELECT id, name, city, country FROM airports ORDER BY city ASC";

// execute the sql query

$airports_result = mysqli_query($conn, $sql);

// convert the mysql result into a normal php array so we can use it in the dropdown form menu

$airports = mysqli_fetch_all($airports_result, MYSQLI_ASSOC);