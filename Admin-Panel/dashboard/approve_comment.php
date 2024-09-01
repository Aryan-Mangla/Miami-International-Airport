<?php
// Include your database connection file
require_once "functions/db.php";

if (isset($_GET["id"])) {
    $id = $_GET['id'];
    $sql = "UPDATE comment_section SET approved = 1 WHERE id = $id";
    // Execute the query
    if (mysqli_query($conn, $sql)) {
        header("Location: comments.php?approved=true");
        exit;
    } else {
        header("Location: comments.php?approval_error=true");
        exit;
    }
}
?>
