<?php

declare(strict_types=1);

function cach3flow_pdo(): PDO
{
    $host = getenv('CACH3FLOW_DB_HOST') ?: 'sql305.infinityfree.com';
    $database = getenv('CACH3FLOW_DB_NAME') ?: 'if0_43172772_Cach3flow';
    $username = getenv('CACH3FLOW_DB_USER') ?: 'if0_43172772';
    $password = getenv('CACH3FLOW_DB_PASSWORD') ?: '7i0QjHyvs8';

    if ($host === false || $host === '' || $password === false || $password === '') {
        throw new RuntimeException('Database connection settings are incomplete.');
    }

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $database);

    return new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}
