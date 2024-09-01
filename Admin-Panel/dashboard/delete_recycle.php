<?php
require_once "functions/myconfig.php";

// Check if the ID is set and is a valid number
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid page ID.");
}

$page_id = $_GET['id'];

// Delete the page from the database
$sql = "DELETE FROM recycle_bin WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $page_id);

if ($stmt->execute()) {
    header("Location: recycle.php?message=Page deleted successfully"); // Redirect to the pages list after deletion
    exit();
} else {
    echo "Error deleting page: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
