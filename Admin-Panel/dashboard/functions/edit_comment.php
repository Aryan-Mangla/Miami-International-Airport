

<?php 

 
require_once "db.php";



// Check if ID is provided in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch comment data based on ID
    $sql = "SELECT * FROM comment_section WHERE id = '$id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Fetch data and store it in $data variable
        $data = $result->fetch_assoc();

        // Extract data for pre-filling form fields
        $name = $data['name'];
        $comment = $data['comment'];
       
        $date = $data['date'];
        $rating = $data['rating'];
        $approved = $data['approved'];
    } else {
        // Handle the case where no data is found for the provided ID
        echo "No data found for the provided ID.";
        exit; // Stop further execution
    }
} else {
    // Handle the case where no ID is provided
    echo "No ID provided.";
    exit; // Stop further execution
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $id = $_POST['id'];
    $name = $_POST['name'];
    $comment = $_POST['comment'];
   
    $date = $_POST['date'];
    $rating = $_POST['rating'];
    $approved = isset($_POST['approved']) ? 1 : 0;

    // Update comment in the database
    $sql = "UPDATE comment_section SET name = '$name', comment = '$comment',  date = '$date', rating = '$rating', approved = '$approved' WHERE id = '$id'";
    if ($conn->query($sql) === TRUE) {
        // If update is successful, redirect to view the updated comment
        header("Location: ../comments.php");
        exit;
    } else {
        // If update fails, display an error message
        echo "Error updating record: " . $conn->error;
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Comment</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php // include 'header.php'; ?>

    <!-- Form for editing comment -->
    <div class="container mt-5">
        <h2 class="text-center fw-bold my-4">Edit Comment</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $id; ?>" method="POST">
            <!-- Hidden input field to store ID -->
            <input type="hidden" name="id" value="<?php echo $id; ?>">

            <!-- Name input -->
            <div class="form-group">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>

            <!-- Comment input -->
            <div class="form-group">
                <label for="comment" class="form-label">Comment</label>
                <textarea class="form-control" id="comment" name="comment" rows="5" required><?php echo htmlspecialchars($comment); ?></textarea>
            </div>

            
            <!-- Date input -->
            <div class="form-group">
                <label for="date" class="form-label">Date</label>
                <input type="datetime-local" class="form-control" id="date" name="date" value="<?php echo date('Y-m-d\TH:i', strtotime($date)); ?>" required>
            </div>

            <!-- Rating input -->
            <div class="form-group">
                <label for="rating" class="form-label">Rating</label>
                <input type="number" class="form-control" id="rating" name="rating" value="<?php echo $rating; ?>" required>
            </div>

            <!-- Approved checkbox -->
            <div class="form-group form-check">
                <input class="form-check-input" type="checkbox" id="approved" name="approved" <?php echo $approved ? 'checked' : ''; ?>>
                <label class="form-check-label" for="approved">Approved</label>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>

    <!-- Footer section -->
    <?php // include 'footer.php'; ?>

    <!-- JavaScript links -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
