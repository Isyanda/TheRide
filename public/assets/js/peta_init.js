(function () {
    'use strict';

    if (!window.L || !window.TAWARJALAN) return;

    var defaultCenter = [-6.2, 106.816666];
    var mapElement = document.getElementById(window.TAWARJALAN.orderMap);
    if (!mapElement) return;

    var map = L.map(mapElement).setView(defaultCenter, 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var pickupMarker;
    var destinationMarker;
    var routeLine;
    var routeEstimate = document.getElementById('route-estimate');
    var form = document.getElementById(window.TAWARJALAN.orderForm);
    var fields = {
        pickupLat: form.querySelector('[name="pickup_lat"]'),
        pickupLng: form.querySelector('[name="pickup_lng"]'),
        destinationLat: form.querySelector('[name="destination_lat"]'),
        destinationLng: form.querySelector('[name="destination_lng"]')
    };

    map.on('click', function (event) {
        if (!fields.pickupLat.value || fields.destinationLat.value) {
            fields.pickupLat.value = event.latlng.lat.toFixed(7);
            fields.pickupLng.value = event.latlng.lng.toFixed(7);
            if (pickupMarker) map.removeLayer(pickupMarker);
            pickupMarker = L.marker(event.latlng).addTo(map).bindPopup('Lokasi jemput').openPopup();
            if (fields.destinationLat.value) {
                fields.destinationLat.value = '';
                fields.destinationLng.value = '';
            }
            if (destinationMarker) {
                map.removeLayer(destinationMarker);
                destinationMarker = null;
            }
            if (routeLine) {
                map.removeLayer(routeLine);
                routeLine = null;
            }
        } else {
            fields.destinationLat.value = event.latlng.lat.toFixed(7);
            fields.destinationLng.value = event.latlng.lng.toFixed(7);
            if (destinationMarker) map.removeLayer(destinationMarker);
            destinationMarker = L.marker(event.latlng).addTo(map).bindPopup('Lokasi tujuan').openPopup();
            if (routeLine) map.removeLayer(routeLine);
            var params = new URLSearchParams({
                from_lat: fields.pickupLat.value,
                from_lng: fields.pickupLng.value,
                to_lat: fields.destinationLat.value,
                to_lng: fields.destinationLng.value
            });
            fetch('?m=peta&a=api_hitung_rute&' + params.toString(), {
                headers: { Accept: 'application/json' }
            }).then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) throw new Error(data.error || 'Gagal menghitung rute.');
                    return data;
                });
            }).then(function (data) {
                routeLine = L.polyline(data.coordinates, {
                    color: '#12756e', weight: 4
                }).addTo(map);
                routeEstimate.textContent = 'Jarak garis lurus sekitar ' + data.distance_km
                    + ' km; estimasi awal ' + data.duration_minutes + ' menit (belum mengikuti jalan).';
            }).catch(function () {
                routeLine = L.polyline([
                    [Number(fields.pickupLat.value), Number(fields.pickupLng.value)],
                    [event.latlng.lat, event.latlng.lng]
                ], { color: '#12756e', weight: 4 }).addTo(map);
                routeEstimate.textContent = 'Rute perkiraan ditampilkan sebagai garis lurus.';
            });
        }
    });
})();
