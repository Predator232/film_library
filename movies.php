<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Filmoteka - Фильмы</title>
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
        }
        .search-container {
            max-width: 800px;
            margin: 0 auto 40px;
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 25px;
            font-size: 1em;
            outline: none;
            transition: border-color 0.3s;
        }
        .search-input:focus {
            border-color: #ff5500;
        }
        .sort-select {
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 25px;
            font-size: 1em;
            background-color: white;
            cursor: pointer;
            outline: none;
        }
        .movie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            padding: 20px;
        }
        .movie-card {
            background-color: #121212;
            border-radius: 10px;
            overflow: hidden;
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
        }
        .movie-info {
            padding: 20px;
            color: white;
        }
        .movie-title {
            font-size: 1.5em;
            margin-bottom: 10px;
            color: #ff5500;
        }
        .movie-meta {
            font-size: 0.9em;
            color: #ccc;
            margin-bottom: 10px;
        }
        .movie-desc {
            font-size: 0.9em;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        .no-results {
            text-align: center;
            color: #666;
            font-size: 1.2em;
            margin-top: 40px;
        }
    </style>
</head>
<body>
<?php 
session_start();
$conn = new mysqli("127.0.0.1:3306", "root", "", "film_library");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Получаем параметры поиска и сортировки
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'title';

// Формируем SQL запрос
$query = "
    SELECT 
        m.movie_id, m.title, m.release_year, m.description, m.duration, m.average_rating, m.poster_url,
        d.name AS director_name,
        GROUP_CONCAT(g.name SEPARATOR ', ') AS genres
    FROM movies m
    LEFT JOIN directors d ON m.director_id = d.director_id
    LEFT JOIN movie_genres mg ON m.movie_id = mg.movie_id
    LEFT JOIN genres g ON mg.genre_id = g.genre_id
    WHERE m.title LIKE ?
    GROUP BY m.movie_id
    ORDER BY ";

// Добавляем сортировку
switch($sort) {
    case 'year':
        $query .= "m.release_year DESC";
        break;
    case 'rating':
        $query .= "m.average_rating DESC";
        break;
    default:
        $query .= "m.title ASC";
}

$stmt = $conn->prepare($query);
$search_param = "%$search%";
$stmt->bind_param("s", $search_param);
$stmt->execute();
$result = $stmt->get_result();
?>
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
    <div class="search-container">
        <form method="get" style="flex: 1; display: flex; gap: 20px;">
            <input type="text" name="search" class="search-input" placeholder="Поиск по названию..." value="<?= htmlspecialchars($search) ?>">
            <select name="sort" class="sort-select" onchange="this.form.submit()">
                <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>По названию</option>
                <option value="year" <?= $sort === 'year' ? 'selected' : '' ?>>По году выпуска</option>
                <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>По рейтингу</option>
            </select>
        </form>
    </div>

    <div class="movie-grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="movie-card">
                    <?php 
                    // Создаем массив соответствия ID фильмов и названий файлов постеров
                    $poster_files = [
                        1 => 'shawshank.jpg',
                        2 => 'godfather.jpg',
                        3 => 'darkknight.jpg',
                        4 => 'pulpfiction.jpg',
                        5 => 'fightclub.jpg',
                        6 => 'matrix.jpg',
                        7 => 'forrestgump.jpg',
                        8 => 'titanic.jpg',
                        9 => 'inception.jpg',
                        10 => 'avatar.jpg'
                    ];
                    
                    $poster_path = 'img/posters/' . ($poster_files[$row['movie_id']] ?? 'default.jpg');
                    ?>
                    <img src="<?= $poster_path ?>" 
                         alt="<?= htmlspecialchars($row['title']) ?>" 
                         class="movie-poster"
                         onerror="this.src='img/default.jpg'">
                    <div class="movie-info">
                        <h3 class="movie-title"><?= htmlspecialchars($row['title']) ?></h3>
                        <div class="movie-meta">
                            <div>Год: <?= $row['release_year'] ?></div>
                            <div>Режиссёр: <?= htmlspecialchars($row['director_name']) ?></div>
                            <div>Жанры: <?= htmlspecialchars($row['genres']) ?></div>
                            <div>Рейтинг: <?= $row['average_rating'] ?>/10</div>
                            <div>Длительность: <?= $row['duration'] ?> мин.</div>
                        </div>
                        <p class="movie-desc"><?= htmlspecialchars($row['description']) ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-results">
                Фильмы не найдены. Попробуйте изменить параметры поиска.
            </div>
        <?php endif; ?>
    </div>
</main>

<footer class="footer">
    <p>&copy; 2025 Filmoteka. Все права защищены.</p>
</footer>
</body>
</html>
