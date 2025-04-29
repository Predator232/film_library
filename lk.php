<?php
session_start();
require_once 'config.php';

$user_id = $_SESSION['user_id'] ?? null;
$redirect_link = "catalog.php"; // по умолчанию ведём в каталог
$error = '';
$success = '';
$user_data = null;
$subscription_info = null;

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

// Получаем данные пользователя
try {
    $user_query = "SELECT * FROM users WHERE user_id = ?";
    $user_stmt = $pdo->prepare($user_query);
    $user_stmt->execute([$user_id]);
    $user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Ошибка запроса: " . $e->getMessage();
}

// Проверка, есть ли у пользователя активная подписка
try {
    $query = "SELECT u.*, s.name as subscription_name, s.duration, s.price 
              FROM users u 
              JOIN subscriptions s ON u.subscription_id = s.subscription_id 
              WHERE u.user_id = ? AND (u.subscription_end IS NULL OR u.subscription_end > NOW())";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($result) > 0) {
        $redirect_link = "news.php";  // Если подписка есть, переходим в новости
        $subscription_info = $result[0];
    }
} catch(PDOException $e) {
    $error = "Ошибка запроса: " . $e->getMessage();
}

// Получаем избранные фильмы пользователя
try {
    $favorites_query = "SELECT m.*, d.name as director_name 
                       FROM user_movies um 
                       JOIN movies m ON um.movie_id = m.movie_id 
                       LEFT JOIN directors d ON m.director_id = d.director_id 
                       WHERE um.user_id = ? AND um.favorite = 1";
    $favorites_stmt = $pdo->prepare($favorites_query);
    $favorites_stmt->execute([$user_id]);
    $favorite_movies = $favorites_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Ошибка запроса: " . $e->getMessage();
}

// Обработка формы редактирования профиля
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'edit_profile') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    
    try {
        // Проверяем, не занято ли имя пользователя или email
        $check_query = "SELECT * FROM users WHERE (username = ? OR email = ?) AND user_id != ?";
        $check_stmt = $pdo->prepare($check_query);
        $check_stmt->execute([$username, $email, $user_id]);
        
        if ($check_stmt->rowCount() > 0) {
            $error = "Пользователь с таким именем или email уже существует.";
        } else {
            // Обновляем данные пользователя
            $update_query = "UPDATE users SET username = ?, email = ? WHERE user_id = ?";
            $update_stmt = $pdo->prepare($update_query);
            if ($update_stmt->execute([$username, $email, $user_id])) {
                $success = "Профиль успешно обновлен.";
                $_SESSION['username'] = $username;
                // Обновляем данные пользователя
                $user_data['username'] = $username;
                $user_data['email'] = $email;
            } else {
                $error = "Ошибка при обновлении профиля.";
            }
        }
    } catch(PDOException $e) {
        $error = "Ошибка при обновлении профиля: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Filmoteka - Личный кабинет</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<header class="header">
  <div class="logo">
    <img src="img/movielogo.png" alt="Filmoteka" style="height: 40px; margin-right: 10px;">
    Filmoteka
  </div>
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
  <?php if ($error): ?>
    <div class="error-message">
      <?php echo $error; ?>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="success-message">
      <?php echo $success; ?>
    </div>
  <?php endif; ?>

  <h1>Личный кабинет</h1>
  
  <?php if ($user_data): ?>
    <div class="profile-container">
      <div class="profile-info">
        <h2>Информация о профиле</h2>
        <div class="info-row">
          <div class="info-label">Имя пользователя:</div>
          <div class="info-value"><?php echo htmlspecialchars($user_data['username']); ?></div>
        </div>
        <div class="info-row">
          <div class="info-label">Email:</div>
          <div class="info-value"><?php echo htmlspecialchars($user_data['email']); ?></div>
        </div>
        <div class="info-row">
          <div class="info-label">Дата регистрации:</div>
          <div class="info-value"><?php echo date('d.m.Y', strtotime($user_data['created_at'])); ?></div>
        </div>
        
        <?php if ($subscription_info): ?>
          <div class="subscription-status active">
            <h3>Активная подписка</h3>
            <div class="info-row">
              <div class="info-label">Название:</div>
              <div class="info-value"><?php echo htmlspecialchars($subscription_info['subscription_name']); ?></div>
            </div>
            <div class="info-row">
              <div class="info-label">Длительность:</div>
              <div class="info-value"><?php echo htmlspecialchars($subscription_info['duration']); ?> месяцев</div>
            </div>
            <div class="info-row">
              <div class="info-label">Стоимость:</div>
              <div class="info-value"><?php echo htmlspecialchars($subscription_info['price']); ?> ₽</div>
            </div>
            <div class="info-row">
              <div class="info-label">Действует до:</div>
              <div class="info-value"><?php echo date('d.m.Y', strtotime($user_data['subscription_end'])); ?></div>
            </div>
          </div>
        <?php else: ?>
          <div class="subscription-status inactive">
            <p>У вас нет активной подписки</p>
            <a href="catalog.php" class="btn">Купить подписку</a>
          </div>
        <?php endif; ?>

        <div class="edit-button-container">
          <button onclick="showEditForm()" class="btn">Редактировать профиль</button>
        </div>

        <div id="editForm" class="edit-form" style="display: none;">
          <h3>Редактирование профиля</h3>
          <form method="POST">
            <input type="hidden" name="action" value="edit_profile">
            <div class="form-group">
              <label for="username">Имя пользователя:</label>
              <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
            </div>
            <div class="form-group">
              <label for="email">Email:</label>
              <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
            </div>
            <button type="submit" class="btn">Сохранить изменения</button>
            <button type="button" onclick="hideEditForm()" class="btn">Отмена</button>
          </form>
        </div>
      </div>
    </div>

    <?php if (!empty($favorite_movies)): ?>
      <h2>Избранные фильмы</h2>
      <div class="gallery">
        <?php foreach ($favorite_movies as $movie): ?>
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
              <p><strong>Рейтинг:</strong> <?php echo htmlspecialchars($movie['average_rating'] ?? '0'); ?>/10</p>
              <p><strong>Длительность:</strong> <?php echo htmlspecialchars($movie['duration']); ?> мин.</p>
              <form method="POST" style="display: inline;">
                <input type="hidden" name="movie_id" value="<?php echo $movie['movie_id']; ?>">
                <input type="hidden" name="action" value="remove">
                <button type="submit" class="favorite-btn added">Удалить из избранного</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p>У вас пока нет избранных фильмов.</p>
    <?php endif; ?>
  <?php endif; ?>
</main>

<footer class="footer">
  <p>&copy; 2025 Filmoteka. Все права защищены.</p>
</footer>

<script>
  function showEditForm() {
    document.getElementById('editForm').style.display = 'block';
  }

  function hideEditForm() {
    document.getElementById('editForm').style.display = 'none';
  }
</script>
</body>
</html>
