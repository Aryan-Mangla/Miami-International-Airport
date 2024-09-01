<?php
require_once "functions/myconfig.php";

// Check if the ID is set and is a valid number
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid page ID.");
}

$page_id = $_GET['id'];

// Start a transaction
$conn->begin_transaction();

try {
    // Insert the page data into the recycle_bin table
    $sql_insert = "INSERT INTO recycle_bin SELECT * FROM pages WHERE id = ?";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("i", $page_id);
    if (!$stmt_insert->execute()) {
        throw new Exception("Error moving page to recycle_bin: " . $stmt_insert->error);
    }

    // Delete the page from the pages table
    $sql_delete = "DELETE FROM pages WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $page_id);
    if (!$stmt_delete->execute()) {
        throw new Exception("Error deleting page: " . $stmt_delete->error);
    }

    // Commit the transaction
    $conn->commit();

    header("Location: mypages.php?message=Page deleted successfully"); // Redirect to the pages list after deletion
    exit();
} catch (Exception $e) {
    // Rollback the transaction in case of an error
    $conn->rollback();
    echo $e->getMessage();
}

$stmt_insert->close();
$stmt_delete->close();
$conn->close();
?>
