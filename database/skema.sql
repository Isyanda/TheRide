CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('passenger', 'driver') NOT NULL DEFAULT 'passenger',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS vehicles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    driver_id INT UNSIGNED NOT NULL,
    brand VARCHAR(80) NOT NULL,
    model VARCHAR(80) NOT NULL,
    plate_number VARCHAR(20) NOT NULL UNIQUE,
    color VARCHAR(40) NOT NULL,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    passenger_id INT UNSIGNED NOT NULL,
    pickup_address VARCHAR(255) NOT NULL,
    destination_address VARCHAR(255) NOT NULL,
    pickup_lat DECIMAL(10, 7) NOT NULL,
    pickup_lng DECIMAL(10, 7) NOT NULL,
    destination_lat DECIMAL(10, 7) NOT NULL,
    destination_lng DECIMAL(10, 7) NOT NULL,
    offered_fare DECIMAL(12, 2) NOT NULL,
    note VARCHAR(500) NULL,
    status ENUM('searching', 'accepted', 'in_progress', 'completed', 'cancelled')
        NOT NULL DEFAULT 'searching',
    selected_driver_id INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (passenger_id) REFERENCES users(id),
    FOREIGN KEY (selected_driver_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX orders_status_pickup_idx (status, pickup_lat, pickup_lng)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS offers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    driver_id INT UNSIGNED NOT NULL,
    fare DECIMAL(12, 2) NOT NULL,
    status ENUM('pending', 'selected', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY offers_order_driver_unique (order_id, driver_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS driver_locations (
    driver_id INT UNSIGNED PRIMARY KEY,
    latitude DECIMAL(10, 7) NOT NULL,
    longitude DECIMAL(10, 7) NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
