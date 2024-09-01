<?php
require_once "functions/myconfig.php";

// Check if the ID is set and is a valid number
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid page ID.");
}

$page_id = $_GET['id'];

// Retrieve the page details
$sql = "SELECT * FROM pages WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $page_id);
$stmt->execute();
$result = $stmt->get_result();
$page = $result->fetch_assoc();

if (!$page) {
    die("Page not found.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $slug1 = $_POST['slug'];
    $slug = strtolower($slug1);
  
    $meta_desc = $_POST['meta_desc'];
    $meta_keywords = $_POST['meta_Keywords'];
    $meta_cronical = $_POST['meta_cronical'];
    $date = $_POST['date'];
    $design = $_POST['design'];
    
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
         $xmlpriority = !empty($_POST['xml_priority']) ? $_POST['xml_priority'] : null;
          // Handle meta robots checkboxes
    $noindex = isset($_POST['noindex']) ? 1 : 0;
    $nofollow = isset($_POST['nofollow']) ? 1 : 0;
    $noarchive = isset($_POST['noarchive']) ? 1 : 0;
    $status = $_POST['status'];
    $image = $page['image']; // Default to existing image
    
    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../../Pic/uploaded/";
        $target_db = "/Pic/uploaded/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $target_file_db = $target_db . basename($_FILES["image"]["name"]);

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $target_file_db;
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

    // Update the page in the database
    $sql = "UPDATE pages SET admin_name = ?, title = ?, content = ?, parent_id = ?, slug = ?, meta_desc = ?, meta_keywords = ?, meta_cronical = ?, date = ?, design = ?, image = ?, XML_Priority = ?,noindex = ?, nofollow = ?, noarchive = ?, status = ? WHERE id = ?";
               
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssisssssssdiiisi",$name, $title, $content, $parent_id, $slug, $meta_desc, $meta_keywords, $meta_cronical, $date, $design, $image, $xmlpriority,  $noindex,$nofollow,$noarchive,$status,$page_id);

    if ($stmt->execute()) {
        header("Location: edit_page.php?id=$page_id");
        
    //     echo '<p>
    //     <a href="https://miamiairport-mia.com/sitemap.php" id="updateUrlLink" class="fw-bolder" style="color: #2007ff;">Click here to update Sitemap.xml</a>
    // </p>';

    // echo' <p>
    //     <a href="mypages.php">Click here to redirect to admin</a>
    // </p>';

    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Page</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <script src="https://cdn.tiny.cloud/1/ebs4evfkr4f8epp30mmtuelc8xqoiktmffjcg8sr6zt11ibd/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="column-plugin.js"></script> 

<script>
    tinymce.init({
      selector: 'textarea',
      plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount bootstrap_columns', // Make sure your custom plugin is included here
      toolbar: 'two_col three_col insert_anchor  | undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
      tinycomments_mode: 'embedded',
      tinycomments_author: 'Author name',
      mergetags_list: [
        { value: 'First.Name', title: 'First Name' },
        { value: 'Email', title: 'Email' },
      ],
      ai_request: (request, respondWith) => respondWith.string(() => Promise.reject("See docs to implement AI Assistant"))
    });
  </script>
</head>
<body>
   <div class="container mt-5">
        <a href="index.php" class="btn btn-primary my-5">Dashboard</a>
    <a href="mypages.php" class="btn btn-primary my-5">All pages</a>
    <a href="posts.php" class="btn btn-primary my-5">All posts</a>
    <h2>Edit Page</h2>
    <form id="editPageForm" action="edit_page.php?id=<?php echo $page_id; ?>" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-9">
                <div class="form-group">
                    <label for="title">Page Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($page['title']); ?>" required>
                </div>
                <textarea id="content" name="content"><?php echo htmlspecialchars($page['content']); ?></textarea>

                <!-- Meta Robots options -->
                <div class="row justify-content-center">
                    <h2 class="text-center fw-bolder my-4">Meta Robot Specifications</h2>
                    <div class="col-md-4">
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="noindex" name="noindex" <?php if ($page['noindex'] == 1) echo 'checked'; ?>>
                            <label class="form-check-label" for="noindex">Noindex</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="nofollow" name="nofollow" <?php if ($page['nofollow'] == 1) echo 'checked'; ?>>
                            <label class="form-check-label" for="nofollow">Nofollow</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="noarchive" name="noarchive" <?php if ($page['noarchive'] == 1) echo 'checked'; ?>>
                            <label class="form-check-label" for="noarchive">Noarchive</label>
                        </div>
                    </div>
                </div>

                <div class="row my-3">
                    <div class="col">
                        <label for="meta_desc">Meta Description</label>
                        <input id="meta_desc" name="meta_desc" class="form-control" type="text" value="<?php echo htmlspecialchars($page['meta_desc']); ?>" placeholder="Enter meta description">
                    </div>
                    <div class="col">
                        <label for="meta_Keywords">Meta Keywords</label>
                        <input id="meta_Keywords" name="meta_Keywords" class="form-control" type="text" value="<?php echo htmlspecialchars($page['meta_Keywords']); ?>" placeholder="Enter meta keywords">
                    </div>
                    <div class="col">
                        <label for="meta_cronical">Meta Cronical</label>
                        <input id="meta_cronical" name="meta_cronical" class="form-control" type="text" value="<?php echo htmlspecialchars($page['meta_cronical']); ?>" placeholder="Enter meta cronical">
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="name">Author Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($page['admin_name']); ?>" required>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label for="date">Date</label>
                        <input id="date" name="date" class="form-control" type="date" value="<?php echo htmlspecialchars($page['date']); ?>" required>
                    </div>
                    <div class="col">
                        <div class="mb-3">
                            <label for="design">Select Design</label>
                            <select id="design" name="design" class="form-select me-2" required>
                                <option value="Design_1" <?php if ($page['design'] == 'Design_1') echo 'selected'; ?>>Simple full width Template</option>
                                <option value="Design_2" <?php if ($page['design'] == 'Design_2') echo 'selected'; ?>>Full Width Template with helpful resources</option>
                                <option value="Design_3" <?php if ($page['design'] == 'Design_3') echo 'selected'; ?>>Blog with left Sidebar</option>
                                <option value="Design_20" <?php if ($page['design'] == 'Design_20') echo 'selected'; ?>>Child with left sidebar</option>
                                <?php
                                    // Check if the current design does not match any of the predefined options
                                    if (!in_array($page['design'], ['Design_1', 'Design_2', 'Design_3','Design_20'])) {
                                        $design_title = $page['design'];
                                        echo '<option value="' . htmlspecialchars($page['design']) . '" selected>' . htmlspecialchars($design_title) . '</option>';
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="slug">Slug</label>
                    <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($page['slug']); ?>" required>
                </div>
                <div class="d-flex justify-content-evenly"> 
                 <a href="faq_generator.php?id=<?php echo $page_id;?>" id="generateFaq" class="btn btn-primary my-3">Generate FAQ</a>
                 <a href="faq_edit.php?id=<?php echo $page_id;?>" id="generateFaq" class="btn btn-primary my-3">Edit FAQ</a>
                </div>
                
                <div id="parentField" class="form-group" style="display: <?php echo $page['parent_id'] !== null ? 'block' : 'none'; ?>;">
                    <label for="parent_id">Parent ID (optional)</label>
                    <select class="form-control" id="parent_id" name="parent_id">
                        <option value="" disabled <?php echo $page['parent_id'] === null ? 'selected' : ''; ?>>Choose Parent</option>
                        <option value="11" <?php echo $page['parent_id'] == 11 ? 'selected' : ''; ?>>Blog</option>
                        <option value="115" <?php echo $page['parent_id'] == 115 ? 'selected' : ''; ?>>Arts and Exhibition</option>
                        <option value="116" <?php echo $page['parent_id'] == 116 ? 'selected' : ''; ?>>Parking</option>
                        <option value="128" <?php echo $page['parent_id'] == 128 ? 'selected' : ''; ?>>Transportation</option>
                        <option value="120" <?php echo $page['parent_id'] == 120 ? 'selected' : ''; ?>>Car Rental</option>
                        <option value="124" <?php echo $page['parent_id'] == 116 ? 'selected' : ''; ?>>Shops and Dine</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="Published" <?php if ($page['status'] == 'published') echo 'selected'; ?>>Published</option>
                        <option value="Draft" <?php if ($page['status'] == 'draft') echo 'selected'; ?>>Draft</option>
                    </select>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="isChildPage" <?php if ($page['parent_id'] !== null) echo 'checked'; ?>>
                    <label class="form-check-label" for="isChildPage">Create as Child Page</label>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="isXML" <?php if ($page['XML_Priority'] !== null) echo 'checked'; ?>>
                    <label class="form-check-label" for="isXML">Add into Sitemap.xml</label>
                </div>

                <div id="xmlpriority" class="form-group" style="display: <?php echo $page['XML_Priority'] !== null ? 'block' : 'none'; ?>;">
                    <label for="xml_priority">Priority</label>
                    <select class="form-control" id="xml_priority" name="xml_priority">
                        <option value="1" <?php echo $page['XML_Priority'] == 1 ? 'selected' : ''; ?>>1</option>
                        <option value="0.8" <?php echo $page['XML_Priority'] == 0.8 ? 'selected' : ''; ?>>0.8</option>
                        <option value="0.6" <?php echo $page['XML_Priority'] == 0.6 ? 'selected' : ''; ?>>0.6</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="formFile" class="form-label">Image</label>
                    <input name="image" class="form-control" type="file" id="formFile" accept=".png, .jpg, .jpeg">
                    <img src="<?php echo htmlspecialchars($page['image']); ?>" alt="Current Image" style="max-width: 100px; max-height: 100px;">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Update Page</button>
    </form>
</div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('isChildPage').addEventListener('change', function() {
            var parentField = document.getElementById('parentField');
            if (this.checked) {
                parentField.style.display = 'block';
            } else {
                parentField.style.display = 'none';
            }
        });
         document.getElementById('isXML').addEventListener('change', function() {
            var parentField = document.getElementById('xmlpriority');
            if (this.checked) {
                parentField.style.display = 'block';
            } else {
                parentField.style.display = 'none';
            }
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
