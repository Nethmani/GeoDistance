# GeoDistance

A professional, framework-free PHP + MySQL web application that calculates straight-line geographic distance between two latitude/longitude pairs. It is designed as a pre-interview assignment submission.

## Features

- Four coordinate inputs with JavaScript and PHP validation.
- Custom PHP Haversine calculation—no geographic library, map API, or external distance service.
- Kilometre result, simplified SVG coordinate visualisation, and reset flow.
- MySQL persistence through `sp_save_distance_calculation`, plus recent history.
- Responsive, accessible vanilla HTML/CSS/JavaScript UI.

## Technology stack

PHP 8+, MySQL 8+/MariaDB, PDO, HTML, CSS, and vanilla JavaScript. There are no packages or build steps.

## How it works

`assets/js/app.js` validates the form, sends an AJAX `POST` request to `api/calculate.php`, and renders the result. PHP validates again in `functions/distance.php`, then runs this manually implemented Haversine formula:

`a = sin²(Δφ/2) + cos(φ1) × cos(φ2) × sin²(Δλ/2)`

`distance = 6371.0 × 2 × atan2(√a, √(1-a))`

This is a great-circle distance, not driving/road distance. The API calls a MySQL stored procedure rather than performing a direct insert. The SVG is only a simple equirectangular visualisation and never calculates distance.

## Project structure

```
GeoDistance/
├── index.php                   UI
├── api/                        JSON calculation and history endpoints
├── assets/css/style.css         responsive styling
├── assets/js/app.js             validation, fetch, UI states, SVG
├── config/database.php          central PDO settings
├── functions/distance.php       validation and custom Haversine logic
└── database/geo_distance_db.sql schema and stored procedure
```

## Run with XAMPP

1. Copy `GeoDistance` into `C:\xampp\htdocs` (or set an Apache virtual host to it).
2. Start Apache and MySQL in XAMPP.
3. Import `database/geo_distance_db.sql` with phpMyAdmin or MySQL.
4. Adjust `config/database.php` if your local MySQL credentials differ.
5. Browse to `http://localhost/GeoDistance/`.

## Validation and edge cases

Latitude is limited to -90..90 and longitude to -180..180. Blank, whitespace-only, malformed, non-numeric, missing, and out-of-range values are rejected in both JavaScript and PHP. Positive/negative/zero and decimal coordinates, equatorial/polar points, matching locations, and international-date-line coordinates are supported.

### Testing checklist

| Test | Expected result |
|---|---|
| Same point: `0, 0` → `0, 0` | `0.00 KM` |
| Positive: Colombo → Kandy | Successful result near 94 km |
| Negative coordinates and both-negative values | Accepted and calculated |
| Equator and ±90° poles | Accepted and calculated |
| Longitudes near ±180° | Correct Haversine result |
| 91, -91, 181, -181 | Appropriate range error |
| Blank/alphabetic/malformed decimal | Friendly validation error |
| Database failure | Friendly save error; technical detail only in PHP log |

## Security and persistence

PDO uses native prepared statements; client input is never trusted; credentials remain server-side; database exceptions are logged but not shown to users. Coordinates and distance use numeric `DECIMAL` columns. The importable SQL file creates `distance_calculations` and `sp_save_distance_calculation`.

## Assignment requirement mapping

| Requirement | Implementation |
|---|---|
| Latitude/longitude input and submit | `index.php` form |
| Custom calculation | `functions/distance.php` Haversine function |
| Display in KM | API response rendered by `app.js` |
| Server validation | `parseCoordinate()` |
| MySQL storage | `distance_calculations` |
| Stored procedure | `sp_save_distance_calculation`, called by `api/calculate.php` |
| Positive, negative, equatorial, polar, invalid inputs | Dual validation and Haversine formula |
| History | `api/history.php` and recent-history UI |
| Custom visual representation | Inline SVG updated by `app.js` |
