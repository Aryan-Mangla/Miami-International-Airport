<?php
require_once "functions/myconfig.php"; // Ensure this points to your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve blog_id from the form
    $blog_id = $_POST['blog_id'];
    
    // Retrieve arrays of titles and contents
    $titles = $_POST['titles']; 
    $contents = $_POST['contents']; 
    
    // Loop through each title and content
    foreach ($titles as $index => $title) {
        $content = $contents[$index];
        
        // Skip empty fields
        if (empty($title) || empty($content)) {
            continue;
        }
        
        // Prepare and bind parameters to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO blog_faq (blog_id, title, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $blog_id, $title, $content);
        
        // Execute the statement
        if ($stmt->execute()) {
            echo "FAQ added successfully.<br>";
        } else {
            echo "Error: " . $stmt->error . "<br>";
        }
        
        $stmt->close();
    }
    
    // Redirect after processing
    header("Location: edit_page.php?id=" . urlencode($blog_id));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add FAQs</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <!-- Bootstrap Core CSS -->
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    <style>
        /* Style for the scrollable FAQ container */
        #faq-container {
            max-height: 600px; /* Adjust height as needed */
            overflow-y: auto;
            padding: 2px;
            margin-bottom: 20px; /* Space for the button */
        }
        .form-container {
            position: relative;
        }
        .submit-button {
            position: sticky;
            bottom: 0;
            background: white; /* Adjust if needed */
            z-index: 1;
            padding: 10px;
        }
    </style>
</head>
<body>

<div class="row">
    <div class="col-md-2">
        <?php include 'left-nav.php'; ?>
    </div>
    <div class="col-md-10"> 
        <div class="container p-3" style="background: white;">
            <h2 class="text-center mb-5 inbox-center">Add FAQs</h2>
            <div class="form-container ">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="w-50 mx-auto">
                    <input type="hidden" name="blog_id" value="<?php echo htmlspecialchars($_GET['id']); ?>">
                    
                    <div id="faq-container">
                        <!-- Repeatable FAQ fields -->
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                        <div class="mb-3 me-3">
                            <label for="title<?php echo $i; ?>" class="form-label">Question <?php echo $i; ?>:</label>
                            <input type="text" id="title<?php echo $i; ?>" name="titles[]" class="form-control">
                        </div>
                        <div class="mb-3 me-3">
                            <label for="content<?php echo $i; ?>" class="form-label">Answer <?php echo $i; ?>:</label>
                            <textarea id="content<?php echo $i; ?>" name="contents[]" class="form-control"></textarea>
                        </div>
                        <?php endfor; ?>
                    </div>

                    <div class="submit-button">
                        <button type="submit" class="btn bg-primary text-white w-100">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
<script src="https://cdn.lordicon.com/lordicon.js"></script>
</body>
</html>
