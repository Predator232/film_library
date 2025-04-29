<?php
session_start();
require_once 'config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            header("Location: lk.php");
            exit();
        } else {
            $error = "Неверный логин или пароль.";
        }
    } catch(PDOException $e) {
        $error = "Ошибка при входе: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Вход</title>
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
    <h1>Вход</h1>
    <?php if (!empty($error)): ?>
      <p class="error-message"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST">
      <input type="text" name="login" placeholder="Имя пользователя" required>
      <input type="password" name="password" placeholder="Пароль" required>
      <button type="submit" class="btn">Войти</button>
    </form>
  </main>

  <footer class="footer">
    <p>&copy; 2025 Filmoteka. Все права защищены.</p>
  </footer>
</body>
</html>
