<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pages</title>
    <link href="bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php 
    require_once 'config.php'; 
    include 'header.php';
    ?>

<div class="container mt-5">
    <h1 class="mb-4">Pages</h1>
    <div class="list-group">
    
    <?php

        // Create a connection to the database
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Query to retrieve all pages
        $sql = "SELECT * FROM pages";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Loop through pages
            while ($row = $result->fetch_assoc()) {
                $page_id = $row["id"];
                $title = $row["title"];
                $slug = $row["slug"];
                $parent_id = $row["parent_id"];

                // Generate URL
                $url = generatePageUrl($page_id);

                // Display page title and URL
                echo "<a href='$url' class='list-group-item list-group-item-action'>$title</a>";

                // Check if page has children
                $child_sql = "SELECT * FROM pages WHERE parent_id = $page_id";
                $child_result = $conn->query($child_sql);

                if ($child_result->num_rows > 0) {
                    // Display child pages
                    echo "<div class='ml-3'>";
                    echo "<ul class='list-group'>";
                    while ($child_row = $child_result->fetch_assoc()) {
                        $child_title = $child_row["title"];
                        $child_slug = $child_row["slug"];
                        $child_url = generatePageUrl($child_row["id"]);

                        // Display child page title and URL
                        echo "<li class='list-group-item'><a href='$child_url'>$child_title</a></li>";
                    }
                    echo "</ul>";
                    echo "</div>";
                }
            }
        } else {
            echo "<p class='text-danger'>No pages found</p>";
        }

        $conn->close();

        function generatePageUrl($page_id)
        {
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
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>

<?php include 'footer.php'; ?>
</body>
</html>
