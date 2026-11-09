<?php

$pageTitle = $pageTitle ?? 'Тропа — экскурсии и путешествия';

$isAdmin = !empty($_SESSION['is_admin']);
$isLoggedIn = isset($_SESSION['user_id']) || $isAdmin;

$userName = $isAdmin
    ? 'Администратор'
    : ($_SESSION['user_name'] ?? '');

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

    <title><?= e($pageTitle) ?></title>

    <link rel="stylesheet" href="/frontend/assets/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="/frontend/assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="header-inner">

        <a href="/index.php" class="brand">Тропа</a>

        <nav class="site-nav">

            <a href="/index.php">Главная</a>

            <?php if ($isAdmin): ?>

                <a href="/frontend/admin.php">Администрирование</a>
                <a href="/backend/actions/logout.php">Выйти</a>

            <?php elseif ($isLoggedIn): ?>

                <a href="/frontend/application_create.php">Записаться</a>
                <a href="/frontend/applications.php">Мои заявки</a>
                <a href="/backend/actions/logout.php">Выйти</a>

            <?php else: ?>

                <a href="/frontend/login.php">Войти</a>
                <a href="/frontend/register.php">Регистрация</a>

            <?php endif; ?>

        </nav>

    </div>
</header>

<main class="main-content">