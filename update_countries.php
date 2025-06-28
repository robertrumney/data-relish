<?php

  // Database connection
  $mysqli = new mysqli('DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME');
  if ($mysqli->connect_error) {
      die('Connection failed: ' . $mysqli->connect_error);
  }
  
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  
  // Helper function to fetch country for an IP
  function get_country_by_ip($ip) {
      $url = "http://ip-api.com/json/" . urlencode($ip);
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
      $resp = curl_exec($ch);
      curl_close($ch);
  
      if ($resp === false) return null;
      $data = json_decode($resp, true);
      if (!$data || !isset($data['country']) || !$data['country']) return null;
      return $data['country'];
  }
  
  // Fetch all rows where country is missing
  $res = $mysqli->query("SELECT id, ip_address FROM analytics_events WHERE country IS NULL OR country = '' OR country = 'unknown'");
  if (!$res) {
      die("Query failed: " . $mysqli->error);
  }
  
  while ($row = $res->fetch_assoc()) {
      $id = (int)$row['id'];
      $ip = $row['ip_address'];
      $country = get_country_by_ip($ip);
      if ($country) {
          $country_esc = $mysqli->real_escape_string($country);
          $mysqli->query("UPDATE analytics_events SET country='$country_esc' WHERE id=$id");
          echo "Updated $ip to $country<br>";
      } else {
          echo "No country info for $ip<br>";
      }
  }
  
  echo "Done.";
  
  $mysqli->close();

?>
