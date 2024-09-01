<?php
// Enable error reporting for debugging (uncomment for development)
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include 'config.php';

// Check if the connection is established
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if all required POST data is present
    if (!isset($_POST['comment'], $_POST['blog_id'], $_POST['name'], $_POST['email'], $_POST['rating'])) {
        die("Required POST data is missing");
    }

    // Retrieve and sanitize POST data
    $comment = $conn->real_escape_string($_POST['comment']);
    $blog_id = intval($_POST['blog_id']); // Convert blog_id to integer
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $rating = intval($_POST['rating']); // Convert rating to integer

    // Validate rating (assuming rating should be between 1 and 5)
    if ($rating < 1 || $rating > 5) {
        die("Invalid rating value");
    }

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO comment_section (comment, blog_id, name, email, rating) VALUES (?, ?, ?, ?, ?)");
    
    // Check if the preparation is successful
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters to the prepared statement
    $stmt->bind_param("sisis", $comment, $blog_id, $name, $email, $rating);

    // Execute the statement and handle redirection based on success or failure
    if ($stmt->execute()) {
        header("Location: index.php?id=$blog_id&status=success");
    } else {
        header("Location: index.php?id=$blog_id&status=error");
    }

    $stmt->close(); // Close the statement
    exit(); // Ensure no further processing is done after redirect
}

// Close the database connection
$conn->close();
?>
