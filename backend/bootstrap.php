<?php

require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Application.php';

$database = new Database();
$pdo = $database->getConnection();

$user = new User($pdo);
$application = new Application($pdo);