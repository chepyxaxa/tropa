
<?php

session_start();

require_once __DIR__ . '/backend/bootstrap.php';

$pageTitle = 'Тропа — экскурсии и путешествия';

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';

// Получение экскурсий из базы данных
$stmt = $pdo->query(
    'SELECT id, name, description, price_per_person
     FROM excursions
     ORDER BY id'
);

$excursions = $stmt->fetchAll();

require_once __DIR__ . '/frontend/partials/header.php';

?>

<section class="hero">

    <div class="container">

        <div class="hero-content">

            <p class="hero-label">ЭКСКУРСИИ И ПУТЕШЕСТВИЯ</p>

            <h1 class="hero-title">
                Открывайте новые места вместе с «Тропой»
            </h1>

            <p class="hero-text">
                Выбирайте экскурсии, знакомьтесь с интересными местами
                и планируйте свои путешествия.
            </p>

            <?php if ($isLoggedIn): ?>

                <p>
                    Добро пожаловать,
                    <strong><?= htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') ?></strong>!
                </p>

                <a href="/frontend/application_create.php" class="btn btn-tropa">
                    Выбрать экскурсию
                </a>

            <?php else: ?>

                <a href="/frontend/register.php" class="btn btn-tropa">
                    Начать путешествие
                </a>

            <?php endif; ?>

        </div>

        <div class="hero-slider">

            <img
                src="/frontend/assets/images/city.jpg"
                alt="Городские виды"
                class="slider-image"
            >

            <div class="slider-controls">

                <button
                    type="button"
                    class="slider-control"
                    data-slider-prev
                    aria-label="Предыдущий слайд"
                >
                    &#10094;
                </button>

                <button
                    type="button"
                    class="slider-control"
                    data-slider-next
                    aria-label="Следующий слайд"
                >
                    &#10095;
                </button>

            </div>

            <div class="slider-dots">

                <button type="button" class="slider-dot active" aria-label="Слайд 1"></button>
                <button type="button" class="slider-dot" aria-label="Слайд 2"></button>
                <button type="button" class="slider-dot" aria-label="Слайд 3"></button>
                <button type="button" class="slider-dot" aria-label="Слайд 4"></button>

            </div>

        </div>

    </div>

</section>

<section class="main-content-section">

    <div class="container">

        <h2 class="section-title">Наши экскурсии</h2>

        <div class="excursion-grid">

            <?php foreach ($excursions as $excursion): ?>

                <article class="content-card">

                    <h3>
                        <?= htmlspecialchars($excursion['name'], ENT_QUOTES, 'UTF-8') ?>
                    </h3>

                    <p>
                        <?= htmlspecialchars($excursion['description'], ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <p class="excursion-price">
                        От
                        <strong>
                            <?= number_format(
                                (float) $excursion['price_per_person'],
                                0,
                                ',',
                                ' '
                            ) ?> ₽
                        </strong>
                        за человека
                    </p>

                    <a
                        href="/frontend/application_create.php"
                        class="btn btn-tropa"
                    >
                        Забронировать
                    </a>

                </article>

            <?php endforeach; ?>

        </div>

    </div>

</section>

<?php require_once __DIR__ . '/frontend/partials/footer.php'; ?>
