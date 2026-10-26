<?php

session_start();

require_once __DIR__ . '/../bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /frontend/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /frontend/applications.php');
    exit;
}

$applicationId = filter_input(
    INPUT_POST,
    'application_id',
    FILTER_VALIDATE_INT
);

$reviewText = trim($_POST['review_text'] ?? '');

$errors = [];

if (!$applicationId) {
    $errors[] = 'Некорректная заявка.';
}

if ($reviewText === '') {
    $errors[] = 'Напишите текст отзыва.';
}

if (strlen($reviewText) > 8000) {
    $errors[] = 'Отзыв не должен превышать 2000 символов.';
}

if ($errors) {
    $_SESSION['review_errors'] = $errors;
    $_SESSION['review_old'][$applicationId ?: 0] = $reviewText;

    header('Location: /frontend/applications.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT applications.id
     FROM applications
     JOIN application_statuses
        ON applications.status_id = application_statuses.id
     WHERE applications.id = :application_id
       AND applications.user_id = :user_id
       AND application_statuses.name = :status'
);

$stmt->execute([
    'application_id' => $applicationId,
    'user_id' => (int) $_SESSION['user_id'],
    'status' => 'Экскурсия состоялась'
]);

$application = $stmt->fetch();

if (!$application) {
    $_SESSION['review_errors'] = [
        'Отзыв можно оставить только на состоявшуюся экскурсию из ваших заявок.'
    ];

    header('Location: /frontend/applications.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id FROM reviews WHERE application_id = :application_id'
);

$stmt->execute([
    'application_id' => $applicationId
]);

if ($stmt->fetch()) {
    $_SESSION['review_errors'] = [
        'Для этой заявки отзыв уже оставлен.'
    ];

    header('Location: /frontend/applications.php');
    exit;
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO reviews (application_id, review_text)
         VALUES (:application_id, :review_text)'
    );

    $stmt->execute([
        'application_id' => $applicationId,
        'review_text' => $reviewText
    ]);

    $_SESSION['review_success'] = 'Отзыв успешно сохранён. Спасибо!';

} catch (PDOException $exception) {
    $_SESSION['review_errors'] = [
        'Не удалось сохранить отзыв. Возможно, он уже был оставлен.'
    ];
}

header('Location: /frontend/applications.php');
exit;