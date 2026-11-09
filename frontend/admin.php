<?php

session_start();

require_once __DIR__ . '/../backend/bootstrap.php';

$pageTitle = 'Панель администратора — Тропа';

if (empty($_SESSION['is_admin'])) {
    header('Location: /frontend/login.php');
    exit;
}

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Выход администратора
    if ($action === 'logout') {
        unset($_SESSION['is_admin']);

        header('Location: /');
        exit;
    }

    // Изменение статуса заявки
    elseif ($action === 'update_status') {
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

            $redirectParams = [
                'status' => $_POST['filter_status'] ?? '',
                'excursion' => $_POST['filter_excursion'] ?? '',
                'page' => $_POST['filter_page'] ?? '1'
            ];

            header(
                'Location: /frontend/admin.php?' .
                http_build_query($redirectParams)
            );
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

$applications = [];
$totalPages = 1;
$currentPage = max(1, (int) ($_GET['page'] ?? 1));

$statusFilter = trim($_GET['status'] ?? '');
$excursionFilter = trim($_GET['excursion'] ?? '');

$statuses = [];
$excursions = [];
$totalApplications = 0;

// Получение статусов и экскурсий
$statuses = $pdo->query(
    'SELECT id, name FROM application_statuses ORDER BY id'
)->fetchAll();

$excursions = $pdo->query(
    'SELECT id, name FROM excursions ORDER BY id'
)->fetchAll();

// Проверка выбранных фильтров
$validStatuses = array_column($statuses, 'name');

$validExcursionIds = array_map(
    'strval',
    array_column($excursions, 'id')
);

if (!in_array($statusFilter, $validStatuses, true)) {
    $statusFilter = '';
}

if (!in_array($excursionFilter, $validExcursionIds, true)) {
    $excursionFilter = '';
}

// Формирование условий выборки
$conditions = [];
$params = [];

if ($statusFilter !== '') {
    $conditions[] = 'application_statuses.name = :status';
    $params['status'] = $statusFilter;
}

if ($excursionFilter !== '') {
    $conditions[] = 'applications.excursion_id = :excursion_id';
    $params['excursion_id'] = (int) $excursionFilter;
}

$where = '';

if ($conditions) {
    $where = ' WHERE ' . implode(' AND ', $conditions);
}

// Подсчёт заявок
$countStmt = $pdo->prepare(
    'SELECT COUNT(*)
     FROM applications
     JOIN application_statuses
        ON applications.status_id = application_statuses.id'
    . $where
);

$countStmt->execute($params);
$totalApplications = (int) $countStmt->fetchColumn();

// Пагинация
$perPage = 5;
$totalPages = max(1, (int) ceil($totalApplications / $perPage));

$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $perPage;

// Получение заявок
$stmt = $pdo->prepare(
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
        ON applications.status_id = application_statuses.id'
    . $where .
    ' ORDER BY applications.id DESC
      LIMIT ' . (int) $perPage .
    ' OFFSET ' . (int) $offset
);

$stmt->execute($params);
$applications = $stmt->fetchAll();

require_once __DIR__ . '/partials/header.php';

?>

<section class="admin-section">

    <div class="admin-container">

        <div class="admin-heading">

            <div>
                <h1 class="section-title">Панель администратора</h1>

                <p class="section-description">
                    Управление заявками на экскурсии.
                </p>
            </div>

            <form action="/frontend/admin.php" method="POST">
                <input type="hidden" name="action" value="logout">

                <button type="submit" class="btn btn-outline-secondary">
                    Выйти из панели
                </button>
            </form>

        </div>

        <div class="admin-summary">
            <span>Всего заявок</span>
            <strong><?= $totalApplications ?></strong>
        </div>

        <div class="admin-filters">

            <h2>Фильтры</h2>

            <form action="/frontend/admin.php" method="GET">

                <div class="admin-filter-fields">

                    <div>
                        <label for="filter-status" class="form-label">
                            Статус
                        </label>

                        <select
                            class="form-select"
                            id="filter-status"
                            name="status"
                        >
                            <option value="">Все статусы</option>

                            <?php foreach ($statuses as $status): ?>
                                <option
                                    value="<?= e($status['name']) ?>"
                                    <?= $statusFilter === $status['name'] ? 'selected' : '' ?>
                                >
                                    <?= e($status['name']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </div>

                    <div>
                        <label for="filter-excursion" class="form-label">
                            Экскурсия
                        </label>

                        <select
                            class="form-select"
                            id="filter-excursion"
                            name="excursion"
                        >
                            <option value="">Все экскурсии</option>

                            <?php foreach ($excursions as $excursion): ?>
                                <option
                                    value="<?= (int) $excursion['id'] ?>"
                                    <?= $excursionFilter === (string) $excursion['id'] ? 'selected' : '' ?>
                                >
                                    <?= e($excursion['name']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </div>

                </div>

                <div class="admin-filter-buttons">

                    <button type="submit" class="btn btn-primary">
                        Применить
                    </button>

                    <a
                        href="/frontend/admin.php"
                        class="btn btn-outline-secondary"
                    >
                        Сбросить
                    </a>

                </div>

            </form>

        </div>

        <div class="admin-applications-heading">
            <h2>Заявки</h2>
            <span>Страница <?= $currentPage ?> из <?= $totalPages ?></span>
        </div>

        <?php if (!$applications): ?>

            <div class="empty-applications">
                <h2>Заявок не найдено</h2>
                <p>Попробуйте изменить параметры фильтрации.</p>
            </div>

        <?php else: ?>

            <div class="admin-applications-list">

                <?php foreach ($applications as $application): ?>

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

                    <article class="admin-application-card">

                        <div class="admin-card-heading">

                            <h3>
                                Заявка №<?= (int) $application['id'] ?>
                            </h3>

                            <span class="application-status">
                                <?= e($application['status_name']) ?>
                            </span>

                        </div>

                        <h4 class="admin-excursion-name">
                            <?= e($application['excursion_name']) ?>
                        </h4>

                        <div class="admin-client-details">

                            <div>
                                <span>Клиент</span>
                                <strong><?= e($application['full_name']) ?></strong>
                            </div>

                            <div>
                                <span>Телефон</span>
                                <strong><?= e($application['phone']) ?></strong>
                            </div>

                            <div>
                                <span>Email</span>
                                <strong><?= e($application['email']) ?></strong>
                            </div>

                            <div>
                                <span>Дата экскурсии</span>
                                <strong>
                                    <?= e(date(
                                        'd.m.Y',
                                        strtotime($application['excursion_date'])
                                    )) ?>
                                </strong>
                            </div>

                            <div>
                                <span>Участники</span>
                                <strong><?= (int) $application['participants'] ?></strong>
                            </div>

                            <div>
                                <span>Способ оплаты</span>
                                <strong><?= e($application['payment_name']) ?></strong>
                            </div>

                        </div>

                        <?php if ($nextStatuses): ?>

                            <form
                                action="/frontend/admin.php"
                                method="POST"
                                class="admin-status-form"
                            >

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

                                <input
                                    type="hidden"
                                    name="filter_status"
                                    value="<?= e($statusFilter) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="filter_excursion"
                                    value="<?= e($excursionFilter) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="filter_page"
                                    value="<?= $currentPage ?>"
                                >

                                <div>
                                    <label
                                        for="status-<?= (int) $application['id'] ?>"
                                        class="form-label"
                                    >
                                        Изменить статус
                                    </label>

                                    <select
                                        class="form-select"
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
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    Сохранить
                                </button>

                            </form>

                        <?php else: ?>

                            <p class="admin-closed-status">
                                Заявка закрыта, изменить её статус нельзя.
                            </p>

                        <?php endif; ?>

                    </article>

                <?php endforeach; ?>

            </div>

            <?php if ($totalPages > 1): ?>

                <nav class="admin-pagination" aria-label="Страницы заявок">

                    <?php if ($currentPage > 1): ?>

                        <a
                            href="?<?= e(http_build_query([
                                'status' => $statusFilter,
                                'excursion' => $excursionFilter,
                                'page' => $currentPage - 1
                            ])) ?>"
                            class="pagination-link"
                        >
                            Назад
                        </a>

                    <?php endif; ?>

                    <?php for ($page = 1; $page <= $totalPages; $page++): ?>

                        <a
                            href="?<?= e(http_build_query([
                                'status' => $statusFilter,
                                'excursion' => $excursionFilter,
                                'page' => $page
                            ])) ?>"
                            class="pagination-link <?= $page === $currentPage ? 'active' : '' ?>"
                        >
                            <?= $page ?>
                        </a>

                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>

                        <a
                            href="?<?= e(http_build_query([
                                'status' => $statusFilter,
                                'excursion' => $excursionFilter,
                                'page' => $currentPage + 1
                            ])) ?>"
                            class="pagination-link"
                        >
                            Далее
                        </a>

                    <?php endif; ?>

                </nav>

            <?php endif; ?>

        <?php endif; ?>

    </div>

</section>

<?php if ($message !== '' || $error !== ''): ?>

    <div class="admin-toast <?= $error !== '' ? 'admin-toast-error' : '' ?>" id="admin-toast">
        <?= e($error !== '' ? $error : $message) ?>
    </div>

<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toast = document.getElementById('admin-toast');

    if (toast) {
        setTimeout(function () {
            toast.classList.add('admin-toast-hidden');
        }, 3500);
    }
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>