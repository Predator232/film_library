<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = $_SESSION['is_admin'] ?? false;

// Обработка добавления/удаления из избранного
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['movie_id']) && isset($_POST['action'])) {
    $movie_id = $_POST['movie_id'];
    $action = $_POST['action'];

    try {
        if ($action === 'add') {
            // Проверяем, есть ли уже фильм в избранном
            $check_query = "SELECT * FROM user_movies WHERE user_id = ? AND movie_id = ?";
            $check_stmt = $pdo->prepare($check_query);
            $check_stmt->execute([$user_id, $movie_id]);
            
            if ($check_stmt->rowCount() === 0) {
                // Добавляем фильм в избранное
                $insert_query = "INSERT INTO user_movies (user_id, movie_id, favorite) VALUES (?, ?, 1)";
                $insert_stmt = $pdo->prepare($insert_query);
                $insert_stmt->execute([$user_id, $movie_id]);
                $_SESSION['success'] = "Фильм добавлен в избранное.";
            }
        } elseif ($action === 'remove') {
            // Удаляем фильм из избранного
            $delete_query = "DELETE FROM user_movies WHERE user_id = ? AND movie_id = ?";
            $delete_stmt = $pdo->prepare($delete_query);
            $delete_stmt->execute([$user_id, $movie_id]);
            $_SESSION['success'] = "Фильм удален из избранного.";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Ошибка при обработке запроса: " . $e->getMessage();
    }
}

// Получаем параметры поиска и сортировки
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'title';

// Формируем базовый запрос
$query = "SELECT m.*, d.name as director_name, g.name as genre_name 
          FROM movies m 
          LEFT JOIN directors d ON m.director_id = d.director_id 
          LEFT JOIN genres g ON m.genre_id = g.genre_id";

// Добавляем условие поиска, если оно есть
if (!empty($search)) {
    $query .= " WHERE m.title LIKE ?";
}

// Добавляем сортировку
switch ($sort) {
    case 'year':
        $query .= " ORDER BY m.release_year DESC";
        break;
    case 'rating':
        $query .= " ORDER BY m.average_rating DESC";
        break;
    default:
        $query .= " ORDER BY m.title";
}

// Получаем список фильмов с информацией о режиссерах и жанрах
try {
    $stmt = $pdo->prepare($query);
    if (!empty($search)) {
        $stmt->execute(['%' . $search . '%']);
    } else {
        $stmt->execute();
    }
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Ошибка запроса: " . $e->getMessage());
}

// Получаем избранные фильмы пользователя
$favorites_stmt = $pdo->prepare("SELECT movie_id FROM user_movies WHERE user_id = ? AND favorite = 1");
$favorites_stmt->execute([$user_id]);
$favorite_movies = $favorites_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Фильмы</title>
    <link rel="stylesheet" href="styles.css">
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
            <a href="add_movie.php" class="btn">Добавить фильм</a>
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
                            <p><strong>Жанры:</strong> <?php echo htmlspecialchars($movie['genre_name']); ?></p>
                            <p><strong>Рейтинг:</strong> <?php echo htmlspecialchars($movie['average_rating'] ?? '0'); ?>/10</p>
                            <p><strong>Длительность:</strong> <?php echo htmlspecialchars($movie['duration']); ?> мин.</p>
                            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                <div class="admin-actions">
                                    <a href="edit_movie.php?id=<?php echo $movie['movie_id']; ?>" class="edit-btn">Редактировать</a>
                                    <a href="delete_movie.php?id=<?php echo $movie['movie_id']; ?>" class="delete-btn" onclick="return confirm('Вы уверены, что хотите удалить этот фильм?')">Удалить</a>
                                </div>
                            <?php endif; ?>
                            <?php if ($user_id): ?>
                                <form method="POST" style="display: inline;">
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
