<?php
// Include your database connection file
require_once "functions/db.php";

if (isset($_GET["id"])) {
    $id = $_GET['id'];
    $sql = "UPDATE comment_section SET approved = 0 WHERE id = $id";
    // Execute the query
    if (mysqli_query($conn, $sql)) {
        header("Location: comments.php?approved=false");
        exit;
    } else {
        header("Location: comments.php?approval_error=true");
        exit;
    }
}
?>