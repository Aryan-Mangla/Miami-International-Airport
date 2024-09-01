<?php
require_once "functions/db.php";

// Check if IDs are passed in the query string
if (isset($_GET['ids'])) {
    // Get the IDs from the query string
    $ids = explode(',', $_GET['ids']);

    // Check the connection
    if ($conn) {
        // Escape each ID to prevent SQL injection
        $escapedIds = array_map('intval', $ids);

        // Convert the array of IDs into a comma-separated string
        $idString = implode(',', $escapedIds);

        // Start a transaction
        mysqli_begin_transaction($conn);

        try {
            // Move data to recycle_bin
            $insertQuery = "INSERT INTO recycle_bin SELECT * FROM pages WHERE id IN ($idString)";
            $resultInsert = mysqli_query($conn, $insertQuery);
            if (!$resultInsert) {
                throw new Exception("Error moving data to recycle_bin: " . mysqli_error($conn));
            }

            // Delete data from pages
            $deleteQuery = "DELETE FROM pages WHERE id IN ($idString)";
            $resultDelete = mysqli_query($conn, $deleteQuery);
            if (!$resultDelete) {
                throw new Exception("Error deleting data from pages: " . mysqli_error($conn));
            }

            // Commit the transaction
            mysqli_commit($conn);
            echo "Selected items have been deleted and moved to recycle bin successfully.";
        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            mysqli_rollback($conn);
            echo "Error: " . $e->getMessage();
        }

        // Close the database connection
        mysqli_close($conn);
    } else {
        echo "Error: Unable to connect to the database.";
    }
} else {
    echo "Error: No IDs were provided for deletion.";
}
?>
