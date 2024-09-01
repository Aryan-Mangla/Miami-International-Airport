<?php


// Include database configuration file
require_once 'config.php';

// Check if ID is provided in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch blog post data based on ID
    $sql = "SELECT * FROM airline_blog WHERE airline_id = '$id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Fetch data and store it in $data variable
        $data = $result->fetch_assoc();

        // Extract data for pre-filling form fields
        $title = $data['air_title'];
        $meta_desc = $data['air_meta'];
        $description = $data['air_description'];
        $date = $data['air_date'];
        $location = $data['air_location'];
        $slug = $data['air_slug'];
        $day = $data['air_day'];
        $design = $data['air_design'];
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
$title = $_POST['title'];
$meta_desc = $_POST['meta_desc'];
$description = $_POST['description'];
$date = $_POST['date'];
$location = $_POST['Location'];
$slug = $_POST['slug'];
$day = $_POST['Day'];
$design = $_POST['design'];

include 'feature-image-function.php';
  
  
  if(isset($_FILES['image'])) {
    $file_name = $_FILES['image']['name'];
    $file_tmp = $_FILES['image']['tmp_name'];
    $file_size = $_FILES['image']['size'];
    $upload_dir = "Pic/uploaded/";
    $max_file_size = 105 * 1024 * 1024;;  
    $targetImagePath = $upload_dir . basename($file_name);
  
    if(!empty($file_name) && is_uploaded_file($file_tmp)) {
      $extension = strtolower(pathinfo($targetImagePath, PATHINFO_EXTENSION));
        // Check the image dimensions
        if(validateImageDimensions($file_tmp)) {
            // Check the image file size
            if($file_size <= $max_file_size) {
              if ($extension === 'png') {
                // Convert PNG image to JPEG format and save to the upload directory
                $targetImagePath = $upload_dir . basename($file_name, '.png') . '.jpeg';
                move_uploaded_file($file_tmp, $targetImagePath);
                // convertPNGtoJPEG($file_tmp, $targetImagePath);
            } else {
                // For other image formats, save the image directly to the upload directory
                $targetImagePath = $upload_dir . basename($file_name);
                move_uploaded_file($file_tmp, $targetImagePath);
            }
                // Proceed with database insertion using the JPEG file path
                $sql = "UPDATE airline_blog SET air_title = '$title', air_meta = '$meta_desc', air_description = '$description', air_design = '$design', air_date = '$date' , air_day = '$day' , air_location = '$location' , air_slug = '$slug',air_image = '$targetImagePath' WHERE airline_id = '$id'";
                if ($conn->query($sql) === TRUE) {
                  header("location: blog.php?id=$id");
                } else {
                    echo "<script>displayMessage('Error: Unable to add this event', 'danger');</script>";
                }
            } else {
                echo "<script>displayMessage('Error: The file size exceeds the maximum allowed size (200 KB)', 'danger');</script>";
            }
        } else {
            echo "<script>displayMessage('Error: Image dimensions exceed the maximum allowed values (348x240)', 'danger');</script>";
        }
    } else {
        echo "<script>displayMessage('Error: Invalid file or no file uploaded', 'danger');</script>";
    }
  } 

   // Retrieve other form data similarly

    // Update blog post in database
    // Update other fields in the query similarly

    if ($conn->query($sql) === TRUE) {
        // If update is successful, redirect to view the updated post
      
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
    <title>Document</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
  <?php 
    require_once 'config.php';
  ?>
<?php include 'header.php'; ?>

    <!-- Form for editing blog post -->
    <div class="container w-75">
        <h2 class="text-center fw-bold my-4">Edit Blog</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">

            <!-- Hidden input field to store ID -->
            <input type="hidden" name="id" value="<?php echo $id; ?>">

            <!-- Title input -->
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo $title; ?>" required>
            </div>
 <div class="row mb-3">
        <div class="col">
          <label for="date">Date</label>
          <input id="date" name="date" class="form-control" type="date"  aria-label="default input example"  value="<?php echo $date; ?>" required >
        </div>
        <div class="col">
          <label for="Location">Location</label>
          <input name="Location" id="Location" class="form-control" type="text" placeholder="Enter location of event" aria-label="default input example" aria-describedby="LocHelp" value="<?php echo $location; ?>" required >
          <div id="LocHelp" class="form-text">Example: Lab 1, E-block, 4th Floor</div>
        </div>
      </div>
      <div class="mb-3">
        <label for="meta_desc">Meta Description</label>
        <input id="meta_desc" name="meta_desc" class="form-control" type="text" placeholder="Enter description " aria-label="default input example" value="<?php echo $meta_desc; ?>" required >
      </div>
      <div class="mb-3">
        <label for="Day">Day</label>
        <input id="Day" name="Day" class="form-control" type="text" placeholder="like Monday" aria-label="default input example" value="<?php echo $day; ?>" required >
      </div>
      <div class="mb-3">
        <label for="design">Select Design</label>
     <select id="design" name="design" class="form-select me-2">
        <option value="Design_1">Design 1</option>
        <option value="Design_2">Design 2</option>
     </select>
      </div>
      <div class="mb-3">
        <label for="slug">Slug</label>
        <input id="slug" name="slug" class="form-control" type="text" placeholder=" Enter URL  " aria-label="default input example" value="<?php echo $slug; ?>" required >
      </div>
      <div class="mb-3">
      <label for="formFile" class="form-label">Image</label>
      <input name="image" class="form-control" type="file" id="formFile" accept=".png, .jpg, .jpeg" required>
    </div>
      <div class="form-floating">
    <textarea oninput="countWords()" name="description" class="form-control" placeholder="Leave a comment here" id="floatingTextarea2" style="height: 100px" aria-describedby="textareaHelp" required><?php echo htmlspecialchars($description); ?></textarea>
    <label for="floatingTextarea2">Type Here</label>
    <p>Word Count: <span id="wordCount">0</span></p>
    <div id="textareaHelp" class="form-text">Description must be at least 15 words</div>
</div>

            <!-- Submit button -->
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>



    
    <!-- Footer section -->
    <?php include 'footer.php'; ?>

    <!-- JavaScript links -->
</body>
</html>
