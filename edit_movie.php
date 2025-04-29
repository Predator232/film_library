<?php
session_start();
require_once 'config.php';

// Проверка авторизации и прав администратора
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';
$movie = null;

// Получаем ID фильма из URL
$movie_id = $_GET['id'] ?? null;

if (!$movie_id) {
    header("Location: gallery.php");
    exit();
}

// Получаем список режиссеров
try {
    $directors_stmt = $pdo->query("SELECT * FROM directors ORDER BY name");
    $directors = $directors_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Ошибка при получении списка режиссеров: " . $e->getMessage();
}

// Получаем список жанров
try {
    $genres_stmt = $pdo->query("SELECT * FROM genres ORDER BY name");
    $genres = $genres_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Ошибка при получении списка жанров: " . $e->getMessage();
}

// Получаем информацию о фильме
try {
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE movie_id = ?");
    $stmt->execute([$movie_id]);
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$movie) {
        header("Location: gallery.php");
        exit();
    }
} catch(PDOException $e) {
    $error = "Ошибка при получении информации о фильме: " . $e->getMessage();
}

// Обработка формы
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $release_year = intval($_POST['release_year']);
    $director_id = intval($_POST['director_id']);
    $genre_id = intval($_POST['genre_id']);
    $duration = intval($_POST['duration']);
    $poster_url = $_FILES['poster']['name'];
    $average_rating = floatval($_POST['average_rating']);

    // Проверяем, что все обязательные поля заполнены
    if (empty($title) || empty($description) || empty($release_year) || empty($director_id) || empty($genre_id) || empty($duration)) {
        $error = "Пожалуйста, заполните все обязательные поля.";
    } else {
        try {
            // Загружаем постер, если он был предоставлен
            if (!empty($poster_url)) {
                $target_dir = "img/posters/";
                $target_file = $target_dir . basename($_FILES["poster"]["name"]);
                $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
                
                // Проверяем, является ли файл изображением
                $check = getimagesize($_FILES["poster"]["tmp_name"]);
                if($check === false) {
                    $error = "Файл не является изображением.";
                }
                
                // Проверяем размер файла
                if ($_FILES["poster"]["size"] > 5000000) {
                    $error = "Файл слишком большой.";
                }
                
                // Разрешаем только определенные форматы
                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
                    $error = "Разрешены только JPG, JPEG и PNG файлы.";
                }
                
                if (empty($error)) {
                    if (move_uploaded_file($_FILES["poster"]["tmp_name"], $target_file)) {
                        $poster_url = basename($_FILES["poster"]["name"]);
                    } else {
                        $error = "Ошибка при загрузке файла.";
                    }
                }
            } else {
                // Если постер не был загружен, оставляем старый
                $poster_url = $movie['poster_url'];
            }

            if (empty($error)) {
                // Обновляем информацию о фильме
                $stmt = $pdo->prepare("UPDATE movies SET title = ?, description = ?, release_year = ?, director_id = ?, genre_id = ?, duration = ?, poster_url = ?, average_rating = ? WHERE movie_id = ?");
                if ($stmt->execute([$title, $description, $release_year, $director_id, $genre_id, $duration, $poster_url, $average_rating, $movie_id])) {
                    $success = "Фильм успешно обновлен.";
                    // Обновляем данные фильма
                    $movie['title'] = $title;
                    $movie['description'] = $description;
                    $movie['release_year'] = $release_year;
                    $movie['director_id'] = $director_id;
                    $movie['genre_id'] = $genre_id;
                    $movie['duration'] = $duration;
                    $movie['poster_url'] = $poster_url;
                    $movie['average_rating'] = $average_rating;
                } else {
                    $error = "Ошибка при обновлении фильма.";
                }
            }
        } catch(PDOException $e) {
            $error = "Ошибка при обновлении фильма: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование фильма</title>
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
        <h1>Редактирование фильма</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($movie): ?>
            <form method="POST" enctype="multipart/form-data" class="edit-form">
                <div class="form-group">
                    <label for="title">Название фильма:</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($movie['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Описание:</label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($movie['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="release_year">Год выпуска:</label>
                    <input type="number" id="release_year" name="release_year" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($movie['release_year']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="director_id">Режиссер:</label>
                    <select id="director_id" name="director_id" required>
                        <option value="">Выберите режиссера</option>
                        <?php foreach ($directors as $director): ?>
                            <option value="<?php echo $director['director_id']; ?>" <?php echo ($movie['director_id'] == $director['director_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($director['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="genre_id">Жанр:</label>
                    <select id="genre_id" name="genre_id" required>
                        <option value="">Выберите жанр</option>
                        <?php foreach ($genres as $genre): ?>
                            <option value="<?php echo $genre['genre_id']; ?>" <?php echo ($movie['genre_id'] == $genre['genre_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($genre['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="duration">Длительность (в минутах):</label>
                    <input type="number" id="duration" name="duration" min="1" value="<?php echo htmlspecialchars($movie['duration']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="poster">Постер:</label>
                    <?php if ($movie['poster_url']): ?>
                        <div class="current-poster">
                            <img src="img/posters/<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="Текущий постер" style="max-width: 200px;">
                            <p>Текущий постер</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="poster" name="poster" accept="image/*">
                    <p class="help-text">Оставьте пустым, чтобы сохранить текущий постер</p>
                </div>

                <div class="form-group">
                    <label for="average_rating">Рейтинг (0-10):</label>
                    <input type="number" id="average_rating" name="average_rating" min="0" max="10" step="0.1" value="<?php echo htmlspecialchars($movie['average_rating'] ?? '0'); ?>">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Сохранить изменения</button>
                    <a href="gallery.php" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        <?php else: ?>
            <p>Фильм не найден.</p>
            <a href="gallery.php" class="btn">Вернуться к списку фильмов</a>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Filmoteka. Все права защищены.</p>
    </footer>
</body>
</html> 