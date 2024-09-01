<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php 
    require_once 'config.php'; 
    include 'header.php';
    // Get the search query
$search_query = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

// Initialize results array
$results = [];

// Perform the search if the query is not empty
if (!empty($search_query)) {
    $sql = "SELECT * FROM pages WHERE title LIKE '%$search_query%' OR content LIKE '%$search_query%'  ";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    }
}
    ?>
    <div class="container mt-5">
        <h1>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h1>
        <?php if (!empty($results)): ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($results as $result): ?>
                    <div class="col">
                        <div class="card h-100">
                            <img src="<?php echo htmlspecialchars($result['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($result['title']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($result['title']); ?></h5>
                               
                                <a href="<?php echo $result['slug']; ?>" class="btn btn-primary">Read More</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No results found for "<?php echo htmlspecialchars($search_query); ?>"</p>
        <?php endif; ?>
    </div>
    <?php include 'footer.php'; ?>
      <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
</body>
</html>