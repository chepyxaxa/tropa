<?php

$dbPath = __DIR__ . '/database/tropa.db';

try {
    $pdo = new PDO('sqlite:' . $dbPath);

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

    $pdo->exec('PRAGMA foreign_keys = ON');

} catch (PDOException $e) {
    exit('Ошибка подключения к базе данных.');
}
