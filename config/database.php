<?php
declare(strict_types=1);

/**
 * Change these values for your local MySQL installation. Credentials stay on
 * the server and are never sent to the browser.
 */
const DB_HOST = '127.0.0.1';
const DB_NAME = 'geo_distance_db';
const DB_USER = 'root';
const DB_PASS = '';

function getDatabaseConnection(): PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    return new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}
