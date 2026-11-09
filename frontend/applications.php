<?php

session_start();

require_once __DIR__ . '/../backend/bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /frontend/login.php');
    exit;
}

$pageTitle = 'Мои заявки — Тропа';


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

require_once __DIR__ . '/partials/header.php';

?>

<section class="applications-section">

    <div class="applications-container">

        <div class="applications-heading">
            <div>
                <h1 class="section-title">Мои заявки</h1>
                <p class="section-description">
                    Здесь отображаются ваши записи на экскурсии.
                </p>
            </div>

            <a
                href="/frontend/application_create.php"
                class="btn btn-primary"
            >
                Записаться на экскурсию
            </a>
        </div>

        <?php if ($success !== ''): ?>
            <div class="alert alert-success">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($reviewSuccess !== ''): ?>
            <div class="alert alert-success">
                <?= e($reviewSuccess) ?>
            </div>
        <?php endif; ?>

        <?php if ($reviewErrors): ?>
            <div class="alert alert-danger">
                <?php foreach ($reviewErrors as $reviewError): ?>
                    <p class="mb-1"><?= e($reviewError) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!$applications): ?>

            <div class="empty-applications">
                <h2>Заявок пока нет</h2>
                <p>Запишитесь на экскурсию, чтобы она появилась здесь.</p>

                <a
                    href="/frontend/application_create.php"
                    class="btn btn-primary"
                >
                    Выбрать экскурсию
                </a>
            </div>

        <?php else: ?>

            <div class="applications-list">

                <?php foreach ($applications as $application): ?>

                    <?php
                    $reviewCheck = $pdo->prepare(
                        'SELECT id FROM reviews WHERE application_id = :application_id'
                    );

                    $reviewCheck->execute([
                        'application_id' => $application['id']
                    ]);

                    $hasReview = (bool) $reviewCheck->fetch();
                    ?>

                    <article class="application-card">

                        <div class="application-card-header">
                            <h2>
                                <?= e($application['excursion_name']) ?>
                            </h2>

                            <span class="application-status">
                                <?= e($application['status_name']) ?>
                            </span>
                        </div>

                        <div class="application-details">

                            <div class="application-detail">
                                <span>Дата экскурсии</span>
                                <strong>
                                    <?= e(date(
                                        'd.m.Y',
                                        strtotime($application['excursion_date'])
                                    )) ?>
                                </strong>
                            </div>

                            <div class="application-detail">
                                <span>Участники</span>
                                <strong>
                                    <?= (int) $application['participants'] ?>
                                </strong>
                            </div>

                            <div class="application-detail">
                                <span>Способ оплаты</span>
                                <strong>
                                    <?= e($application['payment_name']) ?>
                                </strong>
                            </div>

                        </div>

                        <?php if ($application['status_name'] === 'Экскурсия состоялась'): ?>

                            <div class="application-review">

                                <?php if ($hasReview): ?>

                                    <p class="review-completed">
                                        Вы уже оставили отзыв на эту экскурсию.
                                    </p>

                                <?php else: ?>

                                    <h3>Оставить отзыв</h3>

                                    <form
                                        action="/backend/actions/review_create.php"
                                        method="POST"
                                    >
                                        <input
                                            type="hidden"
                                            name="application_id"
                                            value="<?= (int) $application['id'] ?>"
                                        >

                                        <textarea
                                            class="form-control review-textarea"
                                            id="review-<?= (int) $application['id'] ?>"
                                            name="review_text"
                                            rows="4"
                                            maxlength="2000"
                                            placeholder="Поделитесь впечатлениями об экскурсии..."
                                            required
                                        ><?= e($reviewOld[$application['id']] ?? '') ?></textarea>

                                        <button
                                            type="submit"
                                            class="btn btn-primary review-button"
                                        >
                                            Отправить отзыв
                                        </button>
                                    </form>

                                <?php endif; ?>

                            </div>

                        <?php endif; ?>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>