<?php

declare(strict_types=1);

function kirim_tawaran(mysqli $db, int $orderId, int $driverId, float $fare): int
{
    $query = $db->prepare(
        'INSERT INTO offers (order_id, driver_id, fare)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE fare = VALUES(fare), status = "pending"'
    );
    $query->bind_param('iid', $orderId, $driverId, $fare);
    $query->execute();

    return $db->insert_id ?: ambil_id_tawaran($db, $orderId, $driverId);
}

function ambil_id_tawaran(mysqli $db, int $orderId, int $driverId): int
{
    $query = $db->prepare('SELECT id FROM offers WHERE order_id = ? AND driver_id = ?');
    $query->bind_param('ii', $orderId, $driverId);
    $query->execute();
    $row = $query->get_result()->fetch_assoc();

    return (int) ($row['id'] ?? 0);
}

function daftar_tawaran(mysqli $db, int $orderId, int $passengerId): array
{
    $query = $db->prepare(
        'SELECT o.id, o.order_id, o.driver_id, o.fare, o.status, o.created_at,
                u.name AS driver_name, v.brand, v.model, v.plate_number, v.color,
                dl.latitude, dl.longitude
         FROM offers o
         JOIN orders ord ON ord.id = o.order_id AND ord.passenger_id = ?
         JOIN users u ON u.id = o.driver_id
         LEFT JOIN vehicles v ON v.driver_id = o.driver_id
         LEFT JOIN driver_locations dl ON dl.driver_id = o.driver_id
              AND dl.updated_at >= CURRENT_TIMESTAMP - INTERVAL 5 MINUTE
         WHERE o.order_id = ? ORDER BY o.fare ASC, o.created_at ASC'
    );
    $query->bind_param('ii', $passengerId, $orderId);
    $query->execute();

    return $query->get_result()->fetch_all(MYSQLI_ASSOC);
}

function pilih_tawaran(mysqli $db, int $offerId, int $passengerId): int
{
    $db->begin_transaction();
    try {
        $query = $db->prepare(
            'SELECT o.driver_id, o.order_id, o.fare FROM offers o
             JOIN orders ord ON ord.id = o.order_id AND ord.passenger_id = ?
             WHERE o.id = ?'
        );
        $query->bind_param('ii', $passengerId, $offerId);
        $query->execute();
        $offer = $query->get_result()->fetch_assoc();
        if (!$offer) {
            throw new DomainException('Tawaran tidak tersedia.');
        }

        $orderId = (int) $offer['order_id'];
        $driverId = (int) $offer['driver_id'];
        $fare = (float) $offer['fare'];
        $orderLock = $db->prepare(
            'SELECT id FROM orders WHERE id = ? AND status = "searching" FOR UPDATE'
        );
        $orderLock->bind_param('i', $orderId);
        $orderLock->execute();
        if (!$orderLock->get_result()->fetch_assoc()) {
            throw new DomainException('Pesanan sudah tidak mencari driver.');
        }
        $offerLock = $db->prepare(
            'SELECT id FROM offers WHERE id = ? AND order_id = ? AND status = "pending" FOR UPDATE'
        );
        $offerLock->bind_param('ii', $offerId, $orderId);
        $offerLock->execute();
        if (!$offerLock->get_result()->fetch_assoc()) {
            throw new DomainException('Tawaran sudah tidak tersedia.');
        }
        $reject = $db->prepare('UPDATE offers SET status = "rejected" WHERE order_id = ?');
        $reject->bind_param('i', $orderId);
        $reject->execute();
        $select = $db->prepare('UPDATE offers SET status = "selected" WHERE id = ?');
        $select->bind_param('i', $offerId);
        $select->execute();
        $order = $db->prepare(
            'UPDATE orders SET status = "accepted", selected_driver_id = ?, offered_fare = ?
             WHERE id = ? AND status = "searching"'
        );
        $order->bind_param('idi', $driverId, $fare, $orderId);
        $order->execute();
        $db->commit();

        return $orderId;
    } catch (Throwable $error) {
        $db->rollback();
        throw $error;
    }
}
