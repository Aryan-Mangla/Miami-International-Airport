<?php
// Static data for the home page
$title = "Welcome to Miami International Airport";
$metaDescription = "Discover everything you need to know about Miami International Airport, including flight information, amenities, and transportation options.";
$metaKeywords = "Miami International Airport, MIA, flight information, airport amenities, transportation";
$metaCanonical = "https://miamiairport-mia.com/";
$adminName = "Miami Airport Admin";
$datePublished = "2024-01-01";
$dateModified = "2024-08-05";

$jsonLd = [
    "@context" => "https://schema.org",
    "@type" => "WebPage",
    "name" => $title,
    "description" => $metaDescription,
    "mainEntityOfPage" => [
        "@type" => "WebPage",
        "@id" => $metaCanonical
    ],
    "author" => [
        "@type" => "Person",
        "name" => $adminName
    ],
    "datePublished" => $datePublished,
    "dateModified" => $dateModified,
    "headline" => $title,
];

?>

<!-- TSA API -->
<?php
// API key for TSA
$apiKey = 'LmuSAodcVWaLSBIiWPLl8WZQ6kkUHCH2';

// The airport code for which you want to retrieve data
$airportCode = 'MIA'; // Replace with the desired airport code

// API URL
$apiUrl = "https://www.tsawaittimes.com/api/airport/$apiKey/$airportCode/json";

// Initialize cURL
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the request
$response = curl_exec($ch);

// Check if the request was successful
if ($response === FALSE) {
    die('Error fetching data: ' . curl_error($ch));
}

// Close cURL session
curl_close($ch);

// Decode the JSON response
$data = json_decode($response, true);

// Check if the data was decoded successfully
if (json_last_error() !== JSON_ERROR_NONE) {
    die('Error decoding JSON');
}

// Extract data from the response
$code = $data['code'];
$name = $data['name'];
$city = $data['city'];
$state = $data['state'];
$rightnow = $data['rightnow'];
$rightnowDescription = $data['rightnow_description'];
$precheck = $data['precheck'];
$faaAlerts = $data['faa_alerts'];
$estimatedHourlyTimes = $data['estimated_hourly_times'];
$precheckCheckpoints = $data['precheck_checkpoints'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="index, follow" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
       <title>Miami International Airport</title>
       <meta name="description" content="Discover everything you need to know about Miami International Airport, including flight information, amenities, and transportation options." />
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="path/to/jquery.easing.1.3.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://kit.fontawesome.com/efb7a73d46.js" crossorigin="anonymous"></script>
     <link rel="icon" type="image/png" sizes="32x32" href="/Pic/miami-fav-icon-transformed.png">
  <style>
    /*thead{*/
    /*    height: 4em;*/
    /*position: sticky!important*/
    /*;*/
    /*top: 0;*/
    /*z-index: 1;*/
    /*background-color: #f7f7f7;*/
    /*}*/
     #scrollTopBtn {
            display: none; 
            position: fixed; 
            bottom: 20px; 
            right: 30px; 
            z-index: 99; 
            border: none; 
            outline: none; 
            background-color: red; 
            color: white; 
            cursor: pointer; 
            padding: 15px; 
            border-radius: 10px; 
        }

        #scrollTopBtn:hover {
            background-color: #555; 
        }

  </style>
 <script type="application/ld+json">
        <?php echo json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
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

</head>
    
<body style="overflow-x: hidden;">

    <?php 
    require_once 'config.php'; 
    include 'header.php';

    ?>
    
<?php
// Function to generate page URL
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

<!--back to top-->
 <button onclick="topFunction()" id="scrollTopBtn" title="Go to top">Top</button>

  <script>
    $(document).ready(function() {
        $(window).scroll(function() {
            if ($(this).scrollTop() > 20) {
                $('#scrollTopBtn').fadeIn();
            } else {
                $('#scrollTopBtn').fadeOut();
            }
        });

        $('#scrollTopBtn').click(function() {
            $('html, body').animate({ scrollTop: 0 }, 800);
            return false;
        });
    });
