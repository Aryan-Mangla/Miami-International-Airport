<?php
ob_start();
require_once "functions/myconfig.php";

function createPage($name, $title, $content, $slug, $meta_desc, $meta_keywords, $meta_cronical, $date, $design, $target_file_db, $parent_id = null, $xmlpriority, $noindex, $nofollow, $noarchive) {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO pages (admin_name, title, content, parent_id, slug, meta_desc, meta_keywords, meta_cronical, date, design, image, XML_Priority, noindex, nofollow, noarchive) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisssssssdiii", $name, $title, $content, $parent_id, $slug, $meta_desc, $meta_keywords, $meta_cronical, $date, $design, $target_file_db, $xmlpriority, $noindex, $nofollow, $noarchive);

    if ($stmt->execute()) {
        $page_id = $stmt->insert_id;
        $stmt->close(); // Move close statement before returning
        $page_url = generatePageUrl($page_id);
        echo "New page created successfully. URL: <a href='$page_url'>$page_url</a>";
        return $page_url;
    } else {
        echo "Error: " . $stmt->error;
        $stmt->close();
        return false;
    }
}

function generatePageUrl($page_id) {
    global $conn;

    $segments = [];
    $current_id = $page_id;

    while ($current_id !== null) {
        $stmt = $conn->prepare("SELECT slug, parent_id FROM pages WHERE id = ?");
        $stmt->bind_param("i", $current_id);
        $stmt->execute();
        $stmt->bind_result($slug, $parent_id);
        $stmt->fetch();
        $segments[] = $slug;
        $current_id = $parent_id;
        $stmt->close();
    }

    $segments = array_reverse($segments);
    return implode('/', $segments);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $slug = strtolower($_POST['slug']);
    $meta_desc = $_POST['meta_desc'];
    $meta_keywords = $_POST['meta_Keywords'];
    $meta_cronical = $_POST['meta_cronical'];
    $date = $_POST['date'];
    $design = $_POST['design'];
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $xml_priority = !empty($_POST['xml_priority']) ? (float)$_POST['xml_priority'] : null;
    $noindex = isset($_POST['noindex']) ? 1 : 0;
    $nofollow = isset($_POST['nofollow']) ? 1 : 0;
    $noarchive = isset($_POST['noarchive']) ? 1 : 0;
    
    $image = $_FILES['image']['name'];

    $target_dir = "../../Pic/uploaded/";
    $target_db = "/Pic/uploaded/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $target_file_db = $target_db . basename($_FILES["image"]["name"]);
    
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        echo "The file " . basename($_FILES["image"]["name"]) . " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }

    createPage($name, $title, $content, $slug, $meta_desc, $meta_keywords, $meta_cronical, $date, $design, $target_file_db, $parent_id, $xml_priority, $noindex, $nofollow, $noarchive);
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Page</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <script src="https://cdn.tiny.cloud/1/ebs4evfkr4f8epp30mmtuelc8xqoiktmffjcg8sr6zt11ibd/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: 'textarea',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
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
    <div class="container mt-3">
        <a href="index.php" class="btn btn-primary my-5">Dashboard</a>
        <a href="mypages.php" class="btn btn-primary my-5">All pages</a>
        <a href="posts.php" class="btn btn-primary my-5">All posts</a>
        <h2>Create a New Page</h2>
        <form id="createPageForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Page Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="name">Author Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="row mb-3">
                <div class="col mt-3">
                    <label for="meta_desc">Meta Description</label>
                    <input id="meta_desc" name="meta_desc" class="form-control" type="text" placeholder="Enter meta description" aria-label="default input example">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col mt-3">
                    <label for="meta_Keywords">Meta Keywords</label>
                    <input id="meta_Keywords" name="meta_Keywords" class="form-control" type="text" placeholder="Enter meta keywords" aria-label="default input example">
                </div>
                <div class="col mt-3">
                    <label for="meta_cronical">Meta Cronical</label>
                    <input id="meta_cronical" name="meta_cronical" class="form-control" type="text" placeholder="Enter meta cronical" aria-label="default input example">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <label for="date">Date</label>
                    <input id="date" name="date" class="form-control" type="date" aria-label="default input example" required>
                </div>
                <div class="col">
                    <div class="mb-3">
                        <label for="design">Select Design</label>
                        <select id="design" name="design" class="form-select me-2" required>
                            <option value="Design_1">Parent Page</option>
                            <option value="Design_2">Full Width Template</option>
                            <option value="Design_3">Blog with Right Side Bar</option>
                            <option value="Design_4">Full width Blog</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" class="form-control" id="slug" name="slug" required>
            </div>
            <div id="parentField" class="form-group" style="display: none;">
                <input type="hidden" value="11" class="form-control" id="parent_id" name="parent_id">
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="isXML">
                <label class="form-check-label" for="isXML">Add in Sitemap.XML</label>
            </div>
            <div id="xmlpriority" class="form-group" style="display: none;">
                <label for="xml_priority">Priority</label>
                <select class="form-control" id="xml_priority" name="xml_priority">
                    <option value="1">1</option>
                    <option value="0.8">0.8</option>
                    <option value="0.6">0.6</option>
                </select>
            </div>
            <h2 class="text-center fw-bolder my-4">Robot Meta Specifications</h2>
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="noindex" name="noindex">
                        <label class="form-check-label" for="noindex">Noindex</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="nofollow" name="nofollow">
                        <label class="form-check-label" for="nofollow">Nofollow</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="noarchive" name="noarchive">
                        <label class="form-check-label" for="noarchive">Noarchive</label>
                    </div>
                </div>
            </div>
            <h2 class="text-center fw-bolder mt-4">Page Description</h2>
            <div class="mb-3">
                <label for="formFile" class="form-label">Image</label>
                <input name="image" class="form-control" type="file" id="formFile" accept=".png, .jpg, .jpeg" required>
            </div>
            <textarea id="content" name="content">Hello, World!</textarea>
            <button type="submit" class="btn btn-primary">Create Page</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
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
