<?php

session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';

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

    <title>Тропа — экскурсии и путешествия</title>
</head>
<body>

    <h1>Добро пожаловать на сайт «Тропа»!</h1>

    <p>Сайт для записи на экскурсии и путешествия.</p>

    <?php if ($isLoggedIn): ?>

        <h2>Личный кабинет</h2>

        <p>
            Вы вошли в аккаунт,
            <strong><?= e($userName) ?></strong>!
        </p>

        <p>
            <a href="/frontend/application_create.php">
                Записаться на экскурсию
            </a>
        </p>

        <p>
            <a href="/frontend/applications.php">
                Мои заявки
            </a>
        </p>

        <p>
            <a href="/backend/actions/logout.php">
                Выйти из аккаунта
            </a>
        </p>

    <?php else: ?>

        <h2>Вход на сайт</h2>

        <p>
            <a href="/frontend/register.php">
                Зарегистрироваться
            </a>
        </p>

        <p>
            <a href="/frontend/login.php">
                Войти в аккаунт
            </a>
        </p>

    <?php endif; ?>

</body>
</html>