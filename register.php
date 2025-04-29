<?php
session_start();
require_once 'config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($password !== $confirm_password) {
        $error = "Пароли не совпадают.";
    } else {
        try {
            // Проверяем, существует ли пользователь с таким именем или email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                $error = "Пользователь с таким именем или email уже существует.";
            } else {
                // Хешируем пароль
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Добавляем нового пользователя
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password]);
                
                // Авторизуем пользователя
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['username'] = $username;
                $_SESSION['is_admin'] = 0;
                
                header("Location: lk.php");
                exit();
            }
        } catch(PDOException $e) {
            $error = "Ошибка при регистрации: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Регистрация</title>
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
    <h1>Регистрация</h1>
    <?php if (!empty($error)): ?>
      <p class="error-message"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST">
      <input type="text" name="username" placeholder="Имя пользователя" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Пароль" required>
      <input type="password" name="confirm_password" placeholder="Подтвердите пароль" required>
      <button type="submit" class="btn">Зарегистрироваться</button>
    </form>
  </main>

  <footer class="footer">
    <p>&copy; 2025 Filmoteka. Все права защищены.</p>
  </footer>
</body>
</html>
