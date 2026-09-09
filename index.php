<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Calculate straight-line geographic distance between two coordinates.">
    <title>GeoDistance | Geographic Distance Calculator</title>
    <link rel="icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="#calculator" aria-label="GeoDistance home"><span aria-hidden="true">◉</span> GeoDistance</a>
        <nav aria-label="Primary navigation"><a href="#calculator">Calculator</a><a href="#history">History</a><a href="#about">About</a></nav>
    </header>
    <main>
        <section class="hero" aria-labelledby="page-title">
            <p class="eyebrow">Geographic Distance Calculator</p>
            <h1 id="page-title">Calculate distance between two locations</h1>
            <p>Enter latitude and longitude coordinates to find the straight-line distance over Earth’s surface.</p>
        </section>

        <section id="calculator" class="calculator-layout" aria-label="Distance calculator">
            <form id="distance-form" class="card calculator-card" novalidate>
                <div class="card-heading"><div><p class="eyebrow">Coordinates</p><h2>Set your two locations</h2></div><button class="text-button" type="button" id="example-button">Use Colombo → Kandy</button></div>
                <div id="form-message" class="message" role="alert" aria-live="assertive" hidden></div>
                <div class="locations-grid">
                    <fieldset><legend><span class="location-dot a"></span> Location A</legend>
                        <label for="location_a_latitude">Latitude <span aria-hidden="true">(−90 to 90)</span></label>
                        <input id="location_a_latitude" name="location_a_latitude" type="text" inputmode="decimal" autocomplete="off" placeholder="e.g. 6.9271" required>
                        <label for="location_a_longitude">Longitude <span aria-hidden="true">(−180 to 180)</span></label>
                        <input id="location_a_longitude" name="location_a_longitude" type="text" inputmode="decimal" autocomplete="off" placeholder="e.g. 79.8612" required>
                    </fieldset>
                    <fieldset><legend><span class="location-dot b"></span> Location B</legend>
                        <label for="location_b_latitude">Latitude <span aria-hidden="true">(−90 to 90)</span></label>
                        <input id="location_b_latitude" name="location_b_latitude" type="text" inputmode="decimal" autocomplete="off" placeholder="e.g. 7.2906" required>
                        <label for="location_b_longitude">Longitude <span aria-hidden="true">(−180 to 180)</span></label>
                        <input id="location_b_longitude" name="location_b_longitude" type="text" inputmode="decimal" autocomplete="off" placeholder="e.g. 80.6337" required>
                    </fieldset>
                </div>
                <div class="form-actions"><button id="submit-button" class="primary-button" type="submit">Calculate distance</button><button id="clear-button" class="secondary-button" type="button">Clear</button></div>
                <p class="form-note">Latitude accepts −90 to 90°; longitude accepts −180 to 180°. Both positive and negative decimal values are supported.</p>
            </form>

            <aside class="card visual-card" aria-labelledby="visual-title">
                <p class="eyebrow">Simplified view</p><h2 id="visual-title">Coordinate overview</h2>
                <svg id="map" viewBox="0 0 640 320" role="img" aria-label="Simplified coordinate map showing the route and direction from Location A to Location B">
                    <rect class="map-bg" width="640" height="320" rx="16"/>
                    <g class="grid"><path d="M0 80H640M0 160H640M0 240H640M160 0V320M320 0V320M480 0V320"/><path class="equator" d="M0 160H640"/></g>
                    <g class="map-compass" role="group" aria-label="Compass: north is up, east is right, south is down, and west is left">
                        <circle class="map-compass-face" cx="600" cy="58" r="22"/>
                        <path class="map-compass-north" d="M600 38 L606 58 L600 54 L594 58 Z"/>
                        <path class="map-compass-south" d="M600 78 L594 58 L600 62 L606 58 Z"/>
                        <path id="map-compass-course" class="map-compass-course" d="M600 35 L607 60 L600 55 L593 60 Z"/>
                        <text class="map-compass-label map-compass-n" x="600" y="25" text-anchor="middle">N</text>
                        <text class="map-compass-label map-compass-e" x="628" y="62" text-anchor="middle">E</text>
                        <text class="map-compass-label map-compass-s" x="600" y="96" text-anchor="middle">S</text>
                        <text class="map-compass-label map-compass-w" x="572" y="62" text-anchor="middle">W</text>
                    </g>
                    <g id="map-content"><text x="320" y="154" text-anchor="middle">Enter coordinates to visualise them</text></g>
                </svg>
                <section id="map-insight" class="map-insight" aria-live="polite" hidden>
                    <p class="map-route-title">Location A <span aria-hidden="true">→</span> Location B</p>
                    <p id="map-direction" class="map-direction"></p>
                    <div class="map-metrics">
                        <div><span>Bearing</span><strong id="map-bearing">—</strong></div>
                        <div><span>Calculated distance</span><strong id="map-distance">—</strong></div>
                    </div>
                </section>
                <div id="map-coordinate-summary" class="map-coordinate-summary" aria-live="polite" hidden></div>
                <p class="visual-note">A simplified equirectangular view for orientation only. Distance always uses the Haversine formula.</p>
            </aside>
        </section>

        <section id="result-section" class="result-card" aria-live="polite" hidden>
            <p class="eyebrow">Distance</p><output id="distance-output">0.00 KM</output><p id="result-detail">Between Location A and Location B</p>
        </section>

        <section id="history" class="card history-card" aria-labelledby="history-title">
            <div class="card-heading"><div><p class="eyebrow">Saved in MySQL</p><h2 id="history-title">Recent calculations</h2></div><span id="history-status" class="muted">Loading…</span></div>
            <div class="history-wrap"><table><thead><tr><th>Date & time</th><th>Location A</th><th>Location B</th><th>Distance</th></tr></thead><tbody id="history-body"></tbody></table></div>
        </section>

        <section id="about" class="about" aria-labelledby="about-title"><p class="eyebrow">About the calculation</p><h2 id="about-title">Straight-line distance, calculated locally</h2><p>GeoDistance uses a custom PHP implementation of the Haversine formula with an Earth radius of 6,371 km. It calculates a geographic great-circle distance, not driving or road distance. Valid submissions are saved through a MySQL stored procedure.</p></section>
    </main>
    <footer>GeoDistance · 2026 · </footer>
    <script src="assets/js/app.js" defer></script>
</body>
</html>
