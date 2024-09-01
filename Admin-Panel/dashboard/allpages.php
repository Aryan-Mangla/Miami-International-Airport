<?php
session_start(); // Ensure the session is started
require_once "functions/myconfig.php";

// If session variable is not set, redirect to login page
if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    header("location: login.php");
    exit;
}

$email = $_SESSION['email'];

$items_per_page = 10; // Number of items per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1; // Get the current page number, default to 1 if not set or invalid
$offset = ($page - 1) * $items_per_page; // Calculate the offset

// Query to fetch only the required items based on pagination using prepared statements
$stmt = $conn->prepare("SELECT id, title FROM pages LIMIT ?, ?");
$stmt->bind_param("ii", $offset, $items_per_page);
$stmt->execute();
$result_with_pagination = $stmt->get_result();

// Count the total number of pages
$total_pages_query = "SELECT COUNT(*) as total FROM pages";
$total_pages_result = $conn->query($total_pages_query);
$total_pages = $total_pages_result ? ceil($total_pages_result->fetch_assoc()['total'] / $items_per_page) : 1;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" type="image/png" sizes="16x16" href="../plugins/images/icon.png">
    <title>Company Admin</title>
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
   
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
  
    <div id="page-wrapper">
        <div class="container-fluid">
            <div class="row bg-title">
                <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                    <h4 class="page-title"><?php echo htmlspecialchars($email); ?></h4>
                </div>
                <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                    <ol class="breadcrumb">
                        <li><a href="index.php">Dashboard</a></li>
                        <li class="active">Posts</li>
                    </ol>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="white-box">
                        <div class="row">
                            <div class="col-lg-12 col-md-9 col-sm-12 col-xs-12 mail_listing">
                                <div class="inbox-center">
                                    <?php  
                                    if (isset($_GET['posted'])) {
                                        echo '<div class="alert alert-success">
                                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                            <strong>DONE!! </strong><p>Your new post has been successfully uploaded.</p>
                                        </div>';
                                    } elseif (isset($_GET["deleted"])) {
                                        echo '<div class="alert alert-warning">
                                            <a href="#" class="close" data-dismiss="alert" aria-label="close"></a>
                                            <strong>DELETED!! </strong><p>The Blog Post has been successfully deleted.</p>
                                        </div>';
                                    } elseif (isset($_GET["del_error"])) {
                                        echo '<div class="alert alert-danger">
                                            <a href="#" class="close" data-dismiss="alert" aria-label="close"></a>
                                            <strong>ERROR!! </strong><p>There was an error during deleting this record. Please try again.</p>
                                        </div>';
                                    }                                                        
                                    ?>
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Page Title</th>
                                                <th>URL</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($result_with_pagination->num_rows > 0) {
                                                // Collect page ids to generate URLs in bulk
                                                $page_ids = [];
                                                while ($row = $result_with_pagination->fetch_assoc()) {
                                                    $page_ids[$row['id']] = htmlspecialchars($row["title"]);
                                                }
                                                
                                                // Fetch URLs for all pages in one go
                                                $ids_placeholders = implode(',', array_fill(0, count($page_ids), '?'));
                                                $stmt = $conn->prepare("SELECT id, slug, parent_id FROM pages WHERE id IN ($ids_placeholders)");
                                                $stmt->bind_param(str_repeat('i', count($page_ids)), ...array_keys($page_ids));
                                                $stmt->execute();
                                                $url_results = $stmt->get_result();

                                                $urls = [];
                                                while ($url_row = $url_results->fetch_assoc()) {
                                                    $urls[$url_row['id']] = generatePageUrl($url_row['id'], $conn);
                                                }
                                                
                                                foreach ($page_ids as $id => $title) {
                                                    $url = $urls[$id];
                                                    echo "<tr>";
                                                    echo "<td>$title</td>";
                                                    echo "<td>$url</td>";
                                                    echo "<td>";
                                                    echo "<a href='edit_page.php?id=" . urlencode($id) . "' class='text-info'><i class='fas fa-edit'></i></a>";
                                                    echo " | ";
                                                    echo "<a href='delete_page.php?id=" . urlencode($id) . "' class='text-info'><i class='fas fa-trash'></i></a>";
                                                    echo "</td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='3'>No pages found</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row">
                                    <div class="col-xs-7 m-t-20"> Showing <?php echo ($offset + 1) . " - " . ($offset + $result_with_pagination->num_rows) . " of " . ($total_pages * $items_per_page); ?> </div>
                                    <div class="col-xs-5 m-t-20">
                                        <div class="btn-group pull-right">
                                            <?php if ($page > 1): ?>
                                                <a href="?page=<?php echo $page - 1; ?>" class="btn btn-default waves-effect"><i class="fa fa-chevron-left"></i></a>
                                            <?php endif; ?>
                                            <?php if ($page < $total_pages): ?>
                                                <a href="?page=<?php echo $page + 1; ?>" class="btn btn-default waves-effect"><i class="fa fa-chevron-right"></i></a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="pagination">
                                    <?php
                                    for ($i = 1; $i <= $total_pages; $i++) {
                                        $class = $i == $page ? 'class="current-page"' : '';
                                        echo "<a href='?page=$i' $class>$i</a>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include 'service-panel.php'; ?>
        </div>
        <footer class="footer text-center"> 2024 &copy; Airlines Admin </footer>
    </div>
    <script src="../plugins/bower_components/jquery/dist/jquery.min.js"></script>
    <script src="bootstrap/dist/js/tether.min.js"></script>
    <script src="bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="../plugins/bower_components/bootstrap-extension/js/bootstrap-extension.min.js"></script>
    <script src="../plugins/bower_components/sidebar-nav/dist/sidebar-nav.min.js"></script>
    <script src="js/jquery.slimscroll.js"></script>
    <script src="js/waves.js"></script>
    <script src="js/custom.min.js"></script>
    <script src="../plugins/bower_components/styleswitcher/jQuery.style.switcher.js"></script>
    <?php
    function generatePageUrl($page_id, $conn) {
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
    ?>
</body>
</html>
<?php ob_end_flush(); ?>
