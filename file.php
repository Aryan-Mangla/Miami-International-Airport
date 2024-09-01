<?php
require_once "config.php";

$url = $_SERVER['REQUEST_URI'];
$urlSegments = explode('/', rtrim($url, '/'));
$pageSlug = end($urlSegments); // Get the last segment of the URL
// Function to truncate text to a specified length
function truncateText($text, $maxLength = 100) {
    $text = strip_tags($text);
    if (strlen($text) > $maxLength) {
        $text = substr($text, 0, $maxLength) . '...';
    }
    return $text;
}
// Fetch the page details by slug
$sql = "SELECT id, title, content, slug, meta_desc, meta_cronical, meta_Keywords, noindex, nofollow, admin_name, date, updated_at, design, parent_id FROM pages WHERE slug = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $pageSlug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Page found, fetch the details
    $row = $result->fetch_assoc();
    $pageId = $row['id'];
    $title = $row["title"];
    $content = $row["content"];
    $metaDescription = $row["meta_desc"];
    $metaCanonical = $row["meta_cronical"];
    $metaKeywords = $row["meta_Keywords"];
    $noindex = $row["noindex"] == 1 ? "noindex" : "index";
    $nofollow = $row["nofollow"] == 1 ? "nofollow" : "follow";
    $adminName = $row["admin_name"];
    $datePublished = $row["date"];
    $dateModified = $row["updated_at"];
    $jsondesign = $row["design"];
    $parents_id = $row["parent_id"];
    
 
// Extract the first paragraph from the content
$firstParagraph = '';
if (preg_match('/<p>(.*?)<\/p>/', $content, $matches)) {
    $firstParagraph = strip_tags($matches[1]);
} else {
    $paragraphs = preg_split('/\r\n|\r|\n/', $content);
    $firstParagraph = strip_tags($paragraphs[0]);
}

// Truncate the first paragraph to 90 characters
$firstParagraph = truncateText($firstParagraph, 90);


    // Fetch the FAQs related to this page
    $faqSql = "SELECT title, content FROM blog_faq WHERE blog_id = ?";
    $faqStmt = $conn->prepare($faqSql);
    $faqStmt->bind_param("i", $pageId);
    $faqStmt->execute();
    $faqResult = $faqStmt->get_result();
    $faqs = $faqResult->fetch_all(MYSQLI_ASSOC);

   // Determine schema type based on design
if ($jsondesign === 'Design_3') {
    $schemaType = 'BlogPosting';
    $jsonLd = [
        "@context" => "https://schema.org",
        "@type" => $schemaType,
        "headline" => $title,
        "description" => $metaDescription,
        "mainEntityOfPage" => [
            "@type" => "WebPage",
            "@id" => "https://miamiairport-mia.com/" . $pageSlug
        ],
        "author" => [
            "@type" => "Person",
            "name" => $adminName
        ],
        "datePublished" => $datePublished,
        "dateModified" => $dateModified,
        "articleBody" => $firstParagraph
    ];
} else {
    $schemaType = 'WebPage';
    $jsonLd = [
        "@context" => "https://schema.org",
        "@type" => $schemaType,
        "headline" => $title,
        "description" => $metaDescription,
        "mainEntityOfPage" => [
            "@type" => "WebPage",
            "@id" => "https://miamiairport-mia.com/" . $pageSlug
        ],
        "author" => [
            "@type" => "Person",
            "name" => $adminName
        ],
        "datePublished" => $datePublished,
        "dateModified" => $dateModified
    ];
}

    if (!empty($faqs)) {
        $jsonLd["mainEntity"] = [
            "@type" => "FAQPage",
            "mainEntity" => array_map(function($faq) {
                return [
                    "@type" => "Question",
                    "name" => $faq['title'],
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => $faq['content']
                    ]
                ];
            }, $faqs)
        ];
    }
} 