</script>
      <!-- Atlanta Banner -->
    <div class="card rounded-0 position-relative parking-back banner765" >
    <div class="heroContent fw-bold">
                    <p>Welcome To</p>
                    <h1>Miami International Airport</h1>
                </div>
        <!-- <img src="Pic/Atlanta-banner.png " class="card-img banner765"  alt="Atlanta-airlines"> -->
    </div>
    <!-- Atlanta Banner End -->
    
<!-- Arrival & Departure -->
 <div class="container-fluid p-md-5 p-sm-3">
 <div class="row">
    <div class="col-md-5 h-100 m-height">
<div class=" search-container my-5 position-relative pos132">
    <div class="d-md-flex justify-content-between">
    <h2 class="fw-normal fs-1 mb-md-4 p-3">Flight Info</h2>
    <span class="fw-normal mt-3 p-3">⛅ 78°F / 26°C / Mostly Cloudy</span>
    </div>
        
        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mobile-fm" id="flightInfoTabs" role="tablist">
            <li class="nav-item flight-hover">
                <a class="nav-link theme-color fs-5 active" id="departure-tab" data-toggle="tab" href="#departure" role="tab" aria-controls="departure" aria-selected="true">Departure</a>
            </li>
            <li class="nav-item flight-hover">
                <a class="nav-link theme-color fs-5" id="arrival-tab" data-toggle="tab" href="#arrival" role="tab" aria-controls="arrival" aria-selected="false">Arrival</a>
            </li>
        </ul>

        <!-- Search Bar Container -->
        <div class="container mt-4"style="
    padding: 0;
">
            <div class="input-group">
                <input type="text" id="commonSearchBar" class="form-control" placeholder="Enter destination, airline" style="border-radius:0px;height:3rem;">
                <div class="input-group-append">
                    <button class="btn btn-size theme-bg text-light" type="button" id="searchButton"style="border-radius:0px;height:3rem;">Search</button>
                </div>
            </div>
        </div>
        <a id="more-link" class="d-block mt-3 text-decoration-none theme-color p-1" href="flights-arrivals">View All Flights</a>
