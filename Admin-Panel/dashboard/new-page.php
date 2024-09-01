<?php
ob_start();
require_once "functions/myconfig.php";

function createPage($name, $title, $content, $slug, $meta_desc, $meta_keywords, $meta_cronical, $date, $design, $target_file_db, $parent_id = null, $xmlpriority, $noindex, $nofollow, $noarchive, $status) {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO pages (admin_name, title, content, parent_id, slug, meta_desc, meta_keywords, meta_cronical, date, design, image, XML_Priority, noindex, nofollow, noarchive, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisssssssdiiis", $name, $title, $content, $parent_id, $slug, $meta_desc, $meta_keywords, $meta_cronical, $date, $design, $target_file_db, $xmlpriority, $noindex, $nofollow, $noarchive, $status);

    // Execute the statement
    if ($stmt->execute()) {
        $page_id = $stmt->insert_id; // Get the ID of the new page
        $page_url = generatePageUrl($page_id);
        echo "New page created successfully. URL: <a href='$page_url'>$page_url</a>";
        // echo '<p><a href="https://miamiairport-mia.com/sitemap.php" id="updateUrlLink" class="fw-bolder" style="color: #2007ff;">Click here to update Sitemap.xml</a></p>';

        return $page_url; // Return the URL of the new page
    } else {
        echo "Error: " . $stmt->error;
        return false;
    }

    $stmt->close();
}

function generatePageUrl($page_id) {
    global $conn;

    $segments = [];
    $current_id = $page_id;

    // Fetch page and parent details to build the URL
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
    $xml_priority = !empty($_POST['xml_priority']) ? $_POST['xml_priority'] : null;
    $noindex = isset($_POST['noindex']) ? 1 : 0;
    $nofollow = isset($_POST['nofollow']) ? 1 : 0;
    $noarchive = isset($_POST['noarchive']) ? 1 : 0;
    $status = $_POST['status'];
    $image = $_FILES['image']['name']; // Assuming you handle file uploads elsewhere in your code

    // Handle file upload
    $target_dir = "../../Pic/uploaded/";
    $target_db = "/Pic/uploaded/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $target_file_db = $target_db . basename($_FILES["image"]["name"]);
    
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        echo "The file " . basename($_FILES["image"]["name"]) . " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }

    createPage($name, $title, $content, $slug, $meta_desc, $meta_keywords, $meta_cronical, $date, $design, $target_file_db, $parent_id, $xml_priority, $noindex, $nofollow, $noarchive, $status);
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
    <!-- Place the first <script> tag in your HTML's <head> -->
<script src="https://cdn.tiny.cloud/1/ebs4evfkr4f8epp30mmtuelc8xqoiktmffjcg8sr6zt11ibd/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
 
 tinymce.init({
            selector: 'textarea',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount ',
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
    
<!-- Place the following <script> and <textarea> tags your HTML's <body> -->
<div class="container mt-3">
    <a href="index.php" class="btn btn-primary my-5">Dashboard</a>
    <a href="mypages.php" class="btn btn-primary my-5">All pages</a>
    <a href="posts.php" class="btn btn-primary my-5">All posts</a>

    <h2>Create a New Page</h2>
    
    <form id="createPageForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-9">
                <div class="form-group mb-3">
                    <input type="text" class="form-control my-2" id="title" name="title" placeholder="Page Title" required>
                </div>
                <textarea id="content" name="content">Hello, World!</textarea>
                
                
                
                <!-- Meta Robots Column -->
                <h4 class="text-center fw-bolder my-4">Robot Meta Specifications</h4>
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
                <div class="row">
                    <div class="col">
                         <input id="meta_desc" name="meta_desc" class="form-control my-2" type="text" placeholder="Enter meta description" aria-label="default input example">
                    </div>
                    <div class="col">
                        <input id="meta_Keywords" name="meta_Keywords" class="form-control my-2" type="text" placeholder="Enter meta keywords" aria-label="default input example">
                    </div>
                    <div class="col">
                        <input id="meta_cronical" name="meta_cronical" class="form-control my-2" type="text" placeholder="Enter meta cronical" aria-label="default input example">
                    </div>
                </div>
                
                 
                
                
                
            </div>
            
            <div class="col-md-3 sticky-scroll-admin">
                <div class="form-group">
                    <input type="text" class="form-control my-2" id="name" name="name" placeholder="Author Name">
                </div>
                
                <div class="row mb-3">
                    <div class="col">
                        <input id="date" name="date" class="form-control my-2" type="date" aria-label="default input example" required>
                    </div>
                </div>
                <div class="mb-3">
                    <input name="image" class="form-control my-2" type="file" id="formFile" accept=".png, .jpg, .jpeg" required>
                </div>
                <div class="col">
                    <div class="mb-3">
                        <select id="design" name="design" class="form-select me-2 my-2" required>
                            <option value="" disabled selected>Select Design</option>
                            <option value="Design_1">Simple Full with Template</option>
                            <option value="Design_2">Full Width Template with helpful resources</option>
                            <option value="Design_3">Blog with Left Sidebar</option>
                            <option value="Design_20">Child with left Sidebar</option>
                        </select>
                    </div>
                </div>
                 <div class="form-group mb-3">
                    <select id="status" name="status" class="form-select me-2 my-2" required>
                        <option value="" disabled selected>Select Status</option>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" class="form-control my-2" id="slug" name="slug" placeholder="Slug" required>
                </div>
                 <div> 
                 <a href="faq_generator.php?id=<?php echo $page_id;?>" id="generateFaq" class="btn btn-primary my-3">Generate FAQ</a>
                </div>
                 <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="isChildPage">
                    <label class="form-check-label" for="isChildPage">Create as Child Page</label>
                </div>
                <div id="parentField" class="form-group" style="display: none;">
                    <select class="form-control my-2" id="parent_id" name="parent_id">
                        <option value="" disabled selected>Choose Parent</option>
                        <option value="11">Blog</option>
                        <option value="115">Arts and Exhibition</option>
                        <option value="116">Parking</option>
                        <option value="128">Transportation</option>
                        <option value="124">Shops and Dine</option>
                        <option value="120">Car Rental</option>
                    </select>
                </div>
                
              
               
               
                
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="isXML">
                    <label class="form-check-label" for="isXML">Add in Sitemap.XML</label>
                </div>
                <div id="xmlpriority" class="form-group" style="display: none;">
                    <select class="form-control my-2" id="xml_priority" name="xml_priority">
                        <option value="1">1</option>
                        <option value="0.8">0.8</option>
                        <option value="0.6">0.6</option>
                    </select>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary my-3 w-100">Create Page</button>
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

