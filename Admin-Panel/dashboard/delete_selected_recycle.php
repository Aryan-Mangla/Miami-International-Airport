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

        // Construct the delete query
        $deleteQuery = "DELETE FROM recycle_bin WHERE id IN ($idString)";

        // Execute the delete query
        $result = mysqli_query($conn, $deleteQuery);

        if ($result) {
            echo "Selected items have been deleted successfully.";
        } else {
            echo "Error: " . mysqli_error($conn);
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