</div>
    </div>
    <div class="col-md-7">
         <div class="container-fluid search-container my-5 position-relative pos132 h-md-100">
         <div class="security-heading  d-md-flex justify-content-between mob-disp-block"><span class="fw-bolder fs-2 mb-4 p-3">Security</span><span class="text-center mb-4 p-3">
        <a href="#" class="btn btn-size btn-outline-secondary w-100 mt-md-0 mt-sm-3">All Security & TSA Info</a>
    </span></div>
     <div class="row">
        <div class="col-md-12">
            <div class="scrollable-cards-container">
                <div class="scrollable-cards row flex-nowrap overflow-auto">
                    <!-- Card 1 -->
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-center">Checkpoint 1</h5>
                                <p class="card-text text-center">Standard</p>
                                <div class="fw-bold mt-3 text-center">2 </div><div class="text-center">min</div>
                                <div class="details mt-1">
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span>  4:45 am – 8:45 pm</p>
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                                </div>
                            </div>
                        </div>
                    </div>

                     <!-- Card 2 -->
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-center">Checkpoint 2</h5>
                                <p class="card-text text-center">Standard</p>
                                <div class="fw-bold mt-3 text-center">2 </div><div class="text-center">min</div>
                                <div class="details mt-1">
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span>  24 Hours Open</p>
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                     <!-- Card 3 -->
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-center">Checkpoint 3</h5>
                                <p class="card-text text-center">Standard</p>
                                <div class="fw-bold mt-3 text-center">2 </div><div class="text-center">min</div>
                                <div class="details mt-1">
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span>  4:00 am - 9:45 pm</p>
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                                </div>
                            </div>
                        </div>
                    </div> <!-- Card 4 -->
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-center">Checkpoint 4</h5>
                                <p class="card-text text-center">Standard</p>
                                <div class="fw-bold mt-3 text-center">2 </div><div class="text-center">min</div>
                                <div class="details mt-1">
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> 4:45 am - 8:45 pm</p>
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                                </div>
                            </div>
                        </div>
                    </div> <!-- Card 5 -->
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-center">Checkpoint DFIS</h5>
                                <p class="card-text text-center">Standard</p>
                                <div class="fw-bold mt-3 text-center">2 </div><div class="text-center">min</div>
                                <div class="details mt-1">
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> 5:15 am – 8:45 pm</p>
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                                </div>
                            </div>
                        </div>
                    </div> <!-- Card 6 -->
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-center">Checkpoint 5</h5>
                                <p class="card-text text-center">Standard</p>
                                <div class="fw-bold mt-3 text-center">2 </div><div class="text-center">min</div>
                                <div class="details mt-1">
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span>  4:00 am - 10:45 pm</p>
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                                </div>
                            </div>
                        </div>
                    </div> <!-- Card 7 -->
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-center">Checkpoint 6</h5>
                                <p class="card-text text-center">Standard</p>
                                <div class="fw-bold mt-3 text-center">2 </div><div class="text-center">min</div>
                                <div class="details mt-1">
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span>  3:45 am – 10:45 pm</p>
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                                </div>
                            </div>
                        </div>
                    </div>

                     <!-- Card 8 -->
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-center">Checkpoint 7</h5>
                                <p class="card-text text-center">Standard</p>
                                <div class="fw-bold mt-3 text-center">2 </div><div class="text-center">min</div>
                                <div class="details mt-1">
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> 3:30 am – 10:15 pm</p>
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                                </div>
                            </div>
                        </div>
                    </div> <!-- Card 9 -->
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-center">Checkpoint 8</h5>
                                <p class="card-text text-center">Standard</p>
                                <div class="fw-bold mt-3 text-center">2 </div><div class="text-center">min</div>
                                <div class="details mt-1">
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span> 4:00 am – 8:00 pm</p>
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                                </div>
                            </div>
                        </div>
                    </div> <!-- Card 10 -->
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-center">Checkpoint 9</h5>
                                <p class="card-text text-center">Standard</p>
                                <div class="fw-bold mt-3 text-center">2 </div><div class="text-center">min</div>
                                <div class="details mt-1">
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span>  24 Hours Open</p>
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
              <!-- Card 11 -->
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title text-center">Checkpoint 10</h5>
                                <p class="card-text text-center">Standard</p>
                                <div class="fw-bold mt-3 text-center">2 </div><div class="text-center">min</div>
                                <div class="details mt-1">
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-clock" aria-hidden="true"></span>  9:45 am – 8:00 pm</p>
                                    <p class="p-0 m-0 mobile-center"><span class="fa-solid fa-location-dot" aria-hidden="true"> </span> <a href="#" class="underline-hover">Get Directions</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                   
                </div>
            </div>
        </div>
    </div>
    
        </div>
    </div>
 </div>

 </div>



