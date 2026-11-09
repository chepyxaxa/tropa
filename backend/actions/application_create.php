
<?php

session_start();

require_once __DIR__ . '/../bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /frontend/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /frontend/application_create.php');
    exit;
}

// Получаем данные формы
$excursionId = filter_input(INPUT_POST, 'excursion_id', FILTER_VALIDATE_INT);
$date = trim($_POST['excursion_date'] ?? '');
$participants = filter_input(INPUT_POST, 'participants', FILTER_VALIDATE_INT);
$paymentMethodId = filter_input(INPUT_POST, 'payment_method_id', FILTER_VALIDATE_INT);

$errors = [];

// Проверяем экскурсию
if (!$excursionId || $excursionId < 1) {
    $errors[] = 'Выберите экскурсию.';
}

// Проверяем дату
$dateObject = DateTime::createFromFormat('!Y-m-d', $date);

if (
    !$dateObject ||
    $dateObject->format('Y-m-d') !== $date
) {
    $errors[] = 'Введите корректную дату.';
} else {
    $weekday = (int) $dateObject->format('N');

    if ($weekday !== 6 && $weekday !== 7) {
        $errors[] = 'Экскурсия может проходить только в субботу или воскресенье.';
    }

    if ($dateObject < new DateTime('today')) {
        $errors[] = 'Нельзя выбрать прошедшую дату.';
    }
}

// Проверяем количество участников
if (
    $participants === false ||
    $participants < 1 ||
    $participants > 10
) {
    $errors[] = 'Количество участников должно быть от 1 до 10.';
}

// Проверяем способ оплаты
if (!$paymentMethodId || $paymentMethodId < 1) {
    $errors[] = 'Выберите способ оплаты.';
}

// Если есть ошибки, возвращаем пользователя к форме
if ($errors) {
    $_SESSION['application_errors'] = $errors;
    $_SESSION['application_old'] = $_POST;

    header('Location: /frontend/application_create.php');
    exit;
}

// Создаём заявку через класс Application
$result = $application->create(
    (int) $_SESSION['user_id'],
    $excursionId,
    $dateObject->format('Y-m-d'),
    $participants,
    $paymentMethodId
);

// Обрабатываем результат
if (!$result['success']) {
    $_SESSION['application_errors'] = [$result['message']];
    $_SESSION['application_old'] = $_POST;

    header('Location: /frontend/application_create.php');
    exit;
}

$_SESSION['application_success'] = $result['message'];

header('Location: /frontend/applications.php');
exit;
