<?php

session_start();

require_once __DIR__ . '/../backend/bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /frontend/login.php');
    exit;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$success = $_SESSION['application_success'] ?? '';
$reviewSuccess = $_SESSION['review_success'] ?? '';
$reviewErrors = $_SESSION['review_errors'] ?? [];
$reviewOld = $_SESSION['review_old'] ?? [];

unset(
    $_SESSION['application_success'],
    $_SESSION['review_success'],
    $_SESSION['review_errors'],
    $_SESSION['review_old']
);

$stmt = $pdo->prepare(
    'SELECT
        applications.id,
        excursions.name AS excursion_name,
        applications.excursion_date,
        applications.participants,
        payment_methods.name AS payment_name,
        application_statuses.name AS status_name
     FROM applications
     JOIN excursions
        ON applications.excursion_id = excursions.id
     JOIN payment_methods
        ON applications.payment_method_id = payment_methods.id
     JOIN application_statuses
        ON applications.status_id = application_statuses.id
     WHERE applications.user_id = :user_id
     ORDER BY applications.id DESC'
);

$stmt->execute([
    'user_id' => (int) $_SESSION['user_id']
]);

$applications = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заявки — Тропа</title>
</head>
<body>

    <h1>Мои заявки</h1>

    <?php if ($success !== ''): ?>
        <p style="color: green;"><?= e($success) ?></p>
    <?php endif; ?>

    <?php if ($reviewSuccess !== ''): ?>
        <p style="color: green;"><?= e($reviewSuccess) ?></p>
    <?php endif; ?>

    <?php if ($reviewErrors): ?>
        <div style="color: red;">
            <?php foreach ($reviewErrors as $reviewError): ?>
                <p><?= e($reviewError) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!$applications): ?>

        <p>У вас пока нет заявок на экскурсии.</p>

    <?php else: ?>

        <?php foreach ($applications as $application): ?>

            <section>
                <h2><?= e($application['excursion_name']) ?></h2>

                <p>
                    Дата:
                    <?= e(date(
                        'd.m.Y',
                        strtotime($application['excursion_date'])
                    )) ?>
                </p>

                <p>
                    Количество участников:
                    <?= (int) $application['participants'] ?>
                </p>

                <p>
                    Способ оплаты:
                    <?= e($application['payment_name']) ?>
                </p>

                <p>
                    Статус:
                    <?= e($application['status_name']) ?>
                </p>

                <?php
                $reviewCheck = $pdo->prepare(
                    'SELECT id FROM reviews WHERE application_id = :application_id'
                );
                $reviewCheck->execute([
                    'application_id' => $application['id']
                ]);
                $hasReview = (bool) $reviewCheck->fetch();
                ?>
                <?php if ($application['status_name'] === 'Экскурсия состоялась'): ?>
                    <?php if ($hasReview): ?>
                        <p>Вы уже оставили отзыв на эту экскурсию.</p>
                    <?php else: ?>
                        <form
                        action="/backend/actions/review_create.php"
                        method="POST"
                        >
                            <input
                                type="hidden"
                                name="application_id"
                                value="<?= (int) $application['id'] ?>"
                            >
                            <p>
                                <label for="review-<?= (int) $application['id'] ?>">
                                    Ваш отзыв:
                                </label><br>
                                <textarea
                                    id="review-<?= (int) $application['id'] ?>"
                                    name="review_text"
                                    rows="4"
                                    maxlength="2000"
                                    required
                                ><?= e($reviewOld[$application['id']] ?? '') ?></textarea>
                            </p>
                            <button type="submit">Оставить отзыв</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>

                <hr>
            </section>

        <?php endforeach; ?>

    <?php endif; ?>

    <p>
        <a href="/frontend/application_create.php">
            Записаться на экскурсию
        </a>
    </p>

    <p><a href="/">На главную</a></p>

</body>
</html>