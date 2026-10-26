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

$excursionId = filter_input(INPUT_POST, 'excursion_id', FILTER_VALIDATE_INT);
$date = trim($_POST['excursion_date'] ?? '');
$participants = filter_input(INPUT_POST, 'participants', FILTER_VALIDATE_INT);
$paymentMethodId = filter_input(INPUT_POST, 'payment_method_id', FILTER_VALIDATE_INT);

$errors = [];

if (!$excursionId) {
    $errors[] = 'Выберите экскурсию.';
}

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

if ($participants === false || $participants < 1 || $participants > 10) {
    $errors[] = 'Количество участников должно быть от 1 до 10.';
}

if (!$paymentMethodId) {
    $errors[] = 'Выберите способ оплаты.';
}

if (!$errors) {
    $stmt = $pdo->prepare('SELECT id FROM excursions WHERE id = :id');
    $stmt->execute(['id' => $excursionId]);

    if (!$stmt->fetch()) {
        $errors[] = 'Выбранная экскурсия не найдена.';
    }

    $stmt = $pdo->prepare('SELECT id FROM payment_methods WHERE id = :id');
    $stmt->execute(['id' => $paymentMethodId]);

    if (!$stmt->fetch()) {
        $errors[] = 'Выберите доступный способ оплаты.';
    }
}

if ($errors) {
    $_SESSION['application_errors'] = $errors;
    $_SESSION['application_old'] = $_POST;

    header('Location: /frontend/application_create.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id FROM application_statuses WHERE name = :name'
);
$stmt->execute(['name' => 'Новая']);
$status = $stmt->fetch();

if (!$status) {
    exit('Не найден статус «Новая».');
}

$stmt = $pdo->prepare(
    'INSERT INTO applications
        (user_id, excursion_id, excursion_date, participants, payment_method_id, status_id)
     VALUES
        (:user_id, :excursion_id, :excursion_date, :participants, :payment_method_id, :status_id)'
);

$stmt->execute([
    'user_id' => (int) $_SESSION['user_id'],
    'excursion_id' => $excursionId,
    'excursion_date' => $dateObject->format('Y-m-d'),
    'participants' => $participants,
    'payment_method_id' => $paymentMethodId,
    'status_id' => $status['id']
]);

$_SESSION['application_success'] = 'Заявка успешно отправлена!';

header('Location: /frontend/applications.php');
exit;