<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add City Office</title>
    <!-- Add Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once 'config.php'; 
     if(isset($_GET['id'])) {
        $blogc_id = $conn->real_escape_string($_GET['id']);}
    ?>

    <div class="container my-5">
        <h1>Add City Office</h1>
        <form action="city-offices.php" method="post">
        <div class="form-group">
            <label for="blog_id">Blog ID:</label>
            <input type="number" name="blog_id" id="blog_id" class="form-control text-dark" required value="<?php echo $blogc_id ?>" readonly>
        </div>
        <div class="form-group">
            <label for="office_text">Office Text:</label>
            <textarea name="office_text" id="office_text" class="form-control" rows="3" required></textarea>
        </div>
        <div class="form-group">
            <label for="office_link">Office Link:</label>
            <input type="url" name="office_link" id="office_link" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary mt-3">Add City Office</button>
    </form>
    </div>
<?php 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $blog_id = intval($_POST['blog_id']);
    $office_text = $conn->real_escape_string($_POST['office_text']);
    $office_link = $conn->real_escape_string($_POST['office_link']);

    $sql = "INSERT INTO city_offices (blog_id, office_text, office_link) VALUES ($blog_id, '$office_text', '$office_link')";

    if ($conn->query($sql) === TRUE) {
        header("Location: blog.php?id=$blog_id");
    } else {
        echo "Unable to add right now, contact your developer fo rthis issue";
    }
}
?>
    <!-- Add Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
