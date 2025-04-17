<?php
session_start();
require_once 'config.php';

// Проверка авторизации и прав администратора
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $release_year = $_POST['release_year'] ?? '';
    $director_id = $_POST['director_id'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $poster_url = $_POST['poster_url'] ?? '';
    $average_rating = $_POST['average_rating'] ?? '';
    
    if (empty($title) || empty($description) || empty($release_year) || empty($director_id) || empty($duration)) {
        $error = 'Все поля обязательны для заполнения';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO movies (title, description, release_year, director_id, duration, poster_url, average_rating) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $release_year, $director_id, $duration, $poster_url, $average_rating]);
            $success = 'Фильм успешно добавлен';
            
            // Очищаем поля формы после успешного добавления
            $title = $description = $release_year = $director_id = $duration = $poster_url = $average_rating = '';
        } catch(PDOException $e) {
            $error = 'Ошибка при добавлении фильма: ' . $e->getMessage();
        }
    }
}

// Получаем список режиссеров для выпадающего списка
try {
    $directors = $pdo->query("SELECT director_id, name FROM directors ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = 'Ошибка при получении списка режиссеров: ' . $e->getMessage();
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
    <div class="container">
        <h1>Добавление нового фильма</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" class="add-form">
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
                        <option value="<?php echo $director['director_id']; ?>"><?php echo htmlspecialchars($director['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="duration">Продолжительность (в минутах):</label>
                <input type="number" id="duration" name="duration" value="<?php echo htmlspecialchars($duration ?? ''); ?>" required min="1">
            </div>
            
            <div class="form-group">
                <label for="poster_url">URL постера:</label>
                <input type="url" id="poster_url" name="poster_url" value="<?php echo htmlspecialchars($poster_url ?? ''); ?>" placeholder="https://example.com/poster.jpg">
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
    </div>
</body>
</html> 