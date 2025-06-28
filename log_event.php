<?php

  // Fill in your credentials
  $mysqli = new mysqli('DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME');

  if ($mysqli->connect_error) 
  {
      die('Connection failed: ' . $mysqli->connect_error);
  }
  
  function log_event($type, $target)
  {
      global $mysqli;
      $ua = $_SERVER['HTTP_USER_AGENT'];
      $ip = $_SERVER['REMOTE_ADDR'];
      $country = "";
      $stmt = $mysqli->prepare("INSERT INTO analytics_events (event_type, target, user_agent, ip_address, country) VALUES (?, ?, ?, ?, ?)");
      $stmt->bind_param("sssss", $type, $target, $ua, $ip, $country);
      $stmt->execute();
      $stmt->close();
  }
?>
