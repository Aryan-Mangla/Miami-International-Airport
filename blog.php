<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.tiny.cloud/1/ebs4evfkr4f8epp30mmtuelc8xqoiktmffjcg8sr6zt11ibd/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body>
    
    <?php 
    $category = "airlines";
    $category1 = "blog";

    require_once 'config.php';
    if(isset($_GET['id'])) {
        $blog_id = $conn->real_escape_string($_GET['id']);

    // Fetch the blog details from the database based on the blog ID
    $sql = "SELECT * FROM airline_blog WHERE `airline_id` = $blog_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $title = $row['air_title'];
        $blog_description = $row['air_description'];
        $blog_title = $row['air_title'];
        $blog_location = $row['air_location'];
        $blog_image = $row['air_image'];
        $blog_design = $row['air_design'];


    } else {
        // Blog not found, handle error or redirect
        header("Location: error.php");
        exit();
    }
} else {
    // Blog ID not provided, handle error or redirect
    header("Location: error.php");
    exit();
} ?>
   <?php include 'header.php'; ?>

   <?php if ($blog_design == "Design_1"): ?>
<!-- Title and description -->
<div class="container">
<div class="d-flex justify-content-center"> 
                <h1 class="text-center mt-3"><?php echo $title; ?><span class="text-primary"> <?php echo $blog_location;?> </span></h1> 
                
                </div>
    <nav aria-label="breadcrumb" class="d-flex justify-content-center">
        <ol class="breadcrumb">
           <li class="breadcrumb-item"> <a href="index.php" class="text-decoration-none link-dark"><?php echo $category; ?></a></li>
           <li class="breadcrumb-item"> <a href="index.php" class="text-decoration-none link-dark"><?php echo $category1; ?></a></li>
            <li class="breadcrumb-item text-danger fw-bolder"><?php echo $blog_title; ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="blog-content">
                <p><?php echo $blog_description ?></p>
            </div>
        </div>
    </div>
</div>
<div class="container my-5">
<?php  echo '<img src="' . $blog_image . '" class="card-img-top" alt="..." style="    height: 100%;
    width: 100%;
    border-radius: 5em;">'; ?>
</div>

<!-- Headquater -->
      <div class="container">
        <div class="card">
            <div class="card-header">
              Headquaters
            </div>
            <div class="card-body">
              <h5 class="card-title">Airline</h5>
              <p class="card-text">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Repudiandae, harum?.</p>

            </div>
          </div>
      </div>
      <!-- City Offices -->
<?php         
$sql_offices = "SELECT * FROM city_offices WHERE blog_id = $blog_id";
$result_offices = $conn->query($sql_offices);

// Check if there are city offices to display
if ($result_offices->num_rows > 0) {
    echo '<div class="container mt-5">';
    echo '<div class="card">';
    echo '<div class="card-header d-flex justify-content-between"><p class="p-0 m-0">City Offices</p>   <a href="city-offices.php?id='.$row['airline_id'].'" class="btn btn-primary"> Add More</a> </div>';
    echo '<div class="card-body">';
    echo '<div class="row">';

    $col_count = 0;

    while ($office = $result_offices->fetch_assoc()) {
        if ($col_count % 2 == 0) {
            echo '<div class="col-md-6">';
        }

        echo '<a href="' . htmlspecialchars($office['office_link'], ENT_QUOTES, 'UTF-8') . '" class="text-dark anch12"><p class="card-text">' . htmlspecialchars($office['office_text'], ENT_QUOTES, 'UTF-8') . '</p></a>';

        if ($col_count % 2 != 0) {
            echo '</div>';
        }

        $col_count++;
    }

    // Close the last column if the number of items is odd
    if ($col_count % 2 != 0) {
        echo '</div>';
    }

    echo '</div>'; // Close row
    echo '</div>'; // Close card-body
    echo '</div>'; // Close card
    echo '</div>'; // Close container
} else {
    echo '<div class="container mt-5"><div class="card"><div class="card-header"><p class="p-0 m-0">City Offices</p>  </div><div class="card-body"><p>No city offices found.</p></div></div></div>';
}
?>
<?php else: ?>
<!-- Design2 -->
<div class="container">
    <div class="row my-5">
        <div class="col-3">
            <div data-bs-spy="scroll" data-bs-target="#navbar-example3" data-bs-smooth-scroll="true" class="scrollspy-example-2 border-end" tabindex="0">
                <div class="container my-5">
<p class="fw-bolder fs3">Our Latest <span class="text-primary">Blogs</span></p>
<div class=" g-4">
    <?php
    $sql = "SELECT * FROM (SELECT * FROM airline_blog ORDER BY `airline_id` DESC LIMIT 6) AS LastSix ORDER BY `airline_id` ASC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="col my-3">';
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
<div class="d-flex justify-content-center align-items-center">
    <a href="blogs.php" class="btn btn-primary mt-3">View More</a>
</div>
</div> <!-- End container -->
            </div>
        </div>
<!-- Right -->
        <div class="col-8">
            <div data-bs-spy="scroll" data-bs-target="#navbar-example3" data-bs-smooth-scroll="true" class="scrollspy-example-2" tabindex="0">
            <div class="container">
                <div class="d-flex justify-content-between"> 
                <h1 class="text-center mt-3"><?php echo $title; ?><span class="text-primary"> <?php echo $blog_location;?> </span></h1> 
                 
        
                </div>
   
           <nav aria-label="breadcrumb" class="d-flex justify-content-center">
         <ol class="breadcrumb">
           <li class="breadcrumb-item"> <a href="index.php" class="text-decoration-none link-dark"><?php echo $category; ?></a></li>
           <li class="breadcrumb-item"> <a href="index.php" class="text-decoration-none link-dark"><?php echo $category1; ?></a></li>
            <li class="breadcrumb-item text-danger fw-bolder"><?php echo $blog_title; ?></li>
         </ol>
          </nav>

          <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="blog-content">
                <p><?php echo $blog_description ?></p>
            </div>
        </div>
    </div>
