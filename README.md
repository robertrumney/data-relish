# Data Relish

Data Relish is an open-source, self-hosted PHP + MySQL analytics system for tracking and visualizing web activity. It provides simple event logging and a clean dashboard with charts, all without external dependencies or vendor lock-in.

## Features

- Track visits and custom events with a single PHP function
- Dashboard with unique visitors per day, event breakdowns, and top countries using Chart.js
- "Enrich Country Data" button resolves IPs to countries directly from the dashboard
- No built-in branding or forced reporting

## Setup

1. Import `data-relish.sql` into your MySQL database to create the required table.
2. Set your database connection details at the top of `log_event.php` and `dashboard.php`.
3. Add `include('log_event.php');` and `log_event("visit", "yourpage");` to any PHP page you want to track.
4. Open `dashboard.php?pass=changeme` in your browser. Change the password in the file.
5. Click the Enrich Country Data button on the dashboard to populate country info, or run `update_countries.php` directly.

## Files

- `data-relish.sql` – MySQL schema
- `log_event.php` – Database connection and event logging function
- `dashboard.php` – Analytics dashboard and chart display
- `update_countries.php` – Script for updating country data from IP addresses
