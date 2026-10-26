<?php

session_start();

require_once __DIR__ . '/../backend/bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /frontend/login.php');
    exit;
}

$errors = $_SESSION['application_errors'] ?? [];
$old = $_SESSION['application_old'] ?? [];

unset($_SESSION['application_errors'], $_SESSION['application_old']);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$excursions = $pdo->query(
    'SELECT id, name, description FROM excursions ORDER BY id'
)->fetchAll();

$paymentMethods = $pdo->query(
    'SELECT id, name FROM payment_methods ORDER BY id'
)->fetchAll();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Запись на экскурсию — Тропа</title>
</head>
<body>

    <h1>Запись на экскурсию</h1>

    <?php if ($errors): ?>
        <div style="color: red;">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="/backend/actions/application_create.php" method="POST">

        <p>
            <label for="excursion_id">Экскурсия:</label><br>
            <select id="excursion_id" name="excursion_id" required>
                <option value="">Выберите экскурсию</option>

                <?php foreach ($excursions as $excursion): ?>
                    <option
                        value="<?= (int) $excursion['id'] ?>"
                        <?= (string) ($old['excursion_id'] ?? '') === (string) $excursion['id'] ? 'selected' : '' ?>
                    >
                        <?= e($excursion['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="excursion_date">Дата экскурсии:</label><br>
            <input
                type="date"
                id="excursion_date"
                name="excursion_date"
                min="<?= date('Y-m-d') ?>"
                value="<?php
                    $oldDate = $old['excursion_date'] ?? '';
                    if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $oldDate)) {
                        $oldDate = DateTime::createFromFormat('!d.m.Y', $oldDate)
                            ? DateTime::createFromFormat('!d.m.Y', $oldDate)->format('Y-m-d')
                            : '';
                    }
                    echo e($oldDate);
                ?>"
                required
            >

            <br>
            <small>Выберите будущую субботу или воскресенье в календаре.</small>
        </p>

        <p>
            <label for="participants">Количество участников:</label><br>
            <input
                type="number"
                id="participants"
                name="participants"
                min="1"
                max="10"
                value="<?= e((string) ($old['participants'] ?? '1')) ?>"
                required
            >
        </p>

        <p>
            <label for="payment_method_id">Способ оплаты:</label><br>
            <select id="payment_method_id" name="payment_method_id" required>
                <option value="">Выберите способ оплаты</option>

                <?php foreach ($paymentMethods as $method): ?>
                    <option
                        value="<?= (int) $method['id'] ?>"
                        <?= (string) ($old['payment_method_id'] ?? '') === (string) $method['id'] ? 'selected' : '' ?>
                    >
                        <?= e($method['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <button type="submit">Отправить заявку</button>

    </form>

    <p><a href="/">На главную</a></p>
    <p><a href="/frontend/applications.php">Мои заявки</a></p>

</body>
</html>