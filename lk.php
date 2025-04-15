<?php
session_start();
$user_id = $_SESSION['user_id'] ?? null;
$redirect_link = "catalog.php"; // по умолчанию ведём в каталог
$error = '';

if ($user_id) {
    // Подключение к базе данных
    $conn = new mysqli("127.0.0.1:3306", "root", "", "film_library");
    
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }

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
  <?php if ($error): ?>
    <div class="error-message">
      <?php echo $error; ?>
    </div>
  <?php endif; ?>

  <h1>Личный кабинет</h1>
  
  <?php if ($user_id): ?>
    <p>Добро пожаловать в ваш личный кабинет!</p>
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
</body>
</html>
