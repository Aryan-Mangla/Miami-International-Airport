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
<?php require_once 'config.php'; ?>

<?php include 'header.php'; ?>
   
<!-- Dynamic blog -->

<div class="container my-5">
<p class="fw-bolder fs-3 text-center">Our <span class="text-primary">Blogs</span></p>
<div class="row row-cols-1 row-cols-md-3 g-4">
    <?php
    $sql = "SELECT * FROM airline_blog AS LastSix ORDER BY `airline_id` ASC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="col">';
            echo '  <a href="blog.php?id='.$row['airline_id'].'" class="text-decoration-none text-dark">';
            echo '<div class="card h-100">';
            echo '<img src="' . $row['air_image'] . '" class="card-img-top" alt="...">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . $row['air_title'] . ' <span class="text-primary">' . $row['air_location'] . '</span></h5>';
            echo '<p class="card-text">' . $row['air_description'] . '</p>';
            echo '</div>'; // End card-body
            echo '</div>'; // End card
            echo '</div>'; // End col
        }
    } else {
        echo "No blogs found.";
    }
    ?>
</div> <!-- End row -->

</div> <!-- End container -->



<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>

</body>
</html>