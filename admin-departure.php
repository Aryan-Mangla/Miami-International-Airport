<?php

include_once 'config.php';

// Replace with your actual access key and API URL
$accessKey = 'ef2797c9fefecf3e0f5262500bf6236b';
$url = 'http://api.aviationstack.com/v1/flights';

// Parameters for the API request
$params = [
    'access_key' => $accessKey,
    'limit' => 100,
    'dep_iata' => 'MIA'
];

// Build the query string
$queryString = http_build_query($params);

// Make the API request and get JSON response
$response = file_get_contents($url . '?' . $queryString);

// Check if API request was successful
if ($response === false) {
    die('Error fetching data from API');
}

// Decode JSON response into PHP associative array
$data = json_decode($response, true);

// Check if decoding was successful
if (!$data || !isset($data['data'])) {
    die('Error parsing JSON');
}


// Insert data into database
$stmt = $conn->prepare("INSERT INTO flight_data_dep (data) VALUES (?)");
$jsonData = json_encode($data['data']);
$stmt->bind_param('s', $jsonData);
$stmt->execute();

// Close the connection
$stmt->close();
$conn->close();
// Redirect to data.php where data will be displayed
header('Location: flights-departures');

exit;
?>
