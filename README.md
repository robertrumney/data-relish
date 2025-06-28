# Data Relish

Data Relish is an open-source, self-hosted PHP + MySQL analytics system for tracking and visualizing web activity. It provides simple event logging, I.P. metrics, visitt, users & activity.

## Features

- Track visits and custom events with a single PHP function
- Dashboard with unique visitors per day, event breakdowns, and top countries using Chart.js
- "Enrich Country Data" button resolves IPs to countries directly from the dashboard

## Setup

1. Import `data-relish.sql` into your MySQL database to create the required table.
2. Set your database connection details at the top of `log_event.php` and `dashboard.php`.
3. Add `include('log_event.php');` and `log_event("event type", "event target");` to any event you want to track.
4. Open `dashboard.php?pass=changeme` in your browser. Change the password in the file.
5. Click the Enrich Country Data button on the dashboard to populate country info, or run `update_countries.php` directly.

## Files

- `data-relish.sql` – MySQL schema
- `log_event.php` – Database connection and event logging function
- `dashboard.php` – Analytics dashboard and chart display
- `update_countries.php` – Script for updating country data from IP addresses

  This project is still in development, use this code at your own risk. Please feel free to contribute!

## Live Example

I am using this on my free plugin website, I don't have a problem sharing this dashboard as there isn't any real sensitive information in this case.
https://zerovst.com/admin/data/stats.php?pass=jabberwocky!20o2
