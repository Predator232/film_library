<?php
session_start();
require_once 'config.php';

// Получаем параметры поиска и сортировки
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'title';

// Формируем SQL запрос
$query = "SELECT m.*, d.name as director_name, GROUP_CONCAT(g.name SEPARATOR ', ') as genres 
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

try {
    $stmt = $pdo->prepare($query);
    $search_param = "%$search%";
    $stmt->execute([$search_param]);
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Ошибка запроса: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'] ?? null;

// Получаем список избранных фильмов пользователя, если он авторизован
$favorite_movies = [];
if ($user_id) {
    $favorites_query = "SELECT movie_id FROM favorite_movies WHERE user_id = ?";
    $favorites_stmt = $pdo->prepare($favorites_query);
    $favorites_stmt->execute([$user_id]);
    $favorite_movies = $favorites_stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Фильмы</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f5f5;
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
        main {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
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
        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-3px);
        }
        .card img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        .card-content {
            padding: 15px;
        }
        .card h3 {
            margin: 0 0 8px;
            color: #ff5500;
            font-size: 1.1em;
        }
        .card p {
            margin: 4px 0;
            color: #666;
            font-size: 0.9em;
        }
        .director-info {
            display: flex;
            align-items: center;
            margin: 8px 0;
        }
        .favorite-btn {
            background: #ff5500;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
            transition: background-color 0.3s;
        }
        .favorite-btn:hover {
            background: #e64a00;
        }
        .favorite-btn.added {
            background: #dc3545;
        }
        .favorite-btn.added:hover {
            background: #c82333;
        }
        .admin-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .admin-actions a {
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-size: 0.9em;
            transition: background-color 0.3s;
        }
        .edit-btn {
            background-color: #4CAF50;
        }
        .edit-btn:hover {
            background-color: #45a049;
        }
        .delete-btn {
            background-color: #f44336;
        }
        .delete-btn:hover {
            background-color: #da190b;
        }
        .add-movie-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: background-color 0.3s;
        }
        .add-movie-btn:hover {
            background-color: #45a049;
        }
        .success-message, .error-message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success-message {
            background-color: #4CAF50;
            color: white;
        }
        .error-message {
            background-color: #f44336;
            color: white;
        }
        .no-results {
            text-align: center;
            color: #666;
            font-size: 1.2em;
            margin-top: 40px;
            grid-column: 1 / -1;
        }
    </style>
</head>
<body>
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

    <main>
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <a href="add_movie.php" class="add-movie-btn">Добавить фильм</a>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <h1>Каталог фильмов</h1>
        
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

        <div class="gallery">
            <?php if (count($movies) > 0): ?>
                <?php foreach ($movies as $movie): ?>
                    <div class="card">
                        <?php
                        $poster_path = "img/posters/" . $movie['poster_url'];
                        if (file_exists($poster_path)) {
                            echo '<img src="' . $poster_path . '" alt="' . htmlspecialchars($movie['title']) . '">';
                        } else {
                            echo '<img src="img/posters/default.jpg" alt="' . htmlspecialchars($movie['title']) . '">';
                        }
                        ?>
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($movie['title']); ?></h3>
                            <div class="director-info">
                                <p><?php echo htmlspecialchars($movie['director_name']); ?></p>
                            </div>
                            <p><strong>Год:</strong> <?php echo htmlspecialchars($movie['release_year']); ?></p>
                            <p><strong>Жанры:</strong> <?php echo htmlspecialchars($movie['genres'] ?? 'Не указаны'); ?></p>
                            <p><strong>Рейтинг:</strong> <?php echo htmlspecialchars($movie['average_rating'] ?? '0'); ?>/10</p>
                            <p><strong>Длительность:</strong> <?php echo htmlspecialchars($movie['duration']); ?> мин.</p>
                            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                <div class="admin-actions">
                                    <a href="edit_movie.php?id=<?php echo $movie['movie_id']; ?>" class="edit-btn">Редактировать</a>
                                    <a href="delete_movie.php?id=<?php echo $movie['movie_id']; ?>" class="delete-btn" onclick="return confirm('Вы уверены, что хотите удалить этот фильм?')">Удалить</a>
                                </div>
                            <?php endif; ?>
                            <?php if ($user_id): ?>
                                <form action="toggle_favorite.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="movie_id" value="<?php echo $movie['movie_id']; ?>">
                                    <input type="hidden" name="action" value="<?php echo in_array($movie['movie_id'], $favorite_movies) ? 'remove' : 'add'; ?>">
                                    <button type="submit" class="favorite-btn <?php echo in_array($movie['movie_id'], $favorite_movies) ? 'added' : ''; ?>">
                                        <?php echo in_array($movie['movie_id'], $favorite_movies) ? 'Удалить из избранного' : 'В избранное'; ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
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
