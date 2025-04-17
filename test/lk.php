<?php
session_start();
$user_id = $_SESSION['user_id'] ?? null;
$redirect_link = "catalog.php"; // по умолчанию ведём в каталог
$error = '';
$user_data = null;
$subscription_info = null;

if ($user_id) {
    // Подключение к базе данных
    $conn = new mysqli("127.0.0.1:3306", "root", "", "film_library");
    
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }

    // Получаем данные пользователя
    $user_query = "SELECT * FROM users WHERE user_id = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user_data = $user_result->fetch_assoc();
    $user_stmt->close();

    // Получаем избранные фильмы пользователя
    $favorites_query = "SELECT m.*, f.added_date 
                       FROM movies m 
                       JOIN favorite_movies f ON m.movie_id = f.movie_id 
                       WHERE f.user_id = ? 
                       ORDER BY f.added_date DESC";
    $favorites_stmt = $conn->prepare($favorites_query);
    $favorites_stmt->bind_param("i", $user_id);
    $favorites_stmt->execute();
    $favorites_result = $favorites_stmt->get_result();
    $favorite_movies = [];
    while ($movie = $favorites_result->fetch_assoc()) {
        $favorite_movies[] = $movie;
    }
    $favorites_stmt->close();

    // Проверка, есть ли у пользователя активная подписка
    $query = "SELECT us.*, s.name as subscription_name, s.duration, s.price 
              FROM user_subscriptions us 
              JOIN subscriptions s ON us.subscription_id = s.subscription_id 
              WHERE us.user_id = ? AND (us.end_date IS NULL OR us.end_date > NOW())";
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        $error = "Ошибка подготовки запроса: " . $conn->error;
    } else {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $redirect_link = "news.php";  // Если подписка есть, переходим в новости
            $subscription_info = $result->fetch_assoc();
        }

        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Filmoteka - Личный кабинет</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #fff;
      color: #333;
    }
    .header {
      background-color:rgb(0, 0, 0);
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
      text-decoration: none;
      display: inline-block;
      margin: 10px;
    }
    .btn:hover {
      background-color: #e64a00;
    }
    .error-message {
      background: #f8d7da;
      color: #721c24;
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
    }
    .success-message {
      background: #d4edda;
      color: #155724;
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
    }
    .profile-container {
      display: flex;
      justify-content: center;
      margin: 30px 0;
    }
    .profile-info {
      background: #f9f9f9;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      text-align: left;
      max-width: 600px;
      width: 100%;
    }
    .profile-info h2 {
      color: #ff5500;
      border-bottom: 1px solid #eee;
      padding-bottom: 10px;
    }
    .info-row {
      display: flex;
      margin: 15px 0;
    }
    .info-label {
      font-weight: bold;
      width: 150px;
      color: #555;
    }
    .info-value {
      flex: 1;
    }
    .subscription-status {
      padding: 10px;
      border-radius: 5px;
      margin-top: 10px;
      font-weight: bold;
    }
    .active {
      background: #d4edda;
      color: #155724;
    }
    .inactive {
      background: #f8d7da;
      color: #721c24;
    }
    .edit-button-container {
      margin: 20px 0;
    }
    
    .edit-form {
      background: #f9f9f9;
      border-radius: 10px;
      padding: 20px;
      margin-top: 20px;
      text-align: left;
    }
    
    .form-group {
      margin-bottom: 15px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 5px;
      color: #555;
    }
    
    .form-group input {
      width: 100%;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 1em;
    }
    .favorites-section {
        margin-top: 40px;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 10px;
    }
    
    .favorites-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .movie-card {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    
    .movie-card:hover {
        transform: translateY(-5px);
    }
    
    .movie-poster {
        width: 100%;
        height: 300px;
        object-fit: cover;
    }
    
    .movie-info {
        padding: 15px;
    }
    
    .movie-title {
        font-weight: bold;
        margin-bottom: 5px;
        color: #333;
    }
    
    .movie-year {
        color: #666;
        font-size: 0.9em;
    }
    
    .remove-favorite {
        background: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
        margin-top: 10px;
        width: 100%;
    }
    
    .remove-favorite:hover {
        background: #c82333;
    }
  </style>
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
  <?php if (isset($_SESSION['error'])): ?>
    <div class="error-message">
      <?php 
        echo $_SESSION['error'];
        unset($_SESSION['error']);
      ?>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="success-message">
      <?php 
        echo $_SESSION['success'];
        unset($_SESSION['success']);
      ?>
    </div>
  <?php endif; ?>

  <h1>Личный кабинет</h1>
  
  <?php if ($user_id && $user_data): ?>
    <div class="profile-container">
      <div class="profile-info">
        <h2>Личная информация</h2>
        
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
          <div class="info-value"><?php echo date('d.m.Y', strtotime($user_data['registration_date'])); ?></div>
        </div>
        
        <div class="edit-button-container">
          <button class="btn" onclick="showEditForm()">Редактировать данные</button>
        </div>

        <div id="editForm" style="display: none;">
          <h2>Редактирование данных</h2>
          <form action="update_profile.php" method="POST" class="edit-form">
            <div class="form-group">
              <label for="username">Имя пользователя:</label>
              <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
            </div>
            
            <div class="form-group">
              <label for="email">Email:</label>
              <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
            </div>
            
            <div class="form-group">
              <label for="current_password">Текущий пароль:</label>
              <input type="password" id="current_password" name="current_password" required>
            </div>
            
            <div class="form-group">
              <label for="new_password">Новый пароль (оставьте пустым, если не хотите менять):</label>
              <input type="password" id="new_password" name="new_password">
            </div>
            
            <div class="form-group">
              <label for="confirm_password">Подтвердите новый пароль:</label>
              <input type="password" id="confirm_password" name="confirm_password">
            </div>
            
            <button type="submit" class="btn">Сохранить изменения</button>
            <button type="button" class="btn" onclick="hideEditForm()">Отмена</button>
          </form>
        </div>
        
        <h2>Подписка</h2>
        <?php if ($subscription_info): ?>
          <div class="info-row">
            <div class="info-label">Тип подписки:</div>
            <div class="info-value"><?php echo htmlspecialchars($subscription_info['subscription_name']); ?></div>
          </div>
          
          <div class="info-row">
            <div class="info-label">Стоимость:</div>
            <div class="info-value"><?php echo $subscription_info['price']; ?> руб.</div>
          </div>
          
          <div class="info-row">
            <div class="info-label">Дата начала:</div>
            <div class="info-value"><?php echo date('d.m.Y', strtotime($subscription_info['start_date'])); ?></div>
          </div>
          
          <div class="info-row">
            <div class="info-label">Дата окончания:</div>
            <div class="info-value">
              <?php 
                if ($subscription_info['end_date']) {
                  echo date('d.m.Y', strtotime($subscription_info['end_date']));
                } else {
                  echo 'Бессрочная';
                }
              ?>
            </div>
          </div>
          
          <div class="subscription-status active">
            Подписка активна
          </div>
        <?php else: ?>
          <div class="subscription-status inactive">
            У вас нет активной подписки
          </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($favorite_movies)): ?>
    <div class="favorites-section">
      <h2>Избранные фильмы</h2>
      <div class="favorites-grid">
        <?php foreach ($favorite_movies as $movie): ?>
          <div class="movie-card">
            <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-poster">
            <div class="movie-info">
              <div class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></div>
              <div class="movie-year"><?php echo htmlspecialchars($movie['release_year']); ?></div>
              <form action="toggle_favorite.php" method="POST" style="display: inline;">
                <input type="hidden" name="movie_id" value="<?php echo $movie['movie_id']; ?>">
                <input type="hidden" name="action" value="remove">
                <button type="submit" class="remove-favorite">Удалить из избранного</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <a href="<?php echo $redirect_link; ?>" class="btn">Перейти к <?php echo $redirect_link === 'news.php' ? 'новостям' : 'каталогу'; ?></a>
  <?php else: ?>
    <p>Для доступа к личному кабинету необходимо авторизоваться.</p>
    <a href="login.php" class="btn">Войти</a>
    <a href="register.php" class="btn">Зарегистрироваться</a>
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