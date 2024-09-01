<?php
// Your API key
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airport Details</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="my-4">Airport Details</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>Airport Code</th>
                    <th>Name</th>
                    <th>City</th>
                    <th>State</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars($code); ?></td>
                    <td><?php echo htmlspecialchars($name); ?></td>
                    <td><?php echo htmlspecialchars($city); ?></td>
                    <td><?php echo htmlspecialchars($state); ?></td>
                </tr>
            </tbody>
        </table>

        <h2>Current Wait Time</h2>
        <p>Estimated Wait Time: <?php echo htmlspecialchars($rightnow); ?> minutes</p>
        <p>Description: <?php echo htmlspecialchars($rightnowDescription); ?></p>

        <h2>Pre-Check Lanes</h2>
        <p>Number of Pre-Check Lanes: <?php echo htmlspecialchars($precheck); ?></p>

        <h2>FAA Alerts</h2>

        <h3>Ground Stops</h3>
<?php if (!empty($faaAlerts['ground_stops']['reason']) || !empty($faaAlerts['ground_stops']['end_time'])): ?>
    <p>Reason: <?php echo htmlspecialchars($faaAlerts['ground_stops']['reason'] ?? 'No information available'); ?></p>
    <p>End Time: <?php echo htmlspecialchars($faaAlerts['ground_stops']['end_time'] ?? 'No information available'); ?></p>
<?php else: ?>
    <p>No ground stops information available.</p>
<?php endif; ?>

<h3>Ground Delays</h3>
<?php if (!empty($faaAlerts['ground_delays']['reason']) || !empty($faaAlerts['ground_delays']['average'])): ?>
    <p>Reason: <?php echo htmlspecialchars($faaAlerts['ground_delays']['reason'] ?? 'No information available'); ?></p>
    <p>Average Delay: <?php echo htmlspecialchars($faaAlerts['ground_delays']['average'] ?? 'No information available'); ?></p>
<?php else: ?>
    <p>No ground delays information available.</p>
<?php endif; ?>

<h3>General Delays</h3>
<?php if (!empty($faaAlerts['general_delays']['reason']) || !empty($faaAlerts['general_delays']['trend'])): ?>
    <p>Reason: <?php echo htmlspecialchars($faaAlerts['general_delays']['reason'] ?? 'No information available'); ?></p>
    <p>Trend: <?php echo htmlspecialchars($faaAlerts['general_delays']['trend'] ?? 'No information available'); ?></p>
<?php else: ?>
    <p>No general delays information available.</p>
<?php endif; ?>
        <h2>Estimated Hourly Wait Times</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Time Slot</th>
                    <th>Wait Time (minutes)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($estimatedHourlyTimes as $slot): ?>
                <tr>
                    <td><?php echo htmlspecialchars($slot['timeslot']); ?></td>
                    <td><?php echo htmlspecialchars($slot['waittime']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Pre-Check Checkpoints</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Terminal</th>
                    <th>Checkpoint</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($precheckCheckpoints as $terminal => $checkpoints): ?>
                    <?php foreach ($checkpoints as $checkpoint => $status): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($terminal); ?></td>
                        <td><?php echo htmlspecialchars($checkpoint); ?></td>
                        <td><?php echo htmlspecialchars($status); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>


