<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
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
  
.movie-grid {
    display: flex;
    gap: 30px;
    justify-content: center;
    flex-wrap: wrap;
    padding: 20px;
}

.movie-card {
    width: 300px;
    background-color: #121212;
    padding: 15px;
    border-radius: 10px;
    text-align: center;
    color: white;
    box-shadow: 0 0 15px rgba(0,0,0,0.5);
    transition: transform 0.3s;
}

.movie-card:hover {
    transform: scale(1.05);
}

.movie-poster {
    width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 15px;
}

.movie-title {
    font-weight: bold;
    font-size: 20px;
    margin-bottom: 10px;
}

.movie-desc {
    font-size: 14px;
    font-style: italic;
    margin-bottom: 15px;
    color: #ccc;
}

.movie-info {
    list-style: none;
    padding: 0;
    margin: 0;
    text-align: left;
}
.movie-info li {
    font-size: 14px;
    margin-bottom: 8px;
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
<main>

  <h1>Новости кино</h1>
  
  <h2 style="text-align: center; color: #ff5500;">Премьеры недели</h2>
  <div class="movie-grid">
    <?php
    $movies = [
        [
            'poster' => 'img/news/dune2.jpg',
            'title' => 'Дюна: Часть вторая',
            'description' => 'Продолжение эпической саги по мотивам романа Фрэнка Герберта.',
            'info' => ['Режиссер: Дени Вильнёв', 'В ролях: Тимоти Шаламе, Зендая', 'Жанр: Фантастика', 'Рейтинг: 8.9/10']
        ],
        [
            'poster' => 'img/news/oppenheimer.jpg',
            'title' => 'Оппенгеймер',
            'description' => 'История создания атомной бомбы глазами её создателя.',
            'info' => ['Режиссер: Кристофер Нолан', 'В ролях: Киллиан Мерфи', 'Жанр: Драма', 'Рейтинг: 9.1/10']
        ],
        [
            'poster' => 'img/news/barbie.jpeg',
            'title' => 'Барби',
            'description' => 'Комедийная фантазия о путешествии Барби в реальный мир.',
            'info' => ['Режиссер: Грета Гервиг', 'В ролях: Марго Робби, Райан Гослинг', 'Жанр: Комедия', 'Рейтинг: 7.8/10']
        ]
    ];

    foreach ($movies as $movie) {
        echo '<div class="movie-card">';
        echo '<img src="' . $movie['poster'] . '" alt="Poster" class="movie-poster">';
        echo '<div class="movie-title">' . $movie['title'] . '</div>';
        echo '<div class="movie-desc">' . $movie['description'] . '</div>';
        echo '<ul class="movie-info">';
        foreach ($movie['info'] as $info) {
            echo '<li>' . $info . '</li>';
        }
        echo '</ul>';
        echo '</div>';
    }
    ?>
  </div>

</main>
<footer class="footer">
  <p>&copy; 2025 Filmoteka. Все права защищены.</p>
</footer>
</body>
</html>
