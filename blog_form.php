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

 
<script src="script/message.js"></script>
<div id="messageContainer"></div>
<form onsubmit="return validateForm() && validateImageFileType(this.image);" action="blog_form.php" method="post" enctype="multipart/form-data" >
    <div class="container w-75  ">
      <h2 class="text-center fw-bold my-4">Create Blog</h2>
     
      <div class="row mb-3">
        <div class="col">
          <label for="title">Title</label>
          <input id="title" name="title" class="form-control" type="text" placeholder="Enter the Title " aria-label="default input example" required >
        </div>
      </div>
      <div class="row mb-3">
         <div class="col">
          <label for="date">Date</label>
          <input id="date" name="date" class="form-control" type="date"  aria-label="default input example" required >
         </div>
         <div class="col">
          <label for="Location">Location</label>
          <input name="Location" id="Location" class="form-control" type="text" placeholder="Enter location of event" aria-label="default input example" aria-describedby="LocHelp" required >
          <div id="LocHelp" class="form-text">Example: Lab 1, E-block, 4th Floor</div>
        </div>
      </div>
      <div class="mb-3">
        <label for="meta_desc">Meta Description</label>
        <input id="meta_desc" name="meta_desc" class="form-control" type="text" placeholder="Enter description " aria-label="default input example" required >
      </div>
      <div class="mb-3">
        <label for="Day">Day</label>
        <input id="Day" name="Day" class="form-control" type="text" placeholder="like Monday" aria-label="default input example" required >
      </div>
      <div class="mb-3">
        <label for="design">Select Design</label>
        <select id="design" name="design" class="form-select me-2" required>
        <option value="Design_1">Design 1</option>
        <option value="Design_2">Design 2</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="slug">Slug</label>
        <input id="slug" name="slug" class="form-control" type="text" placeholder=" Enter URL  " aria-label="default input example" required >
      </div>
    </div>
      <div class="container w-75">
        <h2 class="text-center fw-bolder mt-4">Blog Description</h2>
        <div class="mb-3">
      <label for="formFile" class="form-label">Image</label>
      <input name="image" class="form-control" type="file" id="formFile" accept=".png, .jpg, .jpeg" required>
    </div>
    <div class="form-floating ">
      <textarea oninput="countWords()" name="description" class="form-control" placeholder="Leave a comment here" id="floatingTextarea2" style="height: 100px" aria-describedby="textareaHelp" required></textarea>
      <label for="floatingTextarea2" class="">Type Here </label><p>Word Count: <span id="wordCount">0</span></p>
      <div id="textareaHelp" class="form-text">Description must be at least 15 words </div>
    </div>
    <button type="submit" class="btn btn-primary text-light mt-3 w-100 p-2 " style="margin-bottom: 5rem;">Create Event</button>
      </div>
      </form>
      <?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// Retrieve form data
$title = $_POST['title'];
$meta_desc = $_POST['meta_desc'];
$description = $_POST['description'];
$date = $_POST['date'];
$location = $_POST['Location'];
$slug = $_POST['slug'];
$day = $_POST['Day'];
$design = $_POST['design'];


$sql_check = "SELECT * FROM airline_blog  WHERE air_title = '$title' AND air_description = '$description'";
$result_check = $conn->query($sql_check);

if ($result_check->num_rows > 0) {
  //If Data already exists in the database
  echo "<script>displayMessage('Data already submitted', 'danger');</script>";
} else {


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
                $sql = "INSERT INTO airline_blog (air_meta, air_image, air_title, air_description, air_date, air_Day, air_location, air_slug)
                        VALUES ('$meta_desc', '$targetImagePath', '$title', '$description', '$date' ,'$day', '$location', '$slug')";
                if ($conn->query($sql) === TRUE) {
                    echo "<script>displayMessage('Event added successfully', 'success');</script>";
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
  } else {
    echo "<script>displayMessage('No image file uploaded', 'danger');</script>";
  }
}
}
// Close the database connection
$conn->close();
?>
    <script src="script/tiny.js"></script>



    
          <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
    <script src="script/script.js"></script>
    <script src="https://cdn.tiny.cloud/1/ebs4evfkr4f8epp30mmtuelc8xqoiktmffjcg8sr6zt11ibd/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
  </body>
    </html>