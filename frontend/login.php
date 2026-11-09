<?php

session_start();

$pageTitle = 'Вход — Тропа';

$errors = $_SESSION['login_errors'] ?? [];
$success = $_SESSION['success'] ?? '';

unset($_SESSION['login_errors'], $_SESSION['success']);

require_once __DIR__ . '/partials/header.php';

?>

<section class="form-section">
    <div class="form-container">

        <h1 class="form-title">Вход</h1>
        <p class="form-description">Войдите в свой аккаунт, чтобы продолжить.</p>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?= e($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="/backend/actions/login.php" method="POST">

            <div class="mb-3">
                <label for="login" class="form-label">Логин</label>
                <input
                    type="text"
                    class="form-control"
                    id="login"
                    name="login"
                    required
                    autocomplete="username"
                >
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Пароль</label>
                <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Войти
            </button>

        </form>

        <p class="form-bottom-text">
            Ещё нет аккаунта?
            <a href="/frontend/register.php">Зарегистрироваться</a>
        </p>

    </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>