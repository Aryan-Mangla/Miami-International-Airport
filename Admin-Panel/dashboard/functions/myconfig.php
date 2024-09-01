
<?php

session_start(); // Start the session to store user authentication status

$servername = '127.0.0.1';
$username = 'u291190896_miamiuser';
$password = 'Hello#@4001';
$dbname = 'u291190896_miami';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
