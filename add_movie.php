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

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $release_year = $_POST['release_year'] ?? '';
    $director_id = $_POST['director_id'] ?? '';
    $genre_id = $_POST['genre_id'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $poster_url = $_FILES['poster']['name'] ?? '';
    $average_rating = $_POST['average_rating'] ?? '';
    
    if (empty($title) || empty($description) || empty($release_year) || empty($director_id) || empty($genre_id) || empty($duration)) {
        $error = 'Все поля обязательны для заполнения';
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
            }

            if (empty($error)) {
                // Добавляем фильм в базу данных
                $stmt = $pdo->prepare("INSERT INTO movies (title, description, release_year, director_id, genre_id, duration, poster_url, average_rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$title, $description, $release_year, $director_id, $genre_id, $duration, $poster_url, $average_rating])) {
                    $success = 'Фильм успешно добавлен';
                    
                    // Очищаем поля формы после успешного добавления
                    $title = $description = $release_year = $director_id = $genre_id = $duration = $poster_url = $average_rating = '';
                } else {
                    $error = 'Ошибка при добавлении фильма';
                }
            }
        } catch(PDOException $e) {
            $error = 'Ошибка при добавлении фильма: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавление фильма</title>
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
        <h1>Добавление нового фильма</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="add-form">
            <div class="form-group">
                <label for="title">Название:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Описание:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="release_year">Год выпуска:</label>
                <input type="number" id="release_year" name="release_year" value="<?php echo htmlspecialchars($release_year ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="director_id">Режиссер:</label>
                <select id="director_id" name="director_id" required>
                    <option value="">Выберите режиссера</option>
                    <?php foreach ($directors as $director): ?>
                        <option value="<?php echo $director['director_id']; ?>" <?php echo (isset($director_id) && $director_id == $director['director_id']) ? 'selected' : ''; ?>>
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
                        <option value="<?php echo $genre['genre_id']; ?>" <?php echo (isset($genre_id) && $genre_id == $genre['genre_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($genre['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="duration">Продолжительность (в минутах):</label>
                <input type="number" id="duration" name="duration" value="<?php echo htmlspecialchars($duration ?? ''); ?>" required min="1">
            </div>
            
            <div class="form-group">
                <label for="poster">Постер:</label>
                <input type="file" id="poster" name="poster" accept="image/*">
            </div>
            
            <div class="form-group">
                <label for="average_rating">Средний рейтинг:</label>
                <input type="number" id="average_rating" name="average_rating" value="<?php echo htmlspecialchars($average_rating ?? ''); ?>" min="0" max="10" step="0.1">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn">Добавить фильм</button>
                <a href="gallery.php" class="btn btn-secondary">Отмена</a>
            </div>
        </form>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Filmoteka. Все права защищены.</p>
    </footer>
</body>
</html> 