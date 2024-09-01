

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PArents</title>
    <link href="/airlines/bootstrap.min.css" rel="stylesheet">
    <style>
          .breadcrumb {
            justify-content: center;
        }
    </style>
</head>
<body>
<?php 
    
    include 'header.php';
?>
<div class="container mt-5">
    <?php
    require_once "config.php";

   
    $url = isset($_GET['url']) ? $_GET['url'] : '';
    $urlSegments = explode('/', $url);
   

    // Create a connection to the database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch page based on URL
    $pageSlug = end($urlSegments); // Get the last segment of the URL
    $sql = "SELECT * FROM pages WHERE slug = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $pageSlug);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Page found, display content
        $row = $result->fetch_assoc();
        $pageId = $row["id"];
        $title = $row["title"];
        $design = $row["design"];
        if($design == "Design_1"){
            echo "<div class='title-container'>";
            echo "<h1 class='mb-4 text-center'>$title</h1>";
            echo '<nav aria-label="breadcrumb"><ol class="breadcrumb ">';
            echo '<li class="breadcrumb-item"><a href="/airlines/index.php" class="text-dark text-decoration-none">Airlines</a></li>';
            $breadcrumbUrl = '/airlines';
            $segmentCount = count(array_filter($urlSegments));
            foreach ($urlSegments as $index => $segment) {
                if (!empty($segment)) {
                    $breadcrumbUrl .= '/' . $segment;
                    $isLastSegment = ($index === $segmentCount - 1);
                    $class = $isLastSegment ? 'text-danger fw-bolder' : 'text-dark';
                    echo '<li class="breadcrumb-item"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $segment . '</a></li>';
                }
            }
            echo '</ol></nav>';
            echo "</div>"; // Close title-container
            $content = $row["content"];
            $image = $row["image"]; // Assuming you have an image column in your table
      
            echo "<p>$content</p>";

            // Check if the page has child pages
            $childSql = "SELECT * FROM pages WHERE parent_id = ?";
            $childStmt = $conn->prepare($childSql);
            $childStmt->bind_param("i", $pageId);
            $childStmt->execute();
            $childResult = $childStmt->get_result();
    
            if ($childResult->num_rows > 0) {
                // Page has child pages, display the links to the child pages
                echo "<h2 class='mt-5'>Child Pages</h2>";
                echo "<div class='row'>";
    
                $childCount = 0;
                while ($childRow = $childResult->fetch_assoc()) {
                    $childTitle = $childRow["title"];
                    $childSlug = $childRow["slug"];
                    $childImage = $childRow["image"]; // Assuming you have an image column for child pages
                    $childUrl = generatePageUrl($childRow["id"]);
    
                    if ($childCount > 0 && $childCount % 3 == 0) {
                        echo '</div><div class="row mt-4">';
                    }
    
                    echo "<div class='col-md-4 mb-4'>";
                    echo "<a href='$childUrl' class='text-decoration-none text-dark ' >";
                    echo "<div class='card border-0' style='box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;'>";
                    if ($childImage) {
                        echo "<img src='$childImage' class='card-img-top p-2' alt='$childTitle'>";
                    }
                    echo "<div class='card-body'>";
                    echo "<h5 class='card-title'>$childTitle</h5>";
                   echo"</a>";
                    echo "</div></div></div>";
    
                    $childCount++;
                }
    
                echo '</div>'; // Close the last row
    
                // Pagination
                $totalPages = ceil($childCount / 12);
                if ($totalPages > 1) {
                    echo '<nav><ul class="pagination justify-content-center mt-4">';
                    for ($i = 1; $i <= $totalPages; $i++) {
                        echo "<li class='page-item'><a class='page-link' href='?url=$url&page=$i'>$i</a></li>";
                    }
                    echo '</ul></nav>';
                }
            }
    
            $childStmt->close();}
//Design with side bar left
//          elseif($design == "Design_3"){
//        echo'     <div class="row my-5">
//     <!-- Left Sidebar -->
//     <div class="col-3">';
//       echo'Pending';
//    echo' </div>
    
//     <!-- Right Content -->
//     <div class="col-9">
//         <!-- Main content -->
//         <div class="container">
//             <!-- Your main content for the child page goes here -->
//         </div>
//     </div>
// </div>';
//         }
        
        
        else{
            echo "<div class='title-container'>";
            echo "<h1 class='mb-4 text-center'>$title</h1>";
            echo '<nav aria-label="breadcrumb"><ol class="breadcrumb ">';
            echo '<li class="breadcrumb-item"><a href="/airlines/index.php" class="text-dark text-decoration-none">Airlines</a></li>';
            $breadcrumbUrl = '/airlines';
            $segmentCount = count(array_filter($urlSegments));
            foreach ($urlSegments as $index => $segment) {
                if (!empty($segment)) {
                    $breadcrumbUrl .= '/' . $segment;
                    $isLastSegment = ($index === $segmentCount - 1);
                    $class = $isLastSegment ? 'text-danger fw-bolder' : 'text-dark';
                    echo '<li class="breadcrumb-item"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $segment . '</a></li>';
                }
            }
            echo '</ol></nav>';
            echo "</div>"; // Close title-container
            $content = $row["content"];
            echo'I am in 2';
            $image = $row["image"]; // Assuming you have an image column in your table
      
            echo "<p>$content</p>";
            if ($image) {
                echo'<div class="container"> ';
                echo "<img src='$image' style='height: 100%;
                width: 100%;
                border-radius: 5em;' class='img-fluid mb-4' alt='" . $row["title"] . "'>";
                echo"</div>'";
            }

        }
        }else {
            // Page not found
            echo "<p class='text-danger'>Page not found</p>";
        }
      

    $stmt->close();
    $conn->close();

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
    ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>

<?php include 'footer.php'; ?>
</body>
</html>
