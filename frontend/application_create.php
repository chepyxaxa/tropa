
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
    'SELECT id, name, description, price_per_person
     FROM excursions
     ORDER BY id'
)->fetchAll();

$paymentMethods = $pdo->query(
    'SELECT id, name
     FROM payment_methods
     ORDER BY id'
)->fetchAll();

// Получаем даты экскурсий и количество свободных мест
$dates = $pdo->query(
    "SELECT
        ed.id,
        ed.excursion_id,
        ed.excursion_date,
        ed.capacity,
        ed.capacity - COALESCE(
            (
                SELECT SUM(a.participants)
                FROM applications a
                JOIN application_statuses s ON s.id = a.status_id
                WHERE a.excursion_id = ed.excursion_id
                  AND a.excursion_date = ed.excursion_date
                  AND s.name IN ('Новая', 'Подтверждена')
            ),
            0
        ) AS available_seats
     FROM excursion_dates ed
     WHERE ed.excursion_date >= date('now')
     ORDER BY ed.excursion_date"
)->fetchAll();

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
                            data-price="<?= (float) $excursion['price_per_person'] ?>"
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

                <select
                    class="form-select"
                    id="excursion_date"
                    name="excursion_date"
                    required
                >
                    <option value="">Сначала выберите экскурсию</option>

                    <?php foreach ($dates as $date): ?>
                        <option
                            value="<?= e($date['excursion_date']) ?>"
                            data-excursion-id="<?= (int) $date['excursion_id'] ?>"
                            data-unavailable="<?= (int) $date['available_seats'] <= 0 ? 'true' : 'false' ?>"
<?= (int) $date['available_seats'] <= 0 ? 'disabled' : '' ?>
                            <?= ($old['excursion_date'] ?? '') === $date['excursion_date'] ? 'selected' : '' ?>
                        >
                            <?= date('d.m.Y', strtotime($date['excursion_date'])) ?>
                            — <?= (int) $date['available_seats'] ?> мест
                        </option>
                    <?php endforeach; ?>

                </select>

                <small class="form-hint">
                    Доступны только запланированные даты экскурсий.
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

            <div class="mb-3">
                <p class="form-label">Стоимость одного участника</p>
                <p id="price-per-person">Выберите экскурсию</p>
            </div>

            <div class="mb-4">
                <p class="form-label">Итоговая стоимость</p>
                <p id="total-price">—</p>
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
    const excursionSelect = document.getElementById('excursion_id');
    const dateSelect = document.getElementById('excursion_date');
    const participantsInput = document.getElementById('participants');

    const pricePerPerson = document.getElementById('price-per-person');
    const totalPrice = document.getElementById('total-price');

    const oldDate = <?= json_encode($old['excursion_date'] ?? '') ?>;

    function updateDates() {
        const excursionId = excursionSelect.value;

        dateSelect.value = '';

        Array.from(dateSelect.options).forEach(function (option, index) {
            if (index === 0) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const matches = option.dataset.excursionId === excursionId;

            option.hidden = !matches;
            option.disabled = !matches || option.dataset.unavailable === 'true';
        });

        if (oldDate && !dateSelect.dataset.restored) {
            const oldOption = Array.from(dateSelect.options).find(function (option) {
                return option.value === oldDate &&
                    option.dataset.excursionId === excursionId &&
                    !option.disabled;
            });

            if (oldOption) {
                dateSelect.value = oldDate;
            }

            dateSelect.dataset.restored = 'true';
        }

        if (dateSelect.selectedIndex === 0) {
            dateSelect.options[0].textContent = excursionId
                ? 'Выберите дату'
                : 'Сначала выберите экскурсию';
        }
    }

    function updatePrice() {
        const selectedOption = excursionSelect.options[excursionSelect.selectedIndex];
        const price = Number(selectedOption.dataset.price || 0);
        const participants = Number(participantsInput.value || 0);

        if (!excursionSelect.value) {
            pricePerPerson.textContent = 'Выберите экскурсию';
            totalPrice.textContent = '—';
            return;
        }

        pricePerPerson.textContent = price.toLocaleString('ru-RU') + ' ₽';
        totalPrice.textContent =
            (price * participants).toLocaleString('ru-RU') + ' ₽';
    }

    excursionSelect.addEventListener('change', function () {
        oldDate && (dateSelect.dataset.restored = 'true');
        updateDates();
        updatePrice();
    });

    participantsInput.addEventListener('input', updatePrice);

    updateDates();
    updatePrice();
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
