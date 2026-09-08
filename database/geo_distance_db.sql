CREATE DATABASE IF NOT EXISTS geo_distance_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE geo_distance_db;

CREATE TABLE IF NOT EXISTS distance_calculations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    location_a_latitude DECIMAL(10, 7) NOT NULL,
    location_a_longitude DECIMAL(10, 7) NOT NULL,
    location_b_latitude DECIMAL(10, 7) NOT NULL,
    location_b_longitude DECIMAL(10, 7) NOT NULL,
    distance_km DECIMAL(12, 4) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DROP PROCEDURE IF EXISTS sp_save_distance_calculation;
DELIMITER $$
CREATE PROCEDURE sp_save_distance_calculation(
    IN p_location_a_latitude DECIMAL(10, 7),
    IN p_location_a_longitude DECIMAL(10, 7),
    IN p_location_b_latitude DECIMAL(10, 7),
    IN p_location_b_longitude DECIMAL(10, 7),
    IN p_distance_km DECIMAL(12, 4)
)
BEGIN
    IF p_location_a_latitude < -90 OR p_location_a_latitude > 90
        OR p_location_b_latitude < -90 OR p_location_b_latitude > 90 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Latitude must be between -90 and 90 degrees.';
    END IF;

    IF p_location_a_longitude < -180 OR p_location_a_longitude > 180
        OR p_location_b_longitude < -180 OR p_location_b_longitude > 180 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Longitude must be between -180 and 180 degrees.';
    END IF;

    IF p_distance_km < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Distance cannot be negative.';
    END IF;

    INSERT INTO distance_calculations (
        location_a_latitude, location_a_longitude,
        location_b_latitude, location_b_longitude, distance_km
    ) VALUES (
        p_location_a_latitude, p_location_a_longitude,
        p_location_b_latitude, p_location_b_longitude, p_distance_km
    );
END $$
DELIMITER ;
