(() => {
  const form = document.querySelector('#distance-form');
  const fields = [...form.querySelectorAll('input')];
  const message = document.querySelector('#form-message');
  const submit = document.querySelector('#submit-button');
  const result = document.querySelector('#result-section');
  const mapContent = document.querySelector('#map-content');
  const mapCourse = document.querySelector('#map-compass-course');
  const mapInsight = document.querySelector('#map-insight');
  const mapDirection = document.querySelector('#map-direction');
  const mapBearing = document.querySelector('#map-bearing');
  const mapDistance = document.querySelector('#map-distance');
  const mapSummary = document.querySelector('#map-coordinate-summary');
  const historyBody = document.querySelector('#history-body');
  const historyStatus = document.querySelector('#history-status');
  const inputNames = ['location_a_latitude', 'location_a_longitude', 'location_b_latitude', 'location_b_longitude'];

  const showMessage = (text = '', type = 'error') => { message.textContent = text; message.className = `message ${type}`; message.hidden = !text; };
  const markInvalid = fieldName => {
    if (!fieldName || !form.elements[fieldName]) return;
    const field = form.elements[fieldName];
    field.setAttribute('aria-invalid', 'true');
    field.focus();
  };
  const format = value => Number(value).toLocaleString(undefined, { maximumFractionDigits: 4 });
  const validate = () => {
    const raw = Object.fromEntries(inputNames.map(name => [name, form.elements[name].value.trim()]));
    const emptyField = inputNames.find(name => !raw[name]);
    if (emptyField) return { error: 'Please enter all four coordinates.', field: emptyField };
    const numericPattern = /^[+-]?(?:\d+(?:\.\d*)?|\.\d+)$/;
    const invalidNumberField = inputNames.find(name => !numericPattern.test(raw[name]) || !Number.isFinite(Number(raw[name])));
    if (invalidNumberField) return { error: 'Please enter valid numeric coordinates.', field: invalidNumberField };
    const values = Object.fromEntries(Object.entries(raw).map(([key, value]) => [key, Number(value)]));
    const invalidLatitudeField = ['location_a_latitude', 'location_b_latitude'].find(name => values[name] < -90 || values[name] > 90);
    if (invalidLatitudeField) return { error: 'Latitude must be between -90 and 90 degrees.', field: invalidLatitudeField };
    const invalidLongitudeField = ['location_a_longitude', 'location_b_longitude'].find(name => values[name] < -180 || values[name] > 180);
    if (invalidLongitudeField) return { error: 'Longitude must be between -180 and 180 degrees.', field: invalidLongitudeField };
    return { values };
  };
  const point = (lat, lon) => ({ x: ((lon + 180) / 360) * 640, y: ((90 - lat) / 180) * 320 });
  const bearingBetween = (fromLat, fromLon, toLat, toLon) => {
    const radians = degrees => (degrees * Math.PI) / 180;
    const degrees = radiansValue => (radiansValue * 180) / Math.PI;
    const lat1 = radians(fromLat);
    const lat2 = radians(toLat);
    const deltaLon = radians(toLon - fromLon);
    const y = Math.sin(deltaLon) * Math.cos(lat2);
    const x = Math.cos(lat1) * Math.sin(lat2) - Math.sin(lat1) * Math.cos(lat2) * Math.cos(deltaLon);
    return (degrees(Math.atan2(y, x)) + 360) % 360;
  };
  const compassDirection = bearing => ['North', 'North-East', 'East', 'South-East', 'South', 'South-West', 'West', 'North-West'][Math.round(bearing / 45) % 8];
  const renderMap = (coords, distance) => {
    const a = point(coords.location_a_latitude, coords.location_a_longitude);
    const actualB = point(coords.location_b_latitude, coords.location_b_longitude);
    const overlap = Math.hypot(actualB.x - a.x, actualB.y - a.y) < 30;
    // Nearby real-world points can occupy the same pixel on a world-scale view.
    // Offset B only for display so both submitted locations remain identifiable.
    const b = overlap ? { x: Math.min(616, a.x + 34), y: Math.max(24, a.y - 30) } : actualB;
    const closeLink = overlap ? `<line class="marker-separator" x1="${a.x}" y1="${a.y}" x2="${b.x}" y2="${b.y}"/>` : '';
    mapContent.innerHTML = `${closeLink}<line class="route" x1="${a.x}" y1="${a.y}" x2="${b.x}" y2="${b.y}"/><g class="map-marker-group"><circle class="marker-halo marker-halo-a" cx="${a.x}" cy="${a.y}" r="13"/><circle class="marker marker-a" cx="${a.x}" cy="${a.y}" r="8"/><text class="map-marker-letter" x="${a.x}" y="${a.y + 4}" text-anchor="middle">A</text></g><g class="map-marker-group"><circle class="marker-halo marker-halo-b" cx="${b.x}" cy="${b.y}" r="13"/><circle class="marker marker-b" cx="${b.x}" cy="${b.y}" r="8"/><text class="map-marker-letter" x="${b.x}" y="${b.y + 4}" text-anchor="middle">B</text></g>`;
    const bearing = bearingBetween(coords.location_a_latitude, coords.location_a_longitude, coords.location_b_latitude, coords.location_b_longitude);
    const sameLocation = Number(coords.location_a_latitude) === Number(coords.location_b_latitude)
      && Number(coords.location_a_longitude) === Number(coords.location_b_longitude);
    mapCourse.hidden = sameLocation;
    mapCourse.setAttribute('transform', `rotate(${bearing} 600 58)`);
    mapInsight.hidden = false;
    mapDirection.innerHTML = sameLocation ? 'Location B is at the same location as Location A.' : `Location B is generally <strong>${compassDirection(bearing)}</strong> of Location A.`;
    mapBearing.textContent = sameLocation ? '—' : `${Math.round(bearing)}° ${compassDirection(bearing)}`;
    mapDistance.textContent = `${Number(distance).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} KM`;
    mapSummary.hidden = false;
    mapSummary.innerHTML = `<div class="coordinate-chip chip-a"><span>Location A</span><strong>${format(coords.location_a_latitude)}°, ${format(coords.location_a_longitude)}°</strong></div><div class="coordinate-chip chip-b"><span>Location B</span><strong>${format(coords.location_b_latitude)}°, ${format(coords.location_b_longitude)}°</strong></div>`;
  };
  const resetMap = () => { mapContent.innerHTML = '<text x="320" y="154" text-anchor="middle">Enter coordinates to visualise them</text>'; mapCourse.hidden = false; mapCourse.removeAttribute('transform'); mapInsight.hidden = true; mapSummary.hidden = true; mapSummary.innerHTML = ''; };
  const loadHistory = async () => {
    try {
      const response = await fetch('api/history.php'); const data = await response.json();
      if (!data.success) throw new Error();
      historyStatus.textContent = data.items.length ? `Latest ${data.items.length}` : 'No saved calculations yet';
      historyBody.innerHTML = data.items.length ? data.items.map(row => `<tr><td>${new Date(row.created_at.replace(' ', 'T')).toLocaleString()}</td><td>${format(row.location_a_latitude)}°, ${format(row.location_a_longitude)}°</td><td>${format(row.location_b_latitude)}°, ${format(row.location_b_longitude)}°</td><td><strong>${Number(row.distance_km).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} KM</strong></td></tr>`).join('') : '<tr><td colspan="4" class="empty">Your saved calculations will appear here.</td></tr>';
    } catch { historyStatus.textContent = 'History unavailable'; historyBody.innerHTML = '<tr><td colspan="4" class="empty">Unable to load recent calculations.</td></tr>'; }
  };
  form.addEventListener('submit', async event => {
    event.preventDefault(); const check = validate();
    fields.forEach(input => input.removeAttribute('aria-invalid'));
    if (check.error) { showMessage(check.error); result.hidden = true; markInvalid(check.field); return; }
    showMessage('Calculating distance…', 'loading'); submit.disabled = true; submit.textContent = 'Calculating…';
    try {
      const response = await fetch('api/calculate.php', { method: 'POST', body: new FormData(form) }); const data = await response.json();
      if (!response.ok || !data.success) {
        const error = new Error(data.message || 'Unable to calculate the distance. Please try again.');
        error.field = data.field;
        throw error;
      }
      const c = data.coordinates; document.querySelector('#distance-output').textContent = `${Number(data.distance).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} KM`;
      document.querySelector('#result-detail').textContent = `A: ${format(c.location_a_latitude)}°, ${format(c.location_a_longitude)}°  ·  B: ${format(c.location_b_latitude)}°, ${format(c.location_b_longitude)}°`;
      result.hidden = false; renderMap(c, data.distance); showMessage('Distance calculated successfully.', 'success'); loadHistory();
    } catch (error) { showMessage(error.message || 'Unable to calculate the distance. Please check your coordinates and try again.'); result.hidden = true; markInvalid(error.field); }
    finally { submit.disabled = false; submit.textContent = 'Calculate distance'; }
  });
  document.querySelector('#clear-button').addEventListener('click', () => { form.reset(); showMessage(); result.hidden = true; resetMap(); fields.forEach(input => input.removeAttribute('aria-invalid')); fields[0].focus(); });
  document.querySelector('#example-button').addEventListener('click', () => { form.elements.location_a_latitude.value = '6.9271'; form.elements.location_a_longitude.value = '79.8612'; form.elements.location_b_latitude.value = '7.2906'; form.elements.location_b_longitude.value = '80.6337'; showMessage(); });
  fields.forEach(input => input.addEventListener('input', () => input.removeAttribute('aria-invalid')));
  loadHistory();
})();
