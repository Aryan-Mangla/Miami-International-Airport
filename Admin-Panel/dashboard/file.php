<?php
require_once "functions/myconfig.php";

// Parse the URL
$url = isset($_GET['url']) ? strtolower($_GET['url']) : '';
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
    $content = $row["content"];

    echo "<h1>$title</h1>";
    echo "<p>$content</p>";
    

    // Check if the page has child pages
    $childSql = "SELECT * FROM pages WHERE parent_id = ?";
    $childStmt = $conn->prepare($childSql);
    $childStmt->bind_param("i", $pageId);
    $childStmt->execute();
    $childResult = $childStmt->get_result();

    if ($childResult->num_rows > 0) {
        // Page has child pages, display the links to the child pages
        echo "<h2>Child Pages</h2>";
        echo "<ul>";
        while ($childRow = $childResult->fetch_assoc()) {
            $childTitle = $childRow["title"];
            $childSlug = $childRow["slug"];
            $childUrl = generatePageUrl($childRow["id"]);
            echo "<li><a href='$childUrl'>$childTitle</a></li>";
        }
        echo "</ul>";
    }

    $childStmt->close();
} else {
    // Page not found
    echo "Page not found";
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
