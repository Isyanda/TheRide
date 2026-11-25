(function () {
    'use strict';

    var config = window.TAWARJALAN || {};
    var mapElement = document.getElementById(config.driverMap);
    var ordersElement = document.getElementById(config.nearbyOrders);
    var assignedElement = document.getElementById(config.assignedOrders);
    var locationStatus = document.getElementById('location-status');
    var locationButton = document.getElementById('driver-location');
    var chooseLocationButton = document.getElementById('choose-driver-location');
    if (!mapElement || !ordersElement || !assignedElement || !window.L) return;

    var map = L.map(mapElement).setView([-6.2, 106.816666], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    var driverMarker = null;
    var orderMarkers = [];
    var latitude = null;
    var longitude = null;
    var locationWatch = null;
    var selectingLocation = false;
    var requestRunning = false;
    var csrfHeaders = { Accept: 'application/json', 'X-CSRF-Token': config.csrf || '' };

    function parseResponse(response) {
        return response.json().then(function (data) {
            if (!response.ok) throw new Error(data.error || 'Permintaan gagal.');
            return data;
        });
    }

    function text(parent, tag, className, value) {
        var element = document.createElement(tag);
        if (className) element.className = className;
        element.textContent = value == null ? '' : String(value);
        parent.appendChild(element);
        return element;
    }

    function post(action, body) {
        return fetch('?m=' + encodeURIComponent(action.module) + '&a=' + encodeURIComponent(action.name), {
            method: 'POST',
            body: body,
            headers: csrfHeaders,
            credentials: 'same-origin'
        }).then(parseResponse);
    }

    function saveLocation(lat, lng, label) {
        latitude = lat;
        longitude = lng;
        if (driverMarker) driverMarker.setLatLng([latitude, longitude]);
        else driverMarker = L.marker([latitude, longitude]).addTo(map);
        driverMarker.bindPopup(label);
        map.setView([latitude, longitude], 14);
        locationStatus.textContent = 'Menyimpan lokasi...';
        var body = new FormData();
        body.append('lat', latitude);
        body.append('lng', longitude);
        post({ module: 'peta', name: 'api_simpan_lokasi' }, body)
            .then(function () {
                locationStatus.textContent = label + ' tersimpan. Pesanan dalam radius 3 km sedang dicari.';
                loadOrders();
            })
            .catch(function (error) {
                locationStatus.textContent = error.message;
            });
    }

    function updateLocation(position) {
        saveLocation(position.coords.latitude, position.coords.longitude, 'Lokasi GPS');
    }

    function showLocationError(error) {
        var messages = {
            1: 'Izin lokasi ditolak. Izinkan lokasi di browser untuk menerima pesanan.',
            2: 'Lokasi tidak tersedia. Coba aktifkan GPS.',
            3: 'Permintaan lokasi timeout. Coba lagi.'
        };
        locationStatus.textContent = messages[error.code] || 'Gagal mendapatkan lokasi.';
    }

    function drawOrderMarkers(orders) {
        orderMarkers.forEach(function (marker) { map.removeLayer(marker); });
        orderMarkers = orders.map(function (order) {
            var popup = document.createElement('span');
            popup.textContent = String(order.pickup_address || 'Titik jemput');
            return L.marker([Number(order.pickup_lat), Number(order.pickup_lng)])
                .addTo(map)
                .bindPopup(popup);
        });
    }

    function buildSearchCard(order) {
        var card = document.createElement('article');
        card.className = 'order-card';
        var heading = document.createElement('header');
        text(heading, 'strong', '', order.pickup_address + ' → ' + order.destination_address);
        text(heading, 'span', 'price', 'Rp ' + Number(order.offered_fare).toLocaleString('id-ID'));
        card.appendChild(heading);
        text(card, 'p', 'muted', Number(order.distance_km).toFixed(1) + ' km dari kamu');
        if (order.note) text(card, 'p', '', 'Catatan: ' + order.note);

        var form = document.createElement('form');
        form.className = 'offer-form';
        var fare = document.createElement('input');
        fare.name = 'fare';
        fare.type = 'number';
        fare.min = '1';
        fare.required = true;
        fare.placeholder = 'Harga tawaran (Rp)';
        form.appendChild(fare);
        var orderId = document.createElement('input');
        orderId.type = 'hidden';
        orderId.name = 'order_id';
        orderId.value = order.id;
        form.appendChild(orderId);
        var submit = text(form, 'button', 'button button-secondary', 'Kirim tawaran');
        submit.type = 'submit';
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            post({ module: 'tawaran', name: 'aksi_kirim' }, new FormData(form))
                .then(function (result) {
                    text(card, 'p', 'notice', result.message);
                    submit.disabled = true;
                })
                .catch(function (error) { text(card, 'p', 'notice notice-error', error.message); });
        });
        card.appendChild(form);

        var acceptBody = new FormData();
        acceptBody.append('order_id', order.id);
        var accept = text(card, 'button', 'button button-primary', 'Terima harga penumpang');
        accept.type = 'button';
        accept.addEventListener('click', function () {
            accept.disabled = true;
            post({ module: 'tawaran', name: 'aksi_terima' }, acceptBody)
                .then(function (result) {
                    text(card, 'p', 'notice', result.message);
                    return loadOrders();
                })
                .catch(function (error) {
                    accept.disabled = false;
                    text(card, 'p', 'notice notice-error', error.message);
                });
        });
        return card;
    }

    function buildAssignedCard(order) {
        var card = document.createElement('article');
        card.className = 'order-card';
        text(card, 'strong', '', order.pickup_address + ' → ' + order.destination_address);
        text(card, 'p', '', 'Status: ' + (order.status === 'accepted' ? 'Driver dipilih' : 'Dalam perjalanan')
            + ' · Rp ' + Number(order.offered_fare).toLocaleString('id-ID'));
        var body = new FormData();
        body.append('order_id', order.id);
        if (order.status === 'accepted') {
            var start = text(card, 'button', 'button button-primary', 'Mulai perjalanan');
            start.type = 'button';
            start.addEventListener('click', function () {
                start.disabled = true;
                post({ module: 'pesanan', name: 'aksi_mulai' }, body)
                    .then(function () { return loadOrders(); })
                    .catch(function (error) {
                        start.disabled = false;
                        text(card, 'p', 'notice notice-error', error.message);
                    });
            });
        } else {
            var finish = text(card, 'button', 'button button-primary', 'Selesaikan perjalanan');
            finish.type = 'button';
            finish.addEventListener('click', function () {
                finish.disabled = true;
                post({ module: 'pesanan', name: 'aksi_selesaikan' }, body)
                    .then(function () { return loadOrders(); })
                    .catch(function (error) {
                        finish.disabled = false;
                        text(card, 'p', 'notice notice-error', error.message);
                    });
            });
        }
        return card;
    }

    function loadOrders() {
        if (latitude === null || longitude === null || requestRunning) return;
        requestRunning = true;
        var query = new URLSearchParams({ radius: '3' });
        fetch('?m=peta&a=api_pesanan_sekitar&' + query.toString(), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin'
        }).then(parseResponse).then(function (data) {
            drawOrderMarkers(data.orders);
            ordersElement.replaceChildren();
            assignedElement.replaceChildren();
            if (data.orders.length === 0) text(ordersElement, 'p', 'muted', 'Belum ada pesanan di sekitar.');
            if (data.assigned_orders.length === 0) text(assignedElement, 'p', 'muted', 'Belum ada pesanan yang diterima.');
            data.orders.forEach(function (order) { ordersElement.appendChild(buildSearchCard(order)); });
            data.assigned_orders.forEach(function (order) { assignedElement.appendChild(buildAssignedCard(order)); });
        }).catch(function (error) {
            ordersElement.textContent = error.message;
        }).finally(function () {
            requestRunning = false;
        });
    }

    locationButton.addEventListener('click', function () {
        selectingLocation = false;
        chooseLocationButton.textContent = 'Pilih lokasi di peta';
        mapElement.classList.remove('selecting-location');
        if (!navigator.geolocation) {
            locationStatus.textContent = 'Browser ini tidak mendukung geolocation.';
            return;
        }
        locationStatus.textContent = 'Meminta izin lokasi...';
        navigator.geolocation.getCurrentPosition(updateLocation, showLocationError, {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 10000
        });
        if (locationWatch === null && navigator.geolocation.watchPosition) {
            locationWatch = navigator.geolocation.watchPosition(updateLocation, showLocationError, {
                enableHighAccuracy: true,
                maximumAge: 15000
            });
        }
    });

    chooseLocationButton.addEventListener('click', function () {
        selectingLocation = !selectingLocation;
        if (selectingLocation) {
            if (locationWatch !== null && navigator.geolocation) {
                navigator.geolocation.clearWatch(locationWatch);
                locationWatch = null;
            }
            chooseLocationButton.textContent = 'Batal pilih lokasi';
            mapElement.classList.add('selecting-location');
            locationStatus.textContent = 'Klik titik di peta untuk menetapkan lokasi driver.';
            mapElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            chooseLocationButton.textContent = 'Pilih lokasi di peta';
            mapElement.classList.remove('selecting-location');
            locationStatus.textContent = 'Pemilihan lokasi dibatalkan.';
        }
    });

    map.on('click', function (event) {
        if (!selectingLocation) return;
        selectingLocation = false;
        chooseLocationButton.textContent = 'Pilih lokasi di peta';
        mapElement.classList.remove('selecting-location');
        saveLocation(event.latlng.lat, event.latlng.lng, 'Lokasi peta');
    });

    window.setInterval(loadOrders, 10000);
})();
