<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filmoteka</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php session_start(); ?>
<header class="header">
    <div class="logo">🎬 Filmoteka</div>
    <nav class="nav">
        <a href="index.php">Главная</a>
        <a href="gallery.php">Фильмы</a>
        <a href="catalog.php">Каталог</a>
        <a href="news.php">Новости</a>
        <a href="contact.php">Контакты</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="lk.php">Личный кабинет</a>
            <a href="logout.php">Выход</a>
        <?php else: ?>
            <a href="login.php">Вход</a>
            <a href="register.php">Регистрация</a>
        <?php endif; ?>
    </nav>
</header>

<section class="hero">
    <h1>Добро пожаловать в Filmoteka</h1>
    <p>Ваш персональный кинотеатр с лучшими фильмами всех времен</p>
    <a href="catalog.php" class="btn">Начать просмотр</a>
</section>

<main>
    <div class="features">
        <div class="feature-card">
            <div class="feature-icon">🎬</div>
            <h3 class="feature-title">Богатая коллекция</h3>
            <p class="feature-desc">Тысячи фильмов различных жанров и эпох в отличном качестве</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🌟</div>
            <h3 class="feature-title">Удобный поиск</h3>
            <p class="feature-desc">Быстрый поиск по названию, жанру, актерам и режиссерам</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🎭</div>
            <h3 class="feature-title">Персональные рекомендации</h3>
            <p class="feature-desc">Индивидуальные подборки фильмов на основе ваших предпочтений</p>
        </div>
    </div>

    <section class="cta-section">
        <h2 class="cta-title">Присоединяйтесь к нам</h2>
        <p class="cta-desc">Создайте аккаунт, чтобы получить доступ к полной коллекции фильмов и персонализированным рекомендациям</p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn">Зарегистрироваться</a>
        <?php endif; ?>
    </section>
</main>

<footer class="footer">
    <p>&copy; 2025 Filmoteka. Все права защищены.</p>
</footer>
</body>
</html>
