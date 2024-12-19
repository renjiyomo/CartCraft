<?php
// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "shipping_db";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Functions

// Get Coordinates Using Google Maps API
function getCoordinates($address) {
    $apiKey = 'YOUR_GOOGLE_MAPS_API_KEY';
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=$apiKey";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if ($data['status'] == 'OK') {
        $location = $data['results'][0]['geometry']['location'];
        return ['lat' => $location['lat'], 'lng' => $location['lng']];
    }
    return false;
}

// Calculate Distance (Haversine Formula)
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Radius in km
    $latDelta = deg2rad($lat2 - $lat1);
    $lonDelta = deg2rad($lon2 - $lon1);

    $a = sin($latDelta / 2) * sin($latDelta / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($lonDelta / 2) * sin($lonDelta / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c;
}

// Fetch Shipping Fee Based on Distance
function getShippingFee($distance, $conn) {
    $sql = "SELECT fee FROM shipping_fees WHERE ? BETWEEN min_distance AND max_distance LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("d", $distance);
    $stmt->execute();
    $stmt->bind_result($fee);
    $stmt->fetch();
    $stmt->close();

    return $fee ?: 0; // Default fee if not found
}

// Example Input
$userAddress = "Daraga, Albay, Philippines";
$albayCoords = ['lat' => 13.2922, 'lng' => 123.5059]; // Central Albay

// Main Logic
$userCoords = getCoordinates($userAddress);

if ($userCoords) {
    $distance = calculateDistance($albayCoords['lat'], $albayCoords['lng'], $userCoords['lat'], $userCoords['lng']);
    $fee = getShippingFee($distance, $conn);

    // Output Results
    echo "<h3>Shipping Calculation</h3>";
    echo "<p>Distance from Albay: " . round($distance, 2) . " km</p>";
    echo "<p>Shipping Fee: ₱" . number_format($fee, 2) . "</p>";
} else {
    echo "<p>Unable to retrieve coordinates for the address.</p>";
}

// Close Connection
$conn->close();
?>
