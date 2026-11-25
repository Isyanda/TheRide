(function () {
    'use strict';

    var config = window.TAWARJALAN || {};
    var csrfHeaders = { Accept: 'application/json', 'X-CSRF-Token': config.csrf || '' };

    function apiUrl(module, action) {
        return '?m=' + encodeURIComponent(module) + '&a=' + encodeURIComponent(action);
    }

    function parseResponse(response) {
        return response.json().then(function (data) {
            if (!response.ok) throw new Error(data.error || 'Permintaan gagal.');
            return data;
        });
    }

    function showMessage(element, message, isError) {
        element.textContent = message;
        element.classList.toggle('notice-error', Boolean(isError));
    }

    var orderForm = document.getElementById(config.orderForm);
    if (orderForm) {
        orderForm.addEventListener('submit', function (event) {
            event.preventDefault();
            var message = document.getElementById('order-message');
            showMessage(message, 'Membuat pesanan...', false);
            fetch(apiUrl('pesanan', 'aksi_buat'), {
                method: 'POST',
                body: new FormData(orderForm),
                headers: csrfHeaders,
                credentials: 'same-origin'
            }).then(parseResponse).then(function (data) {
                window.location.href = '?m=tawaran&a=masuk&order_id=' + encodeURIComponent(data.order_id);
            }).catch(function (error) {
                showMessage(message, error.message, true);
            });
        });
    }

    var offersElement = document.getElementById('offers');
    if (!offersElement || !config.orderId) return;

    var statusElement = document.getElementById('order-status');
    var messageElement = document.getElementById('offers-message');
    var cancelForm = document.getElementById('cancel-order-form');
    var map = null;
    var driverMarker = null;
    var pickupMarker = null;
    var destinationMarker = null;
    var routeLine = null;
    if (window.L && document.getElementById('driver-map')) {
        map = L.map('driver-map').setView([-6.2, 106.816666], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
    }

    var statusLabels = {
        searching: 'Mencari tawaran driver',
        accepted: 'Driver sudah dipilih',
        in_progress: 'Perjalanan sedang berlangsung',
        completed: 'Perjalanan selesai',
        cancelled: 'Pesanan dibatalkan'
    };

    function addText(parent, tag, className, text) {
        var element = document.createElement(tag);
        if (className) element.className = className;
        element.textContent = text == null ? '' : String(text);
        parent.appendChild(element);
        return element;
    }

    function distanceKm(lat1, lng1, lat2, lng2) {
        var radians = Math.PI / 180;
        var dLat = (lat2 - lat1) * radians;
        var dLng = (lng2 - lng1) * radians;
        var a = Math.sin(dLat / 2) ** 2
            + Math.cos(lat1 * radians) * Math.cos(lat2 * radians)
            * Math.sin(dLng / 2) ** 2;
        return 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function render(data) {
        var order = data.order;
        statusElement.textContent = statusLabels[order.status] || order.status;
        cancelForm.hidden = !['searching', 'accepted'].includes(order.status);

        if (map && order.pickup_lat && order.pickup_lng) {
            var pickup = [Number(order.pickup_lat), Number(order.pickup_lng)];
            if (!pickupMarker) {
                pickupMarker = L.marker(pickup).addTo(map).bindPopup('Lokasi jemput');
                map.setView(pickup, 13);
            } else {
                pickupMarker.setLatLng(pickup);
            }
            if (order.destination_lat && order.destination_lng) {
                var destination = [Number(order.destination_lat), Number(order.destination_lng)];
                if (!destinationMarker) {
                    destinationMarker = L.marker(destination).addTo(map).bindPopup('Lokasi tujuan');
                } else {
                    destinationMarker.setLatLng(destination);
                }
                if (routeLine) map.removeLayer(routeLine);
                routeLine = L.polyline([pickup, destination], { color: '#12756e', weight: 4 }).addTo(map);
            }
            if (order.driver_latitude && order.driver_longitude) {
                var driver = [Number(order.driver_latitude), Number(order.driver_longitude)];
                if (!driverMarker) driverMarker = L.marker(driver).addTo(map).bindPopup('Driver');
                else driverMarker.setLatLng(driver);
            }
        }

        offersElement.replaceChildren();
        if (order.status === 'accepted' || order.status === 'in_progress') {
            var driverDetails = order.driver_name || 'Driver';
            var vehicle = [order.brand, order.model, order.color, order.plate_number]
                .filter(Boolean).join(' · ');
            addText(offersElement, 'h2', '', driverDetails);
            addText(offersElement, 'p', '', vehicle || 'Informasi kendaraan tidak tersedia.');
            addText(offersElement, 'p', 'price', 'Harga: Rp ' + Number(order.offered_fare).toLocaleString('id-ID'));
        }

        if (order.status === 'searching' && data.offers.length === 0) {
            addText(offersElement, 'p', 'muted', 'Belum ada driver yang mengirim tawaran. Halaman akan memperbarui otomatis.');
        }
        data.offers.forEach(function (offer) {
            var card = document.createElement('article');
            card.className = 'offer-card';
            var heading = document.createElement('header');
            addText(heading, 'strong', '', offer.driver_name);
            addText(heading, 'span', 'price', 'Rp ' + Number(offer.fare).toLocaleString('id-ID'));
            card.appendChild(heading);
            var vehicle = [offer.brand, offer.model, offer.color, offer.plate_number]
                .filter(Boolean).join(' · ');
            addText(card, 'p', '', vehicle || 'Informasi kendaraan belum diisi.');
            if (offer.latitude && offer.longitude) {
                var km = distanceKm(
                    Number(order.pickup_lat), Number(order.pickup_lng),
                    Number(offer.latitude), Number(offer.longitude)
                );
                addText(card, 'p', 'muted', 'Sekitar ' + km.toFixed(1) + ' km dari titik jemput');
            }
            if (offer.status === 'selected') {
                addText(card, 'p', 'notice', 'Driver ini dipilih.');
            } else if (order.status === 'searching' && offer.status === 'pending') {
                var choose = addText(card, 'button', 'button button-primary', 'Pilih driver');
                choose.type = 'button';
                choose.addEventListener('click', function () {
                    var body = new FormData();
                    body.append('offer_id', offer.id);
                    fetch(apiUrl('tawaran', 'aksi_pilih'), {
                        method: 'POST',
                        body: body,
                        headers: csrfHeaders,
                        credentials: 'same-origin'
                    }).then(parseResponse).then(function (result) {
                        showMessage(messageElement, result.message, false);
                        return loadStatus();
                    }).catch(function (error) {
                        showMessage(messageElement, error.message, true);
                    });
                });
            }
            offersElement.appendChild(card);
        });
    }

    function loadStatus() {
        return fetch(apiUrl('pesanan', 'api_status') + '&order_id=' + encodeURIComponent(config.orderId), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin'
        }).then(parseResponse).then(render).catch(function (error) {
            showMessage(messageElement, error.message, true);
        });
    }

    cancelForm.addEventListener('submit', function (event) {
        event.preventDefault();
        if (!window.confirm('Yakin ingin membatalkan pesanan?')) return;
        fetch(cancelForm.action, {
            method: 'POST',
            body: new FormData(cancelForm),
            headers: csrfHeaders,
            credentials: 'same-origin'
        }).then(parseResponse).then(function (result) {
            showMessage(messageElement, result.message, false);
            return loadStatus();
        }).catch(function (error) {
            showMessage(messageElement, error.message, true);
        });
    });

    loadStatus();
    window.setInterval(loadStatus, 5000);
})();
