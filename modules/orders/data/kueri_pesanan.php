<?php

declare(strict_types=1);

function buat_pesanan(
    mysqli $db,
    int $passengerId,
    string $pickupAddress,
    string $destinationAddress,
    float $pickupLat,
    float $pickupLng,
    float $destinationLat,
    float $destinationLng,
    float $offeredFare,
    string|null $note
): int {
    $query = $db->prepare(
        'INSERT INTO orders
        (passenger_id, pickup_address, destination_address, pickup_lat, pickup_lng,
         destination_lat, destination_lng, offered_fare, note)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $query->bind_param(
        'issddddds',
        $passengerId,
        $pickupAddress,
        $destinationAddress,
        $pickupLat,
        $pickupLng,
        $destinationLat,
        $destinationLng,
        $offeredFare,
        $note
    );
    $query->execute();

    return $db->insert_id;
}

function ambil_pesanan(mysqli $db, int $orderId, int $passengerId): array|null
{
    $query = $db->prepare(
        'SELECT id, passenger_id, pickup_address, destination_address, pickup_lat,
                pickup_lng, destination_lat, destination_lng, offered_fare, note, status
         FROM orders WHERE id = ? AND passenger_id = ? LIMIT 1'
    );
    $query->bind_param('ii', $orderId, $passengerId);
    $query->execute();
    $order = $query->get_result()->fetch_assoc();

    return $order ?: null;
}

function ubah_status_pesanan(
    mysqli $db,
    int $orderId,
    string $status,
    int|null $driverId = null
): void {
    if ($driverId === null) {
        $query = $db->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $query->bind_param('si', $status, $orderId);
    } else {
        $query = $db->prepare(
            'UPDATE orders SET status = ?, selected_driver_id = ? WHERE id = ?'
        );
        $query->bind_param('sii', $status, $driverId, $orderId);
    }
    $query->execute();
}

function daftar_pesanan_sekitar(mysqli $db, float $lat, float $lng, float $radiusKm): array
{
    $query = $db->prepare(
        'SELECT id, pickup_address, destination_address, pickup_lat, pickup_lng,
                destination_lat, destination_lng, offered_fare, note,
                (6371 * ACOS(LEAST(1, GREATEST(-1,
                    COS(RADIANS(?)) * COS(RADIANS(pickup_lat)) *
                    COS(RADIANS(pickup_lng) - RADIANS(?)) +
                    SIN(RADIANS(?)) * SIN(RADIANS(pickup_lat))
                )))) AS distance_km
         FROM orders
         WHERE status = "searching"
         HAVING distance_km <= ?
         ORDER BY distance_km ASC'
    );
    $query->bind_param('dddd', $lat, $lng, $lat, $radiusKm);
    $query->execute();

    return $query->get_result()->fetch_all(MYSQLI_ASSOC);
}

function daftar_pesanan_driver(mysqli $db, int $driverId): array
{
    $query = $db->prepare(
        'SELECT id, passenger_id, pickup_address, destination_address, pickup_lat,
                pickup_lng, destination_lat, destination_lng, offered_fare, note,
                status, updated_at
         FROM orders
         WHERE selected_driver_id = ? AND status IN ("accepted", "in_progress")
         ORDER BY updated_at DESC'
    );
    $query->bind_param('i', $driverId);
    $query->execute();

    return $query->get_result()->fetch_all(MYSQLI_ASSOC);
}

function status_pesanan_penumpang(mysqli $db, int $orderId, int $passengerId): array|null
{
    $query = $db->prepare(
        'SELECT ord.id, ord.status, ord.offered_fare, ord.selected_driver_id,
                ord.pickup_address, ord.destination_address, ord.pickup_lat, ord.pickup_lng,
                ord.destination_lat, ord.destination_lng,
                u.name AS driver_name, dl.latitude AS driver_latitude,
                dl.longitude AS driver_longitude, v.brand, v.model, v.color, v.plate_number
         FROM orders ord
         LEFT JOIN users u ON u.id = ord.selected_driver_id
         LEFT JOIN driver_locations dl ON dl.driver_id = ord.selected_driver_id
              AND dl.updated_at >= CURRENT_TIMESTAMP - INTERVAL 5 MINUTE
         LEFT JOIN vehicles v ON v.driver_id = ord.selected_driver_id
         WHERE ord.id = ? AND ord.passenger_id = ? LIMIT 1'
    );
    $query->bind_param('ii', $orderId, $passengerId);
    $query->execute();
    $order = $query->get_result()->fetch_assoc();

    return $order ?: null;
}

function radius_driver_pesanan(mysqli $db, int $driverId, int $orderId, float $radiusKm = 3): bool
{
    $query = $db->prepare(
        'SELECT o.id
         FROM orders o
         JOIN driver_locations dl ON dl.driver_id = ?
         WHERE o.id = ? AND o.status = "searching"
           AND dl.updated_at >= CURRENT_TIMESTAMP - INTERVAL 5 MINUTE
           AND (6371 * ACOS(LEAST(1, GREATEST(-1,
               COS(RADIANS(dl.latitude)) * COS(RADIANS(o.pickup_lat)) *
               COS(RADIANS(o.pickup_lng) - RADIANS(dl.longitude)) +
               SIN(RADIANS(dl.latitude)) * SIN(RADIANS(o.pickup_lat))
           )))) <= ? LIMIT 1'
    );
    $query->bind_param('iid', $driverId, $orderId, $radiusKm);
    $query->execute();

    return $query->get_result()->fetch_assoc() !== null;
}
