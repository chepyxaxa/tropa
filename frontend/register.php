<?php
session_start();

$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];
$success = $_SESSION['success'] ?? '';

unset($_SESSION['errors'], $_SESSION['old'], $_SESSION['success']);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация — Тропа</title>
</head>
<body>

    <h1>Регистрация на сайте «Тропа»</h1>

    <?php if ($success !== ''): ?>
        <p style="color: green;">
            <?= e($success) ?>
        </p>
    <?php endif; ?>

    <form action="/backend/actions/register.php" method="POST">

        <!-- Логин -->
        <p>
            <label for="login">Логин:</label><br>
            <input
                type="text"
                id="login"
                name="login"
                required
                minlength="6"
                pattern="[a-zA-Z0-9]+"
                title="Используйте не менее 6 латинских букв или цифр"
                value="<?= e($old['login'] ?? '') ?>"
            >

            <?php if (!empty($errors['login'])): ?>
                <br>
                <small style="color: red;">
                    <?= e($errors['login']) ?>
                </small>
            <?php endif; ?>
        </p>

        <!-- Пароль -->
        <p>
            <label for="password">Пароль:</label><br>
            <input
                type="password"
                id="password"
                name="password"
                required
                minlength="8"
                title="Пароль должен содержать не менее 8 символов"
            >

            <?php if (!empty($errors['password'])): ?>
                <br>
                <small style="color: red;">
                    <?= e($errors['password']) ?>
                </small>
            <?php endif; ?>
        </p>

        <!-- ФИО -->
        <p>
            <label for="full_name">ФИО:</label><br>
            <input
                type="text"
                id="full_name"
                name="full_name"
                required
                pattern="[А-Яа-яЁё\s-]+"
                title="Введите ФИО кириллицей"
                value="<?= e($old['full_name'] ?? '') ?>"
            >

            <?php if (!empty($errors['full_name'])): ?>
                <br>
                <small style="color: red;">
                    <?= e($errors['full_name']) ?>
                </small>
            <?php endif; ?>
        </p>

        <!-- Телефон -->
        <p>
            <label for="phone">Телефон:</label><br>
            <input
                type="tel"
                id="phone"
                name="phone"
                required
                inputmode="numeric"
                placeholder="8(900)123-45-67"
                pattern="8\([0-9]{3}\)[0-9]{3}-[0-9]{2}-[0-9]{2}"
                maxlength="15"
                title="Введите номер в формате 8(900)123-45-67"
                value="<?= e($old['phone'] ?? '') ?>"
            >

            <br>
            <small>
                Введите номер телефона. Скобки и тире добавятся автоматически.
            </small>

            <?php if (!empty($errors['phone'])): ?>
                <br>
                <small style="color: red;">
                    <?= e($errors['phone']) ?>
                </small>
            <?php endif; ?>
        </p>

        <!-- Электронная почта -->
        <p>
            <label for="email">Электронная почта:</label><br>
            <input
                type="email"
                id="email"
                name="email"
                required
                value="<?= e($old['email'] ?? '') ?>"
            >

            <?php if (!empty($errors['email'])): ?>
                <br>
                <small style="color: red;">
                    <?= e($errors['email']) ?>
                </small>
            <?php endif; ?>
        </p>

        <button type="submit">Зарегистрироваться</button>
    </form>

    <p>
        Уже зарегистрированы?
        <a href="/frontend/login.php">Войти</a>
    </p>

    <p>
        <a href="/">На главную</a>
    </p>

    <!-- Автоматическое форматирование телефона -->
    <script>
    const phoneInput = document.getElementById('phone');

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
    </script>

</body>
</html>
