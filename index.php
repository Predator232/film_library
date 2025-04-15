<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filmoteka</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #fff;
            color: #333;
        }
        .header {
            background-color: #181818;
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo {
            color: #fff;
            font-size: 1.5em;
            font-weight: bold;
        }
        .nav a {
            color: #ff5500;
            text-decoration: none;
            margin-left: 20px;
            font-weight: 500;
        }
        .nav a:hover {
            text-decoration: underline;
        }
        .footer {
            background-color: #f2f2f2;
            padding: 20px 30px;
            text-align: center;
            font-size: 0.9em;
            color: #777;
            margin-top: 60px;
        }
        main {
            padding: 40px 20px;
            text-align: center;
        }
        .btn {
            background-color: #ff5500;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-size: 1em;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #e64a00;
        }
        .hero {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('img/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 20px;
            text-align: center;
        }
        .hero h1 {
            font-size: 3em;
            margin-bottom: 20px;
        }
        .hero p {
            font-size: 1.2em;
            max-width: 600px;
            margin: 0 auto 30px;
        }
        .features {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin: 60px 0;
            flex-wrap: wrap;
        }
        .feature-card {
            width: 300px;
            background-color: #121212;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            color: white;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            transition: transform 0.3s;
        }
        .feature-card:hover {
            transform: scale(1.05);
        }
        .feature-icon {
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        .feature-title {
            font-size: 1.5em;
            margin-bottom: 15px;
            color: #ff5500;
        }
        .feature-desc {
            font-size: 1em;
            line-height: 1.5;
        }
        .cta-section {
            background-color: #f8f8f8;
            padding: 60px 20px;
            text-align: center;
        }
        .cta-title {
            font-size: 2em;
            margin-bottom: 20px;
            color: #333;
        }
        .cta-desc {
            font-size: 1.2em;
            max-width: 600px;
            margin: 0 auto 30px;
            color: #666;
        }
    </style>
</head>
<body>
<?php session_start(); ?>
<header class="header">
    <div class="logo">🎬 Filmoteka</div>
    <nav class="nav">
        <a href="index.php">Главная</a>
        <a href="movies.php">Фильмы</a>
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
