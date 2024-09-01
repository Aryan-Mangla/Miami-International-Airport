<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Page</title>
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

    // Get the child slug from the URL
    $url = isset($_GET['url']) ? $_GET['url'] : '';
    $urlSegments = explode('/', $url);
    $pageSlug = end($urlSegments); // Get the last segment of the URL

    // Create a connection to the database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch child page based on URL
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
        $content = $row["content"];
        $image = $row["image"]; // Assuming you have an image column in your table
        $child_design = $row["design"]; // Fetch the design for the child page

        echo "<div class='title-container'>";
        echo "<h1 class='mb-4 text-center'>$title</h1>";
        echo '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
        echo '<li class="breadcrumb-item"><a href="/airlines/index.php" class="text-dark text-decoration-none">Airlines</a></li>';
        $breadcrumbUrl = '/airlines';
        $segmentCount = count(array_filter($urlSegments));
        foreach ($urlSegments as $index => $segment) {
            if (!empty($segment)) {
                $breadcrumbUrl += '/' . $segment;
                $isLastSegment = ($index === $segmentCount - 1);
                $class = $isLastSegment ? 'text-danger fw-bolder' : 'text-dark';
                echo '<li class="breadcrumb-item"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $segment . '</a></li>';
            }
        }
        echo '</ol></nav>';
        echo "</div>"; // Close title-container

        echo "<p>$content</p>";
        if ($image) {
            echo '<div class="container">';
            echo "<img src='$image' style='height: 100%; width: 100%; border-radius: 5em;' class='img-fluid mb-4' alt='$title'>";
            echo '</div>';
        }
    } else {
        // Page not found
        echo "<p class='text-danger'>Page not found</p>";
    }

    $stmt->close();
    $conn->close();
    ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>

<?php include 'footer.php'; ?>
</body>
</html>
