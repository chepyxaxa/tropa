<?php

session_start();

require_once __DIR__ . '/../backend/bootstrap.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Вход администратора
    if ($action === 'login') {
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($login === 'Admin' && $password === 'Tropa2026') {
            session_regenerate_id(true);
            $_SESSION['is_admin'] = true;

            header('Location: /frontend/admin.php');
            exit;
        }

        $error = 'Неверный логин или пароль администратора.';
    }

    // Выход администратора
    elseif ($action === 'logout' && !empty($_SESSION['is_admin'])) {
        unset($_SESSION['is_admin']);

        header('Location: /frontend/admin.php');
        exit;
    }

    // Изменение статуса заявки
    elseif ($action === 'update_status' && !empty($_SESSION['is_admin'])) {
        $applicationId = filter_input(
            INPUT_POST,
            'application_id',
            FILTER_VALIDATE_INT
        );

        $newStatus = trim($_POST['status'] ?? '');

        $allowedTransitions = [
            'Новая' => ['Подтверждена', 'Отменена'],
            'Подтверждена' => ['Экскурсия состоялась', 'Отменена'],
            'Экскурсия состоялась' => [],
            'Отменена' => []
        ];

        try {
            if (!$applicationId) {
                throw new RuntimeException('Некорректный номер заявки.');
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'SELECT
                    applications.id,
                    application_statuses.name AS current_status
                 FROM applications
                 JOIN application_statuses
                    ON applications.status_id = application_statuses.id
                 WHERE applications.id = :id'
            );

            $stmt->execute(['id' => $applicationId]);
            $application = $stmt->fetch();

            if (!$application) {
                throw new RuntimeException('Заявка не найдена.');
            }

            $currentStatus = $application['current_status'];

            if (
                !isset($allowedTransitions[$currentStatus]) ||
                !in_array(
                    $newStatus,
                    $allowedTransitions[$currentStatus],
                    true
                )
            ) {
                throw new RuntimeException(
                    'Такой переход между статусами запрещён.'
                );
            }

            $stmt = $pdo->prepare(
                'SELECT id FROM application_statuses WHERE name = :name'
            );
            $stmt->execute(['name' => $newStatus]);
            $status = $stmt->fetch();

            if (!$status) {
                throw new RuntimeException('Указанный статус не найден.');
            }

            $stmt = $pdo->prepare(
                'UPDATE applications
                 SET status_id = :status_id
                 WHERE id = :id'
            );

            $stmt->execute([
                'status_id' => $status['id'],
                'id' => $applicationId
            ]);

            $pdo->commit();

            $_SESSION['admin_message'] = 'Статус заявки успешно изменён.';

            header('Location: /frontend/admin.php');
            exit;

        } catch (RuntimeException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = $exception->getMessage();

        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error = 'Не удалось изменить статус. Попробуйте ещё раз.';
        }
    }
}

$message = $_SESSION['admin_message'] ?? '';
unset($_SESSION['admin_message']);

$isAdmin = !empty($_SESSION['is_admin']);
$applications = [];

if ($isAdmin) {
    $stmt = $pdo->query(
        'SELECT
            applications.id,
            users.full_name,
            users.phone,
            users.email,
            excursions.name AS excursion_name,
            applications.excursion_date,
            applications.participants,
            payment_methods.name AS payment_name,
            application_statuses.name AS status_name
         FROM applications
         JOIN users
            ON applications.user_id = users.id
         JOIN excursions
            ON applications.excursion_id = excursions.id
         JOIN payment_methods
            ON applications.payment_method_id = payment_methods.id
         JOIN application_statuses
            ON applications.status_id = application_statuses.id
         ORDER BY applications.id DESC'
    );

    $applications = $stmt->fetchAll();
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора — Тропа</title>
</head>
<body>

    <h1>Панель администратора</h1>

    <?php if (!$isAdmin): ?>

        <p>Войдите, чтобы просматривать и обрабатывать заявки.</p>

        <?php if ($error !== ''): ?>
            <p style="color: red;"><?= e($error) ?></p>
        <?php endif; ?>

        <form action="/frontend/admin.php" method="POST">
            <input type="hidden" name="action" value="login">

            <p>
                <label for="login">Логин администратора:</label><br>
                <input
                    type="text"
                    id="login"
                    name="login"
                    autocomplete="username"
                    required
                >
            </p>

            <p>
                <label for="password">Пароль:</label><br>
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >
            </p>

            <button type="submit">Войти</button>
        </form>

    <?php else: ?>

        <p>Вы вошли как администратор.</p>

        <?php if ($message !== ''): ?>
            <p style="color: green;"><?= e($message) ?></p>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <p style="color: red;"><?= e($error) ?></p>
        <?php endif; ?>

        <form action="/frontend/admin.php" method="POST">
            <input type="hidden" name="action" value="logout">
            <button type="submit">Выйти из панели</button>
        </form>

        <h2>Все заявки</h2>

        <?php if (!$applications): ?>

            <p>Заявок пока нет.</p>

        <?php else: ?>

            <?php foreach ($applications as $application): ?>

                <section>
                    <hr>

                    <h3>
                        Заявка №<?= (int) $application['id'] ?>
                        — <?= e($application['excursion_name']) ?>
                    </h3>

                    <p>
                        Клиент: <?= e($application['full_name']) ?>
                    </p>

                    <p>
                        Телефон: <?= e($application['phone']) ?>
                    </p>

                    <p>
                        Email: <?= e($application['email']) ?>
                    </p>

                    <p>
                        Дата экскурсии:
                        <?= e(date(
                            'd.m.Y',
                            strtotime($application['excursion_date'])
                        )) ?>
                    </p>

                    <p>
                        Участников: <?= (int) $application['participants'] ?>
                    </p>

                    <p>
                        Оплата: <?= e($application['payment_name']) ?>
                    </p>

                    <p>
                        Текущий статус:
                        <strong><?= e($application['status_name']) ?></strong>
                    </p>

                    <?php
                    $transitions = [
                        'Новая' => ['Подтверждена', 'Отменена'],
                        'Подтверждена' => [
                            'Экскурсия состоялась',
                            'Отменена'
                        ],
                        'Экскурсия состоялась' => [],
                        'Отменена' => []
                    ];

                    $nextStatuses =
                        $transitions[$application['status_name']] ?? [];
                    ?>

                    <?php if ($nextStatuses): ?>

                        <form action="/frontend/admin.php" method="POST">
                            <input
                                type="hidden"
                                name="action"
                                value="update_status"
                            >

                            <input
                                type="hidden"
                                name="application_id"
                                value="<?= (int) $application['id'] ?>"
                            >

                            <label for="status-<?= (int) $application['id'] ?>">
                                Изменить статус:
                            </label>

                            <select
                                id="status-<?= (int) $application['id'] ?>"
                                name="status"
                                required
                            >
                                <?php foreach ($nextStatuses as $statusName): ?>
                                    <option value="<?= e($statusName) ?>">
                                        <?= e($statusName) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <button type="submit">Сохранить статус</button>
                        </form>

                    <?php else: ?>

                        <p>Заявка закрыта, изменить её статус нельзя.</p>

                    <?php endif; ?>

                </section>

            <?php endforeach; ?>

        <?php endif; ?>

    <?php endif; ?>

    <p><a href="/">На главную</a></p>

</body>
</html>