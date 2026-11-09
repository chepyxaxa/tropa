<?php

session_start();

$pageTitle = 'Регистрация — Тропа';

$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];
$success = $_SESSION['success'] ?? '';

unset($_SESSION['errors'], $_SESSION['old'], $_SESSION['success']);

require_once __DIR__ . '/partials/header.php';

?>

<section class="form-section">
    <div class="form-container register-container">

        <h1 class="form-title">Регистрация</h1>
        <p class="form-description">
            Создайте аккаунт, чтобы записываться на экскурсии.
        </p>

        <?php if ($success !== ''): ?>
            <div class="alert alert-success">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <form action="/backend/actions/register.php" method="POST">

            <div class="mb-3">
                <label for="login" class="form-label">Логин</label>
                <input
                    type="text"
                    class="form-control"
                    id="login"
                    name="login"
                    required
                    minlength="6"
                    pattern="[a-zA-Z0-9]+"
                    title="Используйте не менее 6 латинских букв или цифр"
                    value="<?= e($old['login'] ?? '') ?>"
                    autocomplete="username"
                >

                <?php if (!empty($errors['login'])): ?>
                    <small class="text-danger">
                        <?= e($errors['login']) ?>
                    </small>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Пароль</label>
                <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="password"
                    required
                    minlength="8"
                    title="Пароль должен содержать не менее 8 символов"
                    autocomplete="new-password"
                >

                <?php if (!empty($errors['password'])): ?>
                    <small class="text-danger">
                        <?= e($errors['password']) ?>
                    </small>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="full_name" class="form-label">ФИО</label>
                <input
                    type="text"
                    class="form-control"
                    id="full_name"
                    name="full_name"
                    required
                    pattern="[А-Яа-яЁё\s-]+"
                    title="Введите ФИО кириллицей"
                    value="<?= e($old['full_name'] ?? '') ?>"
                    autocomplete="name"
                >

                <?php if (!empty($errors['full_name'])): ?>
                    <small class="text-danger">
                        <?= e($errors['full_name']) ?>
                    </small>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Телефон</label>
                <input
                    type="tel"
                    class="form-control"
                    id="phone"
                    name="phone"
                    required
                    inputmode="numeric"
                    placeholder="8(900)123-45-67"
                    pattern="8\([0-9]{3}\)[0-9]{3}-[0-9]{2}-[0-9]{2}"
                    maxlength="15"
                    title="Введите номер в формате 8(900)123-45-67"
                    value="<?= e($old['phone'] ?? '') ?>"
                    autocomplete="tel"
                >


                <?php if (!empty($errors['phone'])): ?>
                    <small class="text-danger d-block">
                        <?= e($errors['phone']) ?>
                    </small>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="email" class="form-label">Электронная почта</label>
                <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    required
                    value="<?= e($old['email'] ?? '') ?>"
                    autocomplete="email"
                >

                <?php if (!empty($errors['email'])): ?>
                    <small class="text-danger">
                        <?= e($errors['email']) ?>
                    </small>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Зарегистрироваться
            </button>

        </form>

        <p class="form-bottom-text">
            Уже зарегистрированы?
            <a href="/frontend/login.php">Войти</a>
        </p>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const phoneInput = document.getElementById('phone');

    if (!phoneInput) {
        return;
    }

    phoneInput.addEventListener('input', function () {
        let digits = this.value.replace(/\D/g, '');

        if (digits.length === 0) {
            this.value = '';
            return;
        }

        if (digits[0] !== '8') {
            digits = '8' + digits;
        }

        digits = digits.slice(0, 11);

        let formatted = digits[0];

        if (digits.length > 1) {
            formatted += '(' + digits.slice(1, 4);
        }

        if (digits.length >= 4) {
            formatted += ')';
        }

        if (digits.length > 4) {
            formatted += digits.slice(4, 7);
        }

        if (digits.length > 7) {
            formatted += '-' + digits.slice(7, 9);
        }

        if (digits.length > 9) {
            formatted += '-' + digits.slice(9, 11);
        }

        this.value = formatted;
    });
});
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>