
<?php

session_start();

$pageTitle = 'Тропа — экскурсии и путешествия';

require_once __DIR__ . '/frontend/partials/header.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';

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

            <article class="content-card">
                <h3>Обзорная по городу</h3>
                <p>
                    Знакомство с главными достопримечательностями
                    и интересными местами города.
                </p>
            </article>

            <article class="content-card">
                <h3>Музейный квартал</h3>
                <p>
                    Путешествие по музеям и знакомство
                    с историей и культурой.
                </p>
            </article>

            <article class="content-card">
                <h3>Загородный эко-маршрут</h3>
                <p>
                    Прогулка на природе и знакомство
                    с живописными окрестностями.
                </p>
            </article>

        </div>

    </div>

</section>

<?php require_once __DIR__ . '/frontend/partials/footer.php'; ?>