<!-- dine shop -->
<div class="container-fluid p-5 margin-dine margin-dine2">
        <div class="row">
            <div class="col-md-5">
                <div class="row">
                    
                    <div class="col-6">
                        <div class="media2 clip-bottom-left" style="background-image:url('https://miamiairport-mia.com/Pic/uploaded/airrr.webp');"></div>

                    </div>
                    <div class="col-6">
                    <div class="media3 clip-top-right" style="background-image:url('https://miamiairport-mia.com/Pic/uploaded/latest.jpg');"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-7 mob-space">
                <h2>Experience The Best Travel and beyond with MIA</h2>

                <div class="service-item">
                    <div class="service-item-font mx-3  bg-theme">
                    <span class="icon fa-classic fa-solid fa-utensils"></span>
                </div>
                    <div class="ms-2">
                        <div class="title mb-2 text-dark" style="    font-size: 25px;">MIA’s Dine-In</div>
                        <div>Get a variety of options starting from local cuisines to national classics.</div>
                    
                    </div>
                </div>
                <hr class="mt-3">
                <div class="service-item">
                <div class="service-item-font bg- mx-3 theme">
                    <span class="icon fa-classic fa-solid fa-bag-shopping"></span>

                </div>
                    <div class="ms-2">
                        <div class="title mb-2 text-dark" style="    font-size: 25px;">Shopping Stores</div>
                        <div>Varied selection of stores, discover premium apparel and distinctive mementos of Colorado.</div>
                   
                    </div>
                </div>
                <hr class="mt-3">
                <div class="service-item">
                <!-- <span class="icon fa-classic fa-solid fa-loveseat"></span>
                <span class="icon fa-classic fa-solid fa-loveseat"></span> -->
                <div class="service-item-font bg- mx-3 theme">
                    <span class="icon fa-classic fas fa-umbrella-beach m-0" style="font-size: 24px;"></span>

                </div>

                    <div class="ms-2">
                        <div class="title mb-2 text-dark" style="    font-size: 25px;">Pre-Flight Relaxation</div>
                        <div> Before your flight, enjoy some luxurious lounge treatments along with their complete amenities.</div>
               
                    </div>
                </div>
                <hr class="mt-3">
                <div class="service-item">
                    
                    <div class="service-item-font mx-3  bg-theme">
                    <span class="icon fa-classic fa-solid fa-paintbrush"></span>

                </div>
                    
                    <div class="ms-2">
                        <div class="title mb-2 text-dark" style="    font-size: 25px;">Discover Art</div>
                        <div>Explore the extensive, globally recognized art collection, with its niche in Colorado-themed exhibitions.</div>
                 
                    </div>
                </div>
                <hr class="mt-3">
                <a href="#" class="btn btn-size btn btn-custom mob-button">Explore Dine-Shop-Relax</a>
                <a href="#" class="btn btn-size btn btn-outline-secondary mob-button">Services and Amenities</a>
            </div>
        </div>
    </div>
<!-- dine shop end -->

<div class="container-fluid parking-back mt-5 p-5 margin-dine text-light">
    <div class="row">
        <div class="d-md-flex justify-content-between">
            <div class="">
            <h2>Current Wait Time</h2>
        <p>Estimated Wait Time: <?php echo htmlspecialchars($rightnow); ?> minutes</p>
        <p>Description: <?php echo htmlspecialchars($rightnowDescription); ?></p>
            </div>
            <div class="">
            <h2>Pre-Check Lanes</h2>
            <p>Number of Pre-Check Lanes: <?php echo htmlspecialchars($precheck); ?></p>
            </div>
        </div>
   

       
        <div class="">
            <h2 class="text-center fs-2">Pre-Check Checkpoints</h2>
            <table class="table">
                <thead class="text-light fs-2">
                    <tr>
                        <th class="fs-4">Terminal</th>
                        <th class="fs-4">Checkpoint</th>
                        <th class="fs-4">Status</th>
                    </tr>
                </thead>
                <tbody class="text-light">
                    <?php foreach ($precheckCheckpoints as $terminal => $checkpoints): ?>
                        <?php foreach ($checkpoints as $checkpoint => $status): ?>
                        <tr>
                            <td class="fs-4"><?php echo htmlspecialchars($terminal); ?></td>
                            <td class="fs-4"><?php echo htmlspecialchars($checkpoint); ?></td>
                            <td class="fs-4"><?php echo htmlspecialchars($status); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-5">
            <h2 class="text-center">Estimated Hourly Wait Times</h2>
            <div class="table-responsive" style="max-height: 250px; overflow-x: auto;">
                <table class="table">
                <thead class="parking-back" style="position: sticky; top: 0; ">
    <tr>
        <th class="text-light fs-4" >Time Slot</th>
        <th class="text-light fs-4">Wait Time (minutes)</th>
    </tr>