</div>
<div class="container my-5">
<?php  echo '<img src="' . $blog_image . '" class="card-img-top" alt="..." style="    height: 100%;
    width: 100%;
    border-radius: 5em;">'; ?>
</div>

<!-- Headquater -->
      <div class="container">
        <div class="card">
            <div class="card-header">
              Headquaters
            </div>
            <div class="card-body">
              <h5 class="card-title">Airline</h5>
              <p class="card-text">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Repudiandae, harum?.</p>

            </div>
          </div>
      </div>
      <?php

      $sql_offices = "SELECT * FROM airline_blog WHERE air_title LIKE '%$title%' OR air_description LIKE '%$blog_description%'";
      $result_offices = $conn->query($sql_offices);
      if ($result_offices->num_rows > 0) {
     
        echo '<div class="container mt-5">';
        echo '<div class="card">';
        echo '<div class="card-header d-flex justify-content-between"><p class="p-0 m-0">City Offices</p> </div>';
        echo '<div class="card-body">';
        echo '<div class="row">';
    
        $col_count = 0;
    
        while ($office = $result_offices->fetch_assoc()) {
            if ($col_count % 2 == 0) {
                echo '<div class="col-md-6">';
            }
    
            echo '<div class="col">';
            echo '  <a href="blog.php?id='.$office['airline_id'].'" class="anch12 text-dark">';
            echo '<div class="card border-0 h-100">';
            echo '<div class="card-body">';
            echo '<p class="card-text ">' . $office['air_title'] . '</p></a>';
            echo '</div>'; // End card-body
            echo '</div>'; // End card
            echo '</div>'; // End col
            if ($col_count % 2 != 0) {
                echo '</div>';
            }
    
            $col_count++;
        }
    
        // Close the last column if the number of items is odd
        if ($col_count % 2 != 0) {
            echo '</div>';
        }
    
        echo '</div>'; // Close row
        echo '</div>'; // Close card-body
        echo '</div>'; // Close card
        echo '</div>'; // Close container
    } else {
        echo 'No data found';
    }
      ?>
            </div>
        </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <?php
                $airline_id = isset($_GET['airline_id']) ? intval($_GET['airline_id']) : 0;
                if(isset($_GET['id'])) {
                    $airline_id = $conn->real_escape_string($_GET['id']);
                
                }
                ?>
                <!-- Comment Form -->
                <form action="comments.php" method="POST">
                    <div class="form-group">
                        <label for="name">Your Name:</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="rating">Rating:</label>
                        <div class="star-rating">
                    <input type="radio" id="star5" name="rating" value="5"><label for="star5" title="5 stars">★</label>
                    <input type="radio" id="star4" name="rating" value="4"><label for="star4" title="4 stars">★</label>
                    <input type="radio" id="star3" name="rating" value="3"><label for="star3" title="3 stars">★</label>
                    <input type="radio" id="star2" name="rating" value="2"><label for="star2" title="2 stars">★</label>
                    <input type="radio" id="star1" name="rating" value="1"><label for="star1" title="1 star">★</label>
                </div>
                    </div>
                    <div class="form-group">
                        <label for="comment">Enter your comment:</label>
                        <textarea id="comment" name="comment" class="form-control"></textarea>
                    </div>
                    <input type="hidden" name="airline_id" value="<?php echo $airline_id; ?>">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>

                <!-- Comments Section -->
                <div id="commentsSection" class="mt-5">
                    <h4>Comments:</h4>
                    <div id="commentsList">
                        <?php
                        // Fetch approved comments for the specific event from the database
                        $sql = "SELECT name, rating, comment FROM comments WHERE approved = TRUE AND airline_id= $airline_id ORDER BY date DESC";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            // Output data of each row
                            while($row = $result->fetch_assoc()) {
                                echo '<div class="card mb-3">';
                                echo '<div class="card-body">';
                                echo '<h5 class="card-title">' . htmlspecialchars($row['name']) . '</h5>';
                                echo '<h6 class="card-subtitle mb-2 text-muted">Rating: ' . str_repeat('★', $row['rating']) . str_repeat('☆', 5 - $row['rating']) . '</h6>';
                                echo '<p class="card-text">' . htmlspecialchars($row['comment']) . '</p>';
                                echo '</div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p>No comments yet. Be the first to comment!</p>';
                        }

                        $conn->close();
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
   <?php include 'footer.php'; ?>
     
<script>
    const stars = document.querySelectorAll('.star-rating input[type="radio"] + label');

stars.forEach((star) => {
    star.addEventListener('mouseover', () => {
        star.classList.add('hover');
        const previousStars = Array.from(star.parentElement.children).slice(0, Array.from(star.parentElement.children).indexOf(star));

        previousStars.forEach((prevStar) => {
            prevStar.classList.add('hover');
        });
    });

    star.addEventListener('mouseout', () => {
        star.classList.remove('hover');
        const previousStars = Array.from(star.parentElement.children).slice(0, Array.from(star.parentElement.children).indexOf(star));

        previousStars.forEach((prevStar) => {
            prevStar.classList.remove('hover');
        });
    });

    star.addEventListener('click', () => {
        star.classList.toggle('clicked');
        const previousStars = Array.from(star.parentElement.children).slice(0, Array.from(star.parentElement.children).indexOf(star));

        previousStars.forEach((prevStar) => {
            prevStar.classList.toggle('clicked');
        });
    });
});
</script>
      <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
<?php
// $conn->close();
?>
</body>
</html>