<?php

// Function to establish database connection
function connectDB() {
    // Define database connection details
    $db_host = '127.0.0.1';
    $db_user = 'u291190896_miamiuser';
    $db_pass = 'Hello#@4001';
    $db_name = 'u291190896_miami';

    // Create database connection
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);

    // Set PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $conn;
}

// Function to delete admin by ID
function deleteAdmin($id) {
    try {
        $conn = connectDB(); // Establish database connection
        $sql = "DELETE FROM admin WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        return true; // Deletion successful
    } catch (PDOException $e) {
        return false; // Deletion failed
    } finally {
        // Close the database connection
        $conn = null;
    }
}

// Check if ID is set in POST request
if (isset($_POST["id"])) {
    $id = $_POST["id"];
    if (deleteAdmin($id)) {
        header('Location:../users.php?deleted');
        exit();
    } else {
        header('Location:../users.php?del_error');
        exit();
    }
} else {
    header('Location:../users.php?del_error');
    exit();
}
?>
