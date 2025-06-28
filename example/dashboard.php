<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// --- SIMPLE PASSWORD PROTECTION ---
if (!isset($_GET['pass']) || $_GET['pass'] !== 'changeme') {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

// --- DB CONNECTION ---
$mysqli = new mysqli('DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME');
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// --- STATS QUERIES ---
try {
    // Unique visitors per day
    $dates = [];
    $visitors = [];
    $res = $mysqli->query("
        SELECT DATE(timestamp) AS date, COUNT(DISTINCT ip_address) AS unique_visitors
        FROM analytics_events
        GROUP BY DATE(timestamp)
        ORDER BY DATE(timestamp)
    ");
    while ($r = $res->fetch_assoc()) {
        $dates[] = $r['date'];
        $visitors[] = (int)$r['unique_visitors'];
    }
    $res->free();

    // Plugin visits by date
    $plugin_date_data = [];
    $plugin_names = [];
    $date_labels = [];
    $res2 = $mysqli->query("
        SELECT DATE(timestamp) AS date, target, COUNT(*) AS visits
        FROM analytics_events
        WHERE event_type='visit'
        GROUP BY date, target
        ORDER BY date ASC
    ");
    while ($r = $res2->fetch_assoc()) {
        $date = $r['date'];
        $plugin = $r['target'];
        $visits = (int)$r['visits'];
        $plugin_date_data[$plugin][$date] = $visits;
        $plugin_names[$plugin] = true;
        $date_labels[$date] = true;
    }
    $res2->free();

    $plugin_names = array_keys($plugin_names);
    $date_labels = array_keys($date_labels);
    sort($date_labels);

    // Build stacked bar datasets for chart.js
    $plugin_datasets = [];
    foreach ($plugin_names as $plugin) {
        $data = [];
        foreach ($date_labels as $date) {
            $data[] = isset($plugin_date_data[$plugin][$date]) ? $plugin_date_data[$plugin][$date] : 0;
        }
        $plugin_datasets[] = [
            'label' => $plugin,
            'data' => $data,
            'stack' => 'stack1'
        ];
    }

    // Country data
    $countries = [];
    $country_visits = [];
    $res3 = $mysqli->query("
        SELECT country, COUNT(*) AS visits
        FROM analytics_events
        WHERE country IS NOT NULL AND country != ''
        GROUP BY country
        ORDER BY visits DESC
        LIMIT 30
    ");
    while ($r = $res3->fetch_assoc()) {
        $countries[] = $r['country'];
        $country_visits[] = (int)$r['visits'];
    }
    $res3->free();

} catch (Exception $e) {
    echo '<h2>Database Error</h2>';
    echo '<pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES) . "\n";
    echo 'Code: ' . $e->getCode() . "\n";
    echo 'File: ' . $e->getFile() . ':' . $e->getLine() . '</pre>';
    exit;
}

$mysqli->close();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Data Relish Analytics Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    body { font-family: Arial, sans-serif; padding: 30px; background: #111; color: #eee; }
    h2 { margin-top: 40px; }
    .country-table { margin-top:20px;border-collapse:collapse;width:100%;background:#222;color:#eee; }
    .country-table th,.country-table td { border:1px solid #333;padding:8px;text-align:left;}
    .country-table th { background: #333; }
    .button {
        display:inline-block;
        padding:10px 20px;
        margin:20px 0;
        background:#2a2;
        color:#fff;
        border:none;
        border-radius:5px;
        cursor:pointer;
        font-size:1rem;
        transition:background 0.2s;
    }
    .button:hover { background:#191; }
    #enrich-status { margin-left:15px;font-size:0.95em;color:#ccc;}
</style>
</head>
<body>
<h1>Data Relish Analytics Dashboard</h1>
<button class="button" id="enrich-btn">Enrich Country Data</button><span id="enrich-status"></span>

<h2>Unique Visitors per Day</h2>
<canvas id="visitorsChart"></canvas>

<h2>Plugin Popularity by Date</h2>
<canvas id="pluginsStackedChart"></canvas>

<h2>Top 30 Countries</h2>
<table class="country-table">
    <thead>
        <tr><th>Country</th><th>Visits</th></tr>
    </thead>
    <tbody>
        <?php foreach ($countries as $i => $c): ?>
            <tr><td><?php echo htmlspecialchars($c); ?></td><td><?php echo $country_visits[$i]; ?></td></tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
const dates = <?php echo json_encode($dates); ?>;
const visitors = <?php echo json_encode($visitors); ?>;
const pluginLabels = <?php echo json_encode($date_labels); ?>;
const pluginDatasets = <?php echo json_encode($plugin_datasets); ?>;

new Chart(
  document.getElementById('visitorsChart').getContext('2d'),
  {
    type: 'line',
    data: { labels: dates, datasets: [{ label: 'Unique Visitors', data: visitors, fill: false }] },
    options: { responsive: true }
  }
);

new Chart(
  document.getElementById('pluginsStackedChart').getContext('2d'),
  {
    type: 'bar',
    data: { labels: pluginLabels, datasets: pluginDatasets },
    options: {
        responsive: true,
        plugins: { title: { display: false } },
        scales: { x: { stacked: true }, y: { stacked: true } }
    }
  }
);

// Enrich button AJAX call
document.getElementById('enrich-btn').onclick = function() {
    var btn = this;
    btn.disabled = true;
    document.getElementById('enrich-status').textContent = 'Enriching...';
    fetch('update_countries.php')
        .then(resp => resp.text())
        .then(txt => {
            document.getElementById('enrich-status').textContent = 'Done. Refresh to see updated countries.';
            btn.disabled = false;
        })
        .catch(err => {
            document.getElementById('enrich-status').textContent = 'Error running enrichment.';
            btn.disabled = false;
        });
};
</script>
</body>
</html>
