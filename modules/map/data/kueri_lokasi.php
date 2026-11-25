<?php

declare(strict_types=1);

function simpan_lokasi_driver(mysqli $db, int $driverId, float $lat, float $lng): void
{
    $query = $db->prepare(
        'INSERT INTO driver_locations (driver_id, latitude, longitude)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE latitude = VALUES(latitude),
         longitude = VALUES(longitude), updated_at = CURRENT_TIMESTAMP'
    );
    $query->bind_param('idd', $driverId, $lat, $lng);
    $query->execute();
}

function daftar_lokasi_driver(mysqli $db, float $lat, float $lng, float $radiusKm): array
{
    $query = $db->prepare(
        'SELECT dl.driver_id, u.name, dl.latitude, dl.longitude,
                (6371 * ACOS(LEAST(1, GREATEST(-1,
                    COS(RADIANS(?)) * COS(RADIANS(dl.latitude)) *
                    COS(RADIANS(dl.longitude) - RADIANS(?)) +
                    SIN(RADIANS(?)) * SIN(RADIANS(dl.latitude))
                )))) AS distance_km
         FROM driver_locations dl
         JOIN users u ON u.id = dl.driver_id AND u.role = "driver"
         HAVING distance_km <= ?
         ORDER BY distance_km ASC'
    );
    $query->bind_param('dddd', $lat, $lng, $lat, $radiusKm);
    $query->execute();

    return $query->get_result()->fetch_all(MYSQLI_ASSOC);
}

function ambil_lokasi_driver_terbaru(mysqli $db, int $driverId): array|null
{
    $query = $db->prepare(
        'SELECT latitude, longitude FROM driver_locations
         WHERE driver_id = ? AND updated_at >= CURRENT_TIMESTAMP - INTERVAL 5 MINUTE
         LIMIT 1'
    );
    $query->bind_param('i', $driverId);
    $query->execute();
    $location = $query->get_result()->fetch_assoc();

    return $location ?: null;
}
