    const longitude = document.getElementById('input-lng').value;
    const latitude = document.getElementById('input-lat').value;
    var map = L.map('map').setView([0, 0], 13);
    L.geolet({ position: 'topright' }).addTo(map);

    /*
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);
    */

    if (longitude != 0 || latitude != 0)
    {
        map.setView([latitude, longitude], 13);
        marker = L.marker([latitude, longitude], { draggable: true }).addTo(map);
    }

    // When map is clicked, set marker to that location and update the form
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        document.getElementById('input-lat').value = e.latlng.lat;
        document.getElementById('input-lng').value = e.latlng.lng;
    });

    map.on('geolet_success', function (data) 
    {
        if (data.first)
        {
            marker.setLatLng(data.latlng);
            document.getElementById('input-lat').value = data.latlng.lat;
            document.getElementById('input-lng').value = data.latlng.lng;
        }
    });