<?php

session_start();

require_once __DIR__ . '/../backend/bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /frontend/login.php');
    exit;
}

$pageTitle = 'Запись на экскурсию — Тропа';

$errors = $_SESSION['application_errors'] ?? [];
$old = $_SESSION['application_old'] ?? [];

unset($_SESSION['application_errors'], $_SESSION['application_old']);

$excursions = $pdo->query(
    'SELECT id, name, description FROM excursions ORDER BY id'
)->fetchAll();

$paymentMethods = $pdo->query(
    'SELECT id, name FROM payment_methods ORDER BY id'
)->fetchAll();

$oldDate = $old['excursion_date'] ?? '';

if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $oldDate)) {
    $dateObject = DateTime::createFromFormat('!d.m.Y', $oldDate);

    $oldDate = $dateObject
        ? $dateObject->format('Y-m-d')
        : '';
}

require_once __DIR__ . '/partials/header.php';

?>

<section class="form-section">

    <div class="form-container application-form-container">

        <h1 class="form-title">Запись на экскурсию</h1>

        <p class="form-description">
            Выберите экскурсию и заполните данные для записи.
        </p>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p class="mb-1"><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="/backend/actions/application_create.php" method="POST">

            <div class="mb-3">
                <label for="excursion_id" class="form-label">
                    Экскурсия
                </label>

                <select
                    class="form-select"
                    id="excursion_id"
                    name="excursion_id"
                    required
                >
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
            </div>

            <div class="mb-3">
                <label for="excursion_date" class="form-label">
                    Дата экскурсии
                </label>

                <input
                    type="date"
                    class="form-control"
                    id="excursion_date"
                    name="excursion_date"
                    min="<?= date('Y-m-d') ?>"
                    value="<?= e($oldDate) ?>"
                    required
                >

                <small class="form-hint">
                    Выберите будущую субботу или воскресенье.
                </small>
            </div>

            <div class="mb-3">
                <label for="participants" class="form-label">
                    Количество участников
                </label>

                <input
                    type="number"
                    class="form-control"
                    id="participants"
                    name="participants"
                    min="1"
                    max="10"
                    value="<?= e((string) ($old['participants'] ?? '1')) ?>"
                    required
                >

                <small class="form-hint">
                    От 1 до 10 человек.
                </small>
            </div>

            <div class="mb-4">
                <label for="payment_method_id" class="form-label">
                    Способ оплаты
                </label>

                <select
                    class="form-select"
                    id="payment_method_id"
                    name="payment_method_id"
                    required
                >
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
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Отправить заявку
            </button>

        </form>

        <div class="form-bottom-text">
            <a href="/frontend/applications.php">Вернуться к моим заявкам</a>
        </div>

    </div>

</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dateInput = document.getElementById('excursion_date');

    if (!dateInput) {
        return;
    }

    dateInput.addEventListener('input', function () {
        if (!this.value) {
            return;
        }

        const date = new Date(this.value + 'T12:00:00');
        const day = date.getDay();

        if (day !== 0 && day !== 6) {
            this.setCustomValidity('Выберите субботу или воскресенье.');
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>