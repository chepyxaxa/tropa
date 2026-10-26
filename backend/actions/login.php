<?php

session_start();

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /frontend/login.php');
    exit;
}

$login = trim($_POST['login'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];
$user = false;

if ($login === '' || $password === '') {
    $errors[] = 'Заполните логин и пароль.';
} else {
    $userModel = new User($pdo);
    $user = $userModel->findByLogin($login);

    if (!$user || !$userModel->verifyPassword($user, $password)) {
        $errors[] = 'Неверный логин или пароль.';
    }
}

if ($errors) {
    $_SESSION['login_errors'] = $errors;
    header('Location: /frontend/login.php');
    exit;
}

session_regenerate_id(true);

$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['user_login'] = $user['login'];
$_SESSION['user_name'] = $user['full_name'];

$_SESSION['success'] = 'Вы успешно вошли в аккаунт!';

header('Location: /');
exit;
