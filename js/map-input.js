    const OpenStreetMapsEnabled = true;
    const longitude = document.getElementById('input-lng');
    const latitude = document.getElementById('input-lat');
    var map = L.map('map').setView([0, 0], 13);
    L.geolet({ position: 'topright' }).addTo(map);

    if (OpenStreetMapsEnabled)
    {
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);
    }

    if (longitude.value == null)
    {
        longitude.value = 0;
    }

    if (latitude.value == null)
    {
        latitude.value = 0;
    }

    map.setView([latitude.value, longitude.value], 13);
    marker = L.marker([latitude.value, longitude.value], { draggable: true }).addTo(map);

    // When map is clicked, set marker to that location and update the form
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        latitude.value = e.latlng.lat;
        longitude.value = e.latlng.lng;
    });

    map.on('geolet_success', function (data) 
    {
        if (data.first)
        {
            marker.setLatLng(data.latlng);
            latitude.value = data.latlng.lat;
            longitude.value = data.latlng.lng;
        }
    });