<?php  
// Start the Session
session_start();
require('db.php');

// If the form is submitted
if (isset($_POST['email']) and isset($_POST['password'])){
    // Assigning posted values to variables.
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Checking the values are existing in the database or not
    $query = "SELECT * FROM `admin` WHERE email='$email' and password='$password'";
    $result = mysqli_query($conn, $query) or die(mysqli_error($conn));
    $count = mysqli_num_rows($result);

    // If the posted values are equal to the database values, then session will be created for the user.
    if ($count == 1){
      
        $_SESSION['email'] = $email;
   
    } else {
        // If the login credentials don't match, display an error message.
        $fmsg = "Invalid Login Credentials.";
    }
}

// If the user is logged in, greet the user with a message
if (isset($_SESSION['email'])){
    $email = $_SESSION['email'];
    echo "Hello " . $email . "
";
    echo "This is the Admin Area
";
    echo "<a href='logout.php'>Logout</a>";
} else {
    // When the user visits the page for the first time, display a simple login form.
}
?>
