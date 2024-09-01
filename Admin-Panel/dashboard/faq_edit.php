<?php
require_once "functions/myconfig.php";

// Check if blog_id is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "No Blog ID provided!";
    exit;
}

$blog_id = intval($_GET['id']);

// Fetch FAQ details
$faq_sql = "SELECT * FROM blog_faq WHERE blog_id = '$blog_id'";
$faq_result = $conn->query($faq_sql);

if ($faq_result->num_rows == 0) {
    echo "No FAQs found for this blog!";
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Loop through each title and content
    $titles = $_POST['titles']; // Array of titles
    $contents = $_POST['contents']; // Array of contents
    $ids = $_POST['ids']; // Array of FAQ IDs

    foreach ($ids as $index => $id) {
        $title = $titles[$index];
        $content = $contents[$index];

        // Skip empty fields
        if (empty($title) || empty($content)) {
            continue;
        }

        // Update FAQ in the database
        $title = $conn->real_escape_string($title);
        $content = $conn->real_escape_string($content);
        $update_sql = "UPDATE blog_faq SET title = '$title', content = '$content' WHERE id = '$id'";

        if (!$conn->query($update_sql)) {
            echo "Error: " . $update_sql . "<br>" . $conn->error;
        }
    }

    // Redirect after processing
    header("Location: edit.php?id=" . $blog_id);
    exit;
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_sql = "DELETE FROM blog_faq WHERE id = '$delete_id'";
    if ($conn->query($delete_sql) === TRUE) {
        header("Location: edit_faq.php?id=" . $blog_id); // Redirect to avoid form resubmission
        exit;
    } else {
        echo "Error: " . $delete_sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit FAQs</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        #faq-container {
            height: 600px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 15px;
            background-color: #f9f9f9;
        }
        .delete-btn {
            margin-top: 10px;
            display: block;
            color: red;
            cursor: pointer;
            text-align: center;
            text-decoration: underline;
        }
        .delete-btn:hover {
            color: darkred;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center mb-5">Edit FAQs</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $blog_id; ?>" class="w-75 mx-auto">
        <input type="hidden" name="blog_id" value="<?php echo htmlspecialchars($blog_id); ?>">
        
        <div id="faq-container">
            <?php while ($faq = $faq_result->fetch_assoc()): ?>
            <div class="mb-4">
                <input type="hidden" name="ids[]" value="<?php echo htmlspecialchars($faq['id']); ?>">
                <div class="mb-3">
                    <label for="title<?php echo $faq['id']; ?>" class="form-label">Title:</label>
                    <input type="text" id="title<?php echo $faq['id']; ?>" name="titles[]" class="form-control" value="<?php echo htmlspecialchars($faq['title']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="content<?php echo $faq['id']; ?>" class="form-label">Content:</label>
                    <textarea id="content<?php echo $faq['id']; ?>" name="contents[]" class="form-control" required><?php echo htmlspecialchars($faq['content']); ?></textarea>
                </div>
                <a href="?id=<?php echo $blog_id; ?>&delete_id=<?php echo $faq['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this FAQ?');">Delete FAQ</a>
            </div>
            <?php endwhile; ?>
        </div>

        <!-- Submit Button outside of scrollable container -->
        <div class="mt-4">
            <button type="submit" class="btn bg-primary text-white w-100">Update FAQs</button>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
<script src="https://cdn.lordicon.com/lordicon.js"></script>
</body>
</html>
