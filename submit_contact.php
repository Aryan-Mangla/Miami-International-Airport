<?php
// Enable error reporting for debugging
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include 'config.php'; // Ensure this file contains your database connection details

// Check if the connection is established
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize POST data
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $message = $conn->real_escape_string($_POST['message']);

    // Insert data into the database using prepared statements
    $stmt = $conn->prepare("INSERT INTO contact_form (name, email, phone, message) VALUES (?, ?, ?, ?)");

    // Check if the preparation is successful
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssss", $name, $email, $phone, $message);

    if ($stmt->execute()) {
        // Redirect to a thank you page or display a success message
        header("Location: ../?success=true"); // Create a thank_you.php page or use an alternative
        exit();
    } else {
        // Redirect or display an error message
        echo "Error: " . $stmt->error;
    }

    $stmt->close(); // Close the statement
}

$conn->close(); // Close the database connection
?>