</thead>

                    <tbody>
                        <?php foreach ($estimatedHourlyTimes as $slot): ?>
                        <tr>
                            <td class="text-light fs-4"><?php echo htmlspecialchars($slot['timeslot']); ?></td>
                            <td class="text-light fs-4"><?php echo htmlspecialchars($slot['waittime']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

 <!-- others -->
<div class="container-fluid p-5 margin-dine">
<div class="text-center mb-5">
            <h2 class="fw-normal fs-1">Important Insights Before You Fly</h2>
            <div class="btn btn-size-group mt-3" role="group" aria-label="Accessibility Options">
            <a href="#" class="btn btn-size btn btn-custom mob-button">Special Services </a>
            <a href="#" class="btn btn-size btn btn-outline-secondary mob-button">Parking and Transits</a>
              
            </div>
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
<!-- others end -->

  <!-- Planning Ahead -->
  <div class="container-fluid parking-back mt-5 p-5 margin-dine">
        <div class="text-light d-flex justify-content-between">
            <h2>Plan & Get Your Parking!</h2>
            <!-- <div class="btn btn-size-group" role="group" aria-label="Navigation Options">
                <button type="button" class="btn btn-size btn btn-size-light">Parking Lots</button>
                <button type="button" class="btn btn-size btn btn-size-outline-light">Average Walking Times</button>
            </div> -->
        </div>
<p class="text-light">(Parking Information)</p>
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
        <p class="parkingNotes"><span class="fa-solid fa-mobile"></span> MIA’s free cell phone waiting lot just off of Lejeune Road & N.W. 31st Street, heading towards north and south. 
        <div>
            
        <a href="#" target="_blank" class="text-decoration-none text-light">Get Directions<span class="fa-solid fa-arrow-right-long"></span></a></p>
        </div>
    </div>
            </div>
        </div>

        

    </div>

<!-- Planning Ahead end -->





<?php
function truncateText($text, $maxLength = 100) {
    $text = strip_tags($text);
    if (strlen($text) > $maxLength) {
        $text = substr($text, 0, $maxLength) . '...';
    }
    return $text;
}
?>


 <!--  justify-content-between my-3ful resources -->
 <div class="container-fluid p-5 margin-dine">
        <div class="text-center">
            <h2>MIA’s Special Services</h2>
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
    </div>
   
        </div>

        

    </div>
 
 <!-- Helpful resources end-->

 <!-- Help -->
<div class="card parking-back banner766 d-flex justify-content-center align-items-center my-5" >
    <div class="container help-width">
                    <p class="text-center text-light fs-2">Need Help While Navigating?</p>
                    <p class=" text-center text-light">Locate your entry/exit gates, baggage claim belts, dining options, lounges, shops, and others with MIA’s interactive map.</p>
                   
                    <div class="d-flex justify-content-center align-items-center"> <a class="btn btn-size  btn-mysuccess" href="#" target="_blank">Interactive Terminal Maps</a></div>
                </div>
                
    </div>
<!-- Help end -->
<!-- Contact Section -->
<!-- <div class="container my-5">
    <div class="row">
        <div class="col-md-6">
             <h2 class="fw-bolder fs-2 text-center">Contact Us</h2>
             <p class="text-center mb-4">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quae nostrum vero quidem obcaecati quis itaque doloribus quod expedita dignissimos sapiente, dolor possimus aspernatur dolorum rem quasi maxime! Obcaecati molestias dolore cumque. Similique repudiandae excepturi, libero non beatae, vero illo quia eveniet delectus tenetur soluta eos, nulla error laboriosam. Maxime, totam.</p>
            <div class="contact-info p-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4 text-center">
                            <div class="row justify-content-center">
                                <i class="fas fa-map-marker-alt fa-2x mb-3" style="font-size:5em;"></i>
                            </div>
                            <div class="d-inline-block align-middle">
                                <p class="font-weight-bold mb-1">ADDRESS:</p>
                                <p>121 Rock Street, 21 Avenue,<br>New York, NY 92103-9000</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4 text-center">
                            <div class="row justify-content-center">
                                <i class="fas fa-envelope fa-2x mb-3" style="font-size:5em;"></i>
                            </div>
                            <div class="d-inline-block align-middle">
                                <p class="font-weight-bold mb-1">EMAIL:</p>
                                <p class="mb-0"><a href="mailto:hello@company.com" class="text-decoration-none">hello@company.com</a></p>
                                <p class="mb-0"><a href="mailto:support@company.com" class="text-decoration-none">support@company.com</a></p>
                            </div>
                        </div>
                    </div>
                </div>      

                <div class="row">
                   
                    <div class="col-md-6">
                        <div class="mb-4 text-center">
                            <div class="row justify-content-center text-center">
                            <i class="fas fa-phone-alt fa-2x mb-3 text-center" style="font-size:4em;"></i>
                            </div>
                            <div class="row ">
                            <div class="d-inline-block align-middle">
                            <p class="font-weight-bold mb-1">CALL US:</p>
                                <p class="mb-0">1 (234) 567-891</p>
                                <p class="mb-0">1 (234) 987-654</p>
                            </div>
                            </div>
                          
                           
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4 text-center" > 
                            <div class=" justify-content-center ">
                                <i class="fas fa-info-circle fa-2x mb-3" style="font-size:4em;"></i>
                            </div>
                            <div class="d-inline-block align-middle">
                                <p class="font-weight-bold mb-1">CONTACT US:</p>
                                <p>Contact us for a quote. Help or to join the team.</p>
                                <p class="d-flex justify-content-evenly">
                                    <a href="#" class="mr-2" style="font-size:1.5em;"><i class="fab fa-facebook-f"></i></a>
                                    <a href="#" class="mr-2" style="font-size:1.5em;"><i class="fab fa-twitter"></i></a>
                                    <a href="#" class="mr-2" style="font-size:1.5em;"><i class="fab fa-instagram"></i></a>
                                    <a href="#" class="mr-2" style="font-size:1.5em;"><i class="fab fa-pinterest"></i></a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
    <h2 class="fw-bolder fs-2 text-center">Map</h2>
    <p class="text-center mb-4">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quae nostrum vero quidem obcaecati quis itaque doloribus quod expedita dignissimos sapiente, dolor possimus aspernatur dolorum rem quasi maxime! Obcaecati molestias dolore cumque. Similique repudiandae excepturi, libero non beatae, vero illo quia eveniet delectus tenetur soluta eos, nulla error laboriosam. Maxime, totam.</p>


        </div>
    </div>
</div>
 -->
<!-- Contact Section End -->
<!-- Blogs -->
<div class="container-fluid p-md-5 p-4">
    <h2 class="fw-bolder fs-3 text-center">Latest <span class="theme-color">Blogs</span></h2>
    <p class="text-center mb-4 fs-3">Discover the Heartbeat of Travel: Miami International Airport</p>
    <div class="row g-4">
        <?php
        // Retrieve the parent page ID of "Our Blogs"
        $parentPageTitle = "Our Blog"; // Update with the title of the parent page
        $parentPageId = null;

        $parentPageStmt = $conn->prepare("SELECT id FROM pages WHERE title = ?");
        $parentPageStmt->bind_param("s", $parentPageTitle);
        $parentPageStmt->execute();
        $parentPageStmt->bind_result($parentPageId);
        $parentPageStmt->fetch();
        $parentPageStmt->close();

        if ($parentPageId) {
            // Parent page ID found, query the database for child pages ordered by date
            $childPagesStmt = $conn->prepare("SELECT * FROM pages WHERE parent_id = ? ORDER BY date DESC LIMIT 5");
            $childPagesStmt->bind_param("i", $parentPageId);
            $childPagesStmt->execute();
            $childPagesResult = $childPagesStmt->get_result();

            // Display the child pages
            $count = 0; // Initialize a counter for the number of child pages displayed
            if ($childPagesResult->num_rows > 0) {
                while ($childPageRow = $childPagesResult->fetch_assoc()) {
                    if ($count == 0) {
                        $truncatedContent = truncateText($childPageRow['content'], 150);
                        $truncatedContentsmall = truncateText($childPageRow['content'], 50);

                        // Display the latest blog in col-md-6
                        ?>
                        <div class="col-md-6 p-0">
                            <a href="<?php echo generatePageUrl($childPageRow['id']); ?>" class="text-decoration-none text-dark">
                                <div class="card h-100 border-0 m-search-container">
                                    <img src="<?php echo $childPageRow['image']; ?>" class="card-img-top rounded-2 blog-image3 object-fit-cover" 
    object-fit: cover; alt="...">
                                    <div class="card-body p-0">
                                    <small class="badge theme-bg my-2"><?php echo $childPageRow['date']; ?></small>

                                        <h5 class="card-title my-3 blog-mob-font"><?php echo $childPageRow['title']; ?></h5>
                                        <p class="fs-6"><?php echo $truncatedContent; ?></p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php
                    } else {
                        // Display the next four latest blogs in col-md-6
                        if ($count == 1) {
                            echo '<div class="col-md-6">';
                        }
                        ?>
                       <div class="row rounded-2 mb-4 m-search-container">
                            <div class="col-md-5">
                                <a href="<?php echo generatePageUrl($childPageRow['id']); ?>" class="text-decoration-none text-dark ">
                                    <div class="card rounded-2">
                                        <img src="<?php echo $childPageRow['image']; ?>" class="card-img-top rounded-2 blog-image4 object-fit-cover"  alt="...">
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-7">
                                <a href="<?php echo generatePageUrl($childPageRow['id']); ?>" class="text-decoration-none text-dark">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $childPageRow['title']; ?></h5>
                                        <p class="fs-6 my-3"><?php echo $truncatedContentsmall; ?></p>
                                        <small class="badge theme-bg "><?php echo $childPageRow['date']; ?></small>


                                    </div>
                                </a>
                            </div>
                        </div>
                        <?php
                        if ($count == 4) {
                            echo '</div>';
                        }
                    }
                    $count++;
                }
            } else {
                echo "<p>No child pages found for Our Blogs.</p>";
            }
            $childPagesStmt->close();
        } else {
            echo "<p>Parent page 'Our Blogs' not found.</p>";
        }
        ?>
    </div>
</div>

    
    <!-- "Read more" button -->
    <div class="row mt-4">
        <div class="col text-center">
            <a href="<?php echo generatePageUrl($parentPageId); ?>" class="btn btn-size theme-bg text-light rounded-3">Read more</a>
        </div>
    </div>
</div>

<!-- Blogs End -->

<!-- Subscribe -->
<div class="card parking-back banner766  my-5" >
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
 <!--<button onclick="topFunction()" id="backToTop" title="Go to top">-->
 <!--   <i class="fas fa-arrow-up"></i>-->
 <!-- </button>-->

<?php include 'footer.php'; ?>
  <!--<script>-->
  <!--  window.onscroll = function() { scrollFunction() };-->

  <!--  function scrollFunction() {-->
  <!--    const backToTopButton = document.getElementById("backToTop");-->
  <!--    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {-->
  <!--      backToTopButton.style.display = "block";-->
  <!--    } else {-->
  <!--      backToTopButton.style.display = "none";-->
  <!--    }-->
  <!--  }-->

  <!--  function topFunction() {-->
      <!--document.body.scrollTop = 0; // For Safari-->
      <!--document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE, and Opera-->
  <!--  }-->
  <!--</script>-->
 <!-- Font Awesome JS -->
 <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>    
 <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
 <!-- jQuery and Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // Define the placeholders for each tab
            const placeholders = {
                'departure': 'Search for departures...',
                'arrival': 'Search for arrivals...',
                'connections': 'Search for connections...'
            };

            // Define the href values for each tab
            const hrefValues = {
                'departure': 'flights-departures',
                'arrival': 'flights-arrivals',
                'connections': 'flights-connections'
            };

            // Handle tab switch
            $('#flightInfoTabs a').on('shown.bs.tab', function(event) {
                const target = $(event.target).attr('aria-controls'); // Get the target tab id
                $('#commonSearchBar').attr('placeholder', placeholders[target]); // Update the placeholder
                $('#more-link').attr('href', hrefValues[target]); // Update the href value
            });
        

        $('#searchButton').on('click', function() {
                var searchText = $('#commonSearchBar').val(); // Get the search input value
                var activeTab = $('#flightInfoTabs .active').attr('aria-controls'); // Get the active tab

                if (activeTab === 'departure') {
                    window.location.href = 'flights-departures?search=' + encodeURIComponent(searchText);
                } else if (activeTab === 'arrival') {
                    window.location.href = 'flights-arrivals?search=' + encodeURIComponent(searchText);
                }
            });
        });
    </script>
</body>
</html>