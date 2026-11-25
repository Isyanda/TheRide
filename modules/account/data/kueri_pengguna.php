<?php

declare(strict_types=1);

function cari_pengguna_dengan_email(mysqli $db, string $email): array|null
{
    $query = $db->prepare(
        'SELECT id, name, email, password_hash, role FROM users WHERE email = ? LIMIT 1'
    );
    $query->bind_param('s', $email);
    $query->execute();
    $user = $query->get_result()->fetch_assoc();

    return $user ?: null;
}

function buat_pengguna(
    mysqli $db,
    string $name,
    string $email,
    string $passwordHash,
    string $role,
    array|null $vehicle
): int {
    $db->begin_transaction();
    try {
        $query = $db->prepare(
            'INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)'
        );
        $query->bind_param('ssss', $name, $email, $passwordHash, $role);
        $query->execute();
        $userId = $db->insert_id;

        if ($role === 'driver' && $vehicle !== null) {
            $vehicleQuery = $db->prepare(
                'INSERT INTO vehicles (driver_id, brand, model, plate_number, color)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $vehicleQuery->bind_param(
                'issss',
                $userId,
                $vehicle['brand'],
                $vehicle['model'],
                $vehicle['plate_number'],
                $vehicle['color']
            );
            $vehicleQuery->execute();
        }

        $db->commit();
        return $userId;
    } catch (Throwable $error) {
        $db->rollback();
        throw $error;
    }
}

function ambil_pengguna_untuk_sesi(mysqli $db, int $userId): array|null
{
    $query = $db->prepare('SELECT id, name, email, role FROM users WHERE id = ? LIMIT 1');
    $query->bind_param('i', $userId);
    $query->execute();
    $user = $query->get_result()->fetch_assoc();

    return $user ?: null;
}
