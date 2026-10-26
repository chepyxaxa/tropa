<?php

session_start();

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /frontend/register.php');
    exit;
}

$login = trim($_POST['login'] ?? '');
$password = $_POST['password'] ?? '';
$fullName = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');

$errors = [
    'login' => '',
    'password' => '',
    'full_name' => '',
    'phone' => '',
    'email' => ''
];

// Проверка логина
if (!preg_match('/^[a-zA-Z0-9]{6,}$/', $login)) {
    $errors['login'] = 'Логин должен содержать не менее 6 латинских букв или цифр.';
}

// Проверка пароля
if (strlen($password) < 8) {
    $errors['password'] = 'Пароль должен содержать не менее 8 символов.';
}

// Проверка ФИО
if (!preg_match('/^[А-Яа-яЁё\s-]+$/u', $fullName)) {
    $errors['full_name'] = 'Введите ФИО кириллицей.';
}

// Проверка телефона
if (!preg_match('/^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone)) {
    $errors['phone'] = 'Телефон должен быть в формате 8(900)123-45-67.';
}

// Проверка электронной почты
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Введите корректный адрес электронной почты.';
}

$userModel = new User($pdo);

// Проверяем логин на уникальность
if ($errors['login'] === '') {
    if ($userModel->findByLogin($login)) {
        $errors['login'] = 'Такой логин уже занят.';
    }
}

// Если ошибок нет, создаём пользователя
if (!in_array(true, array_map(
    fn($error) => $error !== '',
    $errors
), true)) {
    try {
        $userModel->create(
            $login,
            $password,
            $fullName,
            $phone,
            $email
        );

        $_SESSION['success'] = 'Регистрация прошла успешно. Теперь войдите в аккаунт.';
        header('Location: /frontend/login.php');
        exit;

    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'users.email')) {
            $errors['email'] = 'Этот адрес электронной почты уже зарегистрирован.';
        } elseif (str_contains($e->getMessage(), 'users.login')) {
            $errors['login'] = 'Такой логин уже занят.';
        } else {
            $errors['email'] = 'Не удалось зарегистрировать пользователя. Проверьте введённые данные.';
        }
    }
}

// Сохраняем ошибки и введённые данные
$_SESSION['errors'] = $errors;
$_SESSION['old'] = [
    'login' => $login,
    'full_name' => $fullName,
    'phone' => $phone,
    'email' => $email
];

header('Location: /frontend/register.php');
exit;
