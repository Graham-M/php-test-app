<?php
// Connect to MySQL
$mysqli = new mysqli("localhost", "username", "password", "weather_db");

// Check connection
if ($mysqli->connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli->connect_error;
  exit();
}

// Connect to Redis
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

// Set Redis key prefix
$key_prefix = 'weather_';

// Get cities from Redis cache, if available
$cities = $redis->get($key_prefix . 'cities');

// If cities are not cached in Redis, retrieve them from the database and cache them
if (!$cities) {
  $cities_result = $mysqli->query("SELECT DISTINCT city FROM weather");

  if ($cities_result->num_rows > 0) {
    $cities = [];
    while($row = $cities_result->fetch_assoc()) {
      $cities[] = $row['city'];
    }

    $redis->set($key_prefix . 'cities', json_encode($cities));
  }
} else {
  $cities = json_decode($cities);
}

// Create city dropdown
echo '<label for="city">City:</label>';
echo '<select id="city" name="city">';
echo '<option value="">Select a city</option>';

foreach ($cities as $city) {
  echo '<option value="' . $city . '">' . $city . '</option>';
}

echo '</select>';

// Get min and max dates from database
$dates_result = $mysqli->query("SELECT MIN(date) as min_date, MAX(date) as max_date FROM weather");

// Get min and max dates from Redis cache, if available
$min_date = $redis->get($key_prefix . 'min_date');
$max_date = $redis->get($key_prefix . 'max_date');

// If min and max dates are not cached in Redis, retrieve them from the database and cache them
if (!$min_date || !$max_date) {
  if ($dates_result->num_rows > 0) {
    $dates_row = $dates_result->fetch_assoc();
    $min_date = $dates_row['min_date'];
    $max_date = $dates_row['max_date'];
    $redis->set($key_prefix . 'min_date', $min_date);
    $redis->set($key_prefix . 'max_date', $max_date);
  }
}

// Create date dropdowns
echo '<br><br>';
echo '<label for="start_date">Start date:</label>';
echo '<input type="date" id="start_date" name="start_date" value="' . $min_date . '" min="' . $min_date . '" max="' . $max_date . '">';

echo '<br><br>';
echo '<label for="end_date">End date:</label>';
echo '<input type="date" id="end_date" name="end_date" value="' . $max_date . '" min="' . $min_date . '" max="' . $max_date . '">';

// Get weather data from database and cache it in Redis
function getWeatherData($mysqli, $redis, $key_prefix, $city, $start_date, $end_date) {
  // Construct query
  $query = "SELECT * FROM weather WHERE 1=1";
  if ($city) {
    $query .= " AND city = '$city'";
  }
  if ($start_date) {
    $query .= " AND date >= '$start_date'";
  }
  if ($end_date) {
    $query .= " AND date <= '$end_date'";
}

// Check if data is cached in Redis
$key = $key_prefix . md5($query);
$weather_data = $redis->get($key);

if (!$weather_data) {
// Retrieve data from database
$result = $mysqli->query($query);


  // Check if there are any rows returned
  if ($result->num_rows > 0) {
    // Loop through rows and add them to the result array
    while($row = $result->fetch_assoc()) {
      $weather_data[] = $row;
    }
  
    // Cache data in Redis for 5 minutes
    $redis->setex($key, 300, serialize($weather_data));
   }

} else {
// If data is cached in Redis, unserialize it
$weather_data = unserialize($weather_data);
}

return $weather_data;
}

// Get selected city and date range from dropdowns
$city = isset($_GET['city']) ? $_GET['city'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Get weather data from cache or database
$weather_data = getWeatherData($mysqli, $redis, $key_prefix, $city, $start_date, $end_date);

// Print weather data table
if ($weather_data) {
echo '<br><br>';
echo '<table>';
echo '<tr>';
echo '<th>City</th>';
echo '<th>Date</th>';
echo '<th>Hour</th>';
echo '<th>Temperature</th>';
echo '<th>Precipitation</th>';
echo '<th>Humidity</th>';
echo '</tr>';

foreach ($weather_data as $row) {
echo '<tr>';
echo '<td>' . $row['city'] . '</td>';
echo '<td>' . $row['date'] . '</td>';
echo '<td>' . $row['hour'] . '</td>';
echo '<td>' . $row['temperature'] . '</td>';
echo '<td>' . $row['precipitation'] . '</td>';
echo '<td>' . $row['humidity'] . '</td>';
echo '</tr>';
}

echo '</table>';
} else {
echo '<br><br>No weather data found.';
}

// Close database connection
$mysqli->close();

// Close Redis connection
$redis->close();