function parentbread($conn, $parentId) {
    $sql = "SELECT title, slug FROM pages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $parentId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Initialize breadcrumb schema
$breadcrumbs = [
    "@context" => "https://schema.org",
    "@type" => "BreadcrumbList",
    "itemListElement" => []
];

// Add "Home" breadcrumb
$breadcrumbs["itemListElement"][] = [
    "@type" => "ListItem",
    "position" => 1,
    "name" => 'Home',
    "item" => 'https://miamiairport-mia.com/'
];

// Fetch and add parent page if parents_id is available
if (!empty($parents_id)) {
    $parentPage = parentbread($conn, $parents_id);
    if ($parentPage) {
        $breadcrumbs["itemListElement"][] = [
            "@type" => "ListItem",
            "position" => 2,
            "name" => $parentPage['title'],
            "item" => 'https://miamiairport-mia.com/' . $parentPage['slug']
        ];
    }
}

// Safeguard to ensure variables are set
if (isset($parentPage) && isset($pageSlug)) {
    // Fetch the current page title
    $current_page_title = isset($current_page_title) ? $current_page_title : 'Default Page Title';

    // Add current page breadcrumb with the correct title
    $breadcrumbs["itemListElement"][] = [
        "@type" => "ListItem",
        "position" => count($breadcrumbs["itemListElement"]) + 1,
        "name" => htmlspecialchars($current_page_title, ENT_QUOTES, 'UTF-8'),
        "item" => 'https://miamiairport-mia.com/' . htmlspecialchars($parentPage['slug'], ENT_QUOTES, 'UTF-8') . '/' . htmlspecialchars($pageSlug, ENT_QUOTES, 'UTF-8')
    ];
} else {
    // Log or handle the error gracefully
    error_log("Breadcrumbs: Missing parentPage or pageSlug variables.");
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($metaKeywords); ?>">
    <?php if (!empty($noindex) || !empty($nofollow)): ?>
    <meta name="robots" content="<?php echo trim("{$noindex}, {$nofollow}"); ?>">
    <?php endif; ?>
       <link rel="canonical" href="<?php echo htmlspecialchars($metaCanonical); ?>">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link href="/bootstrap.min.css" rel="stylesheet">
    <link href="/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/ebs4evfkr4f8epp30mmtuelc8xqoiktmffjcg8sr6zt11ibd/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://kit.fontawesome.com/efb7a73d46.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png" sizes="32x32" href="/Pic/miami-fav-icon-transformed.png">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
    <script type="application/ld+json">
        <?php echo json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
       
    </script>
    <script type="application/ld+json">
        
   <?php echo json_encode($breadcrumbs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
    </script>
    <meta name="google-site-verification" content="hs5M8djd6txo9XiFZzajmHmwxYAQqh9CBMDPHIJh-us" />
    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-F49YX5C0SX"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-F49YX5C0SX');
</script>
<style>
    p{
        margin-top:1rem!important;
    }
    
</style>
</head>

<body>
<?php 
    
    require_once "config.php";

    
?>
    <?php


function generateTOC($content) {
    $toc = '<div class="table-of-contents p-3" style="width: 20rem;box-shadow: rgba(50, 50, 93, 0.25) 0px 13px 27px -5px, rgba(0, 0, 0, 0.3) 0px 8px 16px -8px;">';
    $toc .= '<h2 style="font-size: 1.5rem!important;">Table of Contents</h2><ul>';
    $matches = [];
    preg_match_all('/<h([2])(.*?)>(.*?)<\/h\1>/', $content, $matches, PREG_SET_ORDER);
    
    // Track IDs to ensure uniqueness
    $usedIds = [];

    foreach ($matches as $match) {
        $level = $match[1];
        $attributes = $match[2]; // Capture any additional attributes
        $title = strip_tags($match[3]);

        // Generate a safe ID
        $id = preg_replace('/[^a-z0-9\-_\?\.\:\!]/', '-', strtolower($title)); // Replace special characters with dashes
// $id = trim($id, '-'); // Trim leading and trailing dashes

        // Ensure the ID is unique
        $originalId = $id;
        $counter = 1;
        while (in_array($id, $usedIds)) {
            $id = $originalId . '-' . $counter;
            $counter++;
        }
        $usedIds[] = $id;

        $toc .= '<li class="toc-level-' . $level . '"><a class="nav-hov text-decoration-none d-block" href="#' . $id . '">' . $title . '</a></li>';
        
        // Add IDs to headings in content for TOC links, retaining existing attributes and adding the offset class
        $newHeading = '<h' . $level . ' id="' . $id . '"' . $attributes . '>' . $match[3] . '</h' . $level . '>';
        $content = str_replace($match[0], '<span class="anchor-offset" id="' . $id . '"></span>' . $newHeading, $content);
    }
    
    $toc .= '</ul></div>';
    return [$toc, $content];
}



   
    $url = isset($_GET['url']) ? $_GET['url'] : '';
    $urlSegments = explode('/', $url);



// Fetch page based on URL
$urlSegments = explode('/', trim($url, '/'));

// Check for case-sensitive duplicate segments
$lowercaseSegments = array_map('strtolower', $urlSegments);
if (count($lowercaseSegments) !== count(array_unique($lowercaseSegments))) {
     header("Location: ../nopage");
}
    // Create a connection to the database
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
      header("Location: ../nopage");
    }
$url = $_SERVER['REQUEST_URI'];
if (substr($url, -1) !== '/') {
    $url .= '/';
    header("Location: $url", true, 301); // Use 301 redirect for SEO
    exit;
}

// Fetch page based on URL
$urlSegments = explode('/', trim($url, '/'));

    // Fetch page based on URL
    $pageSlug = end($urlSegments); // Get the last segment of the URL
    $sql = "SELECT * FROM pages WHERE BINARY slug = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $pageSlug);
    $stmt->execute();
    $result = $stmt->get_result();
// Function to get the parent slug
function getParentSlug($parent_id, $conn) {
    $query = "SELECT slug FROM pages WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $parent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['slug'];
}
    if ($result->num_rows > 0) {
                // Page found, display content
        $row = $result->fetch_assoc();
         // Check if the page has a parent and if the parent slug is included in the URL
    if (!is_null($row['parent_id'])) {
        $parentSlug = getParentSlug($row['parent_id'], $conn);

        // If the parent slug is not found in the URL segments, redirect to nopage
        if (!in_array($parentSlug, $urlSegments)) {
            header("Location: ../nopage");
            exit;
        }
    }
        $pageId = $row["id"];
        $title = $row["title"];
         $status = $row['status'];
         $image = $row["image"];
        $design = $row["design"];
         $shouldDisplayPage = ($status == "published") || ($status == "draft" && isset($_SESSION['email']));
        if ($design=="Design_3"){
            include 'new_header.php';
        }else{
            include 'new_header.php';
        }
echo'<div class="container-fluid p-0">';
 if ($shouldDisplayPage) {
   if($design == "Design_1"){
            echo "<div class=' p-0'>";
            echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
            echo "<h1 class='ms-md-5 ms-4 'style='margin-bottom:0.5rem;font-size: 3rem;'>$title</h1>";
            echo '</div>';
            if(isset($_SESSION['email'])){

                echo "<a href='../PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
         
                echo "<a href='..//PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
              
            }
            echo "</div>";
           
            echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb bread-bck ps-md-5 ps-4 ">';
         echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a href="../../" class="text-dark text-decoration-none" >Home</a></li>';
            $breadcrumbUrl = '';
            $segmentCount = count(array_filter($urlSegments));
            foreach ($urlSegments as $index => $segment) {
                if (!empty($segment)) {
                    $breadcrumbUrl .= '/' . $segment;
                    $isLastSegment = ($index === $segmentCount - 1);
                    $class = $isLastSegment ? 'text-dark fw-semibold' : 'text-dark';
                    $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
         
                    
                }
                    }
                    echo '</ol></nav>';
                    echo'<div class="container-fluid px-md-5 px-4">';
            $content = $row["content"];
            echo "<p>$content</p>";

            // Check if the page has child pages
            $childSql = "SELECT * FROM pages WHERE parent_id = ?";
            $childStmt = $conn->prepare($childSql);
            $childStmt->bind_param("i", $pageId);
            $childStmt->execute();
            $childResult = $childStmt->get_result();
    
            if ($childResult->num_rows > 0) {
                // Page has child pages, display the links to the child pages
               
                echo "<div class='row'>";
    
                $childCount = 0;
                while ($childRow = $childResult->fetch_assoc()) {
                    $childTitle = $childRow["title"];
                    $childSlug = $childRow["slug"];
                    $childImage = $childRow["image"]; // Assuming you have an image column for child pages
                    $childDate =$childRow["date"];
                    //  $name = $_SESSION['Name'];
                    $childUrl = generatePageUrl($childRow["id"]);
    
                    if ($childCount > 0 && $childCount % 3 == 0) {
                        echo '</div><div class="row mt-4">';
                    }
    
                    echo "<div class='col-md-4 mb-4'>";
                    echo "<a href='../$childUrl' class='text-decoration-none text-dark ' >";
                    echo "<div class='card border-0 h-100' style='box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;'>";
                   if ($childImage) {
                echo "<img src='$childImage' class='card-img-top rounded-0' style='
    max-height: 23em;    height: 16em;
    object-fit: cover;
' alt='$childTitle'>";
            }
            echo "<div class='card-body position-absolute w-100 p-2' style='    bottom: 1px;
    background: #1606a7a9;'>";
            echo "<h5 class='card-title text-light text-center fw-bold'>$childTitle</h5>";
            // echo "<small class='badge theme-bg my-2'>$childDate</small>";
            // echo "<small class='badge theme-bg my-2'>$childName</small>";
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
            $childStmt->close();
        
            echo "</div>"; // Close title-container
        }
        
        // Design with Helpful Resources
        elseif($design == "Design_2"){
    echo "<div class=' p-0'>";
    echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
    echo "<h1 class='ms-5'>$title</h1>";
    echo '</div>';
    if(isset($_SESSION['email'])){

        echo "<a href='../PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
 
        echo "<a href='../PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
      
    }
    echo "</div>";
   
    echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb bread-bck ps-5 ">';
 echo '<li class="breadcrumb-item"><a href="../../" class="text-dark text-decoration-none" style="font-size: .875rem; line-height: 1.25rem;">Home</a></li>';
    $breadcrumbUrl = '/';
    $segmentCount = count(array_filter($urlSegments));
    foreach ($urlSegments as $index => $segment) {
        if (!empty($segment)) {
            $breadcrumbUrl .= '../' . $segment;
            $isLastSegment = ($index === $segmentCount - 1);
            $class = $isLastSegment ? 'text-dark fw-semibold' : 'text-dark';
            $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
            }
            }
            echo '</ol></nav>';
            echo'<div class="container-fluid px-5">';
    $content = $row["content"];
    echo "<p>$content</p>";

    // Check if the page has child pages
    $childSql = "SELECT * FROM pages WHERE parent_id = ?";
    $childStmt = $conn->prepare($childSql);
    $childStmt->bind_param("i", $pageId);
    $childStmt->execute();
    $childResult = $childStmt->get_result();

    if ($childResult->num_rows > 0) {
        // Page has child pages, display the links to the child pages
       
        echo "<div class='row'>";

        $childCount = 0;
        while ($childRow = $childResult->fetch_assoc()) {
            $childTitle = $childRow["title"];
            $childSlug = $childRow["slug"];
            $childImage = $childRow["image"]; // Assuming you have an image column for child pages
            $childDate =$childRow["date"];
            //  $name = $_SESSION['Name'];
            $childUrl = generatePageUrl($childRow["id"]);

            if ($childCount > 0 && $childCount % 3 == 0) {
                echo '</div><div class="row mt-4">';
            }

            echo "<div class='col-md-4 mb-4'>";
            echo "<a href='../$childUrl' class='text-decoration-none text-dark ' >";
            echo "<div class='card border-0 h-100' style='box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;'>";
           if ($childImage) {
                echo "<img src='$childImage' class='card-img-top rounded-0' style='
    max-height: 23em;
' alt='$childTitle'>";
            }
            echo "<div class='card-body position-absolute w-100 p-2' style='    bottom: 1px;
    background: #1606a7a9;'>";
            echo "<h5 class='card-title text-light text-center fw-bold'>$childTitle</h5>";
            // echo "<small class='badge theme-bg my-2'>$childDate</small>";
            // echo "<small class='badge theme-bg my-2'>$childName</small>";
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
    $childStmt->close();
echo'
        <div class="text-center">
            <h2>Helpful Resources</h2>
            <div class="btn btn-size-group mt-3" role="group" aria-label="Accessibility Options">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Accessibility Statement</a>
            <a href="#" class="btn btn-size btn btn-outline-secondary mob-button">Accessibility Services</a>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Traveler Tips</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-globe icond "></i></span>  <a href="#" class="d-flex align-items-center justify-content-between my-3">International Travelers <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-passport icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Infrequent Traveler Tips <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-child icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Children and Families <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-wheelchair icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Wheelchair Requests <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-map icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Interactive Map <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-paw icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Traveling with Pets <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Business Information</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-briefcase icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Doing Business at MIA <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-users icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Business Diversity and Development <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-store icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Concessions Opportunities <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-info-circle icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> General Tenant Information <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-balance-scale icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> CEEA: Center of Equity and Excellence in Aviation <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-gavel icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Rules and Regulations <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    
 ';
    echo "</div>"; // Close title-container
}
        
// Design with side bar left
elseif($design == "Design_3"){
    
    echo "<div class='px-md-5 px-4'>";
    echo "<div class='title-container'>";
    echo '<nav aria-label="breadcrumb" class="mt-2"><ol class="breadcrumb" style="justify-content: flex-start;">';
  echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a href="../../" class="text-dark text-decoration-none" >Home</a></li>';
    $breadcrumbUrl = '..';
    $segmentCount = count(array_filter($urlSegments));
    foreach ($urlSegments as $index => $segment) {
        if (!empty($segment)) {
            $breadcrumbUrl .= '/' . $segment;
            $isLastSegment = ($index === $segmentCount - 1);
            $class = $isLastSegment ? 'theme-color mt-3' : 'theme-color';
            $segmentWithoutHyphens = str_replace('-', ' ', $segment);
            $formattedSegment = ucwords($segmentWithoutHyphens);
            echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="../' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
        }
    }
    echo '</ol></nav>';
    echo "</div>"; // Close title-container
    $author = $row["admin_name"];
    $date = $row["date"];
    echo "<h1 class='mb-4'>$title</h1>";
    
    echo '<div class="d-md-flex justify-content-start">';
    echo "<p class='pe-2' style='font-size: .875rem; line-height: 1.25rem;'><i class='fas fa-calendar-alt'></i> Updated. $date</p>";
    echo "<p class='' style='font-size: .875rem; line-height: 1.25rem;'><i class='fas fa-user'></i> Created by: $author</p>";
    echo "</div>";
    echo "</div>";

    $image = $row["image"];
    if ($image) {
        echo "<img src='$image' style='height: 100%; width: 100%;' class='img-fluid image-mob-p mb-5' alt='" . $row["title"] . "'>";
    }

    echo "<div class='blog-content-p'>";
    echo '<div class="row mb-5">';
    
    // Right Content
    echo '<div class="col-md-9">';
    echo '<div class="container">';
    

// Add the specified code for the child page content
$content = $row["content"];

// Generate TOC and update content with heading IDs
list($toc, $updatedContent) = generateTOC($content);


echo '<div class="dropdown">';
    echo '    <button class="btn btn-light dropdown-toggle  text-dark" style="
    position: absolute;
    bottom: 42px;
    background-color: #6356ce;
    color: white!important;
    border: none;
" type="button" id="tocDropdown" data-bs-toggle="dropdown" aria-expanded="false">Table of Contents</button>';
    echo '    <ul class="dropdown-menu border-0" aria-labelledby="tocDropdown">';
    echo '        <li><a class="dropdown-item" href="#">' . $toc . '</a></li>'; // Place TOC in dropdown
    echo '    </ul>';
    echo '</div>';
    
// Display the updated content
echo $updatedContent;

 
    echo '</div>';

// Fetch accordion items associated with the blog ID
$faq_sql = "SELECT * FROM blog_faq WHERE blog_id = '$pageId'";
$faq_result = $conn->query($faq_sql);
if ($faq_result->num_rows > 0) {
    echo '<h2 class="px-md-3 px-4 text-center my-4 p-3" style="
    border-bottom: 4px solid #1e03f4;
    color: white;
    background: #1e03f475;
    text-shadow: 1px 1px 2px #000;
">Frequently Asked Questions</h2>';
    echo '<div class="accordion accordion-flush px-md-3 px-4" id="accordionFlushExample">';
    while ($faq = $faq_result->fetch_assoc()) {
        $accordionId = 'faqCollapse' . $faq['id'];
        echo '<div class="accordion-item border rounded mb-3">'; // Added mb-3 for spacing
        echo '    <h2 class="accordion-header">';
        echo '        <button class="accordion-button" style="
    background-color: #1e03f412;
    color: black;
" type="button" data-bs-toggle="collapse" data-bs-target="#' . $accordionId . '" aria-expanded="true" aria-controls="' . $accordionId . '">';
        echo '            ' . $faq['title'];
        echo '        </button>';
        echo '    </h2>';
        echo '    <div id="' . $accordionId . '" class="accordion-collapse collapse show" aria-labelledby="flush-heading' . $faq['id'] . '" data-bs-parent="#accordionFlushExample">';
        echo '        <div class="accordion-body" style="overflow-wrap: break-word;">';
        echo '            ' . $faq['content'];
        // If admin then special option
        if (isset($_SESSION['admin']) && $_SESSION['admin'] === '1') {
            echo '            <p> FAQ id: ' . $faq['id'] . '</p>';
            echo '            <a href="del_FAQ.php?id=' . $faq['id'] . '" class="delete-icon float-end" title="Delete FAQ">';
            echo '                <lord-icon src="https://cdn.lordicon.com/wpyrrmcq.json" trigger="hover" style="width:30px;height:30px"></lord-icon>';
            echo '            </a>';
        }
        echo '        </div>';
        echo '    </div>';
        echo '</div>'; // Close accordion-item
    }
    echo '</div>'; // Close accordion
} else {
    // Optional: Handle the case where no FAQs are found
}


    // Comment section start
    ?>

    <div class="container mt-md-5 mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8 w-100">
                <?php
                $blogId = urlencode($pageId); // Ensure $pageId holds the current blog post ID
                ?>
                <!-- Comment Form -->
               <!-- Comment Form -->
<form class="p-4 rounded-3" action="../comments.php" method="POST" style="box-shadow: rgba(50, 50, 93, 0.25) 0px 13px 27px -5px, rgba(0, 0, 0, 0.3) 0px 8px 16px -8px;">
    <div  class="form-group text-center fs-5"> 
    <h3>Rate Your Experience</h3>
     <p class="m-0" style="
    font-size: 1rem;
">We highly value your feedback! Kindly take a moment to rate your experience and provide us with your valuable feedback.</p>
    </div>
    <div class="form-group text-center fs-4">
        
        <div class="star-rating">
            <input type="radio" id="star5" name="rating" value="5"><label for="star5" title="5 stars">★</label>
            <input type="radio" id="star4" name="rating" value="4"><label for="star4" title="4 stars">★</label>
            <input type="radio" id="star3" name="rating" value="3"><label for="star3" title="3 stars">★</label>
            <input type="radio" id="star2" name="rating" value="2"><label for="star2" title="2 stars">★</label>
            <input type="radio" id="star1" name="rating" value="1"><label for="star1" title="1 star">★</label>
        </div>
    </div>
    <div class="row my-3">
        <div class="col-md-6">
            <div class="form-floating">
                <input type="text" class="form-control" id="name" name="name" placeholder="Your Name" required>
                <label for="name">Your Name</label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="Your Email" required>
                <label for="email">Your Email</label>
            </div>
        </div>
    </div>
    <div class="form-floating mb-3">
        <textarea class="form-control" id="comment" name="comment" placeholder="Enter your comment" style="height: 100px" required></textarea>
        <label for="comment">Enter your comment</label>
    </div>
    <input type="hidden" name="blog_id" value="<?php echo $blogId; ?>">
    <button type="submit" class="btn theme-bg text-white my-3 w-100">Submit</button>
</form>

   <?php
                        // Fetch approved comments for the specific blog post from the database
                        $sql = "SELECT name, rating, comment FROM comment_section WHERE approved = TRUE AND blog_id = $blogId ORDER BY date DESC";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
              
              echo'  <div id="commentsSection" class="mt-5 p-3 rounded-3" style="box-shadow: rgba(50, 50, 93, 0.25) 0px 13px 27px -5px, rgba(0, 0, 0, 0.3) 0px 8px 16px -8px;">
                    <h4>Comments:</h4>
                    <div id="commentsList">
                     ';
                            // Output data of each row
                            while ($row = $result->fetch_assoc()) {
                                echo '<div class="card mb-3">';
                                echo '<div class="card-body">';
                                echo '<h5 class="card-title">' . htmlspecialchars($row['name']) . '</h5>';
                                echo '<h6 class="card-subtitle mb-2 text-muted">Rating: ' . str_repeat('★', $row['rating']) . str_repeat('☆', 5 - $row['rating']) . '</h6>';
                                echo '<p class="card-text">' . htmlspecialchars($row['comment']) . '</p>';
                                echo '</div>';
                                echo '</div>';
                            }
                        
                  echo'  </div>
                </div>} ';
                        }?>
            </div>
        </div>
    </div>
    <?php
    // Comment section end
    echo '</div>';

    // Left Sidebar
    echo '<div class="col-md-3 other-blog-p">';

    echo '<div style="position: sticky; top: 72px;">';
         // Display the TOC
// echo $toc;
 
    // Fetch parent ID of the current child page
    $parentSql = "SELECT parent_id FROM pages WHERE id =?";
    $parentStmt = $conn->prepare($parentSql);
    if ($parentStmt === false) {
        die("Prepare failed: " . htmlspecialchars($conn->error));
    }

    $parentStmt->bind_param("i", $pageId);
    if (!$parentStmt->execute()) {
        die("Execute failed: " . htmlspecialchars($parentStmt->error));
    }

    $parentResult = $parentStmt->get_result();
    if ($parentResult === false) {
        die("Get result failed: " . htmlspecialchars($parentStmt->error));
    }

    if ($parentResult->num_rows > 0) {
        $parentRow = $parentResult->fetch_assoc();
        $parentId = $parentRow['parent_id'];

        // Now fetch other child pages of this parent
        $childSql = "SELECT * FROM pages WHERE parent_id =? AND id!=?";
        $childStmt = $conn->prepare($childSql);
        if ($childStmt === false) {
            die("Prepare failed: " . htmlspecialchars($conn->error));
        }

        $childStmt->bind_param("ii", $parentId, $pageId);
        if (!$childStmt->execute()) {
            die("Execute failed: " . htmlspecialchars($childStmt->error));
        }

        $childResult = $childStmt->get_result();
        if ($childResult === false) {
            die("Get result failed: " . htmlspecialchars($childStmt->error));
        }

        if ($childResult->num_rows > 0) {
       
            // Page has sibling child pages, display the links to the sibling child pages
            echo "<h2 class='fs-3 p-2 text-center  text-white mt-3 rounded-3' style='background-color: #6356ce; padding-right: .7rem!important; padding-top: .7rem!important; padding-left: .7rem!important; font-size:20px!important;'>Other Pages</h2>";
            echo "<div class='row mt-4'>";

            $childCount = 0;
            while ($childRow = $childResult->fetch_assoc()) {
                if ($childCount >= 6) {
                    break;
                }

                $childTitle = htmlspecialchars($childRow["title"]);
                $childSlug = htmlspecialchars($childRow["slug"]);
                $childImage = htmlspecialchars($childRow["image"]); // Assuming you have an image column for child pages
                $childUrl = generatePageUrl($childRow["id"]);

                echo "<div class='col-md-12 mb-4'>";
                echo "<a href='../../$childUrl' class='text-decoration-none text-dark'>";
                echo "<div class='card border-0' style='box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;'>";
                if ($childImage) {
                    echo "<img src='$childImage' class='card-img-top p-2' style='height: 12rem; object-fit: cover;' alt='$childTitle'>";
                }
                echo "<div class='card-body' style='padding: 0px 10px;'>";
                echo "<h5 class='card-title nav-hov' style='font-weight: 400; font-size: 18px!important; line-height: 28px;'>$childTitle</h5>";
                echo "</div></div></a></div>";

                $childCount++;
            }
            echo '</div>';

            if ($childResult->num_rows > 6) {
                echo "<div class='text-center mt-4'>";
              
                echo "</div>";
            }
        } else {
           
        }
    } else {
        header("Location: nopage");
    }
    echo '</div>';
    echo '</div>'; // End of row
    echo '</div>';
    echo "</div>";
}


// Airport Security
elseif($design == "Design_4"){
    echo "<div class=' p-0'>";
    echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
    echo "<h1 class='ms-5'>$title</h1>";
    echo '</div>';
    if(isset($_SESSION['email'])){

        echo "<a href='../PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
 
        echo "<a href='../PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
      
    }
    echo "</div>";
   
    echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb bread-bck ps-5 ">';
    echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a href="../../" class="text-dark text-decoration-none" >Home</a></li>';
    $breadcrumbUrl = '/';
    $segmentCount = count(array_filter($urlSegments));
    foreach ($urlSegments as $index => $segment) {
        if (!empty($segment)) {
            $breadcrumbUrl .= '../' . $segment;
            $isLastSegment = ($index === $segmentCount - 1);
            $class = $isLastSegment ? 'text-dark fw-semibold' : 'text-dark';
            $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
            }
            }
            echo '</ol></nav>';
            echo'<div class="container-fluid p-5 margin-dine">
        <div class="row">
              <div class="col-md-5">
            <div class="row">
                
                <div class="col p-3">
                    <div class="media3 clip-top-right mw-100" style="background-image:url(\'/Pic/airport-sec/sec1.jpg\');"></div>
                </div>
            </div>
        </div>
            <div class="col-md-7 mob-space ps-md-5 p-3 mt-md-2">
               <h3 class="fw-strong mt-md-3 mt-2">Be Security Ready</h3>
               <p class="fw-semibold">MIA has three security checkpoint areas in the Terminal to access all airlines gates: North Terminal, South Terminal and Central Terminal <br>

Tips for going through security:</p>
               <p>
<ul class="custom-bullet">
  <li>Liquids and gels in containers of 3.4oz or less</li>
  <li>Place your containers in a single one quart zip-top plastic bag before placing in the screening bin</li>
  <li>Place metal objects such as coins and keys in your carry-on bag</li>
  <li>Put laptop computers and other electronic devices in a screening bin</li>
  <li>Remove shoes, jackets and belts and place in a screening bin</li>
  <li>All baggage and vehicles are subject to search at any time</li>
  <li>Do not leave bags unattended as they will be confiscated</li>
  <li>Report unattended items or suspicious activity immediately to airport personnel</li>
  <li>TSA prohibits explosive materials, flammable items and other hazardous materials in your checked baggage.</li>
</ul>
</p>

              
            </div>
        </div>
    </div>
';

 echo'<div class="container-fluid p-5 margin-dine">
        <div class="row">
             
            <div class="col-md-7 mob-space mt-md-5 p-3">
               <h3 class="fw-strong mt-md-3 mt-2">TSA Cares</h3>
               <p class="fw-semibold">The Transportation Security Administration (TSA) is responsible for transitioning passengers through the airport’s security checkpoints. <br>

Tips for going through security:</p>
               <p>
<ul class="custom-bullet">
   <li>Travelers with disabilities who need to use the TSA Restricted Access lanes at all security checkpoints.</li>
  <li>For assistance through security, please contact TSA Cares. TSA Cares is a helpline that provides travelers with disabilities, medical conditions and other special circumstances additional assistance during the security screening process.</li>
</ul>
</p>

                <p class="mt-3">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Learn More at TSA Care</a>
</p>
            </div>
             <div class="col-md-5">
            <div class="row">
                
                <div class="col p-3">
                    <div class="media3 clip-top-right mw-100" style="background-image:url(\'/Pic/airport-sec/sec2.jpg\');"></div>
                </div>
            </div>
        </div>
        </div>
    </div>
';
            echo'  <!-- Planning Ahead -->
<div class="container-fluid parking-back mt-5 p-5 margin-dine">
    <div class="row mt-5 p-md-0 p-2">
        <!-- Walking Times Section -->
        <div class="col-md-12">
            <div class="text-light text-center">
                <h2>Average Times to Your Gate After Security</h2>
                <p class="my-4">Whether you take the train to the gates or walk the Bridge, below are the average walk times to get to your gate after you have passed the security checkpoint. These are estimates only and persons with reduced mobility may experience longer travel times.</p>
                <!-- <div class="btn btn-size-group" role="group" aria-label="Navigation Options">
                    <button type="button" class="btn btn-size btn btn-size-light">Parking Lots</button>
                    <button type="button" class="btn btn-size btn btn-size-outline-light">Average Walking Times</button>
                </div> -->
            </div>
            <div class="row mt-3">
                <div class="col-md-4 mb-3">
                    <div class="card walking-card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-map-marker-alt"></i> Concourse D (North Terminal)</h5>
                            <ul class="custom-bullet">
                                <li>Gates D1-D59: 5-15 minutes</li>
                                <li>Gates D60-D99: 10-20 minutes</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card walking-card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-map-marker-alt"></i> Concourse E (Central Terminal)</h5>
                            <ul class="custom-bullet">
                                <li>Gates E2-E33: 5-15 minutes</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card walking-card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-map-marker-alt"></i> Concourse F (Central Terminal)</h5>
                            <ul class="custom-bullet">
                                <li>Gates F3-F23: 5-15 minutes</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card walking-card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-map-marker-alt"></i> Concourse G (Central Terminal)</h5>
                            <ul class="custom-bullet">
                                <li>Gates G2-G19: 5-15 minutes</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card walking-card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-map-marker-alt"></i> Concourse H (South Terminal)</h5>
                            <ul class="custom-bullet">
                                <li>Gates H3-H17: 5-15 minutes</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card walking-card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-map-marker-alt"></i> Concourse J (South Terminal)</h5>
                            <ul class="custom-bullet">
                                <li>Gates J2-J18: 5-15 minutes</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Planning Ahead end -->';
            echo'<div class="container-fluid px-md-5 px-4">';
    $content = $row["content"];
    echo "<p class='mt-5'>$content</p>";

    // Check if the page has child pages
    $childSql = "SELECT * FROM pages WHERE parent_id = ?";
    $childStmt = $conn->prepare($childSql);
    $childStmt->bind_param("i", $pageId);
    $childStmt->execute();
    $childResult = $childStmt->get_result();

    if ($childResult->num_rows > 0) {
        // Page has child pages, display the links to the child pages
       
        echo "<div class='row'>";

        $childCount = 0;
        while ($childRow = $childResult->fetch_assoc()) {
            $childTitle = $childRow["title"];
            $childSlug = $childRow["slug"];
            $childImage = $childRow["image"]; // Assuming you have an image column for child pages
            $childDate =$childRow["date"];
            //  $name = $_SESSION['Name'];
            $childUrl = generatePageUrl($childRow["id"]);

            if ($childCount > 0 && $childCount % 3 == 0) {
                echo '</div><div class="row mt-4">';
            }

            echo "<div class='col-md-4 mb-4'>";
            echo "<a href='../$childUrl' class='text-decoration-none text-dark ' >";
            echo "<div class='card border-0 h-100' style='box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;'>";
           if ($childImage) {
                echo "<img src='$childImage' class='card-img-top rounded-0' style='
    max-height: 23em;
' alt='$childTitle'>";
            }
            echo "<div class='card-body position-absolute w-100 p-2' style='    bottom: 1px;
    background: #1606a7a9;'>";
            echo "<h5 class='card-title text-light text-center fw-bold'>$childTitle</h5>";
            // echo "<small class='badge theme-bg my-2'>$childDate</small>";
            // echo "<small class='badge theme-bg my-2'>$childName</small>";
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
    $childStmt->close();
   echo'<div class="container-fluid my-5 p-0">
    <div class="row">
    <h3 class="text-center mb-md-3">Airport Security Checkpoints</h3>
        <div class="col-md-4">
            <div class="card my-md-0 my-4">
                <div class="card-header">
                    <h5 class="card-title">North Terminal</h5>
                </div>
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Concourse D:</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Checkpoint 1:</strong> 4:45 am – 8:45 pm<br>TSA Precheck available during checkpoint hours</li>
                        <li class="list-group-item"><strong>Checkpoint 2:</strong> Open 24 hours<br>American Airlines priority lane available</li>
                        <li class="list-group-item"><strong>Checkpoint 3:</strong> 4:00 am - 9:45 pm</li>
                        <li class="list-group-item"><strong>Checkpoint 4:</strong> 4:45 am - 8:45 pm</li>
                        <li class="list-group-item"><strong>Checkpoint DFIS:</strong> 5:15 am – 8:45 pm</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Central Terminal</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Checkpoint 5:</strong> 4:00 am - 10:45 pm<br>TSA Precheck available during checkpoint hours</li>
                        <li class="list-group-item"><strong>Checkpoint 6:</strong> 3:45 am – 10:45 pm<br>TSA Precheck available during checkpoint hours</li>
                        <li class="list-group-item"><strong>Checkpoint 7:</strong> 3:30 am – 10:15 pm<br>TSA Precheck available during checkpoint hours</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card my-md-0 my-4">
                <div class="card-header">
                    <h5 class="card-title">South Terminal</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Checkpoint 8:</strong> 4:00 am – 8:00 pm<br>TSA Precheck available during checkpoint hours</li>
                        <li class="list-group-item"><strong>Checkpoint 9:</strong> Open 24 Hours<br>TSA Precheck available during checkpoint hours</li>
                        <li class="list-group-item"><strong>Checkpoint 10:</strong> 9:45 am – 8:00 pm<br>TSA Precheck available during checkpoint hours</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
';
    echo '<div class="container-fluid mt-3 p-0">
    <div class="nav nav-tabs">
        <a class="nav-item nav-link security-nav active border-0 nav-hov mx-md-3" id="nav-item1" onclick="showContent(\'content1\')">Checkpoint Map</a>
        <a class="nav-item nav-link security-nav border-0 nav-hov mx-md-3" id="nav-item2" onclick="showContent(\'content2\')">Restrictions</a>
        <a class="nav-item nav-link security-nav border-0 nav-hov mx-md-3" id="nav-item3" onclick="showContent(\'content3\')">Hours</a>
        <a class="nav-item nav-link security-nav border-0 nav-hov mx-md-3" id="nav-item4" onclick="showContent(\'content4\')">Arrive early</a>
        <a class="nav-item nav-link security-nav border-0 nav-hov mx-md-3" id="nav-item5" onclick="showContent(\'content5\')">Travel Tips</a>
        <a class="nav-item nav-link security-nav border-0 nav-hov mx-md-3" id="nav-item6" onclick="showContent(\'content6\')">Call for help</a>
    </div>
    <div id="content" class="content border-0">
        <img src="/Pic/airport-sec/sec3.jpg" alt="Content for Item 1" class="img-fluid">
    </div>
</div>';
echo '<script>
    function showContent(contentId) {
        var content = document.getElementById(\'content\');
        var navItems = document.querySelectorAll(\'.security-nav\');
        navItems.forEach(function(navItem) {
            navItem.classList.remove(\'active\');
        });
        switch(contentId) {
            case \'content1\':
                document.getElementById(\'nav-item1\').classList.add(\'active\');
                content.innerHTML = \'<img src="/Pic/airport-sec/sec3.jpg" alt="Content for Item 1" class="img-fluid">\';
                break;
            case \'content2\':
                document.getElementById(\'nav-item2\').classList.add(\'active\');
                content.innerHTML = `<p>The Prohibited Items list is not all-inclusive. The items listed are strictly prohibited from being carried into the aircraft. However, many of these items may be transported in checked baggage. If you have questions, check with your airline.</p>
               <ul>
    <li>The Prohibited Items list is not all-inclusive. The items listed are strictly prohibited from being carried into the aircraft. However, many of these items may be transported in checked baggage. If you have questions, check with your airline.</li>
    <li>If you have a medical condition that requires you to carry a needle and/or syringe either with you or in your carry-on baggage, then you also need to bring the medication that requires an injection. The medication must be packaged with a pharmaceutical label or professionally printed label identifying the medication.</li>
    <li>Avoid carrying bottles of liquid through the screening checkpoint.</li>
    <li>If you plan on purchasing food to carry on board the plane, wait until you have completed the screening process.</li>
    <li>Food, gifts, and other services are generally available in the concourses after screening.</li>
    <li>If you have special dietary needs, please contact your airline to confirm what arrangements will be provided on your flight.</li>
    <li>If you are traveling with gifts, wrap them after you arrive at your destination. They may have to be unwrapped for security inspection. Gifts should be packed in your checked luggage or shipped via mail, due to the limitations of carry-on items.</li>
</ul>`;
                break;
            case \'content3\':
                document.getElementById(\'nav-item3\').classList.add(\'active\');
                content.innerHTML = `<h3>North Terminal</h3>
                <h4>Concourse D:</h4>
                <ul>
                    <li><strong>Checkpoint 1:</strong> 4:45 am – 8:45 pm<br>TSA Precheck available during checkpoint hours</li>
                    <li><strong>Checkpoint 2:</strong> Open 24 hours<br>American Airlines priority lane available</li>
                    <li><strong>Checkpoint 3:</strong> 4:00 am - 9:45 pm</li>
                    <li><strong>Checkpoint 4:</strong> 4:45 am - 8:45 pm</li>
                    <li><strong>Checkpoint DFIS:</strong> 5:15 am – 8:45 pm</li>
                </ul>
                <h3>Central Terminal</h3>
                <ul>
                    <li><strong>Checkpoint 5:</strong> 4:00 am - 10:45 pm<br>TSA Precheck available during checkpoint hours</li>
                    <li><strong>Checkpoint 6:</strong> 3:45 am – 10:45 pm<br>TSA Precheck available during checkpoint hours</li>
                    <li><strong>Checkpoint 7:</strong> 3:30 am – 10:15 pm<br>TSA Precheck available during checkpoint hours</li>
                </ul>
                <h3>South Terminal</h3>
                <ul>
                    <li><strong>Checkpoint 8:</strong> 4:00 am – 8:00 pm<br>TSA Precheck available during checkpoint hours</li>
                    <li><strong>Checkpoint 9:</strong> Open 24 Hours<br>TSA Precheck available during checkpoint hours</li>
                    <li><strong>Checkpoint 10:</strong> 9:45 am – 8:00 pm<br>TSA Precheck available during checkpoint hours</li>
                </ul>
                <p>Real-time wait times of the security checkpoints are available to see which checkpoint is most convenient to your gate.</p>`;
                break;
            case \'content4\':
                document.getElementById(\'nav-item4\').classList.add(\'active\');
                content.innerHTML = `<p>Arrival time recommendations vary by airline and day of travel, so please check with your airline. Remember to leave adequate time for transit or parking, checking baggage and getting through security.</p>
                <p>The Transportation Security Administration (TSA) encourages travelers to arrive at the airport at least two hours prior to a domestic flight and three hours prior to an international flight.</p>`;
                break;
            case \'content5\':
                document.getElementById(\'nav-item5\').classList.add(\'active\');
                content.innerHTML = `<p>To expedite your visit through Miami International Airport, become familiar with the latest security guidelines and suggestions:</p>
                <ul>
                    <li>Do not leave personal items unattended at any time in the airport or at curbside. This includes:
                        <ul>
                            <li>Purse</li>
                            <li>Briefcase</li>
                            <li>Electronic equipment</li>
                            <li>Carry-on bags</li>
                        </ul>
                    </li>
                    <li>Do not enter areas listed as “Restricted” or “Authorized.”</li>
                </ul>
                <p>If you are traveling with children:</p>
                <ul>
                    <li>Child safety seat recommendations</li>
                    <li>Requirements for unaccompanied minors</li>
                    <li>Identification for minors</li>
                </ul>
                <p>For travel tips regarding accessibility and assistance for travelers with disabilities, visit our myMIAaccess program. It is a dedicated platform for accessing services, amenities, and information when traveling through Miami International Airport.</p>
                <p>If traveling with a service animal, there are outdoor and indoor animal relief areas located in Concourses D, E, F, G, and J. All the MIA relief areas are equipped with dual surfaces and waste disposal stations (map locations).</p>`;
                break;
            case \'content6\':
                document.getElementById(\'nav-item6\').classList.add(\'active\');
                content.innerHTML = `<p>We want you to feel safe while traveling through our airport. Here are some of the security resources available should you need help:</p>
                <ul>
                    <li>In case of an emergency, including safety and medical, call 9-1-1.</li>
                    <li>In case of a non-emergency or to file a police report, contact the Airport District Police at 305-876-7373 or visit this website. They oversee the safety and security of MIA, its employees, and the traveling public.</li>
                    <li>The Miami-Dade Aviation Fire Rescue Division responds to medical emergencies as well as fires, fuel spills, and disasters. To provide rapid response, two stations are located at MIA.</li>
                    <li>Call the Crime Stoppers Tip Line 305-471-TIPS (8477) if you have a tip and see someone who has, or is about to, commit a crime at Miami International Airport. All callers will remain anonymous.</li>
                    <li>If you lost an item, please fill out a Lost Item Claim. Our Lost and Found team will be in touch with the status of your item.</li>
                </ul>`;
                break;
            default:
                content.innerHTML = \'Content not found\';
        }
    }
</script>';
    echo "</div>"; // Close title-container
}
// Airport Security ends


// lost and found start
elseif($design == "Design_5"){
    echo "<div class=' p-0'>";
    echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
    echo "<h1 class='ms-5'>$title</h1>";
    echo '</div>';
    if(isset($_SESSION['email'])){

        echo "<a href='../PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
 
        echo "<a href='../PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
      
    }
    echo "</div>";
   
    echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb bread-bck ps-5 ">';
    echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a href="../../" class="text-dark text-decoration-none" >Home</a></li>';
    $breadcrumbUrl = '/';
    $segmentCount = count(array_filter($urlSegments));
    foreach ($urlSegments as $index => $segment) {
        if (!empty($segment)) {
            $breadcrumbUrl .= '../' . $segment;
            $isLastSegment = ($index === $segmentCount - 1);
            $class = $isLastSegment ? 'text-dark fw-semibold' : 'text-dark';
            $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
            }
            }
            echo '</ol></nav>';
             echo'<div class="container-fluid p-5 margin-dine">
        <div class="row">
              <div class="col-md-5">
            <div class="row">
                
                <div class="col p-3">
                    <div class="media3 clip-top-right mw-100" style="background-image:url(\'/Pic/lost-and-found.jpg\');"></div>
                </div>
            </div>
        </div>
            <div class="col-md-7 mob-space ps-md-5 p-3 mt-md-4">
               <h3 class="fw-strong mt-md-3 mt-2">Lost an item? Where you lost it determines who to contact.</h3>
               <p class="">MIA lost and found only receives items lost in public areas of the airport.</p>
<p>
<span class="fw-bold my-2">Items Not Accepted:</span><br>
The following items cannot be accepted if outside a suitcase, backpack or other suitable travel bag: Soiled items, Abandoned items, Blankets, Hats, Pillows, Water Bottles (except name brand), Food items (except alcohol), Hazardous Materials (Sharp objects, vape pens, weapons, knives greater than 3.5 inches, and illegal drugs and illegal drug paraphernalia).</p>
              
      <p class="mt-3">
                <a href="#" class="btn btn-size btn btn-custom mob-button">File a Free Claim for Items Lost in a Public Area</a>
                <a href="#" class="btn btn-size btn btn-outline-secondary mob-button">Location</a></p>
              
            </div>
        </div>
    </div>
';
            echo'<div class="container-fluid px-md-5 px-4">';
    $content = $row["content"];
    echo "<p>$content</p>";

    // Check if the page has child pages
    $childSql = "SELECT * FROM pages WHERE parent_id = ?";
    $childStmt = $conn->prepare($childSql);
    $childStmt->bind_param("i", $pageId);
    $childStmt->execute();
    $childResult = $childStmt->get_result();

    if ($childResult->num_rows > 0) {
        // Page has child pages, display the links to the child pages
       
        echo "<div class='row'>";

        $childCount = 0;
        while ($childRow = $childResult->fetch_assoc()) {
            $childTitle = $childRow["title"];
            $childSlug = $childRow["slug"];
            $childImage = $childRow["image"]; // Assuming you have an image column for child pages
            $childDate =$childRow["date"];
            //  $name = $_SESSION['Name'];
            $childUrl = generatePageUrl($childRow["id"]);

            if ($childCount > 0 && $childCount % 3 == 0) {
                echo '</div><div class="row mt-4">';
            }

            echo "<div class='col-md-4 mb-4'>";
            echo "<a href='../$childUrl' class='text-decoration-none text-dark ' >";
            echo "<div class='card border-0 h-100' style='box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;'>";
            if ($childImage) {
                echo "<img src='$childImage' class='card-img-top rounded-0' style='
    max-height: 23em;
' alt='$childTitle'>";
            }
            echo "<div class='card-body position-absolute w-100 p-2' style='    bottom: 1px;
    background: #1606a7a9;'>";
            echo "<h5 class='card-title text-light text-center fw-bold'>$childTitle</h5>";
            // echo "<small class='badge theme-bg my-2'>$childDate</small>";
            // echo "<small class='badge theme-bg my-2'>$childName</small>";
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
    $childStmt->close();
   
 echo' <div class="container-fluid p-0 mt-5">
        <div class="row">
            <div class="col-md-3 mb-4 h-100">
                <div class="card lost-card h-100">
                    <div class="card-body">
                        <div class="card-icon mb-3 text-center">
                            <i class="fas fa-wrench fs-3" style="color: #6f42c1;"></i>
                        </div>
                        <h5 class="card-title">MIA receives items from public areas in the airport</h5>
                        <p class="card-text">This includes restrooms, TSA security checkpoints, MIA parking shuttles, MIA parking lots, and any public interior or exterior areas of the airport.</p>
                        <a href="#" class="btn theme-color text-decoration-none">File a Free Claim</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4 h-100">
                <div class="card lost-card h-100">
                    <div class="card-body">
                        <div class="card-icon mb-3 text-center">
                            <i class="fas fa-plane fs-3" style="color: #6f42c1;"></i>
                        </div>
                        <h5 class="card-title">Airlines hold onto items lost on the airplane, at baggage claim, check-in, and gates</h5>
                        <p class="card-text">If you’ve lost an item at any of these locations, you should call your airline directly.</p>
                        <a href="#" class="btn theme-color text-decoration-none">Get Airline Contact Info</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4 h-100">
                <div class="card lost-card h-100">
                    <div class="card-body">
                        <div class="card-icon mb-3 text-center">
                            <i class="fas fa-taxi fs-3" style="color: #6f42c1;"></i>
                        </div>
                        <h5 class="card-title">Lost items on a taxi, limo, shuttle, ride app service (Uber, Lyft), or RTD bus or train?</h5>
                        <p class="card-text">Please check your receipt for a direct phone number or look on our transportation page for contact information.</p>
                        <a href="#" class="btn theme-color text-decoration-none">Transportation and Parking Info</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4 h-100">
                <div class="card lost-card h-100">
                    <div class="card-body">
                        <div class="card-icon mb-3 text-center">
                            <i class="fas fa-utensils fs-3" style="color: #6f42c1;"></i>
                        </div>
                        <h5 class="card-title">Lost something at a restaurant, shop, or business?</h5>
                        <p class="card-text">Businesses in the airport are responsible for items lost in their areas. Visit Dine-Shop-Relax to find the business contact information.</p>
                        <a href="#" class="btn theme-color text-decoration-none">Dine-Shop-Relax</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
';
echo'</div>';

echo'<div class="container-fluid card parking-back banner766 d-flex justify-content-center align-items-center my-5 rounded-0" >
    <div class="container help-width">
                    <p class="text-center text-light fs-2">Miami International Airports Lost and Found facility is located in North Terminal D - Level 4 and is open 7 days a week from 8 a.m.- 6 p.m.</p>
                    <p class=" text-center text-light"></p>
                   
                    <div class="d-md-flex justify-content-center align-items-center"> <a class="btn btn-size  btn-mysuccess mob-button" href="#" target="_blank">Get Walking Direction</a><a class="btn btn-size  btn-mysuccess mob-button mx-md-3" href="#" target="_blank">File a free Claim</a></div>
                </div>
                
                
    </div>';
    echo'<div class="container-fluid px-md-5 px-4">';
echo'
        <div class="text-center">
            <h2>Helpful Resources</h2>
            <div class="btn btn-size-group mt-3" role="group" aria-label="Accessibility Options">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Accessibility Statement</a>
            <a href="#" class="btn btn-size btn btn-outline-secondary mob-button">Accessibility Services</a>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Traveler Tips</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-globe icond "></i></span>  <a href="#" class="d-flex align-items-center justify-content-between my-3">International Travelers <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-passport icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Infrequent Traveler Tips <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-child icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Children and Families <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-wheelchair icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Wheelchair Requests <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-map icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Interactive Map <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-paw icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Traveling with Pets <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Business Information</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-briefcase icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Doing Business at MIA <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-users icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Business Diversity and Development <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-store icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Concessions Opportunities <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-info-circle icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> General Tenant Information <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-balance-scale icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> CEEA: Center of Equity and Excellence in Aviation <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-gavel icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Rules and Regulations <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    
 ';
echo'<div class="container-fluid my-5 p-md-2">
        <div class="text-center mb-md-2 mb-2">
            <h2>Contact Us</h2>
        </div>
        <div class="row">
            <div class="col-md-4 p-0 my-md-0 my-3">
                <div class="card lost-contact-card text-center m-md-2  h-100">
                    <div class="card-body">
                        <div class="icon mb-3">
                            <i class="fas fa-home fs-3" style="color: #6f42c1;"></i>
                        </div>
                        <h5 class="card-title">Hours of Operation</h5>
                        <p class="card-text">Monday – Sunday: 8 a.m.- 6 p.m.</p>
                        <a href="#" class="btn btn-link theme-color text-decoration-none">File Your Free Claim Online &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 p-0 my-md-0 my-3">
                <div class="card lost-contact-card text-center m-md-2  h-100">
                    <div class="card-body">
                        <div class="icon mb-3">
                            <i class="fas fa-phone fs-3" style="color: #6f42c1;"></i>
                        </div>
                        <h5 class="card-title">Call or Text Us</h5>
                        <p class="card-text">Call us at: <a href="#"></a></p>
                        <p class="card-text">Phone lines close 30 minutes before the office closes.</p>
                        <a href="#" class="btn btn-link theme-color text-decoration-none">Call Lost and Found &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 p-0 my-md-0 my-3">
                <div class="card lost-contact-card text-center m-md-2  h-100">
                    <div class="card-body">
                        <div class="icon mb-3">
                            <i class="fas fa-map-marker-alt fs-3" style="color: #6f42c1;"></i>
                        </div>
                        <h5 class="card-title">North Terminal D - Level 4</h5>
                        <p class="card-text">Miami International Airports Lost and Found facility is located in North Terminal D - Level 4 and is open 7 days a week from 8 a.m.- 6 p.m.</p>
                        <a href="#" class="btn btn-link theme-color text-decoration-none">Get Directions &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>';
    echo "</div>"; // Close title-container
}

// lost and found start end
                        
        // Arrival page
// Arrival page
elseif ($design == "Design_6") {
    echo "<div class=' p-0'>";
    echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
    echo "<h1 class='ms-md-5 ms-4 'style='margin-bottom:0.5rem;font-size: 3rem;'>$title</h1>";
    echo '</div>';

    // Fetch the latest flight data from the database
    $flightResult = $conn->query("SELECT data FROM flight_data_arr ORDER BY timestamp DESC LIMIT 1");
    if ($flightResult->num_rows > 0) {
        $flightRow = $flightResult->fetch_assoc();
        $flightData = json_decode($flightRow['data'], true);
    } else {
        echo 'Flight data not found.';
        exit;
    }

    // Extract unique airline names for the filter dropdown
    $airlines = [];
    foreach ($flightData as $flight) {
        $airlines[$flight['airline']['name']] = $flight['airline']['name'];
    }

    echo '<style>
        .scrollable-container {
            width: 100%;
            overflow-x: auto; /* Enable horizontal scrolling */
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        #example24 {
            border-collapse: collapse;
        }
        #example24 th,
        #example24 td {
            border: none !important;
        }
        #example24 thead {
            border-bottom: none !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border: none !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            border: none !important;
        }
    </style>

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />';

    // Display airline filter dropdown
    echo '<div class="p-md-5 p-4">';
    echo '<div class="mb-3">';
    echo '<label for="airlineFilter" class="form-label">Filter by Airline:</label>';
    echo '<select id="airlineFilter" class="form-select">';
    echo '<option value="">All Airlines</option>';
    foreach ($airlines as $airline) {
        echo '<option value="' . htmlspecialchars($airline) . '">' . htmlspecialchars($airline) . '</option>';
    }
    echo '</select>';
    echo '</div>';

    // Display flight data
    echo '<div class="scrollable-container">
        <table id="example24" class="table table-striped table-bordered display">
            <thead class="theme-bg text-white rounded-2">
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Airline</th>
                    <th>Airline Code</th>
                    <th>Arrival Airport</th>
                    <th>Arrival IATA</th>
                    <th>Departed From Airport</th>
                    <th>Departure IATA</th>
                    <th>Scheduled Arrival</th>
                    <th>Delay (min)</th>
                    <th>Terminal</th>
                    <th>Gate</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($flightData as $flight) {
        $statusClass = 'badge-bg';
        if ($flight['flight_status'] == 'active') {
            $statusClass = 'green-bg';
        } elseif ($flight['flight_status'] == 'cancelled') {
            $statusClass = 'red-bg';
        }
        echo '<tr>
            <td style="width: 84.0156px!important;">' . htmlspecialchars($flight['flight_date']) . '</td>
            <td><span class="badge ' . $statusClass . '" style="font-size: .8rem!important;">' . htmlspecialchars($flight['flight_status']) . '</span></td>
            <td>' . htmlspecialchars($flight['airline']['name']) . '</td>
            <td>' . htmlspecialchars($flight['airline']['iata']) . '</td>
            <td>' . htmlspecialchars($flight['arrival']['airport']) . '</td>
            <td>' . htmlspecialchars($flight['arrival']['iata']) . '</td>
            <td>' . htmlspecialchars($flight['departure']['airport']) . '</td>
            <td>' . htmlspecialchars($flight['departure']['iata']) . '</td>
            <td class="theme-color">' . date('Y-m-d H:i:s', strtotime($flight['arrival']['scheduled'])) . '</td>
            <td>' . ($flight['arrival']['delay'] ? htmlspecialchars($flight['arrival']['delay']) : 'N/A') . '</td>
            <td>' . ($flight['arrival']['terminal'] ? htmlspecialchars($flight['arrival']['terminal']) : 'N/A') . '</td>
            <td>' . ($flight['arrival']['gate'] ? htmlspecialchars($flight['arrival']['gate']) : 'N/A') . '</td>
        </tr>';
    }

    echo '</tbody></table></div></div>';
?>

<!-- jQuery and DataTables JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>

<script>
    $(document).ready(function() {
        var table = $("#example24").DataTable({
            dom: "Blfrtip",
            buttons: [
              // You can add buttons here if needed
            ],
            lengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]]
        });

        // Airline filter
        $('#airlineFilter').on('change', function() {
            var selectedAirline = $(this).val();
            if (selectedAirline) {
                table.columns(2).search('^' + selectedAirline + '$', true, false).draw();
            } else {
                table.columns(2).search('').draw();
            }
        });

        // Get search parameter from URL
        var urlParams = new URLSearchParams(window.location.search);
        var searchQuery = urlParams.get("search");

        if (searchQuery) {
            // Populate the DataTables search box with the search query
            table.search(searchQuery).draw();
        }
    });
</script>

<?php
}


       
       
               // Departure page
elseif ($design == "Design_7") {
     echo "<div class=' p-0'>";
            echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
            echo "<h1 class='ms-md-5 ms-4 'style='margin-bottom:0.5rem;font-size: 3rem;'>$title</h1>";
            echo '</div>';
            
    // Fetch the latest flight data from the database
    $flightResult = $conn->query("SELECT data FROM flight_data_dep ORDER BY timestamp DESC LIMIT 1");
    if ($flightResult->num_rows > 0) {
        $flightRow = $flightResult->fetch_assoc();
        $flightData = json_decode($flightRow['data'], true);
    } else {
        echo 'Flight data not found.';
        exit;
    }



echo '<style>
   .scrollable-container {
        width: 100%;
        overflow-x: auto; /* Enable horizontal scrolling */
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
   #example24 {
        border-collapse: collapse;
    }
    #example24 th,
    #example24 td {
        border: none !important;
    }
    #example24 thead {
        border-bottom: none !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: none !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        border: none !important;
    }
</style>

<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />';

// Display flight data
echo'<div class="p-md-5 p-4">';
echo '
<div class="scrollable-container">
     <table id="example24" class="table table-striped table-bordered display">
     <thead class="theme-bg text-white rounded-2">
         <tr>
             <th>Date</th>
             <th>Status</th>
             <th>Airline</th>
             <th>Airline Code</th>
             <th>Arrival Airport</th>
             <th>Arrival IATA</th>
             <th>Departed From Airport</th>
             <th>Departure IATA</th>
             <th>Scheduled Arrival</th>
             <th>Delay (min)</th>
             <th>Terminal</th>
             <th>Gate</th>
         </tr>
     </thead>
     <tbody>';

foreach ($flightData as $flight) {
     $statusClass = 'badge-bg';
    if ($flight['flight_status'] == 'active') {
        $statusClass = 'green-bg';
    } elseif ($flight['flight_status'] == 'cancelled') {
        $statusClass = 'red-bg';
    }

    echo '<tr>
        <td style="    width: 84.0156px!important;">' . htmlspecialchars($flight['flight_date']) . '</td>
       <td><span class="badge ' . $statusClass . '" style="font-size: .8rem!important;">' . htmlspecialchars($flight['flight_status']) . '</span></td>
        <td>' . htmlspecialchars($flight['airline']['name']) . '</td>
        <td>' . htmlspecialchars($flight['airline']['iata']) . '</td>
        <td>' . htmlspecialchars($flight['arrival']['airport']) . '</td>
        <td>' . htmlspecialchars($flight['arrival']['iata']) . '</td>
        <td>' . htmlspecialchars($flight['departure']['airport']) . '</td>
        <td>' . htmlspecialchars($flight['departure']['iata']) . '</td>
        <td class="theme-color">' . date('Y-m-d H:i:s', strtotime($flight['arrival']['scheduled'])) . '</td>
        <td>' . ($flight['arrival']['delay'] ? htmlspecialchars($flight['arrival']['delay']) : 'N/A') . '</td>
        <td>' . ($flight['arrival']['terminal'] ? htmlspecialchars($flight['arrival']['terminal']) : 'N/A') . '</td>
        <td>' . ($flight['arrival']['gate'] ? htmlspecialchars($flight['arrival']['gate']) : 'N/A') . '</td>
    </tr>';
}
echo '</tbody></table></div></div>';
?>

<!-- jQuery and DataTables JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<?php
    // DataTables JavaScript
    echo '<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
    <script src="https://cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

     <script>
        $(document).ready(function() {
        var table = $("#example24").DataTable({
            dom: "Blfrtip",
            buttons: [
              ';//  
              echo'
            ],
            lengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]]
            });
            
            // Get search parameter from URL
             var urlParams = new URLSearchParams(window.location.search);
    var searchQuery = urlParams.get("search");
        // var searchQuery = urlParams.get("search");

        if (searchQuery) {
            // Populate the DataTables search box with the search query
            table.search(searchQuery).draw();
        }
    });
    </script>';
}

// Terminal Guide Starts
elseif($design == "Design_8"){
    echo "<div class=' p-0'>";
    echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
    echo "<h1 class='ms-5'>$title</h1>";
    echo '</div>';
    if(isset($_SESSION['email'])){

        echo "<a href='../PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
 
        echo "<a href='../PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
      
    }
    echo "</div>";
   
    echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb bread-bck ps-5 ">';
    echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a href="../../" class="text-dark text-decoration-none" >Home</a></li>';
    $breadcrumbUrl = '/';
    $segmentCount = count(array_filter($urlSegments));
    foreach ($urlSegments as $index => $segment) {
        if (!empty($segment)) {
            $breadcrumbUrl .= '../' . $segment;
            $isLastSegment = ($index === $segmentCount - 1);
            $class = $isLastSegment ? 'text-dark fw-semibold' : 'text-dark';
            $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
            }
            }
            echo '</ol></nav>';
             echo'<div class="container-fluid p-5 margin-dine">
        <div class="row">
              <div class="col-md-5">
            <div class="row">
                
                <div class="col p-3">
                    <div class="media3 clip-top-right mw-100" style="background-image:url(\'/Pic/terminal-guide-map.gif\');"></div>
                </div>
            </div>
        </div>
            <div class="col-md-7 mob-space ps-md-5 p-3 mt-md-4">
             
                <h3 class="mt-md-5">Terminal Gate Map of Miami International Airport (MIA)</h3>
        <p>Miami International Airport (MIA) is a major hub for international and domestic flights. The airport is designed to efficiently handle the large volume of passengers passing through its gates. Below is a detailed guide to help you navigate the terminal buildings and concourses.</p>

      
      <p class="mt-3">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Terminal Guide Map</a>
                <a href="#" class="btn btn-size btn btn-outline-secondary mob-button">Terminals Detail</a></p>
              
            </div>
        </div>
    </div>
';
echo'<div class="container-fluid row mt-5 px-md-5 px-4">
    <h1 class="mb-4">Terminal Information - Miami International Airport (MIA)</h1>

    <div class="card mb-4 shadow-sm col-md-4 p-0 mx-2">
        <div class="card-header terminal-header bg-primary text-white">
            <i class="fas fa-plane-departure me-3 fs-4"></i>
            <h2>Miami Airport North Terminal (Blue Terminal)</h2>
        </div>
        <div class="card-body">
            <p>Miami airport’s North terminal is comprised of concourse D and spreads over a 1-mile area (1.6km), housing 51 gates and 4 security checkpoints along with a wide variety of shopping and dining options, lounges, and spa facilities. Handling international and domestic flights as well, it is seen as an American Airlines terminal. As already stated, Blue terminal has the usual MIA layout:</p>
            <ul>
                <li><strong>Level 1:</strong> Arrivals and baggage claim</li>
                <li><strong>Level 2:</strong> Departures</li>
                <li><strong>Level 3:</strong> Connections to concourse E and MIA Mover Station</li>
            </ul>
            <p>Since the North terminal covers a huge surface (an average 30-minute time is required to traverse it from one end to the other), Skytrain undertakes transfers throughout concourse D. With 4 stops along its way, Skytrain runs every 3 minutes and calls at 4 stations. Station 1 is near Gate D17, Station 2 is between Gates D24 and D25, Station 3 is close to Gate D29, and Station 4 lies next to Gate D46. Skytrain (one out of three of MIA’s automated people movers) needs as much as 5 minutes to cross the Blue terminal.</p>
        </div>
    </div>

    <div class="card mb-4 shadow-sm col-md-4 p-0 mx-2">
        <div class="card-header terminal-header bg-warning text-dark">
            <i class="fas fa-plane-arrival me-3 fs-4"></i>
            <h2>Miami Airport Central Terminal (Yellow Terminal)</h2>
        </div>
        <div class="card-body">
            <p>Adjacent to the North terminal building is the Central terminal, or as it is also called the Yellow terminal. Concourses E, F, and G are parts of the Yellow terminal. Overall, plenty of shops and restaurants are to be found at the Central terminal, along with the inside-the-airport Miami International Airport Hotel. In general, concourse E serves domestic and international flights, while concourses F and G mostly deal with domestic destinations. Concourse E has 18 gates and is mainly dedicated to American Airlines. Still, other Oneworld partners and some Caribbean and Latin American air carriers also use concourse E. Concourse E has a satellite building as well, reachable via the MIA E Train or a walkway. In fact, the E’s satellite can even serve Airbus A380. MIA E Train is another automated people mover that is part of Miami airport’s ground transportation and connects Gates E2-E11 (starting from level 4 of the main concourse) to Gates E20-E33, lying in the satellite building.</p>
            <p>On the other hand, concourses F and G have 19 and 14 gates respectively and serve domestic flights and Canadian destinations.</p>
            <p>The structure of the Central terminal is no different than the North’s one, while all three concourses house security checkpoints at their entrances.</p>
        </div>
    </div>

    <div class="card mb-4 shadow-sm col-md p-0 mx-2">
        <div class="card-header terminal-header bg-danger text-white">
            <i class="fas fa-globe me-3 fs-4"></i>
            <h2>Miami Airport South Terminal (Red Terminal)</h2>
        </div>
        <div class="card-body">
            <p>MIA South terminal (Red terminal) welcomes both international and domestic flights operated to a great extent by non-Oneworld international air carriers. Its two concourses (concourses H and J) are connected via a walkway. In that walkway is where the vast majority of the terminal’s shops and dining stores are to be found.</p>
            <ul>
                <li><strong>Concourse H:</strong> 13 gates, principally used by Delta and non-Oneworld global airlines</li>
                <li><strong>Concourse J:</strong> 15 gates, serves non-Oneworld air companies and their intercontinental routes. One gate can accommodate Airbus A380.</li>
            </ul>
            <p>The terminal’s configuration bears no difference from the rest of the airport complex:</p>
            <ul>
                <li><strong>Level 1:</strong> Arrivals</li>
                <li><strong>Level 2:</strong> Departures</li>
                <li><strong>Level 3:</strong> Between-terminal and from-and-to MIA Mover Station connections</li>
            </ul>
            <p>Moreover, concourses H and J also host security checks on their entrances. However, one more checkpoint lies on their connecting walkway.</p>
        </div>
    </div>
</div>';
            echo'<div class="container-fluid px-md-5 px-4">';
    $content = $row["content"];
    echo "<p>$content</p>";

    // Check if the page has child pages
    $childSql = "SELECT * FROM pages WHERE parent_id = ?";
    $childStmt = $conn->prepare($childSql);
    $childStmt->bind_param("i", $pageId);
    $childStmt->execute();
    $childResult = $childStmt->get_result();

    if ($childResult->num_rows > 0) {
        // Page has child pages, display the links to the child pages
       
        echo "<div class='row'>";

        $childCount = 0;
        while ($childRow = $childResult->fetch_assoc()) {
            $childTitle = $childRow["title"];
            $childSlug = $childRow["slug"];
            $childImage = $childRow["image"]; // Assuming you have an image column for child pages
            $childDate =$childRow["date"];
            //  $name = $_SESSION['Name'];
            $childUrl = generatePageUrl($childRow["id"]);

            if ($childCount > 0 && $childCount % 3 == 0) {
                echo '</div><div class="row mt-4">';
            }

            echo "<div class='col-md-4 mb-4'>";
            echo "<a href='../$childUrl' class='text-decoration-none text-dark ' >";
            if ($childImage) {
                echo "<img src='$childImage' class='card-img-top rounded-0' style='
    max-height: 23em;
' alt='$childTitle'>";
            }
            echo "<div class='card-body position-absolute w-100 p-2' style='    bottom: 1px;
    background: #1606a7a9;'>";
            echo "<h5 class='card-title text-light text-center fw-bold'>$childTitle</h5>";
            // echo "<small class='badge theme-bg my-2'>$childDate</small>";
            // echo "<small class='badge theme-bg my-2'>$childName</small>";
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
    $childStmt->close();
   
 echo' <div class="container-fluid p-0 mt-5">
        <div class="row">
            <div class="col-md-3 mb-4 h-100">
                <div class="card lost-card h-100">
                    <div class="card-body">
                        <div class="card-icon mb-3 text-center">
                            <i class="fas fa-wrench fs-3" style="color: #6f42c1;"></i>
                        </div>
                        <h5 class="card-title">MIA receives items from public areas in the airport</h5>
                        <p class="card-text">This includes restrooms, TSA security checkpoints, MIA parking shuttles, MIA parking lots, and any public interior or exterior areas of the airport.</p>
                        <a href="#" class="btn theme-color text-decoration-none">File a Free Claim</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4 h-100">
                <div class="card lost-card h-100">
                    <div class="card-body">
                        <div class="card-icon mb-3 text-center">
                            <i class="fas fa-plane fs-3" style="color: #6f42c1;"></i>
                        </div>
                        <h5 class="card-title">Airlines hold onto items lost on the airplane, at baggage claim, check-in, and gates</h5>
                        <p class="card-text">If you’ve lost an item at any of these locations, you should call your airline directly.</p>
                        <a href="#" class="btn theme-color text-decoration-none">Get Airline Contact Info</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4 h-100">
                <div class="card lost-card h-100">
                    <div class="card-body">
                        <div class="card-icon mb-3 text-center">
                            <i class="fas fa-taxi fs-3" style="color: #6f42c1;"></i>
                        </div>
                        <h5 class="card-title">Lost items on a taxi, limo, shuttle, ride app service (Uber, Lyft), or RTD bus or train?</h5>
                        <p class="card-text">Please check your receipt for a direct phone number or look on our transportation page for contact information.</p>
                        <a href="#" class="btn theme-color text-decoration-none">Transportation and Parking Info</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4 h-100">
                <div class="card lost-card h-100">
                    <div class="card-body">
                        <div class="card-icon mb-3 text-center">
                            <i class="fas fa-utensils fs-3" style="color: #6f42c1;"></i>
                        </div>
                        <h5 class="card-title">Lost something at a restaurant, shop, or business?</h5>
                        <p class="card-text">Businesses in the airport are responsible for items lost in their areas. Visit Dine-Shop-Relax to find the business contact information.</p>
                        <a href="#" class="btn theme-color text-decoration-none">Dine-Shop-Relax</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
';
echo'</div>';
echo'<div class="container-fluid p-0">';
echo'<div class="card parking-back banner766 d-flex justify-content-center align-items-center my-5 rounded-0" >
    <div class="container help-width">
                    <p class="text-center text-light fs-2">Miami International Airports Lost and Found facility is located in North Terminal D - Level 4 and is open 7 days a week from 8 a.m.- 6 p.m.</p>
                    <p class=" text-center text-light"></p>
                   
                    <div class="d-md-flex justify-content-center align-items-center"> <a class="btn btn-size  btn-mysuccess mob-button" href="#" target="_blank">Get Walking Direction</a><a class="btn btn-size  btn-mysuccess mob-button mx-md-3" href="#" target="_blank">File a free Claim</a></div>
                </div>
      
                
    </div>';
    echo'</div>';
    echo'<div class="container-fluid px-md-5 px-4">';
echo'
        <div class="text-center">
            <h2>Helpful Resources</h2>
            <div class="btn btn-size-group mt-3" role="group" aria-label="Accessibility Options">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Accessibility Statement</a>
            <a href="#" class="btn btn-size btn btn-outline-secondary mob-button">Accessibility Services</a>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Traveler Tips</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-globe icond "></i></span>  <a href="#" class="d-flex align-items-center justify-content-between my-3">International Travelers <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-passport icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Infrequent Traveler Tips <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-child icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Children and Families <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-wheelchair icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Wheelchair Requests <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-map icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Interactive Map <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-paw icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Traveling with Pets <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Business Information</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-briefcase icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Doing Business at MIA <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-users icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Business Diversity and Development <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-store icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Concessions Opportunities <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-info-circle icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> General Tenant Information <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-balance-scale icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> CEEA: Center of Equity and Excellence in Aviation <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-gavel icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Rules and Regulations <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    
 ';
echo'<div class="container-fluid my-5 p-md-2">
        <div class="text-center mb-md-5 mb-2">
            <h2>Contact Us</h2>
        </div>
        <div class="row">
            <div class="col-md-4 p-0 my-md-0 my-3">
                <div class="card lost-contact-card text-center m-md-2  h-100">
                    <div class="card-body">
                        <div class="icon mb-3">
                            <i class="fas fa-home fs-3" style="color: #6f42c1;"></i>
                        </div>
                        <h5 class="card-title">Hours of Operation</h5>
                        <p class="card-text">Monday – Sunday: 8 a.m.- 6 p.m.</p>
                        <a href="#" class="btn btn-link theme-color text-decoration-none">File Your Free Claim Online &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 p-0 my-md-0 my-3">
                <div class="card lost-contact-card text-center m-md-2  h-100">
                    <div class="card-body">
                        <div class="icon mb-3">
                            <i class="fas fa-phone fs-3" style="color: #6f42c1;"></i>
                        </div>
                        <h5 class="card-title">Call or Text Us</h5>
                        <p class="card-text">Call us at: <a href="#"></a></p>
                        <p class="card-text">Phone lines close 30 minutes before the office closes.</p>
                        <a href="#" class="btn btn-link theme-color text-decoration-none">Call Lost and Found &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 p-0 my-md-0 my-3">
                <div class="card lost-contact-card text-center m-md-2  h-100">
                    <div class="card-body">
                        <div class="icon mb-3">
                            <i class="fas fa-map-marker-alt fs-3" style="color: #6f42c1;"></i>
                        </div>
                        <h5 class="card-title">North Terminal D - Level 4</h5>
                        <p class="card-text">Miami International Airports Lost and Found facility is located in North Terminal D - Level 4 and is open 7 days a week from 8 a.m.- 6 p.m.</p>
                        <a href="#" class="btn btn-link theme-color text-decoration-none">Get Directions &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>';
    echo'  <div class="container-fluid mt-5 p-0">
        <h2>FAQs</h2>
        <div class="accordion" id="faqAccordion">
            <div class="card">
                <div class="card-header" id="headingOne">
                    <h5 class="mb-0">
                        <button class="btn btn-link theme-color text-decoration-none" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            How many terminals are there at Miami Airport?
                        </button>
                    </h5>
                </div>

                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#faqAccordion">
                    <div class="card-body">
                        Miami International Airport has 3 terminal buildings, sub-divided into 6 concourses. Resembling a huge “U” in shape, its vast area splits into 3 buildings (the Central, South, and North terminals), which are connected to each other, mostly landside.
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header" id="headingTwo">
                    <h5 class="mb-0">
                        <button class="btn btn-link theme-color text-decoration-none collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            What terminal is international at Miami Airport?
                        </button>
                    </h5>
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#faqAccordion">
                    <div class="card-body">
                        The North terminal (concourse D) serves international flights operated by American Airlines, while concourse E (Central terminal) handles international flights by Oneworld partners and some Caribbean and Latin American air carriers. Finally, the concourses H and J of the South terminal welcome global flights provided by Delta and other non-Oneworld airlines.
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header" id="headingThree">
                    <h5 class="mb-0">
                        <button class="btn btn-link theme-color text-decoration-none collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Can you walk between terminals at Miami Airport?
                        </button>
                    </h5>
                </div>
                <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#faqAccordion">
                    <div class="card-body">
                        All three Miami airport terminals are interconnected via moving walkways. Thus, walking from one terminal to another is doable. However, please note that apart from concourses E-D and H-J which are linked airside, all other concourses are connected landside, meaning that passengers have to pass through security control and re-clear customs to reach their destination.
                    </div>
                </div>
            </div>
        </div>
    </div>
';
echo' <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>';
    echo "</div>"; // Close title-container
}

// Terminal Guide end

// Baggage claim start
elseif($design == "Design_9"){
    echo "<div class=' p-0'>";
    echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
    echo "<h1 class='ms-5'>$title</h1>";
    echo '</div>';
    if(isset($_SESSION['email'])){

        echo "<a href='../PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
 
        echo "<a href='../PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
      
    }
    echo "</div>";
   
    echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb bread-bck ps-5 ">';
    echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a href="../../" class="text-dark text-decoration-none" >Home</a></li>';
    $breadcrumbUrl = '/';
    $segmentCount = count(array_filter($urlSegments));
    foreach ($urlSegments as $index => $segment) {
        if (!empty($segment)) {
            $breadcrumbUrl .= '../' . $segment;
            $isLastSegment = ($index === $segmentCount - 1);
            $class = $isLastSegment ? 'text-dark fw-semibold' : 'text-dark';
            $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
            }
            }
            echo '</ol></nav>';
            echo'<div class="container-fluid p-5 margin-dine">
        <div class="row">
              <div class="col-md-5">
            <div class="row">
                
                <div class="col p-3">
                    <div class="media3 clip-top-right mw-100" style="background-image:url(\'https://www.miami-airport.com/images/maps/baggage-claim-international.jpg\');    background-size: cover;"></div>
                </div>
            </div>
        </div>
            <div class="col-md-7 mob-space ps-md-5 p-3 mt-md-4">
                <h2>Baggage Claim Locations:</h2>
    <ul>
        <li><strong>North Terminal (Concourse D):</strong> Claims 1-12</li>
        <li><strong>Central Terminal (Concourses E, F, G):</strong> Claims 13-26</li>
        <li><strong>South Terminal (Concourses H, J):</strong> Claims 27-33</li>
    </ul>        
     <h2>Baggage Claim Services:</h2>
    <ul>
        <li><strong>Lost & Found:</strong> Located in the Central Terminal, 4th Floor, Terminal Operations.</li>
        <li><strong>Baggage Carts:</strong> Available near all baggage claim areas. Fees apply.</li>
        <li><strong>Baggage Wrapping:</strong> Provided by Safe Wrap in the baggage claim area.</li>
    </ul>
            </div>
        </div>
    </div>
';

 echo'<div class="container-fluid p-5 margin-dine">
        <div class="row">
             
            <div class="col-md-7 mob-space mt-md-5 p-3">
               <h3 class="fw-strong mt-md-3 mt-2">TSA Cares</h3>
               <p class="fw-semibold">The Transportation Security Administration (TSA) is responsible for transitioning passengers through the airport’s security checkpoints. <br>

Tips for going through security:</p>
               <p>
<ul>
   <li>Travelers with disabilities who need to use the TSA Restricted Access lanes at all security checkpoints.</li>
  <li>For assistance through security, please contact TSA Cares. TSA Cares is a helpline that provides travelers with disabilities, medical conditions and other special circumstances additional assistance during the security screening process.</li>
</ul>
</p>

                <p class="mt-3">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Learn More at TSA Care</a>
</p>
            </div>
             <div class="col-md-5">
            <div class="row">
                
                <div class="col p-3">
                    <div class="media3 clip-top-right mw-100" style="background-image:url(\'/Pic/airport-sec/sec2.jpg\');"></div>
                </div>
            </div>
        </div>
        </div>
    </div>
';
echo'<div class="container-fluid px-md-5 px-4 my-5">
    <h2>Baggage Claim Locations</h2>
    <div class="row claim-cards mt-3">
        <div class="col-md-4">
            <div class="card shadow my-md-0 my-4 h-100">
                <img src="Pic/baggage-claim/a.jpg" class="card-img-top img-fluid" style="object-fit: cover; height: 200px;" alt="North Terminal">
                <div class="card-body">
                    <h5 class="card-title">North Terminal (Concourse D)</h5>
                    <p class="card-text">Claims 1-12</p>
                    <p class="card-text">Services: Restaurants, shops, and lounges available.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow my-md-0 my-4 h-100">
                <img src="Pic/baggage-claim/b.jpg" class="card-img-top img-fluid" style="object-fit: cover; height: 200px;" alt="Central Terminal">
                <div class="card-body">
                    <h5 class="card-title">Central Terminal (Concourses E, F, G)</h5>
                    <p class="card-text">Claims 13-26</p>
                    <p class="card-text">Services: Baggage carts, information desks, and rental car shuttles.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow my-md-0 my-4 h-100">
                <img src="Pic/baggage-claim/c.jpg" class="card-img-top img-fluid" style="object-fit: cover; height: 200px;" alt="South Terminal">
                <div class="card-body">
                    <h5 class="card-title">South Terminal (Concourses H, J)</h5>
                    <p class="card-text">Claims 27-33</p>
                    <p class="card-text">Services: Currency exchange, baggage wrapping, and taxi stands.</p>
                </div>
            </div>
        </div>
    </div>
</div>
';
 echo'<div class="container-fluid px-md-5 px-4">';
    $content = $row["content"];
    // echo "<p class='mt-5'>$content</p>";

    // Check if the page has child pages
    $childSql = "SELECT * FROM pages WHERE parent_id = ?";
    $childStmt = $conn->prepare($childSql);
    $childStmt->bind_param("i", $pageId);
    $childStmt->execute();
    $childResult = $childStmt->get_result();

    if ($childResult->num_rows > 0) {
        // Page has child pages, display the links to the child pages
       
        echo "<div class='row'>";

        $childCount = 0;
        while ($childRow = $childResult->fetch_assoc()) {
            $childTitle = $childRow["title"];
            $childSlug = $childRow["slug"];
            $childImage = $childRow["image"]; // Assuming you have an image column for child pages
            $childDate =$childRow["date"];
            //  $name = $_SESSION['Name'];
            $childUrl = generatePageUrl($childRow["id"]);

            if ($childCount > 0 && $childCount % 3 == 0) {
                echo '</div><div class="row mt-4">';
            }

            echo "<div class='col-md-4 mb-4'>";
            echo "<a href='../$childUrl' class='text-decoration-none text-dark ' >";
            echo "<div class='card border-0 h-100' style='box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;'>";
           if ($childImage) {
                echo "<img src='$childImage' class='card-img-top rounded-0' style='
    max-height: 23em;
' alt='$childTitle'>";
            }
            echo "<div class='card-body position-absolute w-100 p-2' style='    bottom: 1px;
    background: #1606a7a9;'>";
            echo "<h5 class='card-title text-light text-center fw-bold'>$childTitle</h5>";
            // echo "<small class='badge theme-bg my-2'>$childDate</small>";
            // echo "<small class='badge theme-bg my-2'>$childName</small>";
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
    $childStmt->close();
 
echo'
        <div class="text-center">
            <h2>Helpful Resources</h2>
            <div class="btn btn-size-group mt-3" role="group" aria-label="Accessibility Options">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Accessibility Statement</a>
            <a href="#" class="btn btn-size btn btn-outline-secondary mob-button">Accessibility Services</a>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Traveler Tips</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-globe icond "></i></span>  <a href="#" class="d-flex align-items-center justify-content-between my-3">International Travelers <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-passport icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Infrequent Traveler Tips <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-child icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Children and Families <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-wheelchair icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Wheelchair Requests <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-map icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Interactive Map <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-paw icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Traveling with Pets <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Business Information</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-briefcase icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Doing Business at MIA <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-users icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Business Diversity and Development <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-store icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Concessions Opportunities <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-info-circle icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> General Tenant Information <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-balance-scale icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> CEEA: Center of Equity and Excellence in Aviation <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-gavel icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Rules and Regulations <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    
 ';
echo'<div class="container-fluid my-5 p-md-2">
        <div class="text-center mb-md-5 mb-2">
            <h2>Contact Us</h2>
        </div>
        <div class="row">
            <div class="col-md-4 p-0 my-md-0 my-3">
                <div class="card lost-contact-card text-center m-md-2  h-100">
                    <div class="card-body">
                        <div class="icon mb-3">
                            <i class="fas fa-home  fs-5" style="color: #6f42c1;"></i>
                        </div>
                        <h5 class="card-title">Hours of Operation</h5>
                        <p class="card-text">Monday – Sunday: 8 a.m.- 6 p.m.</p>
                        <a href="#" class="btn btn-link theme-color text-decoration-none">File Your Free Claim Online &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 p-0 my-md-0 my-3">
                <div class="card lost-contact-card text-center m-md-2  h-100">
                    <div class="card-body">
                        <div class="icon mb-3">
                            <i class="fas fa-phone  fs-5" style="color: #6f42c1;"></i>
                        </div>
                        <h5 class="card-title">Call or Text Us</h5>
                        <p class="card-text">Call us at: <a href="#"></a></p>
                        <p class="card-text">Phone lines close 30 minutes before the office closes.</p>
                        <a href="#" class="btn btn-link theme-color text-decoration-none">Call Lost and Found &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 p-0 my-md-0 my-3">
                <div class="card lost-contact-card text-center m-md-2  h-100">
                    <div class="card-body">
                        <div class="icon mb-3">
                            <i class="fas fa-map-marker-alt fs-5 " style="color: #6f42c1;"></i>
                        </div>
                        <h5 class="card-title">North Terminal D - Level 4</h5>
                        <p class="card-text">Miami International Airports Lost and Found facility is located in North Terminal D - Level 4 and is open 7 days a week from 8 a.m.- 6 p.m.</p>
                        <a href="#" class="btn btn-link theme-color text-decoration-none">Get Directions &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>';
    echo '<div class="container-fluid mt-3 p-0">
        <div class="text-center">
            <h2>Baggage Location</h2>
           
        </div>
    <div class="nav nav-tabs">
        <a class="nav-item nav-link security-nav theme-color active nav-hov border-0 mx-md-3" id="nav-item1" onclick="showContent(\'content1\')">Domestic Baggage Claim</a>
        <a class="nav-item nav-link security-nav theme-color nav-hov border-0 mx-md-3" id="nav-item2" onclick="showContent(\'content2\')">International Baggage Claim</a>
        <a class="nav-item nav-link security-nav theme-color nav-hov border-0 mx-md-3" id="nav-item3" onclick="showContent(\'content3\')">Baggage Service Offices</a>
       
    </div>
    <div id="content" class="content border-0">
        <img src="https://www.miami-airport.com/images/maps/baggage-claim-international.jpg" alt="Content for Item 1" class="img-fluid">
    </div>
</div>';

echo '<script>
    function showContent(contentId) {
        var content = document.getElementById(\'content\');
        var navItems = document.querySelectorAll(\'.security-nav\');
        navItems.forEach(function(navItem) {
            navItem.classList.remove(\'active\');
        });
        switch(contentId) {
            case \'content1\':
                document.getElementById(\'nav-item1\').classList.add(\'active\');
                content.innerHTML = \'<img src="https://www.miami-airport.com/images/maps/baggage-claim-international.jpg" alt="Content for Item 1" class="img-fluid">\';
                break;
            case \'content2\':
                document.getElementById(\'nav-item2\').classList.add(\'active\');
                content.innerHTML = `
                 <h2>International Baggage Claim at Miami International Airport (MIA)</h2>
    
    <h3>Location:</h3>
    <p>The international baggage claim area is located in the South Terminal, Concourses H and J, and the Central Terminal, Concourse E. Follow the signs for "International Arrivals" once you exit the plane.</p>
    
    <h3>Customs and Border Protection:</h3>
    <p>All international passengers must go through U.S. Customs and Border Protection (CBP). Have your passport, visa, and customs declaration form ready for inspection.</p>
    
    <h3>Baggage Claim Carousels:</h3>
    <ul>
        <li><strong>South Terminal (Concourses H, J):</strong> Claims 27-33</li>
        <li><strong>Central Terminal (Concourse E):</strong> Claims 16-21</li>
    </ul>
    
    <h3>Services Available:</h3>
    <ul>
        <li><strong>Currency Exchange:</strong> Located near the baggage claim area for your convenience.</li>
        <li><strong>Duty-Free Shops:</strong> Accessible in the arrival area for last-minute purchases.</li>
        <li><strong>Baggage Carts:</strong> Available near all baggage claim carousels. Fees apply.</li>
        <li><strong>Lost & Found:</strong> Located in the Central Terminal, 4th Floor, Terminal Operations.</li>
    </ul>
    
    <h3>Connecting Flights:</h3>
    <p>If you have a connecting flight, check your baggage claim tags to see if your luggage is checked through to your final destination. If not, you will need to collect your luggage and re-check it after clearing customs.</p>
    
    <h3>Transportation Options:</h3>
    <ul>
        <li><strong>Rental Cars:</strong> Accessible via the MIA Mover, a complimentary shuttle service.</li>
        <li><strong>Public Transportation:</strong> Miami-Dade Transit buses and the Metrorail are available from the airport.</li>
        <li><strong>Shuttles and Taxis:</strong> Located just outside the baggage claim area.</li>
    </ul>
    
    <h3>Tips for a Smooth Experience:</h3>
    <ul>
        <li><strong>Check Monitors:</strong> Verify your flights baggage claim carousel on the monitors.</li>
        <li><strong>Keep Your Claim Ticket:</strong> Ensure you have your baggage claim ticket for any issues.</li>
        <li><strong>Report Lost Luggage Promptly:</strong> Contact your airline’s baggage service office if your luggage is missing.</li>
    </ul>
                <p>There are 3 Immigration and Customs areas:</p>
               <ul>
    <li>Central Terminal E, on the 1st level.</li>
    <li>Central North Terminal D, on the 1st level.</li>
    <li>South Terminal J, on the 3rd level.</li>

    </ul>`;
                break;
            case \'content3\':
                document.getElementById(\'nav-item3\').classList.add(\'active\');
                content.innerHTML = ` <h2>Baggage Service Offices Around the World</h2>
        
        <h3>Overview:</h3>
        <p>Baggage service offices are essential facilities located in major airports globally. They provide assistance related to lost luggage, damaged baggage claims, baggage tracing, and delivery arrangements.</p>
        
        <h3>Services Provided:</h3>
        <ul>
            <li><strong>Lost Luggage Assistance:</strong> Help with locating lost or delayed luggage.</li>
            <li><strong>Damaged Baggage Claims:</strong> Assistance with reporting and handling damaged luggage.</li>
            <li><strong>Baggage Tracing:</strong> Real-time updates on the status of your missing luggage.</li>
            <li><strong>Delivery Arrangements:</strong> Coordination for the delivery of found luggage to your destination.</li>
        </ul>
        
        <h3>Operating Hours:</h3>
        <p>Typically, baggage service offices operate 24/7 to assist passengers with any luggage-related issues.</p>
        
        <h3>Procedures:</h3>
        <ol>
            <li>Visit the nearest baggage service office upon discovering an issue with your luggage.</li>
            <li>Present your baggage claim ticket and relevant travel documents.</li>
            <li>Fill out a report detailing your luggage issue.</li>
            <li>Retain a copy of the report for your records and future reference.</li>
        </ol>
        
        <h3>Additional Tips:</h3>
        <ul>
            <li>Report luggage issues promptly after your flight.</li>
            <li>Keep all travel and baggage claim documents until the issue is resolved.</li>
            <li>Monitor the status of your luggage using the reference number provided in your report.</li>
        </ul>
        
        <p>Each airport may have specific procedures and contact information for their baggage service offices, so we advise to check the airport official website or contact their customer service for detailed information.</p>
`;
                break;
          
            default:
                content.innerHTML = \'Content not found\';
        }
    }
</script>';
    echo "</div>"; // Close title-container
}
// Baggage claim end

       // Car Rental, Parking, Hotels Card
elseif($design == "Design_10"){
    echo "<div class=' p-0'>";
    echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
    echo "<h1 class='ms-md-5 ms-4'>$title</h1>";
    echo '</div>';
    if(isset($_SESSION['email'])){

        echo "<a href='/PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
        echo "<a href='/PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
      
    }
    echo "</div>";
   
    echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb bread-bck ps-md-5 ps-4 ">';
    echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a href="../../" class="text-dark text-decoration-none" >Home</a></li>';
    $breadcrumbUrl = '..';
    $segmentCount = count(array_filter($urlSegments));
    foreach ($urlSegments as $index => $segment) {
        if (!empty($segment)) {
            $breadcrumbUrl .= '/' . $segment;
            $isLastSegment = ($index === $segmentCount - 1);
            $class = $isLastSegment ? 'text-dark fw-semibold' : 'text-dark';
            $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
            }
            }
            echo '</ol></nav>';
            echo'<div class="container-fluid px-md-5 px-4">';
    $content = $row["content"];
    echo "<p>$content</p>";
    // Check if the page has child pages
    $childSql = "SELECT * FROM pages WHERE parent_id = ?";
    $childStmt = $conn->prepare($childSql);
    $childStmt->bind_param("i", $pageId);
    $childStmt->execute();
    $childResult = $childStmt->get_result();

    if ($childResult->num_rows > 0) {
        // Page has child pages, display the links to the child pages
        echo "<div class='row'>";
        
        $childCount = 0;
        while ($childRow = $childResult->fetch_assoc()) {
            $childTitle = $childRow["title"];
            $childSlug = $childRow["slug"];
            $childImage = $childRow["image"]; // Assuming you have an image column for child pages
            $childDate = $childRow["date"];
            $truncatedContent = truncateText($childRow["content"], 90);
            $childUrl = generatePageUrl($childRow["id"]);
    
            // Start a new row after every third card
            if ($childCount > 0 && $childCount % 3 == 0) {
                echo '</div><div class="row mt-4">';
            }
    
            echo '<div class="col-md-4 mb-4">';
            echo '<a href="' . $childUrl . '" class="text-decoration-none text-dark">';
            echo '<div class="card border-0 h-100 custom-card" style="box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;">';
            echo '<div class="row g-0">';
            echo '<div class="col-md-5">';
            if ($childImage) {
                echo '<img src="' . $childImage . '" class="card-img-top rounded-start" alt="' . $childTitle . '" style="
    object-fit: cover;
    height: 100%;
">';
            }
            echo '</div>'; // Close col-md-5
            echo '<div class="col-md-7">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . $childTitle . '</h5>';
            echo '<p class="fs-6 my-2">' . $truncatedContent . '</p>';
            echo '<small class="badge theme-bg my-2">' . $childDate . '</small>';
            echo '</div>'; // Close card-body
            echo '</div>'; // Close col-md-7
            echo '</div>'; // Close row g-0
            echo '</div>'; // Close card
            echo '</a>';
            echo '</div>'; // Close col-md-4
    
            $childCount++;
        }
    
        echo '</div>'; // Close the last row
    
        // Pagination
        $totalPages = ceil($childCount / 3); // Assuming 3 cards per row
        if ($totalPages > 1) {
            echo '<nav><ul class="pagination justify-content-center mt-4">';
            for ($i = 1; $i <= $totalPages; $i++) {
                echo "<li class='page-item'><a class='page-link' href='?url=$url&page=$i'>$i</a></li>";
            }
            echo '</ul></nav>';
        }
    }
    

    $childStmt->close();
    echo "</div>"; // Close title-container
}

// End
        // Travel tips start
        elseif($design == "Design_11"){
    echo "<div class=' p-0'>";
    echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
    echo "<h1 class='ms-5'>$title</h1>";
    echo '</div>';
    if(isset($_SESSION['email'])){

        echo "<a href='../PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
 
        echo "<a href='../PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
      
    }
    echo "</div>";
   
    echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb bread-bck ps-5 ">';
    echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a href="../../" class="text-dark text-decoration-none" >Home</a></li>';
    $breadcrumbUrl = '/';
    $segmentCount = count(array_filter($urlSegments));
    foreach ($urlSegments as $index => $segment) {
        if (!empty($segment)) {
            $breadcrumbUrl .= '../' . $segment;
            $isLastSegment = ($index === $segmentCount - 1);
            $class = $isLastSegment ? 'text-dark fw-semibold' : 'text-dark';
            $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
            }
            }
            echo '</ol></nav>';
            echo'<div class="container-fluid p-5 margin-dine mt-md-0 mt-2">
        <div class="row">
              <div class="col-md-5">
            <div class="row">
                
                <div class="col p-3">
                    <div class="media3 clip-top-right mw-100" style="background-image:url(\'/Pic/travel-tips/a.jpg\');    background-size: cover;;"></div>
                </div>
            </div>
        </div>
            <div class="col-md-7 mob-space ps-md-5 p-3 mt-md-4">
                 <h2>Travel Tips</h2>
        <p>Plan ahead to make the most of your journey. Research your destination weather, local customs, and attractions beforehand.
        Pack light and smart. Choose versatile clothing and essential items. Check baggage restrictions and airline policies.
        Keep important documents safe. Carry copies of your passport, visa, travel insurance, and emergency contacts.
        Stay hydrated and eat well. Bring snacks for long journeys and explore local cuisine responsibly.
        Stay connected. Have local maps, apps for translation or navigation, and keep your devices charged.
        Be respectful of local customs and traditions. Learn basic phrases in the local language.
        Stay vigilant with your belongings. Use secure luggage and be cautious in crowded or touristy areas.
        Plan for emergencies. Know emergency numbers, medical facilities, and have a basic first aid kit.
        Be flexible and patient. Travel disruptions can happen; keep a positive attitude and enjoy the journey.
        Stay informed about travel advisories and updates. </p>
            </div>
        </div>
    </div>
';

 echo'<div class="container-fluid px-md-5 px-4 mt-md-0 mt-2">
        <div class="row">
             
            <div class="col-md-7 mob-space mt-md-5 p-3">
               <h3 class="fw-strong mt-md-3 mt-2">TSA Cares</h3>
               <p class="fw-semibold">The Transportation Security Administration (TSA) is responsible for transitioning passengers through the airport’s security checkpoints. <br>

Tips for going through security:</p>
               <p>
<ul>
   <li>Travelers with disabilities who need to use the TSA Restricted Access lanes at all security checkpoints.</li>
  <li>For assistance through security, please contact TSA Cares. TSA Cares is a helpline that provides travelers with disabilities, medical conditions and other special circumstances additional assistance during the security screening process.</li>
</ul>
</p>

                <p class="mt-3">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Learn More at TSA Care</a>
</p>
            </div>
             <div class="col-md-5">
            <div class="row">
                
                <div class="col p-3">
                    <div class="media3 clip-top-right mw-100" style="background-image:url(\'/Pic/airport-sec/sec2.jpg\');"></div>
                </div>
            </div>
        </div>
        </div>
    </div>
';

 echo'<div class="container-fluid px-md-5 px-4">';
    $content = $row["content"];
    // echo "<p class='mt-5'>$content</p>";

    // Check if the page has child pages
    $childSql = "SELECT * FROM pages WHERE parent_id = ?";
    $childStmt = $conn->prepare($childSql);
    $childStmt->bind_param("i", $pageId);
    $childStmt->execute();
    $childResult = $childStmt->get_result();

    if ($childResult->num_rows > 0) {
        // Page has child pages, display the links to the child pages
       
        echo "<div class='row'>";

        $childCount = 0;
        while ($childRow = $childResult->fetch_assoc()) {
            $childTitle = $childRow["title"];
            $childSlug = $childRow["slug"];
            $childImage = $childRow["image"]; // Assuming you have an image column for child pages
            $childDate =$childRow["date"];
            //  $name = $_SESSION['Name'];
            $childUrl = generatePageUrl($childRow["id"]);

            if ($childCount > 0 && $childCount % 3 == 0) {
                echo '</div><div class="row mt-4">';
            }

            echo "<div class='col-md-4 mb-4'>";
            echo "<a href='../$childUrl' class='text-decoration-none text-dark ' >";
            echo "<div class='card border-0 h-100' style='box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;'>";
           if ($childImage) {
                echo "<img src='$childImage' class='card-img-top rounded-0' style='
    max-height: 23em;
' alt='$childTitle'>";
            }
            echo "<div class='card-body position-absolute w-100 p-2' style='    bottom: 1px;
    background: #1606a7a9;'>";
            echo "<h5 class='card-title text-light text-center fw-bold'>$childTitle</h5>";
            // echo "<small class='badge theme-bg my-2'>$childDate</small>";
            // echo "<small class='badge theme-bg my-2'>$childName</small>";
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
    $childStmt->close();
    echo'</div>
    <div class="container-fluid px-md-5 px-4 parking-back">
    <div class="text-center mb-5">
        <h2 class="fw-normal fs-1 text-light">Security Check Points</h2>
       
    </div>
    <div class="row">
        <!-- Card 1 -->
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-center">Checkpoint 1</h5>
                    <p class="card-text text-center">Standard</p>
                    <div class="fw-bold mt-3 text-center">2 </div>
                    <div class="text-center">min</div>
                    <div class="details mt-1">
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> 4:45 am – 8:45 pm</p>
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 2 -->
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-center">Checkpoint 2</h5>
                    <p class="card-text text-center">Standard</p>
                    <div class="fw-bold mt-3 text-center">2 </div>
                    <div class="text-center">min</div>
                    <div class="details mt-1">
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> 24 Hours Open</p>
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 3 -->
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-center">Checkpoint 3</h5>
                    <p class="card-text text-center">Standard</p>
                    <div class="fw-bold mt-3 text-center">2 </div>
                    <div class="text-center">min</div>
                    <div class="details mt-1">
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> 4:00 am - 9:45 pm</p>
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 4 -->
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-center">Checkpoint 4</h5>
                    <p class="card-text text-center">Standard</p>
                    <div class="fw-bold mt-3 text-center">2 </div>
                    <div class="text-center">min</div>
                    <div class="details mt-1">
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> 4:45 am - 8:45 pm</p>
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 5 -->
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-center">Checkpoint DFIS</h5>
                    <p class="card-text text-center">Standard</p>
                    <div class="fw-bold mt-3 text-center">2 </div>
                    <div class="text-center">min</div>
                    <div class="details mt-1">
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> 5:15 am – 8:45 pm</p>
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 6 -->
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-center">Checkpoint 5</h5>
                    <p class="card-text text-center">Standard</p>
                    <div class="fw-bold mt-3 text-center">2 </div>
                    <div class="text-center">min</div>
                    <div class="details mt-1">
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> 4:00 am - 10:45 pm</p>
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 7 -->
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-center">Checkpoint 6</h5>
                    <p class="card-text text-center">Standard</p>
                    <div class="fw-bold mt-3 text-center">2 </div>
                    <div class="text-center">min</div>
                    <div class="details mt-1">
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> 3:45 am – 10:45 pm</p>
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 8 -->
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-center">Checkpoint 7</h5>
                    <p class="card-text text-center">Standard</p>
                    <div class="fw-bold mt-3 text-center">2 </div>
                    <div class="text-center">min</div>
                    <div class="details mt-1">
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> 3:30 am – 10:15 pm</p>
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 9 -->
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-center">Checkpoint 8</h5>
                    <p class="card-text text-center">Standard</p>
                    <div class="fw-bold mt-3 text-center">2 </div>
                    <div class="text-center">min</div>
                    <div class="details mt-1">
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> 4:00 am – 8:00 pm</p>
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 10 -->
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-center">Checkpoint 9</h5>
                    <p class="card-text text-center">Standard</p>
                    <div class="fw-bold mt-3 text-center">2 </div>
                    <div class="text-center">min</div>
                    <div class="details mt-1">
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> 24 Hours Open</p>
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card 11 -->
        <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-center">Checkpoint 10</h5>
                    <p class="card-text text-center">Standard</p>
                    <div class="fw-bold mt-3 text-center">2 </div>
                    <div class="text-center">min</div>
                    <div class="details mt-1">
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> 9:45 am – 8:00 pm</p>
                        <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <div class="container-fluid p-5 margin-dine">
<div class="text-center mb-5">
            <h2 class="fw-normal fs-1">Helpful Services</h2>
            </div>
        <div class="row">
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                <div class="myimage3">
                    <img src="https://miamiairport-mia.com/Pic/uploaded/Homepage-Wheelchair-min.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Wheelchair Requests &rarr;</a>
            </div>
                </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
                    <img  src="https://miamiairport-mia.com/Pic/uploaded/WiFi-and-Technology-Hero.jpg " alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Wifi at Mia &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/uploaded/Airport-Facilities-and-Grounds-22-min.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Airport Facility &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/uploaded/Lost-and-Found-Hero-1.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Lost and Found &rarr;</a>
                
                    </div>
                    </div>
            </div>
        </div>
        <div class="row mt-md-5">
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/transportation.jpg" alt="Transportation">
                    <a class="helpful-font" href="#">Transportation &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Miami-International-Airport-parking.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Parking Option &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/uploaded/Airport-Safety-01-min.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Travel Tips &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/uploaded/Children-and-Families-01.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Traveling with Children &rarr;</a>
                
                    </div>
                    </div>
            </div>
        </div>
    </div>
<div class="card parking-back banner766 d-flex justify-content-center align-items-center my-5 px-md-5 px-1" >
    <div class="container help-width">
                    <p class="text-center text-light fs-2">Need Help Navigating MIA?</p>
                    <p class=" text-center text-light">Find your gate, baggage claim, art to explore, and places to eat, relax, or shop in seconds with our interactive map.</p>
                   
                    <div class="d-flex justify-content-center align-items-center"> <a class="btn btn-size  btn-mysuccess" href="#" target="_blank">Interactive Terminal Maps</a></div>
                </div>
                
    </div>
<div class="container-fluid px-md-5 px-4">';
    echo'
        <div class="text-center">
            <h2>Arrive Early and Explore Miami</h2>
           
        </div>

        <div class="row">
            <div class="col-md-6 p-0">
                <div class="resource shadow-none border-0 p-0">
                    <div class="resource-body">
                      <img src="/Pic/travel-tips/b.jpg" class="card-img-top img-fluid" style="object-fit: cover;height: 200px; " alt="North Terminal">
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-palette icond "></i></span>  <a href="#" class="d-flex align-items-center justify-content-between my-3">Explore the Art Deco District <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-city icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Visit Little Havana <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                           
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-paw icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Plan a Trip to the Everglades <span class="ml-auto arrow">→</span></a></li>
                          
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6 p-0">
                <div class="resource shadow-none border-0 p-0">
                    <div class="resource-body">
                       <img src="/Pic/travel-tips/c.jpg" class="card-img-top img-fluid" style="object-fit: cover; height: 200px;" alt="North Terminal">
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-store icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Experience Miami’s Nightlife <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                           
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-swimmer icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3">Enjoy Water Activities <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                           
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-gavel icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Rules and Regulations <span class="ml-auto arrow">→</span></a></li>
                          
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    
 ';
    
 
echo'
        <div class="text-center">
            <h2>Helpful Resources</h2>
            <div class="btn btn-size-group mt-3" role="group" aria-label="Accessibility Options">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Accessibility Statement</a>
            <a href="#" class="btn btn-size btn btn-outline-secondary mob-button">Accessibility Services</a>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Traveler Tips</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-globe icond "></i></span>  <a href="#" class="d-flex align-items-center justify-content-between my-3">International Travelers <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-passport icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Infrequent Traveler Tips <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-child icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Children and Families <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-wheelchair icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Wheelchair Requests <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-map icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Interactive Map <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-paw icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Traveling with Pets <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Business Information</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-briefcase icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Doing Business at MIA <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-users icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Business Diversity and Development <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-store icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Concessions Opportunities <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-info-circle icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> General Tenant Information <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-balance-scale icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> CEEA: Center of Equity and Excellence in Aviation <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-gavel icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Rules and Regulations <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    
 ';
 
  
    echo "</div>"; // Close title-container
}

// Services and amenities start
 elseif($design == "Design_12"){
    echo "<div class=' p-0'>";
    echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
    echo "<h1 class='ms-5'>$title</h1>";
    echo '</div>';
    if(isset($_SESSION['email'])){

        echo "<a href='../PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
 
        echo "<a href='../PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
      
    }
    echo "</div>";
   
    echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb bread-bck ps-5 ">';
    echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a href="../../" class="text-dark text-decoration-none" >Home</a></li>';
    $breadcrumbUrl = '/';
    $segmentCount = count(array_filter($urlSegments));
    foreach ($urlSegments as $index => $segment) {
        if (!empty($segment)) {
            $breadcrumbUrl .= '../' . $segment;
            $isLastSegment = ($index === $segmentCount - 1);
            $class = $isLastSegment ? 'text-dark fw-semibold' : 'text-dark';
            $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
            }
            }
            echo '</ol></nav>';
            echo'<div class="container-fluid p-5 margin-dine mt-md-0 mt-2">
        <div class="row">
              <div class="col-md-5">
            <div class="row">
                
                <div class="col p-3">
                    <div class="media3 clip-top-right mw-100" style="background-image:url(\'/Pic/services and amenities/a.jpg\');    background-size: cover;"></div>
                </div>
            </div>
        </div>
            <div class="col-md-7 mob-space ps-md-5 p-3 mt-md-5">
                 <h2>Accessibility and Mobility</h2>
        <p class="fw-semibold">The Miami International Airport offers a number of services available for all passengers of all abilities who may need assistance navigating the airport.</p>
        <ul class="custom-bullet">
          <li><strong>Wheelchair Assistance:</strong> Available upon request through airlines, from curbside to gate.</li>
          <li><strong>Accessible Restrooms:</strong> Located throughout the terminal.</li>
          <li><strong>Service Animal Relief Areas:</strong> Both inside and outside the terminals.</li>
          <li><strong>Accessible Parking:</strong> Spaces in all parking garages near elevators and walkways.</li>
          <li><strong>Shuttle Services:</strong> Accessible shuttles between parking areas and terminals.</li>
          <li><strong>Public Transportation:</strong> Accessible options via Miami-Dade Transit Metrorail and Metrobus systems.</li>
        </ul>
 <p class="mt-3">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Learn More</a>
</p>
            </div>
        </div>
    </div>
';

 echo'<div class="container-fluid p-5 margin-dine mt-md-0 mt-2">
        <div class="row">
             
            <div class="col-md-7 mob-space mt-md-5 p-3">
               <h3 class="fw-strong mt-md-3 mt-2">Water Bottle Filling Stations</h3>
              <p>Miami International Airport (MIA) offers convenient water bottle filling stations for passengers:</p>
<ul class="custom-bullet">
  <li><strong>Location:</strong> Stations are strategically placed throughout the terminal for easy access.</li>
  <li><strong>Eco-Friendly:</strong> Encourages the use of reusable water bottles to reduce plastic waste.</li>
  <li><strong>Hydration:</strong> Provides free, filtered water to keep travelers hydrated.</li>
  <li><strong>Accessibility:</strong> Stations are designed to be accessible to all passengers, including those with disabilities.</li>
</ul>
</p>

                <p class="mt-3">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Learn More</a>
</p>
            </div>
             <div class="col-md-5">
            <div class="row">
                
                <div class="col p-3">
                    <div class="media3 clip-top-right mw-100" style="background-image:url(\'/Pic/services and amenities/b.jpg\');"></div>
                </div>
            </div>
        </div>
        </div>
    </div>
';

 echo'<div class="container-fluid px-md-5 px-4">';
    $content = $row["content"];
    // echo "<p class='mt-5'>$content</p>";

    // Check if the page has child pages
    $childSql = "SELECT * FROM pages WHERE parent_id = ?";
    $childStmt = $conn->prepare($childSql);
    $childStmt->bind_param("i", $pageId);
    $childStmt->execute();
    $childResult = $childStmt->get_result();

    if ($childResult->num_rows > 0) {
        // Page has child pages, display the links to the child pages
       
        echo "<div class='row'>";

        $childCount = 0;
        while ($childRow = $childResult->fetch_assoc()) {
            $childTitle = $childRow["title"];
            $childSlug = $childRow["slug"];
            $childImage = $childRow["image"]; // Assuming you have an image column for child pages
            $childDate =$childRow["date"];
            //  $name = $_SESSION['Name'];
            $childUrl = generatePageUrl($childRow["id"]);

            if ($childCount > 0 && $childCount % 3 == 0) {
                echo '</div><div class="row mt-4">';
            }

            echo "<div class='col-md-4 mb-4'>";
            echo "<a href='../$childUrl' class='text-decoration-none text-dark ' >";
            echo "<div class='card border-0 h-100' style='box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;'>";
          if ($childImage) {
                echo "<img src='$childImage' class='card-img-top rounded-0' style='
    max-height: 23em;
' alt='$childTitle'>";
            }
            echo "<div class='card-body position-absolute w-100 p-2' style='    bottom: 1px;
    background: #1606a7a9;'>";
            echo "<h5 class='card-title text-light text-center fw-bold'>$childTitle</h5>";
            // echo "<small class='badge theme-bg my-2'>$childDate</small>";
            // echo "<small class='badge theme-bg my-2'>$childName</small>";
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
    $childStmt->close();
    echo'</div>
   <div class="container-fluid p-md-5 p-4 parking-back">
    <div class="text-center mb-5">
        <h2 class="fw-normal fs-1 text-light">Miami International Airport Services</h2>
    </div>
    <div class="row">
        <!-- Card 1 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/305-pizza.jpg" class="card-img-top img-fluid object-fit-cover" alt="Air Bar">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-mug-hot"></i> 305 Pizza</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> North Terminal,Concourse D</p>
                    <p class="card-text text-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> Open until 8:00 pm</p>
                </div>
            </div>
        </div>
        <!-- Card 2 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/Bacardi Mojito Bar.jpg " class="card-img-top img-fluid object-fit-cover" alt="Gateway Bake Shop">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-bread-slice"></i> Bacardi Mojito Bar</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> North Terminal,Concourse D</p>
                    <p class="card-text text-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> Open until 9:00 pm</p>
                </div>
            </div>
        </div>
        <!-- Card 3 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/Corona Beach House.jpg" class="card-img-top img-fluid object-fit-cover" alt="Metro News & Gifts">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-newspaper"></i> Corona Beach House</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> North Terminal,Concourse D</p>
                    <p class="card-text text-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> Open until 9:30 pm</p>
                </div>
            </div>
        </div>
        <!-- Card 4 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/Fig & Fennel.jpg" class="card-img-top img-fluid object-fit-cover" alt="Metro News & Gifts (Temporarily Closed)">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-newspaper"></i> Famous Famiglia</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> South Terminal, Concourse H-J Connector</p>
                    <p class="card-text text-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> Open until 9:00 pm</p>
                </div>
            </div>
        </div>
        <!-- Card 5 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://shopmiami.wpenginepowered.com/wp-content/uploads/2019/04/Fig-Fennel_Terminal-D-1048x618-1-330x195.jpg" class="card-img-top img-fluid object-fit-cover" alt="New York Deli">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-utensils"></i> Fig & Fennel</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> North Terminal, Concourse D</p>
                    <p class="card-text text-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> Open until 9:00 pm</p>
                </div>
            </div>
        </div>
        <!-- Card 6 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/Gilbert’s Food Bar (Pre-Security).jpg" class="card-img-top img-fluid object-fit-cover" alt="Restrooms">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-restroom"></i> Gilbert’s Food Bar (Pre-Security)</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> South Terminal- Door 26 - 2nd Level Departures					 </p>
                    <p class="card-text text-center"> <span class="fa-solid fa-clock" aria-hidden="true"></span> Open until 9:30 pm</p>
                </div>
            </div>
        </div>
        <!-- Card 7 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/La Pausa.jpg" class="card-img-top img-fluid object-fit-cover" alt="Restrooms">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-restroom"></i> La Pausa</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> South Terminal, Concourse H-J Connector</p>
                    <p class="card-text text-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> Open until 8:00 pm</p>
                </div>
            </div>
        </div>
        <!-- Card 8 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/Nathan’s Famous.jpg" class="card-img-top img-fluid object-fit-cover" alt="Tim Hortons">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-coffee"></i> Nathan’s Famous</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> South Terminal, Concourse H</p>
                    <p class="card-text text-center"> <span class="fa-solid fa-clock" aria-hidden="true"></span> Open until 4:00 pm</p>
                </div>
            </div>
        </div>
    </div>
</div>


    <div class="container-fluid p-md-5 p-4 mt-5">
<div class="text-center mb-4">
            <h2 class="fw-normal fs-1">Some Helpful Amenities</h2>
           
        </div>
        <div class="row">
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                <div class="myimage3">
                    <img src="https://miamiairport-mia.com/Pic/Card/s1.jpeg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Wheelchair Requests &rarr;</a>
            </div>
                </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
                    <img  src="https://miamiairport-mia.com/Pic/Card/s2.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Wifi at Mia &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Card/s3.webp" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Airport Facility &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Card/s4.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Lost and Found &rarr;</a>
                
                    </div>
                    </div>
            </div>
        </div>
        <div class="row mt-md-5">
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Card/s5.webp" alt="Transportation">
                    <a class="helpful-font" href="#">Transportation &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Card/s6.webp" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Parking Option &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Card/s7.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Travel Tips &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Card/s8.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Traveling with Children &rarr;</a>
                
                    </div>
                    </div>
            </div>
        </div>
    </div>

<div class="container-fluid px-md-5 px-4">';
    echo'
        <div class="text-center">
            <h2>Flight Information</h2>
           
        </div>

        <div class="row">
            <div class="col-md-6 p-0">
                <div class="resource shadow-none border-0 p-0">
                   <div class="resource-body">
    <img src="/Pic/services and amenities/c.jpg" class="card-img-top img-fluid" style="object-fit: cover;height: 200px;" alt="North Terminal">
    <h3 class="mt-3">Arrivals</h3>
    <p>Welcome to Miami International Airport. We hope you have a pleasant stay and enjoy all that our airport and the vibrant city of Miami have to offer.</p>
</div>
                </div>
            </div>
            <div class="col-md-6 p-0">
                <div class="resource shadow-none border-0 p-0">
                    <div class="resource-body">
    <img src="/Pic/services and amenities/d.jpg" class="card-img-top img-fluid" style="object-fit: cover;height: 200px;" alt="North Terminal">
    <h3 class="mt-3">Departures</h3>
    <p>Prepare for your journey from Miami International Airport. Ensure you have all your belongings and necessary documents, and enjoy a smooth departure experience.</p>
</div>
                </div>
            </div>
        </div>
    
 ';
    
 
echo'
        <div class="text-center">
            <h2>Helpful Resources</h2>
            <div class="btn btn-size-group mt-3" role="group" aria-label="Accessibility Options">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Accessibility Statement</a>
            <a href="#" class="btn btn-size btn btn-outline-secondary mob-button">Accessibility Services</a>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Traveler Tips</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-globe icond "></i></span>  <a href="#" class="d-flex align-items-center justify-content-between my-3">International Travelers <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-passport icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Infrequent Traveler Tips <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-child icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Children and Families <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-wheelchair icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Wheelchair Requests <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-map icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Interactive Map <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-paw icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Traveling with Pets <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Business Information</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-briefcase icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Doing Business at MIA <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-users icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Business Diversity and Development <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-store icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Concessions Opportunities <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-info-circle icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> General Tenant Information <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-balance-scale icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> CEEA: Center of Equity and Excellence in Aviation <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-gavel icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Rules and Regulations <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    
 ';
 
  
    echo "</div>"; // Close title-container
}

// Services and amenities end
      // Customer Service start
 elseif($design == "Design_13"){
    echo "<div class=' p-0'>";
    echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
    echo "<h1 class='ms-5'>$title</h1>";
    echo '</div>';
    if(isset($_SESSION['email'])){

        echo "<a href='../PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
 
        echo "<a href='../PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
      
    }
    echo "</div>";
   
    echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb bread-bck ps-5 ">';
    echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a href="../../" class="text-dark text-decoration-none" >Home</a></li>';
    $breadcrumbUrl = '/';
    $segmentCount = count(array_filter($urlSegments));
    foreach ($urlSegments as $index => $segment) {
        if (!empty($segment)) {
            $breadcrumbUrl .= '../' . $segment;
            $isLastSegment = ($index === $segmentCount - 1);
            $class = $isLastSegment ? 'text-dark fw-semibold' : 'text-dark';
            $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
            }
            }
            echo '</ol></nav>';
            echo'<div class="container-fluid p-5 margin-dine mt-md-0 mt-2">
        <div class="row">
              <div class="col-md-5">
            <div class="row">
                
                <div class="col p-3">
                    <div class="media3 clip-top-right mw-100" style="background-image:url(\'/Pic/customer-service.jpg\');    background-size: cover;"></div>
                </div>
            </div>
        </div>
            <div class="col-md-7 mob-space ps-md-5 p-3 mt-md-4">
                 <h2>Accessibility Services at Miami International Airport</h2>
<p class="fw-semibold">Miami International Airport offers a variety of services to assist passengers of all abilities:</p>
<ul class="custom-bullet">
  <li><strong>Wheelchair Assistance:</strong> Request wheelchair service from airlines for assistance from curbside to your gate.</li>
  <li><strong>Accessible Restrooms:</strong> Easily accessible restrooms are located throughout the terminals.</li>
  <li><strong>Service Animal Relief Areas:</strong> Find designated areas both inside and outside the terminals.</li>
  <li><strong>Accessible Parking:</strong> Accessible parking spaces are available in all parking garages near elevators and walkways.</li>
  <li><strong>Shuttle Services:</strong> Accessible shuttles operate between parking areas and terminals for convenience.</li>
  <li><strong>Public Transportation:</strong> Accessible options include Miami-Dade Transit Metrorail and Metrobus systems.</li>
</ul>

 <p class="mt-3">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Learn More</a>
</p>
            </div>
        </div>
    </div>
';



 echo'<div class="container-fluid px-md-5 px-4">';
    $content = $row["content"];
    // echo "<p class='mt-5'>$content</p>";

    // Check if the page has child pages
    $childSql = "SELECT * FROM pages WHERE parent_id = ?";
    $childStmt = $conn->prepare($childSql);
    $childStmt->bind_param("i", $pageId);
    $childStmt->execute();
    $childResult = $childStmt->get_result();

    if ($childResult->num_rows > 0) {
        // Page has child pages, display the links to the child pages
       
        echo "<div class='row'>";

        $childCount = 0;
        while ($childRow = $childResult->fetch_assoc()) {
            $childTitle = $childRow["title"];
            $childSlug = $childRow["slug"];
            $childImage = $childRow["image"]; // Assuming you have an image column for child pages
            $childDate =$childRow["date"];
            //  $name = $_SESSION['Name'];
            $childUrl = generatePageUrl($childRow["id"]);

            if ($childCount > 0 && $childCount % 3 == 0) {
                echo '</div><div class="row mt-4">';
            }

            echo "<div class='col-md-4 mb-4'>";
            echo "<a href='../$childUrl' class='text-decoration-none text-dark ' >";
            echo "<div class='card border-0 h-100' style='box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;'>";
          if ($childImage) {
                echo "<img src='$childImage' class='card-img-top rounded-0' style='
    max-height: 23em;
' alt='$childTitle'>";
            }
            echo "<div class='card-body position-absolute w-100 p-2' style='    bottom: 1px;
    background: #1606a7a9;'>";
            echo "<h5 class='card-title text-light text-center fw-bold'>$childTitle</h5>";
            // echo "<small class='badge theme-bg my-2'>$childDate</small>";
            // echo "<small class='badge theme-bg my-2'>$childName</small>";
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
    $childStmt->close();
    echo'</div>


    <div class="container-fluid p-md-5 p-4 my-5 ">
<div class="text-center mb-4">
            <h2 class="fw-normal fs-1">Some Helpful Amenities</h2>
           
        </div>
        <div class="row">
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                <div class="myimage3">
                    <img src="https://presspage-production-prd-content.s3.amazonaws.com/uploads/1911/0be11484-a95e-4388-b68b-882f0493380a/800_autowheelchairsmayorampspeakers3.jpeg?10000" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Wheelchair Requests &rarr;</a>
            </div>
                </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
                    <img  src="https://fastly.4sqi.net/img/general/1398x536/22749419_f33SrOPjnZbgvQA_jxXv_1HNTWug9IfYYRoh-c99ySQ.jpg " alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Wifi at Mia &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/uploaded/Airport-Facilities-and-Grounds-22-min.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Airport Facility &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTHP6BMcxa7thOiEyh5PggTPGVzKdF58bpzxA&s" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Lost and Found &rarr;</a>
                
                    </div>
                    </div>
            </div>
        </div>
        <div class="row mt-md-5">
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/transportation.jpg" alt="Transportation">
                    <a class="helpful-font" href="#">Transportation &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Miami-International-Airport-parking.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Parking Option &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://assets1.cbsnewsstatic.com/hub/i/r/2023/09/01/96bbac4b-c0dc-4d88-86e6-d82ad0c1e56f/thumbnail/1200x630g6/d6b07e98a0a6b0236d5ee5966f9e411c/gettyimages-1487118872.jpg?v=a23cb4bdf4fa7f3cb72e5118085577f9" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Travel Tips &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://res.cloudinary.com/mommy-nearest/image/upload/c_fill,h_450,w_800/jwjizomgqdpzuj5fxsnr.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Traveling with Children &rarr;</a>
                
                    </div>
                    </div>
            </div>
        </div>
    </div>

<div class="container-fluid px-md-5 px-4">';
    echo'
        <div class="text-center mb-4">
            <h2 class="fw-normal fs-1">Flight Information</h2>
           
        </div>

        <div class="row">
            <div class="col-md-6 p-0">
                <div class="resource shadow-none border-0 p-0">
                   <div class="resource-body">
    <img src="/Pic/services and amenities/c.jpg" class="card-img-top img-fluid" style="object-fit: cover;height: 200px;" alt="North Terminal">
    <h3 class="mt-3">Arrivals</h3>
    <p>Welcome to Miami International Airport. We hope you have a pleasant stay and enjoy all that our airport and the vibrant city of Miami have to offer.</p>
</div>
                </div>
            </div>
            <div class="col-md-6 p-0">
                <div class="resource shadow-none border-0 p-0">
                    <div class="resource-body">
    <img src="/Pic/services and amenities/d.jpg" class="card-img-top img-fluid" style="object-fit: cover;height: 200px;" alt="North Terminal">
    <h3 class="mt-3">Departures</h3>
    <p>Prepare for your journey from Miami International Airport. Ensure you have all your belongings and necessary documents, and enjoy a smooth departure experience.</p>
</div>
                </div>
            </div>
        </div>
    
 ';
    
 
echo'
        <div class="text-center mb-4">
            <h2 class="fw-normal fs-1">Helpful Resources</h2>
            <div class="btn btn-size-group mt-3" role="group" aria-label="Accessibility Options">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Accessibility Statement</a>
            <a href="#" class="btn btn-size btn btn-outline-secondary mob-button">Accessibility Services</a>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Traveler Tips</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-globe icond "></i></span>  <a href="#" class="d-flex align-items-center justify-content-between my-3">International Travelers <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-passport icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Infrequent Traveler Tips <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-child icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Children and Families <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-wheelchair icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Wheelchair Requests <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-map icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Interactive Map <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-paw icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Traveling with Pets <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Business Information</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-briefcase icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Doing Business at MIA <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-users icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Business Diversity and Development <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-store icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Concessions Opportunities <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-info-circle icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> General Tenant Information <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-balance-scale icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> CEEA: Center of Equity and Excellence in Aviation <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-gavel icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Rules and Regulations <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    
 ';
 
  
    echo "</div>"; // Close title-container
}

// Customer Services end



// Arts & Exhibitions start
 elseif($design == "Design_14"){
    echo "<div class=' p-0'>";
    echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
    echo "<h1 class='ms-5'>$title</h1>";
    echo '</div>';
    if(isset($_SESSION['email'])){

        echo "<a href='../PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
 
        echo "<a href='../PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
      
    }
    echo "</div>";
   
    echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb bread-bck ps-5 ">';
    echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a href="../../" class="text-dark text-decoration-none" >Home</a></li>';
    $breadcrumbUrl = '/';
    $segmentCount = count(array_filter($urlSegments));
    foreach ($urlSegments as $index => $segment) {
        if (!empty($segment)) {
            $breadcrumbUrl .= '../' . $segment;
            $isLastSegment = ($index === $segmentCount - 1);
            $class = $isLastSegment ? 'text-dark fw-semibold' : 'text-dark';
            $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
            }
            }
            echo '</ol></nav>';
            echo'<div class="container-fluid p-5 margin-dine mt-md-0 mt-2">
        <div class="row">
              <div class="col-md-5">
            <div class="row">
                
                <div class="col p-3">
                    <div class="media3 clip-top-right mw-100" style="background-image:url(\'/Pic/art/art hero.jpg\'); background-size: cover;;"></div>
                </div>
            </div>
        </div>
            <div class="col-md-7 mob-space ps-md-5 p-3 mt-md-5">
                 <h2>Miami International Airport Art Program</h2>
<p class="fw-semibold">Explore the Art of Miami International Airport</p>
Discover the vibrant art scene at Miami International Airport. Our Art Program showcases a diverse collection of artworks from local and international artists. From stunning murals and sculptures to innovative installations, each piece reflects the rich cultural tapestry of Miami. Enjoy a journey through creativity and inspiration as you navigate through the airport.
</p>
<p>
Art is integrated into various terminals and public spaces, offering travelers a unique and enriching experience. Whether you have a layover or are just passing through, take a moment to explore the art pieces that bring beauty and a sense of place to Miami International Airport.


            </div>
        </div>
    </div>
';


echo'<div class="container-fluid p-md-5 p-4 my-5 parking-back">
  <div class="row">
    <div class="col-md-6 text-light">
      <h2>Explore MIA public art and exhibitions.</h2>
     <img src="/Pic/art/arts.jpg" class="card-img-top img-fluid my-1" style="object-fit: cover;     max-height: 266px;" alt="Arts">
            
  <div class=""> <a class="btn btn-size  btn-mysuccess" href="#" target="_blank">Learn More About our Art Programs</a></div>
      </div>
       <div class="col-md-6 text-light">
     <p class="mt-md-0 mt-3">
    Miami International Airport (MIA) celebrates art and culture with dynamic exhibitions that adorn its terminals and concourses, showcasing local artists and cultural organizations. These exhibitions are curated to enrich the journey for travelers and staff alike. MIA Public Art Program features a collection of over 50 artworks spread throughout the airport, reflecting Miamis vibrant cultural scene.
</p>
<ul class="custom-bullet">
    <li><strong>Current Exhibitions:</strong> Explore the latest art and cultural exhibitions currently on display at MIA. These exhibitions are regularly updated, providing visitors with fresh experiences during their time at the airport.</li>
    <li><strong>Past Exhibitions:</strong> Discover previous exhibitions that have graced MIA. While no longer on display, you can still appreciate these artworks through photo galleries and learn about the artists and their contributions to the airport cultural landscape.</li>
    <li><strong>Public Art:</strong> Experience a diverse array of permanent sculptures, murals, and installations that form an integral part of MIAs aesthetic appeal and cultural identity.</li>
</ul>

    </div>
  </div>
</div>';
 echo'<div class="container-fluid px-md-5 px-4">';
    $content = $row["content"];
    // echo "<p class='mt-5'>$content</p>";

   
    echo'</div>


   
<div class="container-fluid px-md-5 px-4">';
  
    
 
echo'
        <div class="text-center">
            <h2>Helpful Resources</h2>
            <div class="btn btn-size-group mt-3" role="group" aria-label="Accessibility Options">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Accessibility Statement</a>
            <a href="#" class="btn btn-size btn btn-outline-secondary mob-button">Accessibility Services</a>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Traveler Tips</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-globe icond "></i></span>  <a href="#" class="d-flex align-items-center justify-content-between my-3">International Travelers <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-passport icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Infrequent Traveler Tips <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-child icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Children and Families <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-wheelchair icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Wheelchair Requests <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-map icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Interactive Map <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-paw icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Traveling with Pets <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Business Information</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-briefcase icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Doing Business at MIA <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-users icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Business Diversity and Development <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-store icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Concessions Opportunities <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-info-circle icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> General Tenant Information <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-balance-scale icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> CEEA: Center of Equity and Excellence in Aviation <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-gavel icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Rules and Regulations <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    
 ';
  // Check if the page has child pages
    $childSql = "SELECT * FROM pages WHERE parent_id = ?";
    $childStmt = $conn->prepare($childSql);
    $childStmt->bind_param("i", $pageId);
    $childStmt->execute();
    $childResult = $childStmt->get_result();

    if ($childResult->num_rows > 0) {
        // Page has child pages, display the links to the child pages
       echo'<h3 class="text-center mt-5">Explore arts at Miami</h3>';
        echo "<div class='row mt-3'>";

        $childCount = 0;
        while ($childRow = $childResult->fetch_assoc()) {
            $childTitle = $childRow["title"];
            $childSlug = $childRow["slug"];
            $childImage = $childRow["image"]; // Assuming you have an image column for child pages
            $childDate =$childRow["date"];
            //  $name = $_SESSION['Name'];
            $childUrl = generatePageUrl($childRow["id"]);

            if ($childCount > 0 && $childCount % 3 == 0) {
                echo '</div><div class="row mt-4">';
            }

            echo "<div class='col-md-4 mb-4'>";
            echo "<a href='../$childUrl' class='text-decoration-none text-dark ' >";
            echo "<div class='card border-0 h-100' style='box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;'>";
            if ($childImage) {
                echo "<img src='$childImage' class='card-img-top rounded-0' style='
    max-height: 23em;
' alt='$childTitle'>";
            }
            echo "<div class='card-body position-absolute w-100 p-2' style='    bottom: 1px;
    background: #1606a7a9;'>";
            echo "<h5 class='card-title text-light text-center fw-bold'>$childTitle</h5>";
            // echo "<small class='badge theme-bg my-2'>$childDate</small>";
            // echo "<small class='badge theme-bg my-2'>$childName</small>";
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
    $childStmt->close();
  
    echo "</div>"; // Close title-container
}
// Arts & Exhibitions end
// Parking Start
 elseif($design == "Design_15"){
    echo "<div class=' p-0'>";
    echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
    echo "<h1 class='ms-5'>$title</h1>";
    echo '</div>';
    if(isset($_SESSION['email'])){

        echo "<a href='../PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
 
        echo "<a href='../PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
      
    }
    echo "</div>";
   
    echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb bread-bck ps-5 ">';
    echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a href="../../" class="text-dark text-decoration-none" >Home</a></li>';
    $breadcrumbUrl = '/';
    $segmentCount = count(array_filter($urlSegments));
    foreach ($urlSegments as $index => $segment) {
        if (!empty($segment)) {
            $breadcrumbUrl .= '../' . $segment;
            $isLastSegment = ($index === $segmentCount - 1);
            $class = $isLastSegment ? 'text-dark fw-semibold' : 'text-dark';
            $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
            }
            }
            echo '</ol></nav>';
            echo'<div class="container-fluid p-5 margin-dine mt-md-0 mt-2">
        <div class="row">
              <div class="col-md-5">
            <div class="row">
                
                <div class="col p-3">
                    <div class="media3 clip-top-right mw-100" style="background-image:url(\'/Pic/customer-service.jpg\');    background-size: cover;"></div>
                </div>
            </div>
        </div>
            <div class="col-md-7 mob-space ps-md-5 p-3 mt-md-4">
                 <h2>Lot Info and Availability</h2>
<p class="fw-semibold">Miami International Airport offers a variety of services to assist passengers of all abilities:</p>
<ul class="custom-bullet">
  <li><strong>Wheelchair Assistance:</strong> Request wheelchair service from airlines for assistance from curbside to your gate.</li>
  <li><strong>Accessible Restrooms:</strong> Easily accessible restrooms are located throughout the terminals.</li>
  <li><strong>Service Animal Relief Areas:</strong> Find designated areas both inside and outside the terminals.</li>
  <li><strong>Accessible Parking:</strong> Accessible parking spaces are available in all parking garages near elevators and walkways.</li>
  <li><strong>Shuttle Services:</strong> Accessible shuttles operate between parking areas and terminals for convenience.</li>
  <li><strong>Public Transportation:</strong> Accessible options include Miami-Dade Transit Metrorail and Metrobus systems.</li>
</ul>

 <p class="mt-3">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Learn More</a>
</p>
            </div>
        </div>
    </div>
';



 echo'<div class="container-fluid px-md-5 px-4">';
    $content = $row["content"];
    // echo "<p class='mt-5'>$content</p>";

    // Check if the page has child pages

    echo'</div>
 <div class="container-fluid parking-back mt-5 px-md-5 px-4">
        <div class="text-light d-flex justify-content-between">
            <h2>Planning Ahead</h2>
            <!-- <div class="btn btn-size-group" role="group" aria-label="Navigation Options">
                <button type="button" class="btn btn-size btn btn-size-light">Parking Lots</button>
                <button type="button" class="btn btn-size btn btn-size-outline-light">Average Walking Times</button>
            </div> -->
        </div>

        <div class="row mt-5">
            <!-- Parking Lots Section -->
            <div class="col-md-12">
                <div class="d-md-flex justify-content-between align-items-center">
                    <div class="text-light">
                        <div>
                             <i class="fas fa-walking"></i> Walk to Terminal
                        </div>
                        <div>
                            <i class="fas fa-shuttle-van ml-3"></i> Free Shuttle to Terminal
                        </div>
                       
                        
                    </div>
                    <a href="#" class="text-light text-decoration-none">All Parking Lot Information →</a>
                </div>
                <div class="row mt-3">
                    <div class="col-md-3 mb-3">
                        <div class="card parking-card">
                            <div class="card-body">
                                <h5 class="card-title">West Garage</h5>
                                <p class="card-text text-dark"><span class="badge badge-theme-bg p-2 rounded-pill"><i class="fas fa-check-circle text-dark"></i><span class="mx-2 text-dark">Open</span> </span> <span class="text-black"><span class="parking-card-icon"><i class="fas fa-walking"></i></span>
                                </span></p>
                                <a href="#" class="theme-color fw-bold text-decoration-none">Learn More <span class="ml-auto arrow">→</span></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card parking-card">
                            <div class="card-body">
                                <h5 class="card-title">Premium Reserve</h5>
                                <p class="card-text">Reservation Only <span class="text-black"><span class="parking-card-icon"><i class="fas fa-walking"></i></span>
                                </span></p>
                                <a href="#" class="theme-color fw-bold text-decoration-none">Reserve Now <span class="ml-auto arrow">→</span></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card parking-card">
                            <div class="card-body">
                                <h5 class="card-title">East Garage</h5>
                                <p class="card-text"><span class="badge badge-theme-bg p-2 rounded-pill"><i class="fas fa-check-circle text-dark"></i><span class="mx-2 text-dark">Open</span> </span>  <span class="text-black"><span class="parking-card-icon"><i class="fas fa-walking"></i></span>
                                <span class="parking-card-icon"><i class="fas fa-shuttle-van ml-3"></i></span></span></p>
                                <a href="#" class="theme-color fw-bold text-decoration-none">Learn More <span class="ml-auto arrow">→</span></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card parking-card">
                            <div class="card-body">
                                <h5 class="card-title">East Economy</h5>
                                <p class="card-text"><span class="badge badge-theme-bg p-2 rounded-pill"><i class="fas fa-check-circle text-dark"></i><span class="mx-2 text-dark">Open</span> </span>  <span class="text-black"><span class="parking-card-icon"><i class="fas fa-walking"></i></span>
                                <span class="parking-card-icon"><i class="fas fa-shuttle-van ml-3"></i></span></span></p>
                                <a href="#" class="theme-color fw-bold text-decoration-none">Learn More <span class="ml-auto arrow">→</span></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card parking-card">
                            <div class="card-body">
                                <h5 class="card-title">West Economy</h5>
                                <p class="card-text"><span class="badge badge-theme-bg p-2 rounded-pill"><i class="fas fa-check-circle text-dark"></i><span class="mx-2 text-dark">Open</span> </span>  <span class="text-black"><span class="parking-card-icon"><i class="fas fa-walking"></i></span>
                                </span></p>
                                <a href="#" class="theme-color fw-bold text-decoration-none">Learn More <span class="ml-auto arrow">→</span></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card parking-card">
                            <div class="card-body">
                                <h5 class="card-title">Pikes Peak Lot</h5>
                                <p class="card-text"><span class="badge badge-theme-bg p-2 rounded-pill"><i class="fas fa-check-circle text-dark"></i><span class="mx-2 text-dark">Open</span> </span>  <span class="text-black"><span class="parking-card-icon"><i class="fas fa-walking"></i></span>
                                </span></p>
                                <a href="#" class="theme-color fw-bold text-decoration-none">Learn More <span class="ml-auto arrow">→</span></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card parking-card">
                            <div class="card-body">
                                <h5 class="card-title">Longs Peak Lot</h5>
                                <p class="card-text"><span class="badge badge-theme-bg p-2 rounded-pill"><i class="fas fa-check-circle text-dark"></i><span class="mx-2 text-dark">Open</span> </span>  <span class="text-black"><span class="parking-card-icon"><i class="fas fa-walking"></i></span>
                                </span></p>
                                <a href="#" class="theme-color fw-bold text-decoration-none">Learn More <span class="ml-auto arrow">→</span></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card parking-card">
                            <div class="card-body">
                                <h5 class="card-title">Short Term East</h5>
                                <p class="card-text"><span class="badge badge-theme-bg p-2 rounded-pill"><i class="fas fa-check-circle text-dark"></i><span class="mx-2 text-dark">Open</span> </span>  <span class="text-black"><span class="parking-card-icon"><i class="fas fa-walking"></i></span>
                                </span></p>
                                <a href="#" class="theme-color fw-bold text-decoration-none">Learn More <span class="ml-auto arrow">→</span></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card parking-card">
                            <div class="card-body">
                                <h5 class="card-title">Short Term West</h5>
                                <p class="card-text"><span class="badge badge-theme-bg p-2 rounded-pill"><i class="fas fa-check-circle text-dark"></i><span class="mx-2 text-dark">Open</span> </span>  <span class="text-black"><span class="parking-card-icon"><i class="fas fa-walking"></i></span>
                                </span></p>
                                <a href="#" class="theme-color fw-bold text-decoration-none">Learn More <span class="ml-auto arrow">→</span></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card parking-card">
                            <div class="card-body">
                                <h5 class="card-title">West Garage</h5>
                                <p class="card-text text-dark"><span class="badge badge-theme-bg p-2 rounded-pill"><i class="fas fa-check-circle text-dark"></i><span class="mx-2 text-dark">Open</span> </span> <span class="text-black"><span class="parking-card-icon"><i class="fas fa-walking"></i></span>
                                </span></p>
                                <a href="#" class="theme-color fw-bold text-decoration-none">Learn More <span class="ml-auto arrow">→</span></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card parking-card">
                            <div class="card-body">
                                <h5 class="card-title">Premium Reserve</h5>
                                <p class="card-text">Reservation Only <span class="text-black"><span class="parking-card-icon"><i class="fas fa-walking"></i></span>
                                </span></p>
                                <a href="#" class="theme-color fw-bold text-decoration-none">Reserve Now <span class="ml-auto arrow">→</span></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card parking-card">
                            <div class="card-body">
                                <h5 class="card-title">61st and Peña</h5>
                                <p class="card-text"><span class="badge badge-theme-bg p-2 rounded-pill"><i class="fas fa-check-circle text-dark"></i><span class="mx-2 text-dark">Open</span> </span>  <span class="text-black d-none"><span class="parking-card-icon"><i class="fas fa-walking"></i></span>
                                </span></p>
                                <a href="#" class="theme-color fw-bold text-decoration-none">Learn More <span class="ml-auto arrow">→</span></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
        <p class="parkingNotes"><span class="fa-solid fa-mobile"></span> MIA’s free cell phone waiting lot (Final Approach) is located approximately three miles west of the Jeppesen Terminal. 
        <div>
            
        <a href="#" target="_blank" class="text-decoration-none text-light">Get Directions<span class="fa-solid fa-arrow-right-long"></span></a></p>
        </div>
    </div>
            </div>
        </div>


    </div>


    <div class="container-fluid p-md-5 p-4 my-5 ">
<div class="text-center mb-4">
            <h2 class="fw-normal fs-1">Some Helpful Amenities</h2>
           
        </div>
        <div class="row">
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                <div class="myimage3">
                    <img src="https://presspage-production-prd-content.s3.amazonaws.com/uploads/1911/0be11484-a95e-4388-b68b-882f0493380a/800_autowheelchairsmayorampspeakers3.jpeg?10000" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Wheelchair Requests &rarr;</a>
            </div>
                </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
                    <img  src="https://fastly.4sqi.net/img/general/1398x536/22749419_f33SrOPjnZbgvQA_jxXv_1HNTWug9IfYYRoh-c99ySQ.jpg " alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Wifi at Mia &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/uploaded/Airport-Facilities-and-Grounds-22-min.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Airport Facility &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTHP6BMcxa7thOiEyh5PggTPGVzKdF58bpzxA&s" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Lost and Found &rarr;</a>
                
                    </div>
                    </div>
            </div>
        </div>
        <div class="row mt-md-5">
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/transportation.jpg" alt="Transportation">
                    <a class="helpful-font" href="#">Transportation &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Miami-International-Airport-parking.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Parking Option &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://assets1.cbsnewsstatic.com/hub/i/r/2023/09/01/96bbac4b-c0dc-4d88-86e6-d82ad0c1e56f/thumbnail/1200x630g6/d6b07e98a0a6b0236d5ee5966f9e411c/gettyimages-1487118872.jpg?v=a23cb4bdf4fa7f3cb72e5118085577f9" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Travel Tips &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://res.cloudinary.com/mommy-nearest/image/upload/c_fill,h_450,w_800/jwjizomgqdpzuj5fxsnr.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Traveling with Children &rarr;</a>
                
                    </div>
                    </div>
            </div>
        </div>
    </div>

<div class="container-fluid px-md-5 px-4">';
 
    
 
echo'
        <div class="text-center">
            <h2>Transportation Options at MIA</h2>
            
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Options Available</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-solid fa-car icond "></i></span>  <a href="#" class="d-flex align-items-center justify-content-between my-3">Rental Car <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-users icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Car Share (Turo) <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-taxi icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Taxi <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-car-side icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3">Ride Share (Uber/Lyft) <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-map icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Interactive Map <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-bicycle icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Bike <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Business Information</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-briefcase icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Doing Business at MIA <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-users icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Business Diversity and Development <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-store icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Concessions Opportunities <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-info-circle icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> General Tenant Information <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-balance-scale icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> CEEA: Center of Equity and Excellence in Aviation <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-gavel icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Rules and Regulations <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    
 ';
  // Check if the page has child pages
    $childSql = "SELECT * FROM pages WHERE parent_id = ?";
    $childStmt = $conn->prepare($childSql);
    $childStmt->bind_param("i", $pageId);
    $childStmt->execute();
    $childResult = $childStmt->get_result();

    if ($childResult->num_rows > 0) {
        // Page has child pages, display the links to the child pages
       echo'<h3 class="text-center mt-5">Parking at Miami</h3>';
        echo "<div class='row mt-3'>";

        $childCount = 0;
        while ($childRow = $childResult->fetch_assoc()) {
            $childTitle = $childRow["title"];
            $childSlug = $childRow["slug"];
            $childImage = $childRow["image"]; // Assuming you have an image column for child pages
            $childDate =$childRow["date"];
            //  $name = $_SESSION['Name'];
            $childUrl = generatePageUrl($childRow["id"]);

            if ($childCount > 0 && $childCount % 3 == 0) {
                echo '</div><div class="row mt-4">';
            }

            echo "<div class='col-md-4 mb-4'>";
            echo "<a href='../$childUrl' class='text-decoration-none text-dark ' >";
            echo "<div class='card border-0 h-100' style='box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;'>";
            if ($childImage) {
                echo "<img src='$childImage' class='card-img-top rounded-0' style='
    max-height: 23em;
' alt='$childTitle'>";
            }
            echo "<div class='card-body position-absolute w-100 p-2' style='    bottom: 1px;
    background: #1606a7a9;'>";
            echo "<h5 class='card-title text-light text-center fw-bold'>$childTitle</h5>";
            // echo "<small class='badge theme-bg my-2'>$childDate</small>";
            // echo "<small class='badge theme-bg my-2'>$childName</small>";
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
    $childStmt->close();
  
    echo "</div>"; // Close title-container
}
// Parking End

// Car Rental Start
 elseif($design == "Design_16"){
    echo "<div class=' p-0'>";
    echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
    echo "<h1 class='ms-5'>$title</h1>";
    echo '</div>';
    if(isset($_SESSION['email'])){

        echo "<a href='../PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
 
        echo "<a href='../PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
      
    }
    echo "</div>";
   
    echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb bread-bck ps-5 ">';
    echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a href="../../" class="text-dark text-decoration-none" >Home</a></li>';
    $breadcrumbUrl = '/';
    $segmentCount = count(array_filter($urlSegments));
    foreach ($urlSegments as $index => $segment) {
        if (!empty($segment)) {
            $breadcrumbUrl .= '../' . $segment;
            $isLastSegment = ($index === $segmentCount - 1);
            $class = $isLastSegment ? 'text-dark fw-semibold' : 'text-dark';
            $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
            }
            }
            echo '</ol></nav>';
            echo'<div class="container-fluid p-5 margin-dine mt-md-0 mt-2">
        <div class="row">
              <div class="col-md-5">
            <div class="row">
                
                <div class="col p-3">
                    <div class="media3 clip-top-right mw-100" style="background-image:url(\'/Pic/car-rental/car_rental_1.jpg\');    background-size: cover;"></div>
                </div>
            </div>
        </div>
            <div class="col-md-7 mob-space ps-md-5 p-3 mt-md-4">
                 <h2>Car Rentals at Miami International Airport</h2>
<p class="fw-semibold">Convenient Car Rentals</p>
<p>Miami International Airport (MIA) offers a wide range of car rental services to make your travel experience seamless and convenient. Whether you\'re here for business or pleasure, you\'ll find the perfect vehicle to suit your needs.</p>


 <p class="fw-semibold mt-2">Convenient Car Rentals</p>
 <ul class="custom-bullet">
  <li>Alamo</li>
    <li>Avis</li>
    <li>Budget</li>
    <li>Dollar</li>
    <li>Enterprise</li>
    <li>Hertz</li>
    <li>National</li></ul>
            </div>
        </div>
    </div>
';



 echo'<div class="container-fluid px-md-5 px-4">';
 
    $content = $row["content"];
    // echo "<p class='mt-5'>$content</p>";

    // Check if the page has child pages

    echo'</div>

 <div class="container-fluid px-md-5 px-4 parking-back">
    <h2 class="text-center mb-5 text-light"><strong>Why <span style="color: #E55324;">Us</span></strong></h2>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body"> <div class="d-flex justify-content-center">
                    <div class="icon-container">
                        <i class="fas fa-car icon m-0 h-100 fs-3"></i>
                    </div>
                    </div>
                    <h5 class="heading">Diversity</h5>
                    <p>We guarantee that you will find the best car for your trip thanks to special offers from 800+ suppliers.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body"> <div class="d-flex justify-content-center">
                    <div class="icon-container">
                        <i class="fas fa-tags icon m-0 h-100 fs-4"></i>
                    </div>
                    </div>
                    <h5 class="heading">Value for money</h5>
                    <p>We are happy to offer our customers the best prices due to having access to discounts provided by rental companies.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body"> <div class="d-flex justify-content-center">
                    <div class="icon-container">
                        <i class="fas fa-trophy icon m-0 h-100 fs-4"></i>
                    </div>
                    </div>
                    <h5 class="heading">Experience & expertise</h5>
                    <p>With over a decade on the market, we are one of the most experienced and trusted experts in the car rental field.</p>
                </div>
            </div>
        </div>
    </div>
</div>
   
<div class="container-fluid px-md-5 px-4">';
 
    
 
echo'
        <div class="text-center my-5">
            <h2>Helpful Resources</h2>
            <div class="btn btn-size-group mt-3" role="group" aria-label="Accessibility Options">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Accessibility Statement</a>
            <a href="#" class="btn btn-size btn btn-outline-secondary mob-button">Accessibility Services</a>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Traveler Tips</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-globe icond "></i></span>  <a href="#" class="d-flex align-items-center justify-content-between my-3">International Travelers <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-passport icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Infrequent Traveler Tips <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-child icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Children and Families <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-wheelchair icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Wheelchair Requests <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-map icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Interactive Map <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-paw icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Traveling with Pets <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Business Information</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-briefcase icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Doing Business at MIA <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-users icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Business Diversity and Development <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-store icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Concessions Opportunities <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-info-circle icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> General Tenant Information <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-balance-scale icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> CEEA: Center of Equity and Excellence in Aviation <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-gavel icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Rules and Regulations <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    
 ';
  // Check if the page has child pages
    $childSql = "SELECT * FROM pages WHERE parent_id = ?";
    $childStmt = $conn->prepare($childSql);
    $childStmt->bind_param("i", $pageId);
    $childStmt->execute();
    $childResult = $childStmt->get_result();

    if ($childResult->num_rows > 0) {
        // Page has child pages, display the links to the child pages
       echo'<h3 class="text-center mt-5">Car Rental Services at Miami</h3>';
        echo "<div class='row mt-3'>";

        $childCount = 0;
        while ($childRow = $childResult->fetch_assoc()) {
            $childTitle = $childRow["title"];
            $childSlug = $childRow["slug"];
            $childImage = $childRow["image"]; // Assuming you have an image column for child pages
            $childDate =$childRow["date"];
            //  $name = $_SESSION['Name'];
            $childUrl = generatePageUrl($childRow["id"]);

            if ($childCount > 0 && $childCount % 4 == 0) {
                echo '</div><div class="row mt-4">';
            }

            echo "<div class='col-md-4 mb-4'>";
            echo "<a href='../$childUrl' class='text-decoration-none text-dark ' >";
            echo "<div class='card border-0 h-100' style='box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;'>";
            if ($childImage) {
                echo "<img src='$childImage' class='card-img-top rounded-0' style='
    max-height: 23em;
' alt='$childTitle'>";
            }
            echo "<div class='card-body position-absolute w-100 p-2' style='    bottom: 1px;
    background: #1606a7a9;'>";
            echo "<h5 class='card-title text-light text-center fw-bold'>$childTitle</h5>";
            // echo "<small class='badge theme-bg my-2'>$childDate</small>";
            // echo "<small class='badge theme-bg my-2'>$childName</small>";
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
    $childStmt->close();
  
    echo "</div>"; // Close title-container
}
// Car Rental End

// Airline directory Start
 elseif($design == "Design_17"){
    echo "<div class=' p-0'>";
    echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
    echo "<h1 class='ms-5'>$title</h1>";
    echo '</div>';
    if(isset($_SESSION['email'])){

        echo "<a href='../PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
 
        echo "<a href='../PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
      
    }
    echo "</div>";
   
    echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb bread-bck ps-5 ">';
    echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a href="../../" class="text-dark text-decoration-none" >Home</a></li>';
    $breadcrumbUrl = '/';
    $segmentCount = count(array_filter($urlSegments));
    foreach ($urlSegments as $index => $segment) {
        if (!empty($segment)) {
            $breadcrumbUrl .= '../' . $segment;
            $isLastSegment = ($index === $segmentCount - 1);
            $class = $isLastSegment ? 'text-dark fw-semibold' : 'text-dark';
            $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
            }
            }
            echo '</ol></nav>';
            echo'<div class="container-fluid p-5 margin-dine mt-md-0 mt-2">
        <div class="row">
              <div class="col-md-5">
            <div class="row">
                
                <div class="col p-3">
                    <div class="media3 clip-top-right mw-100" style="background-image:url(\'/Pic/airline-directory.jpg\');    background-size: cover;"></div>
                </div>
            </div>
        </div>
            <div class="col-md-7 mob-space ps-md-5 p-3 mt-md-5">
                 <h2>Airline Directory - Miami International Airport</h2>
<p>Welcome to the Airline Directory at Miami International Airport. Here, you will find a comprehensive list of all airlines operating at MIA, including their contact information and terminal locations. Whether you\'re planning a trip, need to make a reservation, or require assistance, this directory will help you quickly connect with your airline. For any further assistance, please contact our customer service team or visit the information desks located throughout the airport. Safe travels!</p>

<p>Our directory covers domestic, international, low-cost, and cargo airlines to ensure all your travel needs are met. From major carriers like American Airlines and Delta Air Lines to international options such as British Airways and Emirates, we\'ve got you covered. Check the terminal information and contact details to ensure a smooth and hassle-free journey. For the latest updates and more personalized assistance, our customer service team is always ready to help.</p>
            </div>
        </div>
    </div>
';



 echo'<div class="container-fluid px-md-5 px-4">';
 
    $content = $row["content"];
    // echo "<p class='mt-5'>$content</p>";

    // Check if the page has child pages

    echo'</div>

<div class="container-fluid px-md-5 px-4 parking-back">
    <h2 class="text-center mb-5 text-light"><strong>Airline Directory at <span style="color: #E55324;">Miami International Airport</span></strong></h2>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100 tip-card">
                <div class="card-body">
                    <div class="d-flex justify-content-center">
                        <div class="icon-container">
                            <i class="fas fa-plane-departure icon m-0 h-100 fs-3"></i>
                        </div>
                    </div>
                    <h5 class="heading">Domestic Airlines</h5>
                    <p>Miami International Airport hosts a variety of domestic airlines, providing convenient travel options across the United States. Some of the major carriers include:</p>
                    <ul>
                        <li><strong>American Airlines</strong> - North Terminal (Concourse D), 1-800-433-7300</li>
                        <li><strong>Delta Air Lines</strong> - South Terminal (Concourse H), 1-800-221-1212</li>
                        <li><strong>Southwest Airlines</strong> - Concourse H, 1-800-435-9792</li>
                        <li><strong>JetBlue Airways</strong> - South Terminal (Concourse H), 1-800-538-2583</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 tip-card">
                <div class="card-body">
                    <div class="d-flex justify-content-center">
                        <div class="icon-container">
                            <i class="fas fa-globe icon m-0 h-100 fs-3"></i>
                        </div>
                    </div>
                    <h5 class="heading">International Airlines</h5>
                    <p>Connect with the world through Miami International Airport\'s diverse range of international airlines. Key international carriers include:</p>
                    <ul>
                        <li><strong>British Airways</strong> - South Terminal (Concourse H), 1-800-247-9297</li>
                        <li><strong>Lufthansa</strong> - North Terminal (Concourse D), 1-800-645-3880</li>
                        <li><strong>Emirates</strong> - North Terminal (Concourse D), 1-800-777-3999</li>
                        <li><strong>Air Canada</strong> - South Terminal (Concourse J), 1-888-247-2262</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100 tip-card">
                <div class="card-body">
                    <div class="d-flex justify-content-center">
                        <div class="icon-container">
                            <i class="fas fa-boxes icon m-0 h-100 fs-3"></i>
                        </div>
                    </div>
                    <h5 class="heading">Low-Cost and Cargo Airlines</h5>
                    <p>Miami International Airport also serves low-cost carriers and cargo airlines to meet all travel and logistics needs:</p>
                    <h6>Low-Cost Carriers:</h6>
                    <ul>
                        <li><strong>Spirit Airlines</strong> - South Terminal (Concourse H), 1-801-401-2222</li>
                        <li><strong>Frontier Airlines</strong> - South Terminal (Concourse J), 1-801-401-9000</li>
                    </ul>
                    <h6>Cargo Airlines:</h6>
                    <ul>
                        <li><strong>FedEx Express</strong> - North Cargo Area, 1-800-463-3339</li>
                        <li><strong>UPS Airlines</strong> - South Cargo Area, 1-800-742-5877</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-md-5 px-4">';

echo '<div class="my-5" style="overflow: auto; height: 600px;">';

include 'airline_data.php';
echo '</div>';

echo'
        <div class="text-center my-5">
            <h2>Helpful Resources</h2>
            <div class="btn btn-size-group mt-3" role="group" aria-label="Accessibility Options">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Accessibility Statement</a>
            <a href="#" class="btn btn-size btn btn-outline-secondary mob-button">Accessibility Services</a>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Traveler Tips</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-globe icond "></i></span>  <a href="#" class="d-flex align-items-center justify-content-between my-3">International Travelers <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-passport icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Infrequent Traveler Tips <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-child icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Children and Families <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-wheelchair icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Wheelchair Requests <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-map icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Interactive Map <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-paw icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Traveling with Pets <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Business Information</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-briefcase icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Doing Business at MIA <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-users icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Business Diversity and Development <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-store icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Concessions Opportunities <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-info-circle icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> General Tenant Information <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-balance-scale icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> CEEA: Center of Equity and Excellence in Aviation <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-gavel icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Rules and Regulations <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    
 ';
  // Check if the page has child pages
    $childSql = "SELECT * FROM pages WHERE parent_id = ?";
    $childStmt = $conn->prepare($childSql);
    $childStmt->bind_param("i", $pageId);
    $childStmt->execute();
    $childResult = $childStmt->get_result();

    if ($childResult->num_rows > 0) {
        // Page has child pages, display the links to the child pages
       echo'<h3 class="text-center mt-5">Car Rental Services at Miami</h3>';
        echo "<div class='row mt-3'>";

        $childCount = 0;
        while ($childRow = $childResult->fetch_assoc()) {
            $childTitle = $childRow["title"];
            $childSlug = $childRow["slug"];
            $childImage = $childRow["image"]; // Assuming you have an image column for child pages
            $childDate =$childRow["date"];
            //  $name = $_SESSION['Name'];
            $childUrl = generatePageUrl($childRow["id"]);

            if ($childCount > 0 && $childCount % 4 == 0) {
                echo '</div><div class="row mt-4">';
            }

            echo "<div class='col-md-4 mb-4'>";
            echo "<a href='../$childUrl' class='text-decoration-none text-dark ' >";
            echo "<div class='card border-0 h-100' style='box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;'>";
            if ($childImage) {
                echo "<img src='$childImage' class='card-img-top rounded-0' style='
    max-height: 23em;
' alt='$childTitle'>";
            }
            echo "<div class='card-body position-absolute w-100 p-2' style='    bottom: 1px;
    background: #1606a7a9;'>";
            echo "<h5 class='card-title text-light text-center fw-bold'>$childTitle</h5>";
            // echo "<small class='badge theme-bg my-2'>$childDate</small>";
            // echo "<small class='badge theme-bg my-2'>$childName</small>";
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
    $childStmt->close();
  
    echo "</div>"; // Close title-container
}
// Airline Directory End
// Shop and dine start
 elseif($design == "Design_18"){
    echo "<div class=' p-0'>";
    echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
    echo "<h1 class='ms-5'>$title</h1>";
    echo '</div>';
    if(isset($_SESSION['email'])){

        echo "<a href='../PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
 
        echo "<a href='../PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
      
    }
    echo "</div>";
   
    echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb bread-bck ps-5 ">';
    echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a href="../../" class="text-dark text-decoration-none" >Home</a></li>';
    $breadcrumbUrl = '/';
    $segmentCount = count(array_filter($urlSegments));
    foreach ($urlSegments as $index => $segment) {
        if (!empty($segment)) {
            $breadcrumbUrl .= '../' . $segment;
            $isLastSegment = ($index === $segmentCount - 1);
            $class = $isLastSegment ? 'text-dark fw-semibold' : 'text-dark';
            $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
            }
            }
            echo '</ol></nav>';
            echo'<div class="container-fluid p-5 margin-dine mt-md-0 mt-2">
        <div class="row">
              <div class="col-md-5">
            <div class="row">
                
                <div class="col p-3">
                    <div class="media3 clip-top-right mw-100" style="background-image:url(\'Pic/shop and dine/dine 1.jpg\');    background-size: cover;"></div>
                </div>
            </div>
        </div>
            <div class="col-md-7 mob-space ps-md-5 p-3">
                 <h2>Shopping at Miami International Airport</h2>
        <p class="fw-semibold">Miami International Airport (MIA) offers a world-class shopping experience that caters to all your needs and preferences. Whether you\'re looking for luxury brands, last-minute souvenirs, or everyday essentials, MIA has it all. Explore a diverse range of shops conveniently located throughout the airport, providing you with a delightful and hassle-free shopping experience.</p>
        <ul class="custom-bullet">
          <li><strong>Duty-Free Shopping:</strong> Enjoy tax-free shopping on perfumes, cosmetics, liquor, tobacco, and more. Find great prices on your favorite luxury items.</li>
  <li><strong>Fashion & Accessories:</strong> Explore the latest fashion trends from international and local brands. Shop for clothing, accessories, footwear, and jewelry for every style.</li>
  <li><strong>Electronics & Gadgets:</strong> Stay connected with the latest electronics and gadgets, including smartphones, tablets, headphones, and travel accessories.</li>
  <li><strong>Souvenirs & Gifts:</strong> Bring home a piece of Miami with unique souvenirs and gifts, including local art, crafts, and memorabilia.</li>
  <li><strong>Books & Magazines:</strong> Relax with a good book or catch up on the latest magazines. MIA\'s bookstores offer a wide range of reading materials.</li>
  <li><strong>Convenience Stores:</strong> Forgot something? MIA\'s convenience stores have travel essentials, snacks, beverages, and personal care items for a comfortable journey.</li>
        </ul>

            </div>
        </div>
    </div>
';
echo'<div class="container-fluid p-md-5 p-4 parking-back my-5">
    <div class="text-center mb-5">
        <h2 class="fw-normal fs-1 text-light">Shops at Miami International Airport</h2>
    </div>
    <div class="row">
        <!-- Card 1 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/305-pizza.jpg" class="card-img-top img-fluid object-fit-cover" alt="Air Bar">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-mug-hot"></i> 305 Pizza</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> North Terminal,Concourse D</p>
                    <p class="card-text text-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> Open until 8:00 pm</p>
                </div>
            </div>
        </div>
        <!-- Card 2 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/Bacardi Mojito Bar.jpg " class="card-img-top img-fluid object-fit-cover" alt="Gateway Bake Shop">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-bread-slice"></i> Bacardi Mojito Bar</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> North Terminal,Concourse D</p>
                    <p class="card-text text-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> Open until 9:00 pm</p>
                </div>
            </div>
        </div>
        <!-- Card 3 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/Corona Beach House.jpg" class="card-img-top img-fluid object-fit-cover" alt="Metro News & Gifts">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-newspaper"></i> Corona Beach House</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> North Terminal,Concourse D</p>
                    <p class="card-text text-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> Open until 9:30 pm</p>
                </div>
            </div>
        </div>
        <!-- Card 4 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/Fig & Fennel.jpg" class="card-img-top img-fluid object-fit-cover" alt="Metro News & Gifts (Temporarily Closed)">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-newspaper"></i> Famous Famiglia</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> South Terminal, Concourse H-J Connector</p>
                    <p class="card-text text-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> Open until 9:00 pm</p>
                </div>
            </div>
        </div>
        <!-- Card 5 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://shopmiami.wpenginepowered.com/wp-content/uploads/2019/04/Fig-Fennel_Terminal-D-1048x618-1-330x195.jpg" class="card-img-top img-fluid object-fit-cover" alt="New York Deli">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-utensils"></i> Fig & Fennel</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> North Terminal, Concourse D</p>
                    <p class="card-text text-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> Open until 9:00 pm</p>
                </div>
            </div>
        </div>
        <!-- Card 6 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/Gilbert’s Food Bar (Pre-Security).jpg" class="card-img-top img-fluid object-fit-cover" alt="Restrooms">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-restroom"></i> Gilbert’s Food Bar (Pre-Security)</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> South Terminal- Door 26 - 2nd Level Departures					 </p>
                    <p class="card-text text-center"> <span class="fa-solid fa-clock" aria-hidden="true"></span> Open until 9:30 pm</p>
                </div>
            </div>
        </div>
        <!-- Card 7 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/La Pausa.jpg" class="card-img-top img-fluid object-fit-cover" alt="Restrooms">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-restroom"></i> La Pausa</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> South Terminal, Concourse H-J Connector</p>
                    <p class="card-text text-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> Open until 8:00 pm</p>
                </div>
            </div>
        </div>
        <!-- Card 8 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/Nathan’s Famous.jpg" class="card-img-top img-fluid object-fit-cover" alt="Tim Hortons">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-coffee"></i> Nathan’s Famous</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> South Terminal, Concourse H</p>
                    <p class="card-text text-center"> <span class="fa-solid fa-clock" aria-hidden="true"></span> Open until 4:00 pm</p>
                </div>
            </div>
        </div>
    </div>
</div>
';

 echo'<div class="container-fluid px-md-5 px-4">';
    $content = $row["content"];
    // echo "<p class='mt-5'>$content</p>";

    // Check if the page has child pages
    $childSql = "SELECT * FROM pages WHERE parent_id = ?";
    $childStmt = $conn->prepare($childSql);
    $childStmt->bind_param("i", $pageId);
    $childStmt->execute();
    $childResult = $childStmt->get_result();

    if ($childResult->num_rows > 0) {
        // Page has child pages, display the links to the child pages
        echo'<div class="text-center mb-4">
            <h2 class="fw-normal fs-1">Other Shops at Miami</h2>
        </div>';
        echo "<div class='row'>";

        $childCount = 0;
        while ($childRow = $childResult->fetch_assoc()) {
            $childTitle = $childRow["title"];
            $childSlug = $childRow["slug"];
            $childImage = $childRow["image"]; // Assuming you have an image column for child pages
            $childDate =$childRow["date"];
            //  $name = $_SESSION['Name'];
            $childUrl = generatePageUrl($childRow["id"]);

            if ($childCount > 0 && $childCount % 4 == 0) {
                echo '</div><div class="row mt-4">';
            }

            echo "<div class='col-md-3 mb-4'>";
            echo "<a href='../$childUrl' class='text-decoration-none text-dark ' >";
            echo "<div class='card border-0 h-100' style='box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;'>";
          if ($childImage) {
                echo "<img src='$childImage' class='card-img-top rounded-0' style='
    max-height: 23em;
' alt='$childTitle'>";
            }
            echo "<div class='card-body position-absolute w-100 p-2' style='    bottom: 1px;
    background: #1606a7a9;'>";
            echo "<h5 class='card-title text-light text-center fw-bold'>$childTitle</h5>";
            // echo "<small class='badge theme-bg my-2'>$childDate</small>";
            // echo "<small class='badge theme-bg my-2'>$childName</small>";
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
    $childStmt->close();
    echo'</div>
   


    <div class="container-fluid p-md-5 p-4 mt-5">
<div class="text-center mb-4">
            <h2 class="fw-normal fs-1">Some Helpful Amenities</h2>
           
        </div>
        <div class="row">
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                <div class="myimage3">
                    <img src="https://miamiairport-mia.com/Pic/Card/s1.jpeg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Wheelchair Requests &rarr;</a>
            </div>
                </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
                    <img  src="https://miamiairport-mia.com/Pic/Card/s2.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Wifi at Mia &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Card/s3.webp" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Airport Facility &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Card/s4.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Lost and Found &rarr;</a>
                
                    </div>
                    </div>
            </div>
        </div>
        <div class="row mt-md-5">
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Card/s5.webp" alt="Transportation">
                    <a class="helpful-font" href="#">Transportation &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Card/s6.webp" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Parking Option &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Card/s7.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Travel Tips &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Card/s8.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Traveling with Children &rarr;</a>
                
                    </div>
                    </div>
            </div>
        </div>
    </div>

<div class="container-fluid px-md-5 px-4">';
  
       
echo'
        <div class="text-center">
            <h2>Helpful Resources</h2>
            <div class="btn btn-size-group mt-3" role="group" aria-label="Accessibility Options">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Accessibility Statement</a>
            <a href="#" class="btn btn-size btn btn-outline-secondary mob-button">Accessibility Services</a>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Traveler Tips</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-globe icond "></i></span>  <a href="#" class="d-flex align-items-center justify-content-between my-3">International Travelers <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-passport icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Infrequent Traveler Tips <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-child icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Children and Families <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-wheelchair icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Wheelchair Requests <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-map icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Interactive Map <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-paw icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Traveling with Pets <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Business Information</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-briefcase icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Doing Business at MIA <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-users icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Business Diversity and Development <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-store icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Concessions Opportunities <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-info-circle icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> General Tenant Information <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-balance-scale icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> CEEA: Center of Equity and Excellence in Aviation <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-gavel icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Rules and Regulations <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    
 ';
 
  
    echo "</div>"; // Close title-container
}

// Shop and dine end

// Transportation Start
 elseif($design == "Design_19"){
    echo "<div class=' p-0'>";
    echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
    echo "<h1 class='ms-5'>$title</h1>";
    echo '</div>';
    if(isset($_SESSION['email'])){

        echo "<a href='../PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
 
        echo "<a href='../PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
      
    }
    echo "</div>";
   
    echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb bread-bck ps-5 ">';
    echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a href="../../" class="text-dark text-decoration-none" >Home</a></li>';
    $breadcrumbUrl = '/';
    $segmentCount = count(array_filter($urlSegments));
    foreach ($urlSegments as $index => $segment) {
        if (!empty($segment)) {
            $breadcrumbUrl .= '../' . $segment;
            $isLastSegment = ($index === $segmentCount - 1);
            $class = $isLastSegment ? 'text-dark fw-semibold' : 'text-dark';
            $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="../' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
            }
            }
            echo '</ol></nav>';
            echo'<div class="container-fluid p-5 margin-dine mt-md-0 mt-2">
        <div class="row">
              <div class="col-md-5">
            <div class="row">
                
                <div class="col p-3">
                    <div class="media3 clip-top-right mw-100" style="background-image:url(\'/Pic/transportation/miami-airport-transportation.jpg\');    background-size: cover;"></div>
                </div>
            </div>
        </div>
            <div class="col-md-7 mob-space ps-md-5 p-3 mt-md-5">
                 <h2>Discover Miami’s Diverse Transportation Systems</h2>
<p>Miami offers a wide range of transport options that make it convenient for travelers to explore the city and reach any location directly from the airport. The Miami-Dade Transit operates public buses that serve the entire city as well as surrounding areas. The easy & quick Metrorail system is also available to travel throughout Miami and connect to the cities main neighborhoods and attractions. When exploring downtown, the free Metromover service provides convenient transportation within the city center, eliminating parking concerns.</p>
<p>For flexibility and convenience of travelers, taxis and ride-sharing services like Uber and Lyft are always available. Bike sharing in Miami or renting an electric scooter are great options for individuals who prefer environment-friendly and active forms of transportation. These vehicles are ideal for quick trips and sightseeing on your schedule. Miami cost-effective, dependable, and efficient transportation system guarantees that you will arrive at your destination quickly and without difficulty.</p>


            </div>
        </div>
    </div>
';



 echo'<div class="container-fluid px-md-5 px-4">';
    $content = $row["content"];
    // echo "<p class='mt-5'>$content</p>";

    // Check if the page has child pages

    echo'</div>
 <div class="container-fluid parking-back mt-5 p-5 margin-dine">
        <div class="text-light d-flex justify-content-between">
            <h2>Transport Services Provided by MIA</h2>
            
        </div>

        <div class="row mt-5 px-md-0 px-2">
            <!-- Parking Lots Section -->
            <div class="col-md-12">
                
                  <div class="row">
        <!-- Card 1: Metrorail -->
        <div class="col-md-3 my-md-0 my-4">
            <div class="card h-100 ">
                <img src="/Pic/transportation/mover.jpg" class="card-img-top" style="height:13em;object-fit:cover;" alt="Metrorail">
                <div class="card-body">
                    <h5 class="card-title">Metrorail</h5>
                    <p class="card-text">MIA is connected to other locations and downtown Miami via the Orange Line of Metrorail. Train service throughout the city is inexpensive and convenient, departing every 15 minutes. There are also connections to the Tri-Rail, which travels even further north.</p>
                </div>
            </div>
        </div>
        <!-- Card 2: Tri-Rail -->
        <div class="col-md-3 my-md-0 my-4">
            <div class="card h-100 ">
                <img src="/Pic/transportation/tri-rail.jpg" class="card-img-top" style="height:13em;object-fit:cover;" alt="Tri-Rail">
                <div class="card-body">
                    <h5 class="card-title">Tri-Rail</h5>
                    <p class="card-text">The majority of South Florida is served by the Tri-Rail, which travels from MIA to West Palm Beach. With frequent daily service, it is an affordable way to get to places like Fort Lauderdale and Boca Raton.</p>
                </div>
            </div>
        </div>
        <!-- Card 3: Metrobus -->
        <div class="col-md-3 my-md-0 my-4">
            <div class="card h-100 ">
                <img src="/Pic/transportation/public-transport.jpg" class="card-img-top" style="height:13em;object-fit:cover;" alt="Metrobus">
                <div class="card-body">
                    <h5 class="card-title">Metrobus</h5>
                    <p class="card-text">From MIA, Miami-Dade Transit runs several bus routes. These buses provide an affordable method of exploring the city because they run throughout the entire Miami-Dade County region and have numerous direct connections to some of the cities most well-known neighborhoods and tourist attractions.</p>
                </div>
            </div>
        </div>
        <!-- Card 4: Airport Shuttle Services -->
        <div class="col-md-3 my-md-0 my-4">
            <div class="card h-100 ">
                <img src="/Pic/transportation/54.jpg" class="card-img-top" style="height:13em;object-fit:cover;" alt="Airport Shuttle Services">
                <div class="card-body">
                    <h5 class="card-title">Airport Shuttle Services</h5>
                    <p class="card-text">Shuttle services are shared and run from MIA to hotels and other major locations in Miami. These shuttles do offer practical door-to-door service, making them ideal for passengers with heavy bags or those heading directly to their accommodations.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <!-- Card 5: Taxi Services -->
        <div class="col-md-3 my-md-0 my-4">
            <div class="card h-100 ">
                <img src="/Pic/transportation/taxi.jpg" class="card-img-top" style="height:13em;object-fit:cover;" alt="Taxi Services">
                <div class="card-body">
                    <h5 class="card-title">Taxi Services</h5>
                    <p class="card-text">Taxis are readily available all over MIA and offer a quick and discreet means of getting to any location in Miami. For those who are traveling in groups or are in a hurry after a long flight, this method provides comfort and convenience.</p>
                </div>
            </div>
        </div>
        <!-- Card 6: Ridesharing Apps -->
        <div class="col-md-3 my-md-0 my-4">
            <div class="card h-100 ">
                <img src="/Pic/transportation/shuttle.jpg" class="card-img-top" style="height:13em;object-fit:cover;" alt="Ridesharing Apps">
                <div class="card-body">
                    <h5 class="card-title">Ridesharing Apps</h5>
                    <p class="card-text">At MIA, Uber and Lyft are accessible. Compared to taxis, it provides flexible and frequently less expensive options. Using mobile apps, passengers can conveniently book a ride. It is simple to locate pickup spots close to the terminal.</p>
                </div>
            </div>
        </div>
        <!-- Card 7: Car Rental -->
        <div class="col-md-3 my-md-0 my-4">
            <div class="card h-100 ">
                <img src="/Pic/transportation/rental.jpg" class="card-img-top" style="height:13em;object-fit:cover;" alt="Car Rental">
                <div class="card-body">
                    <h5 class="card-title">Car Rental</h5>
                    <p class="card-text">Travelers can explore Miami and its surroundings at their own pace after clearing customs, as there are multiple car rental companies located at MIA. For tourists who plan to visit several different places, this is a great option.</p>
                </div>
            </div>
        </div>
        <!-- Card 8: Brightline -->
        <div class="col-md-3 my-md-0 my-4">
            <div class="card h-100 ">
                <img src="/Pic/transportation/brightline.jpeg" class="card-img-top" style="height:13em;object-fit:cover;" alt="Brightline">
                <div class="card-body">
                    <h5 class="card-title">Brightline</h5>
                    <p class="card-text">With plans to expand to Orlando, Brightline currently provides high-speed rail service to and from Miami, Fort Lauderdale, and West Palm Beach. The Brightline station is conveniently close to the Tri-Rail stop, or it can be quickly reached from MIA by taxi or rideshare.</p>
                </div>
            </div>
        </div>
    </div>
                
            </div>
        </div>


    </div>


    <div class="container-fluid p-md-5 p-4 my-5 ">
<div class="text-center mb-4">
            <h2 class="fw-normal fs-1">Some Helpful Amenities</h2>
           
        </div>
        <div class="row">
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                <div class="myimage3">
                    <img src="https://presspage-production-prd-content.s3.amazonaws.com/uploads/1911/0be11484-a95e-4388-b68b-882f0493380a/800_autowheelchairsmayorampspeakers3.jpeg?10000" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Wheelchair Requests &rarr;</a>
            </div>
                </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
                    <img  src="https://fastly.4sqi.net/img/general/1398x536/22749419_f33SrOPjnZbgvQA_jxXv_1HNTWug9IfYYRoh-c99ySQ.jpg " alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Wifi at Mia &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/uploaded/Airport-Facilities-and-Grounds-22-min.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Airport Facility &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTHP6BMcxa7thOiEyh5PggTPGVzKdF58bpzxA&s" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Lost and Found &rarr;</a>
                
                    </div>
                    </div>
            </div>
        </div>
        <div class="row mt-md-5">
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/transportation.jpg" alt="Transportation">
                    <a class="helpful-font" href="#">Transportation &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Miami-International-Airport-parking.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Parking Option &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://assets1.cbsnewsstatic.com/hub/i/r/2023/09/01/96bbac4b-c0dc-4d88-86e6-d82ad0c1e56f/thumbnail/1200x630g6/d6b07e98a0a6b0236d5ee5966f9e411c/gettyimages-1487118872.jpg?v=a23cb4bdf4fa7f3cb72e5118085577f9" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Travel Tips &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://res.cloudinary.com/mommy-nearest/image/upload/c_fill,h_450,w_800/jwjizomgqdpzuj5fxsnr.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Traveling with Children &rarr;</a>
                
                    </div>
                    </div>
            </div>
        </div>
    </div>

<div class="container-fluid px-md-5 px-4">';
 
    
 
echo'
        <div class="text-center">
            <h2>Transportation Options at MIA</h2>
            
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Options Available</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-solid fa-car icond "></i></span>  <a href="#" class="d-flex align-items-center justify-content-between my-3">Rental Car <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-users icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Car Share (Turo) <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-taxi icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Taxi <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-car-side icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3">Ride Share (Uber/Lyft) <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-map icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Interactive Map <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-bicycle icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Bike <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Business Information</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-briefcase icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Doing Business at MIA <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-users icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Business Diversity and Development <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-store icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Concessions Opportunities <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-info-circle icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> General Tenant Information <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-balance-scale icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> CEEA: Center of Equity and Excellence in Aviation <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-gavel icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Rules and Regulations <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    
 ';

// Assuming you get the current page number from the URL
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 12;
$offset = ($currentPage - 1) * $itemsPerPage;

// Fetch the total number of child pages
$totalCountSql = "SELECT COUNT(*) as total FROM pages WHERE parent_id = ?";
$totalCountStmt = $conn->prepare($totalCountSql);
$totalCountStmt->bind_param("i", $pageId);
$totalCountStmt->execute();
$totalCountResult = $totalCountStmt->get_result();
$totalCountRow = $totalCountResult->fetch_assoc();
$totalItems = $totalCountRow['total'];
$totalCountStmt->close();

$totalPages = ceil($totalItems / $itemsPerPage);

// Fetch the child pages for the current page
$childSql = "SELECT * FROM pages WHERE parent_id = ? LIMIT ? OFFSET ?";
$childStmt = $conn->prepare($childSql);
$childStmt->bind_param("iii", $pageId, $itemsPerPage, $offset);
$childStmt->execute();
$childResult = $childStmt->get_result();

if ($childResult->num_rows > 0) {
    // Page has child pages, display the links to the child pages
    echo '<h3 id="child-section" class="text-center mt-5">Transportation Services From Miami International Airport</h3>';
    echo "<div class='row mt-3'>";

    $childCount = 0;
    while ($childRow = $childResult->fetch_assoc()) {
        $childTitle = $childRow["title"];
        $childSlug = $childRow["slug"];
        $childImage = $childRow["image"]; // Assuming you have an image column for child pages
        $childDate = $childRow["date"];
        $childUrl = generatePageUrl($childRow["id"]);

        if ($childCount > 0 && $childCount % 4 == 0) {
            echo '</div><div class="row mt-4">';
        }

        echo "<div class='col-md-3 mb-4'>";
        echo "<a href='../$childUrl' class='text-decoration-none text-dark'>";
        echo "<div class='card border-0 h-100' style='box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;'>";
        if ($childImage) {
            echo "<img src='$childImage' class='card-img-top rounded-0' style='height: 17em; object-fit: cover;' alt='$childTitle'>";
        }
        echo "<div class='card-body position-absolute w-100 p-2' style='bottom: 1px; background: #1606a7a9;'>";
        echo "<h5 class='card-title text-light text-center fw-bold'>$childTitle</h5>";
        echo "</a>";
        echo "</div></div></div>";

        $childCount++;
    }

    echo '</div>'; // Close the last row

    // Pagination
    if ($totalPages > 1) {
        echo '<nav><ul class="pagination justify-content-center mt-4">';
        for ($i = 1; $i <= $totalPages; $i++) {
            $activeClass = ($i == $currentPage) ? ' active' : '';
            echo "<li class='page-item$activeClass'><a class='page-link' href='?url=$url&page=$i#child-section'>$i</a></li>";
        }
        echo '</ul></nav>';
    }
}
$childStmt->close();


  
    echo "</div>"; // Close title-container
}
// transportation End

//search start
elseif($design == "Design_search"){
    echo "<div class=' p-0'>";
    echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
    echo "<h1 class='ms-5'>$title</h1>";
    echo '</div>';
    if(isset($_SESSION['email'])){

        echo "<a href='../PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
 
        echo "<a href='../PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
      
    }
    echo "</div>";
  


 echo'<div class="container-fluid px-md-5 px-4">';
$search_query = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

// Initialize results array
$results = [];



// Perform the search if the query is not empty
if (!empty($search_query)) {
    $sql = "SELECT * FROM pages WHERE title LIKE '%$search_query%' OR content LIKE '%$search_query%'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Get the full slug with parent if available
            $slug = $row["slug"];
            if (!is_null($row["parent_id"])) {
                $parentSlug = getParentSlug($row["parent_id"], $conn);
                if ($parentSlug) {
                    $slug = $parentSlug . '/' . $row["slug"];
                }
            }
            $row['full_slug'] = $slug;
            $results[] = $row;
        }
    }
}

echo '<div class="my-5">
        <h1>Search Results for "' . htmlspecialchars($search_query) . '"</h1>';
if (!empty($results)) {
    echo '<div class="row row-cols-1 row-cols-md-3 g-4">';
    foreach ($results as $result) {
        echo '<div class="col">
                <div class="card h-100">
                    <img src="' . htmlspecialchars($result['image']) . '" class="card-img-top" style="height: 18em; object-fit: cover;" alt="' . htmlspecialchars($result['title']) . '">
                    <div class="card-body">
                        <h5 class="card-title">' . htmlspecialchars($result['title']) . '</h5>
                        <a href="' . htmlspecialchars($result['full_slug']) . '" class="btn theme-bg text-white w-100">Read More</a>
                    </div>
                </div>
              </div>';
    }
    echo '</div>';
} else {
    echo '<p>No results found for "' . htmlspecialchars($search_query) . '"</p>';
}
echo '</div>';


// Assuming you get the current page number from the URL
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 12;
$offset = ($currentPage - 1) * $itemsPerPage;

// Fetch the total number of child pages
$totalCountSql = "SELECT COUNT(*) as total FROM pages WHERE parent_id = ?";
$totalCountStmt = $conn->prepare($totalCountSql);
$totalCountStmt->bind_param("i", $pageId);
$totalCountStmt->execute();
$totalCountResult = $totalCountStmt->get_result();
$totalCountRow = $totalCountResult->fetch_assoc();
$totalItems = $totalCountRow['total'];
$totalCountStmt->close();

$totalPages = ceil($totalItems / $itemsPerPage);

// Fetch the child pages for the current page
$childSql = "SELECT * FROM pages WHERE parent_id = ? LIMIT ? OFFSET ?";
$childStmt = $conn->prepare($childSql);
$childStmt->bind_param("iii", $pageId, $itemsPerPage, $offset);
$childStmt->execute();
$childResult = $childStmt->get_result();

if ($childResult->num_rows > 0) {
    // Page has child pages, display the links to the child pages
    echo '<h3 id="child-section" class="text-center mt-5">Other Content</h3>';
    echo "<div class='row mt-3'>";

    $childCount = 0;
    while ($childRow = $childResult->fetch_assoc()) {
        $childTitle = $childRow["title"];
        $childSlug = $childRow["slug"];
        $childImage = $childRow["image"]; // Assuming you have an image column for child pages
        $childDate = $childRow["date"];
        $childUrl = generatePageUrl($childRow["id"]);

        if ($childCount > 0 && $childCount % 4 == 0) {
            echo '</div><div class="row mt-4">';
        }

       echo '
<div class="col-md-3 mb-4">
    <a href="' . htmlspecialchars($result['slug']) . '" class="text-decoration-none text-dark">
        <div class="card border-0 h-100" style="box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;">
            <img src="' . htmlspecialchars($result['image']) . '" class="card-img-top rounded-0" style="height: 17em; object-fit: cover;" alt="' . htmlspecialchars($result['title']) . '">
            <div class="card-body position-absolute w-100 p-2" style="bottom: 1px; background: #1606a7a9;">
                <h5 class="card-title text-light text-center fw-bold">' . htmlspecialchars($result['title']) . '</h5>
            </div>
        </div>
    </a>
</div>
';

        $childCount++;
    }

    echo '</div>'; // Close the last row

    // Pagination
    if ($totalPages > 1) {
        echo '<nav><ul class="pagination justify-content-center mt-4">';
        for ($i = 1; $i <= $totalPages; $i++) {
            $activeClass = ($i == $currentPage) ? ' active' : '';
            echo "<li class='page-item$activeClass'><a class='page-link' href='?url=$url&page=$i#child-section'>$i</a></li>";
        }
        echo '</ul></nav>';
    }
}
$childStmt->close();


  
    echo "</div>"; // Close title-container
}
// Search End
   
//nopage start
elseif($design == "Design_404"){
    echo "<div class=' p-0'>";
    echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
    echo "<h1 class='ms-5 text-center'style='
    text-shadow: 4px 4px 6px rgba(0, 0, 0, 1.3);
    font-size: 3.5em;
'>$title</h1>";
    echo '</div>';
    if(isset($_SESSION['email'])){

        echo "<a href='../PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
 
        echo "<a href='../PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
      
    }
    echo "</div>";
  


 echo'<div class="container-fluid px-md-5 px-4 my-5">';

 
echo'
        <div class="text-center">
            <h2 class="fw-normal fs-1">Top Searches</h2>
            
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Options Available</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-solid fa-car icond "></i></span>  <a href="#" class="d-flex align-items-center justify-content-between my-3">Rental Car <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-users icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Car Share (Turo) <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-taxi icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Taxi <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-car-side icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3">Ride Share (Uber/Lyft) <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-map icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Interactive Map <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-bicycle icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Bike <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Business Information</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-briefcase icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Doing Business at MIA <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-users icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Business Diversity and Development <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-store icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Concessions Opportunities <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-info-circle icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> General Tenant Information <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-balance-scale icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> CEEA: Center of Equity and Excellence in Aviation <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-gavel icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Rules and Regulations <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    
 ';

// Assuming you get the current page number from the URL
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 12;
$offset = ($currentPage - 1) * $itemsPerPage;

// Fetch the total number of child pages
$totalCountSql = "SELECT COUNT(*) as total FROM pages WHERE parent_id = ?";
$totalCountStmt = $conn->prepare($totalCountSql);
$totalCountStmt->bind_param("i", $pageId);
$totalCountStmt->execute();
$totalCountResult = $totalCountStmt->get_result();
$totalCountRow = $totalCountResult->fetch_assoc();
$totalItems = $totalCountRow['total'];
$totalCountStmt->close();

$totalPages = ceil($totalItems / $itemsPerPage);

// Fetch the child pages for the current page
$childSql = "SELECT * FROM pages WHERE parent_id = ? LIMIT ? OFFSET ?";
$childStmt = $conn->prepare($childSql);
$childStmt->bind_param("iii", $pageId, $itemsPerPage, $offset);
$childStmt->execute();
$childResult = $childStmt->get_result();

if ($childResult->num_rows > 0) {
    // Page has child pages, display the links to the child pages
    echo '<h3 id="child-section" class="text-center mt-5">Other Content</h3>';
    echo "<div class='row mt-3'>";

    $childCount = 0;
    while ($childRow = $childResult->fetch_assoc()) {
        $childTitle = $childRow["title"];
        $childSlug = $childRow["slug"];
        $childImage = $childRow["image"]; // Assuming you have an image column for child pages
        $childDate = $childRow["date"];
        $childUrl = generatePageUrl($childRow["id"]);

        if ($childCount > 0 && $childCount % 4 == 0) {
            echo '</div><div class="row mt-4">';
        }

       echo '
<div class="col-md-3 mb-4">
    <a href="' . htmlspecialchars($result['slug']) . '" class="text-decoration-none text-dark">
        <div class="card border-0 h-100" style="box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;">
            <img src="' . htmlspecialchars($result['image']) . '" class="card-img-top rounded-0" style="height: 17em; object-fit: cover;" alt="' . htmlspecialchars($result['title']) . '">
            <div class="card-body position-absolute w-100 p-2" style="bottom: 1px; background: #1606a7a9;">
                <h5 class="card-title text-light text-center fw-bold">' . htmlspecialchars($result['title']) . '</h5>
            </div>
        </div>
    </a>
</div>
';

        $childCount++;
    }

    echo '</div>'; // Close the last row

    // Pagination
    if ($totalPages > 1) {
        echo '<nav><ul class="pagination justify-content-center mt-4">';
        for ($i = 1; $i <= $totalPages; $i++) {
            $activeClass = ($i == $currentPage) ? ' active' : '';
            echo "<li class='page-item$activeClass'><a class='page-link' href='?url=$url&page=$i#child-section'>$i</a></li>";
        }
        echo '</ul></nav>';
    }
}
$childStmt->close();


  
    echo "</div>"; // Close title-container
}
// nopage End

// child start
   elseif($design == "Design_20"){
        $image = $row["image"];
        echo "<div class=' p-0'>";
            echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
            echo "<h1 class='ms-md-5 ms-4 'style='margin-bottom:0.5rem;font-size: 3rem;'>$title</h1>";
            echo '</div>';
    echo "<div class='px-md-5 px-4'>";
     
echo "<div class='title-container mt-5'>";
            echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb" style="justify-content: flex-start;">';
            echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a href="../../" class="text-dark text-decoration-none" >Home</a></li>';
            $breadcrumbUrl = '..';
            $segmentCount = count(array_filter($urlSegments));
            foreach ($urlSegments as $index => $segment) {
                if (!empty($segment)) {
                    $breadcrumbUrl .= '/' . $segment;
                    $isLastSegment = ($index === $segmentCount - 1);
                    $class = $isLastSegment ? 'theme-color mt-3  ' : 'text-dark';
                    $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="../' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
                }
            }
            echo '</ol></nav>';
        echo "</div>"; // Close title-container
      
          
echo "</div>";
            

 

            echo "<div class='blog-content-p'>";
    echo '<div class="row mb-5">';
    
    // Right Content
    echo '<div class="col-md-9">';
    echo '<div class="container-fluid px-3">';
        // Add the specified code for the child page content
                $content = $row["content"];
                // Generate TOC and update content with heading IDs
list($toc, $updatedContent) = generateTOC($content);
                
        echo '<div class="dropdown">';
    echo '    <button class="btn btn-light dropdown-toggle  text-dark" style="
    position: absolute;
    bottom: 71px;
    background-color: #6356ce;
    color: white!important;
    border: none;
" type="button" id="tocDropdown" data-bs-toggle="dropdown" aria-expanded="false">Table of Contents</button>';
    echo '    <ul class="dropdown-menu border-0" aria-labelledby="tocDropdown">';
    echo '        <li><a class="dropdown-item" href="#">' . $toc . '</a></li>'; // Place TOC in dropdown
    echo '    </ul>';
    echo '</div>';
    
// Display the updated content
echo $updatedContent;

     
    echo '</div>';
    // Fetch accordion items associated with the blog ID
$faq_sql = "SELECT * FROM blog_faq WHERE blog_id = '$pageId'";
$faq_result = $conn->query($faq_sql);
if ($faq_result->num_rows > 0) {
    echo '<h2 class="px-md-3 px-4 text-center my-4 p-3" style="
    border-bottom: 4px solid #1e03f4;
    color: white;
    background: #1e03f475;
    text-shadow: 1px 1px 2px #000;
">Frequently Asked Questions</h2>';
    echo '<div class="accordion accordion-flush px-md-3 px-4" id="accordionFlushExample">';
    while ($faq = $faq_result->fetch_assoc()) {
        $accordionId = 'faqCollapse' . $faq['id'];
        echo '<div class="accordion-item border rounded mb-3">'; // Added mb-3 for spacing
        echo '    <h2 class="accordion-header">';
        echo '        <button class="accordion-button" style="
    background-color: #1e03f412;
    color: black;
" type="button" data-bs-toggle="collapse" data-bs-target="#' . $accordionId . '" aria-expanded="true" aria-controls="' . $accordionId . '">';
        echo '            ' . $faq['title'];
        echo '        </button>';
        echo '    </h2>';
        echo '    <div id="' . $accordionId . '" class="accordion-collapse collapse show" aria-labelledby="flush-heading' . $faq['id'] . '" data-bs-parent="#accordionFlushExample">';
        echo '        <div class="accordion-body" style="overflow-wrap: break-word;">';
        echo '            ' . $faq['content'];
        // If admin then special option
        if (isset($_SESSION['admin']) && $_SESSION['admin'] === '1') {
            echo '            <p> FAQ id: ' . $faq['id'] . '</p>';
            echo '            <a href="del_FAQ.php?id=' . $faq['id'] . '" class="delete-icon float-end" title="Delete FAQ">';
            echo '                <lord-icon src="https://cdn.lordicon.com/wpyrrmcq.json" trigger="hover" style="width:30px;height:30px"></lord-icon>';
            echo '            </a>';
        }
        echo '        </div>';
        echo '    </div>';
        echo '</div>'; // Close accordion-item
    }
    echo '</div>'; // Close accordion
} else {
    // Optional: Handle the case where no FAQs are found
}

           echo '</div>';

    // Left Sidebar
    echo '<div class="col-md-3 other-blog-p">';
    echo'<div style="position: sticky;
    top: 72px;"> ';
    // Fetch parent ID of the current child page
    $parentSql = "SELECT parent_id FROM pages WHERE id =?";
    $parentStmt = $conn->prepare($parentSql);
    if ($parentStmt === false) {
        die("Prepare failed: ". htmlspecialchars($conn->error));
    }

    $parentStmt->bind_param("i", $pageId);
    if (!$parentStmt->execute()) {
        die("Execute failed: ". htmlspecialchars($parentStmt->error));
    }

    $parentResult = $parentStmt->get_result();
    if ($parentResult === false) {
        die("Get result failed: ". htmlspecialchars($parentStmt->error));
    }

    if ($parentResult->num_rows > 0) {
        $parentRow = $parentResult->fetch_assoc();
        $parentId = $parentRow['parent_id'];

        // Now fetch other child pages of this parent
        $childSql = "SELECT * FROM pages WHERE parent_id =? AND id!=?";
        $childStmt = $conn->prepare($childSql);
        if ($childStmt === false) {
            die("Prepare failed: ". htmlspecialchars($conn->error));
        }

        $childStmt->bind_param("ii", $parentId, $pageId);
        if (!$childStmt->execute()) {
            die("Execute failed: ". htmlspecialchars($childStmt->error));
        }

        $childResult = $childStmt->get_result();
        if ($childResult === false) {
            die("Get result failed: ". htmlspecialchars($childStmt->error));
        }

        if ($childResult->num_rows > 0) {
            // Page has sibling child pages, display the links to the sibling child pages
           echo "<h2 class='fs-3 p-2 text-center  text-white mt-3 rounded-3' style='background-color: #6356ce; padding-right: .7rem!important; padding-top: .7rem!important; padding-left: .7rem!important; font-size:20px!important;'>Other Pages</h2>";
            echo "<div class='row mt-4'>";

            $childCount = 0;
            while ($childRow = $childResult->fetch_assoc()) {
                if ($childCount >= 6) {
                    break;
                }

                $childTitle = htmlspecialchars($childRow["title"]);
                $childSlug = htmlspecialchars($childRow["slug"]);
                $childImage = htmlspecialchars($childRow["image"]); // Assuming you have an image column for child pages
                $childUrl = generatePageUrl($childRow["id"]);

                echo "<div class='col-md-12 mb-4'>";
                echo "<a href='../../$childUrl' class='text-decoration-none text-dark'>";
                echo "<div class='card border-0' style='box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;'>";
                if ($childImage) {
                    echo "<img src='$childImage' class='card-img-top p-2' alt='$childTitle'>";
                }
                echo "<div class='card-body' style='
    padding: 0px 10px;
'>";
                echo "<h5 class='card-title'style='
    font-weight: 400;
'>$childTitle</h5>";
                echo "</div></div></a></div>";

                $childCount++;
            }
            echo '</div>';

            if ($childResult->num_rows > 6) {
                echo "<div class='text-center mt-4'>";
              
                echo "</div>";
            }
        } else {
            echo "<p>No Recent Posts.</p>";
        }
    } else {
       header("Location: nopage");
    }
    echo '</div>';
    echo '</div>'; // End of row
echo'</div>';
 echo "</div>";
}
// child end

// TSA Wait Times Start
elseif($design == "Design_21"){
    echo "<div class=' p-0'>";
    echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
    echo "<h1 class='ms-5'>$title</h1>";
    echo '</div>';
    if(isset($_SESSION['email'])){

        echo "<a href='../PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
 
        echo "<a href='../PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
      
    }
    echo "</div>";
   
    echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb bread-bck ps-5 ">';
    echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a href="../../" class="text-dark text-decoration-none" >Home</a></li>';
    $breadcrumbUrl = '/';
    $segmentCount = count(array_filter($urlSegments));
    foreach ($urlSegments as $index => $segment) {
        if (!empty($segment)) {
            $breadcrumbUrl .= '../' . $segment;
            $isLastSegment = ($index === $segmentCount - 1);
            $class = $isLastSegment ? 'text-dark fw-semibold' : 'text-dark';
            $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
            }
            }
            echo '</ol></nav>';


 echo'<div class="container-fluid p-5 margin-dine">
        <div class="row">
             
            <div class="col-md-7 mob-space mt-md-5 p-3">
               <h3 class="fw-strong mt-md-3 mt-2">TSA Cares</h3>
               <p class="fw-semibold">The Transportation Security Administration (TSA) is responsible for transitioning passengers through the airport’s security checkpoints. <br>

Tips for going through security:</p>
               <p>
<ul class="custom-bullet">
   <li>Travelers with disabilities who need to use the TSA Restricted Access lanes at all security checkpoints.</li>
  <li>For assistance through security, please contact TSA Cares. TSA Cares is a helpline that provides travelers with disabilities, medical conditions and other special circumstances additional assistance during the security screening process.</li>
</ul>
</p>

                <p class="mt-3">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Learn More at TSA Care</a>
</p>
            </div>
             <div class="col-md-5">
            <div class="row">
                
                <div class="col p-3">
                    <div class="media3 clip-top-right mw-100" style="background-image:url(\'/Pic/airport-sec/sec2.jpg\');"></div>
                </div>
            </div>
        </div>
        </div>
    </div>
';
            echo'  <!-- Planning Ahead -->
<div class="container-fluid parking-back mt-5 p-5 margin-dine">
    <div class="row mt-5 p-md-0 p-2">
        <!-- Walking Times Section -->
        <div class="col-md-12">
            <div class="text-light text-center">
                <h2>Average Times to Your Gate After Security</h2>
                <p class="my-4">Whether you take the train to the gates or walk the Bridge, below are the average walk times to get to your gate after you have passed the security checkpoint. These are estimates only and persons with reduced mobility may experience longer travel times.</p>
                <!-- <div class="btn btn-size-group" role="group" aria-label="Navigation Options">
                    <button type="button" class="btn btn-size btn btn-size-light">Parking Lots</button>
                    <button type="button" class="btn btn-size btn btn-size-outline-light">Average Walking Times</button>
                </div> -->
            </div>
            <div class="row mt-3">
                <div class="col-md-4 mb-3">
                    <div class="card walking-card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-map-marker-alt"></i> Concourse D (North Terminal)</h5>
                            <ul class="custom-bullet">
                                <li>Gates D1-D59: 5-15 minutes</li>
                                <li>Gates D60-D99: 10-20 minutes</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card walking-card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-map-marker-alt"></i> Concourse E (Central Terminal)</h5>
                            <ul class="custom-bullet">
                                <li>Gates E2-E33: 5-15 minutes</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card walking-card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-map-marker-alt"></i> Concourse F (Central Terminal)</h5>
                            <ul class="custom-bullet">
                                <li>Gates F3-F23: 5-15 minutes</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card walking-card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-map-marker-alt"></i> Concourse G (Central Terminal)</h5>
                            <ul class="custom-bullet">
                                <li>Gates G2-G19: 5-15 minutes</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card walking-card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-map-marker-alt"></i> Concourse H (South Terminal)</h5>
                            <ul class="custom-bullet">
                                <li>Gates H3-H17: 5-15 minutes</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card walking-card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-map-marker-alt"></i> Concourse J (South Terminal)</h5>
                            <ul class="custom-bullet">
                                <li>Gates J2-J18: 5-15 minutes</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Planning Ahead end -->';
            echo'<div class="container-fluid px-md-5 px-4">';
   echo'<div class="container-fluid my-5 p-0">
    <div class="row">
    <h3 class="text-center mb-md-3">Airport Security Checkpoints</h3>
        <div class="col-md-4">
            <div class="card my-md-0 my-4">
                <div class="card-header">
                    <h5 class="card-title">North Terminal</h5>
                </div>
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Concourse D:</h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Checkpoint 1:</strong> 4:45 am – 8:45 pm<br>TSA Precheck available during checkpoint hours</li>
                        <li class="list-group-item"><strong>Checkpoint 2:</strong> Open 24 hours<br>American Airlines priority lane available</li>
                        <li class="list-group-item"><strong>Checkpoint 3:</strong> 4:00 am - 9:45 pm</li>
                        <li class="list-group-item"><strong>Checkpoint 4:</strong> 4:45 am - 8:45 pm</li>
                        <li class="list-group-item"><strong>Checkpoint DFIS:</strong> 5:15 am – 8:45 pm</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Central Terminal</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Checkpoint 5:</strong> 4:00 am - 10:45 pm<br>TSA Precheck available during checkpoint hours</li>
                        <li class="list-group-item"><strong>Checkpoint 6:</strong> 3:45 am – 10:45 pm<br>TSA Precheck available during checkpoint hours</li>
                        <li class="list-group-item"><strong>Checkpoint 7:</strong> 3:30 am – 10:15 pm<br>TSA Precheck available during checkpoint hours</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card my-md-0 my-4">
                <div class="card-header">
                    <h5 class="card-title">South Terminal</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Checkpoint 8:</strong> 4:00 am – 8:00 pm<br>TSA Precheck available during checkpoint hours</li>
                        <li class="list-group-item"><strong>Checkpoint 9:</strong> Open 24 Hours<br>TSA Precheck available during checkpoint hours</li>
                        <li class="list-group-item"><strong>Checkpoint 10:</strong> 9:45 am – 8:00 pm<br>TSA Precheck available during checkpoint hours</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
';
    echo '<div class="container-fluid mt-3 p-0">
    <div class="nav nav-tabs">
        <a class="nav-item nav-link security-nav active border-0 nav-hov mx-md-3" id="nav-item1" onclick="showContent(\'content1\')">Checkpoint Map</a>
        <a class="nav-item nav-link security-nav border-0 nav-hov mx-md-3" id="nav-item2" onclick="showContent(\'content2\')">Restrictions</a>
        <a class="nav-item nav-link security-nav border-0 nav-hov mx-md-3" id="nav-item3" onclick="showContent(\'content3\')">Hours</a>
        <a class="nav-item nav-link security-nav border-0 nav-hov mx-md-3" id="nav-item4" onclick="showContent(\'content4\')">Arrive early</a>
        <a class="nav-item nav-link security-nav border-0 nav-hov mx-md-3" id="nav-item5" onclick="showContent(\'content5\')">Travel Tips</a>
        <a class="nav-item nav-link security-nav border-0 nav-hov mx-md-3" id="nav-item6" onclick="showContent(\'content6\')">Call for help</a>
    </div>
    <div id="content" class="content border-0">
        <img src="/Pic/airport-sec/sec3.jpg" alt="Content for Item 1" class="img-fluid">
    </div>
</div>';
echo '<script>
    function showContent(contentId) {
        var content = document.getElementById(\'content\');
        var navItems = document.querySelectorAll(\'.security-nav\');
        navItems.forEach(function(navItem) {
            navItem.classList.remove(\'active\');
        });
        switch(contentId) {
            case \'content1\':
                document.getElementById(\'nav-item1\').classList.add(\'active\');
                content.innerHTML = \'<img src="/Pic/airport-sec/sec3.jpg" alt="Content for Item 1" class="img-fluid">\';
                break;
            case \'content2\':
                document.getElementById(\'nav-item2\').classList.add(\'active\');
                content.innerHTML = `<p>The Prohibited Items list is not all-inclusive. The items listed are strictly prohibited from being carried into the aircraft. However, many of these items may be transported in checked baggage. If you have questions, check with your airline.</p>
               <ul>
    <li>The Prohibited Items list is not all-inclusive. The items listed are strictly prohibited from being carried into the aircraft. However, many of these items may be transported in checked baggage. If you have questions, check with your airline.</li>
    <li>If you have a medical condition that requires you to carry a needle and/or syringe either with you or in your carry-on baggage, then you also need to bring the medication that requires an injection. The medication must be packaged with a pharmaceutical label or professionally printed label identifying the medication.</li>
    <li>Avoid carrying bottles of liquid through the screening checkpoint.</li>
    <li>If you plan on purchasing food to carry on board the plane, wait until you have completed the screening process.</li>
    <li>Food, gifts, and other services are generally available in the concourses after screening.</li>
    <li>If you have special dietary needs, please contact your airline to confirm what arrangements will be provided on your flight.</li>
    <li>If you are traveling with gifts, wrap them after you arrive at your destination. They may have to be unwrapped for security inspection. Gifts should be packed in your checked luggage or shipped via mail, due to the limitations of carry-on items.</li>
</ul>`;
                break;
            case \'content3\':
                document.getElementById(\'nav-item3\').classList.add(\'active\');
                content.innerHTML = `<h3>North Terminal</h3>
                <h4>Concourse D:</h4>
                <ul>
                    <li><strong>Checkpoint 1:</strong> 4:45 am – 8:45 pm<br>TSA Precheck available during checkpoint hours</li>
                    <li><strong>Checkpoint 2:</strong> Open 24 hours<br>American Airlines priority lane available</li>
                    <li><strong>Checkpoint 3:</strong> 4:00 am - 9:45 pm</li>
                    <li><strong>Checkpoint 4:</strong> 4:45 am - 8:45 pm</li>
                    <li><strong>Checkpoint DFIS:</strong> 5:15 am – 8:45 pm</li>
                </ul>
                <h3>Central Terminal</h3>
                <ul>
                    <li><strong>Checkpoint 5:</strong> 4:00 am - 10:45 pm<br>TSA Precheck available during checkpoint hours</li>
                    <li><strong>Checkpoint 6:</strong> 3:45 am – 10:45 pm<br>TSA Precheck available during checkpoint hours</li>
                    <li><strong>Checkpoint 7:</strong> 3:30 am – 10:15 pm<br>TSA Precheck available during checkpoint hours</li>
                </ul>
                <h3>South Terminal</h3>
                <ul>
                    <li><strong>Checkpoint 8:</strong> 4:00 am – 8:00 pm<br>TSA Precheck available during checkpoint hours</li>
                    <li><strong>Checkpoint 9:</strong> Open 24 Hours<br>TSA Precheck available during checkpoint hours</li>
                    <li><strong>Checkpoint 10:</strong> 9:45 am – 8:00 pm<br>TSA Precheck available during checkpoint hours</li>
                </ul>
                <p>Real-time wait times of the security checkpoints are available to see which checkpoint is most convenient to your gate.</p>`;
                break;
            case \'content4\':
                document.getElementById(\'nav-item4\').classList.add(\'active\');
                content.innerHTML = `<p>Arrival time recommendations vary by airline and day of travel, so please check with your airline. Remember to leave adequate time for transit or parking, checking baggage and getting through security.</p>
                <p>The Transportation Security Administration (TSA) encourages travelers to arrive at the airport at least two hours prior to a domestic flight and three hours prior to an international flight.</p>`;
                break;
            case \'content5\':
                document.getElementById(\'nav-item5\').classList.add(\'active\');
                content.innerHTML = `<p>To expedite your visit through Miami International Airport, become familiar with the latest security guidelines and suggestions:</p>
                <ul>
                    <li>Do not leave personal items unattended at any time in the airport or at curbside. This includes:
                        <ul>
                            <li>Purse</li>
                            <li>Briefcase</li>
                            <li>Electronic equipment</li>
                            <li>Carry-on bags</li>
                        </ul>
                    </li>
                    <li>Do not enter areas listed as “Restricted” or “Authorized.”</li>
                </ul>
                <p>If you are traveling with children:</p>
                <ul>
                    <li>Child safety seat recommendations</li>
                    <li>Requirements for unaccompanied minors</li>
                    <li>Identification for minors</li>
                </ul>
                <p>For travel tips regarding accessibility and assistance for travelers with disabilities, visit our myMIAaccess program. It is a dedicated platform for accessing services, amenities, and information when traveling through Miami International Airport.</p>
                <p>If traveling with a service animal, there are outdoor and indoor animal relief areas located in Concourses D, E, F, G, and J. All the MIA relief areas are equipped with dual surfaces and waste disposal stations (map locations).</p>`;
                break;
            case \'content6\':
                document.getElementById(\'nav-item6\').classList.add(\'active\');
                content.innerHTML = `<p>We want you to feel safe while traveling through our airport. Here are some of the security resources available should you need help:</p>
                <ul>
                    <li>In case of an emergency, including safety and medical, call 9-1-1.</li>
                    <li>In case of a non-emergency or to file a police report, contact the Airport District Police at 305-876-7373 or visit this website. They oversee the safety and security of MIA, its employees, and the traveling public.</li>
                    <li>The Miami-Dade Aviation Fire Rescue Division responds to medical emergencies as well as fires, fuel spills, and disasters. To provide rapid response, two stations are located at MIA.</li>
                    <li>Call the Crime Stoppers Tip Line 305-471-TIPS (8477) if you have a tip and see someone who has, or is about to, commit a crime at Miami International Airport. All callers will remain anonymous.</li>
                    <li>If you lost an item, please fill out a Lost Item Claim. Our Lost and Found team will be in touch with the status of your item.</li>
                </ul>`;
                break;
            default:
                content.innerHTML = \'Content not found\';
        }
    }
</script>';
    echo "</div>"; // Close title-container
}
// TSA Wait Times  end

//hotels start
elseif($design == "Design_22"){
    echo "<div class=' p-0'>";
    echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
    echo "<h1 class='ms-5'>$title</h1>";
    echo '</div>';
    if(isset($_SESSION['email'])){

        echo "<a href='../PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
 
        echo "<a href='../PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
      
    }
    echo "</div>";
   
    echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb bread-bck ps-5 ">';
    echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a href="../../" class="text-dark text-decoration-none" >Home</a></li>';
    $breadcrumbUrl = '/';
    $segmentCount = count(array_filter($urlSegments));
    foreach ($urlSegments as $index => $segment) {
        if (!empty($segment)) {
            $breadcrumbUrl .= '../' . $segment;
            $isLastSegment = ($index === $segmentCount - 1);
            $class = $isLastSegment ? 'text-dark fw-semibold' : 'text-dark';
            $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
            }
            }
            echo '</ol></nav>';
            echo'<div class="container-fluid p-5 margin-dine mt-md-0 mt-2">
        <div class="row">
              <div class="col-md-5">
            <div class="row">
                
                <div class="col p-3">
                    <div class="media3 clip-top-right mw-100" style="background-image:url(\'Pic/shop and dine/dine 1.jpg\');    background-size: cover;"></div>
                </div>
            </div>
        </div>
            <div class="col-md-7 mob-space ps-md-5 p-3 mt-5">
                 <h2>Hotels at Miami International Airport</h2>
        <p> Miami International Airport (MIA) offers a range of convenient hotel options for travelers. Located right within the airport, the Miami International Airport Hotel provides easy access to terminals and amenities, ensuring a comfortable stay for those with early flights or long layovers. Nearby, you\'ll find several other hotels, such as the Sheraton Miami Airport Hotel & Executive Meeting Center, which offers modern accommodations and excellent services just minutes away. These hotels provide shuttle services, dining options, and comfortable rooms, making your travel experience smooth and stress-free. Whether you\'re on a quick stopover or planning a longer stay, Miami International Airport\'s hotel options cater to all your needs.</p>
       <p>Nearby, you\'ll find several other hotels, such as the Sheraton Miami Airport Hotel & Executive Meeting Center, which offers modern accommodations and excellent services just minutes away. These hotels provide shuttle services, dining options, and comfortable rooms, making your travel experience smooth and stress-free. Whether you\'re on a quick stopover or planning a longer stay, Miami International Airport\'s hotel options cater to all your needs.






</p>

            </div>
        </div>
    </div>
';
echo'<div class="container-fluid p-md-5 p-4 parking-back my-5">
    <div class="text-center mb-5">
        <h2 class="fw-normal fs-1 text-light">Popular Hotels at Miami International Airport</h2>
    </div>
    <div class="row">
        <!-- Card 1 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/hotels/hotel1.jpg" class="card-img-top img-fluid object-fit-cover" style="
    height: 14em;
    object-fit: cover;
" alt="Air Bar">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-utensils"></i> Miami International Airport Hotel</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> North Terminal,Concourse D</p>
                    <p class="card-text text-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> Open 24/7</p>
                </div>
            </div>
        </div>
        <!-- Card 2 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/hotels/sheraton.jpg " class="card-img-top img-fluid object-fit-cover" style="
    height: 14em;
    object-fit: cover;
" alt="Gateway Bake Shop">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-utensils"></i> Sheraton Miami Airport Hotel & Executive Meeting Center</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> North Terminal,Concourse D</p>
                    <p class="card-text text-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> Open 24/7</p>
                </div>
            </div>
        </div>
        <!-- Card 3 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/hotels/EB Hotel Miami.jpg" class="card-img-top img-fluid object-fit-cover" style="
    height: 14em;
    object-fit: cover;
" alt="Metro News & Gifts">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-utensils"></i> EB Hotel Miami</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> North Terminal,Concourse D</p>
                    <p class="card-text text-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> Open 24/7</p>
                </div>
            </div>
        </div>
        <!-- Card 4 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/hotels/Hilton Miami Airport Blue Lagoon.jpg" class="card-img-top img-fluid object-fit-cover" style="
    height: 14em;
    object-fit: cover;
" alt="Metro News & Gifts (Temporarily Closed)">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-utensils"></i> Hilton Miami Airport Blue Lagoon</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> South Terminal, Concourse H-J Connector</p>
                    <p class="card-text text-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> Open 24/7</p>
                </div>
            </div>
        </div>
        <!-- Card 5 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="Pic/hotels/Pullman Miami Airport Hotel.jpg" class="card-img-top img-fluid object-fit-cover" style="
    height: 14em;
    object-fit: cover;
" alt="New York Deli">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-utensils"></i> Pullman Miami Airport Hotel</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> North Terminal, Concourse D</p>
                    <p class="card-text text-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> Open 24/7</p>
                </div>
            </div>
        </div>
        <!-- Card 6 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/hotels/Courtyard by Marriott Miami Airport.jpg" class="card-img-top img-fluid object-fit-cover" style="
    height: 14em;
    object-fit: cover;
" alt="Restrooms">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-utensils"></i> Courtyard</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> South Terminal- Door 26 - 2nd Level Departures					 </p>
                    <p class="card-text text-center"> <span class="fa-solid fa-clock" aria-hidden="true"></span> Open 24/7</p>
                </div>
            </div>
        </div>
        <!-- Card 7 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/hotels/holiday inn.jpg" class="card-img-top img-fluid object-fit-cover" style="
    height: 14em;
    object-fit: cover;
" alt="Restrooms">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-utensils"></i> Holiday Inn Express & Suites Miami Airport East</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> South Terminal, Concourse H-J Connector</p>
                    <p class="card-text text-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> Open 24/7</p>
                </div>
            </div>
        </div>
        <!-- Card 8 -->
        <div class="col-lg-3 col-md-6 col-12 mb-3">
            <div class="card h-100">
                <img src="https://miamiairport-mia.com/Pic/hotels/Hyatt Place Miami Airport-East.jpg" class="card-img-top img-fluid object-fit-cover" style="
    height: 14em;
    object-fit: cover;
" alt="Tim Hortons">
                <div class="card-body">
                    <h5 class="card-title text-center"><i class="fa-solid fa-utensils"></i> Hyatt Place Miami Airport-East</h5>
                    <p class="card-text text-center"><i class="fa-solid fa-location-dot"></i> 3549 NW Le Jeune Rd, Miami, FL 33142</p>
                    <p class="card-text text-center"> <span class="fa-solid fa-clock" aria-hidden="true"></span> Open 24/7</p>
                </div>
            </div>
        </div>
    </div>
</div>
';

 echo'<div class="container-fluid px-md-5 px-4">';
    $content = $row["content"];
    // echo "<p class='mt-5'>$content</p>";

    // Check if the page has child pages
    $childSql = "SELECT * FROM pages WHERE parent_id = ?";
    $childStmt = $conn->prepare($childSql);
    $childStmt->bind_param("i", $pageId);
    $childStmt->execute();
    $childResult = $childStmt->get_result();

    if ($childResult->num_rows > 0) {
        // Page has child pages, display the links to the child pages
        echo'<div class="text-center mb-4">
            <h2 class="fw-normal fs-1">Other Shops at Miami</h2>
        </div>';
        echo "<div class='row'>";

        $childCount = 0;
        while ($childRow = $childResult->fetch_assoc()) {
            $childTitle = $childRow["title"];
            $childSlug = $childRow["slug"];
            $childImage = $childRow["image"]; // Assuming you have an image column for child pages
            $childDate =$childRow["date"];
            //  $name = $_SESSION['Name'];
            $childUrl = generatePageUrl($childRow["id"]);

            if ($childCount > 0 && $childCount % 4 == 0) {
                echo '</div><div class="row mt-4">';
            }

            echo "<div class='col-md-3 mb-4'>";
            echo "<a href='../$childUrl' class='text-decoration-none text-dark ' >";
            echo "<div class='card border-0 h-100' style='box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;'>";
          if ($childImage) {
                echo "<img src='$childImage' class='card-img-top rounded-0' style='
    max-height: 23em;
' alt='$childTitle'>";
            }
            echo "<div class='card-body position-absolute w-100 p-2' style='    bottom: 1px;
    background: #1606a7a9;'>";
            echo "<h5 class='card-title text-light text-center fw-bold'>$childTitle</h5>";
            // echo "<small class='badge theme-bg my-2'>$childDate</small>";
            // echo "<small class='badge theme-bg my-2'>$childName</small>";
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
    $childStmt->close();
    echo'</div>
   


    <div class="container-fluid p-md-5 p-4 mt-5">
<div class="text-center mb-4">
            <h2 class="fw-normal fs-1">Some Helpful Amenities</h2>
           
        </div>
        <div class="row">
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                <div class="myimage3">
                    <img src="https://miamiairport-mia.com/Pic/Card/s1.jpeg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Wheelchair Requests &rarr;</a>
            </div>
                </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
                    <img  src="https://miamiairport-mia.com/Pic/Card/s2.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Wifi at Mia &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Card/s3.webp" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Airport Facility &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Card/s4.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Lost and Found &rarr;</a>
                
                    </div>
                    </div>
            </div>
        </div>
        <div class="row mt-md-5">
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Card/s5.webp" alt="Transportation">
                    <a class="helpful-font" href="#">Transportation &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Card/s6.webp" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Parking Option &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Card/s7.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Travel Tips &rarr;</a>
                
                    </div>
                    </div>
            </div>
            <div class="col-md-3 col-6 my-2">
                <div class="feature-card h-100">
                    <div class="myimage3">
<img src="https://miamiairport-mia.com/Pic/Card/s8.jpg" alt="Wheelchair Requests">
                    <a class="helpful-font" href="#">Traveling with Children &rarr;</a>
                
                    </div>
                    </div>
            </div>
        </div>
    </div>

<div class="container-fluid px-md-5 px-4">';
  
       
echo'
        <div class="text-center">
            <h2>Helpful Resources</h2>
            <div class="btn btn-size-group mt-3" role="group" aria-label="Accessibility Options">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Accessibility Statement</a>
            <a href="#" class="btn btn-size btn btn-outline-secondary mob-button">Accessibility Services</a>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Traveler Tips</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-globe icond "></i></span>  <a href="#" class="d-flex align-items-center justify-content-between my-3">International Travelers <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-passport icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Infrequent Traveler Tips <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-child icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Children and Families <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-wheelchair icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Wheelchair Requests <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-map icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Interactive Map <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-paw icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Traveling with Pets <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="resource">
                    <div class="resource-body">
                        <h5 class="resource-title">Business Information</h5>
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-briefcase icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Doing Business at MIA <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-users icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Business Diversity and Development <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-store icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Concessions Opportunities <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-info-circle icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> General Tenant Information <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-balance-scale icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> CEEA: Center of Equity and Excellence in Aviation <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                            <li class="d-flex align-items-center ar1"> <span class="service-item-font mx-2"><i class="fas fa-gavel icond "></i></span> <a href="#" class="d-flex align-items-center justify-content-between my-3"> Rules and Regulations <span class="ml-auto arrow">→</span></a></li>
                            <hr>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    
 ';
 
  
    echo "</div>"; // Close title-container
}

// hotels end

elseif($design == "Design_23"){
            echo "<div class=' p-0'>";
            echo '<div class="new-feature" style="background: linear-gradient(90deg, rgb(23 6 167 / 81%), rgba(22, 6, 167, 0.356) 30.52%), url(\'' . $image . '\'); background-size: cover; background-position: center;">';
            echo "<h1 class='ms-md-5 ms-4 'style='margin-bottom:0.5rem;font-size: 3rem;'>$title</h1>";
            echo '</div>';
            if(isset($_SESSION['email'])){

                echo "<a href='../PHP-Blog-Admin/admin/edit_page.php?id=" . urlencode($pageId) . "' class='btn'>Edit</a>";
         
                echo "<a href='..//PHP-Blog-Admin/admin/delete_page.php?id=" . urlencode($pageId) . "' class='btn'>Delete</a>";
              
            }
            echo "</div>";
           
            echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb bread-bck ps-md-5 ps-4 ">';
         echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a href="../../" class="text-dark text-decoration-none" >Home</a></li>';
            $breadcrumbUrl = '';
            $segmentCount = count(array_filter($urlSegments));
            foreach ($urlSegments as $index => $segment) {
                if (!empty($segment)) {
                    $breadcrumbUrl .= '/' . $segment;
                    $isLastSegment = ($index === $segmentCount - 1);
                    $class = $isLastSegment ? 'text-dark fw-semibold' : 'text-dark';
                    $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
         
                    
                }
                    }
                    echo '</ol></nav>';
                    echo'<div class="container-fluid px-md-5 px-4">';
            $content = $row["content"];
            echo "<p>$content</p>";
echo'  <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="contact-form">
                    <h2 class="text-center">Contact Us</h2>
                    <form action="submit_contact.php" method="post">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>';
            // Check if the page has child pages
            $childSql = "SELECT * FROM pages WHERE parent_id = ?";
            $childStmt = $conn->prepare($childSql);
            $childStmt->bind_param("i", $pageId);
            $childStmt->execute();
            $childResult = $childStmt->get_result();
    
            if ($childResult->num_rows > 0) {
                // Page has child pages, display the links to the child pages
               
                echo "<div class='row'>";
    
                $childCount = 0;
                while ($childRow = $childResult->fetch_assoc()) {
                    $childTitle = $childRow["title"];
                    $childSlug = $childRow["slug"];
                    $childImage = $childRow["image"]; // Assuming you have an image column for child pages
                    $childDate =$childRow["date"];
                    //  $name = $_SESSION['Name'];
                    $childUrl = generatePageUrl($childRow["id"]);
    
                    if ($childCount > 0 && $childCount % 3 == 0) {
                        echo '</div><div class="row mt-4">';
                    }
    
                    echo "<div class='col-md-4 mb-4'>";
                    echo "<a href='../$childUrl' class='text-decoration-none text-dark ' >";
                    echo "<div class='card border-0 h-100' style='box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;'>";
                   if ($childImage) {
                echo "<img src='$childImage' class='card-img-top rounded-0' style='
    max-height: 23em;    height: 16em;
    object-fit: cover;
' alt='$childTitle'>";
            }
            echo "<div class='card-body position-absolute w-100 p-2' style='    bottom: 1px;
    background: #1606a7a9;'>";
            echo "<h5 class='card-title text-light text-center fw-bold'>$childTitle</h5>";
            // echo "<small class='badge theme-bg my-2'>$childDate</small>";
            // echo "<small class='badge theme-bg my-2'>$childName</small>";
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
            $childStmt->close();
        
            echo "</div>"; // Close title-container
        }
        else{
            echo "<div class='title-container'>";
            echo "<h1 class='mb-4 text-center'>$title</h1>";
            echo '<nav  aria-label="breadcrumb"><ol class="breadcrumb ">';
          echo '<li class="breadcrumb-item"><a href="../index.php" class="text-dark text-decoration-none">Home</a></li>';
            $breadcrumbUrl = '/';
            $segmentCount = count(array_filter($urlSegments));
            foreach ($urlSegments as $index => $segment) {
                if (!empty($segment)) {
                    $breadcrumbUrl .= '/' . $segment;
                    $isLastSegment = ($index === $segmentCount - 1);
                    $class = $isLastSegment ? 'text-dark fw-semibold' : 'text-dark';
                    $segmentWithoutHyphens = str_replace('-', ' ', $segment); $formattedSegment = ucwords($segmentWithoutHyphens);   echo '<li class="breadcrumb-item" style="font-size: .875rem; line-height: 1.25rem;"><a class="text-decoration-none ' . $class . '" href="' . $breadcrumbUrl . '">' . $formattedSegment . '</a></li>';
                }
            }
            echo '</ol></nav>';
            echo "</div>"; // Close title-container
            $content = $row["content"];
            
            $image = $row["image"]; // Assuming you have an image column in your table
      
            echo "<p>$content</p>";
            if ($image) {
                echo'<div class="container"> ';
                echo "<img src='$image' style='height: 100%;
                width: 100%;
                border-radius: 5em;' class='img-fluid mb-4' alt='" . $row["title"] . "'>";
                echo"</div>'";
            }

   
        }}
        }else {
            // Page not found
           header("Location: ../nopage");
        }


    $stmt->close();
    // $conn->close();

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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tocButton = document.getElementById('toggle-toc');
    const tocContent = document.querySelector('.table-of-contents');
    tocButton.addEventListener('click', function() {
        if (tocContent.style.display === 'none') {
            tocContent.style.display = 'block';
            tocButton.textContent = 'Hide →';
        } else {
            tocContent.style.display = 'none';
            tocButton.textContent = 'Show Table of Contents →';
        }
    });

    const links = document.querySelectorAll('.table-of-contents a');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default anchor behavior

            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                const yOffset = -107; // Adjust this offset for the header height
                const y = targetElement.getBoundingClientRect().top + window.pageYOffset + yOffset;

                // Smooth scroll to the target element
                window.scrollTo({ top: y, behavior: 'smooth' });

                // Update the URL without fragment identifier
                history.pushState(null, null, window.location.pathname + window.location.search);
            }
        });
    });
});
</script>



 <!-- Font Awesome JS -->
 <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
<!-- Subscribe -->
<div class="card parking-back banner766  mt-5" >
<div class="row align-items-center">
            <div class="col-md-6 mx-md-5 p-5">
                <h2 class="text-light mb-4">Flying to/from MIA? Know More Here</h2>
                <form id="subscribeForm" action="subscribe.php" method="post">
                <div class="input-group">
                <input type="text" name="email"  class="form-control" placeholder="Enter Your Email" style="border-radius:0px;height:3rem;" required>
                <div class="input-group-append">
                    <button class="btn btn-size btn-mysuccess text-dark" type="submit" style="border-radius:0px;height:3rem;">Submit</button>
                </div>
            </div>
                </form>
            </div>
        </div>
                
    </div>
   
 <!-- Subscribe end -->
<?php include 'myfooter.php'; ?>
</body>
</html>
