
<?php
session_start();

$errors = $_SESSION['login_errors'] ?? [];
$success = $_SESSION['success'] ?? '';

unset($_SESSION['login_errors'], $_SESSION['success']);

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
    <title>Вход — Тропа</title>
</head>
<body>
    <h1>Вход на сайт «Тропа»</h1>

    <?php if ($success): ?>
        <p><?= e($success) ?></p>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div>
            <h2>Не удалось войти:</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="/backend/actions/login.php" method="POST">
        <p>
            <label for="login">Логин:</label><br>
            <input
                type="text"
                id="login"
                name="login"
                required
                autocomplete="username"
            >
        </p>

        <p>
            <label for="password">Пароль:</label><br>
            <input
                type="password"
                id="password"
                name="password"
                required
                autocomplete="current-password"
            >
        </p>

        <button type="submit">Войти</button>
    </form>

    <p>
        Ещё нет аккаунта?
        <a href="/frontend/register.php">Зарегистрироваться</a>
    </p>

    <p><a href="/">На главную</a></p>
</body>
</html>
