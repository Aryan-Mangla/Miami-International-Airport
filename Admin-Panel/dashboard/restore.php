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
    // Insert the page data from recycle_bin into pages table
    $sql_insert = "INSERT INTO pages SELECT * FROM recycle_bin WHERE id = ?";
    $stmt_insert = $conn->prepare($sql_insert);
    if ($stmt_insert === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt_insert->bind_param("i", $page_id);
    if (!$stmt_insert->execute()) {
        throw new Exception("Error restoring page to pages table: " . $stmt_insert->error);
    }

    // Check if any rows were inserted
    if ($stmt_insert->affected_rows === 0) {
        throw new Exception("No rows restored from recycle_bin. Page ID may not exist.");
    }

    // Delete the page data from recycle_bin
    $sql_delete = "DELETE FROM recycle_bin WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    if ($stmt_delete === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt_delete->bind_param("i", $page_id);
    if (!$stmt_delete->execute()) {
        throw new Exception("Error deleting page from recycle_bin: " . $stmt_delete->error);
    }

    // Commit the transaction
    $conn->commit();

    header("Location: recycle.php?message=Page restored successfully"); // Redirect to recycle bin list after restoration
    exit();
} catch (Exception $e) {
    // Rollback the transaction in case of an error
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}

$stmt_insert->close();
$stmt_delete->close();
$conn->close();
?>
