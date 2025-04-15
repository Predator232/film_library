<?php
session_start();

$servername = "127.0.0.1:3306";
$username = "root";
$password = "";
$dbname = "film_library";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Ошибка подключения: " . $conn->connect_error);
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $login = trim($_POST['login']);
  $password = trim($_POST['password']);

  $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->bind_param("s", $login);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['user_id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['is_admin'] = (bool)$user['is_admin'];
      header("Location: lk.php");
      exit();
    } else {
      $error = "Неверный пароль.";
    }
  } else {
    $error = "Пользователь не найден.";
  }

  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Вход</title>
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
    main {
      text-align: center;
      padding: 60px 20px;
    }
    form {
      max-width: 400px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    input {
      padding: 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    button {
      background-color: #ff5500;
      color: white;
      padding: 10px;
      border: none;
      border-radius: 25px;
      font-size: 1em;
      cursor: pointer;
    }
    button:hover {
      background-color: #e64a00;
    }
    .error {
      color: red;
    }
    .footer {
      background-color: #f2f2f2;
      padding: 20px 30px;
      text-align: center;
      font-size: 0.9em;
      color: #777;
      margin-top: 60px;
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
    <h1>Вход</h1>
    <?php if (!empty($error)): ?>
      <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST">
      <input type="text" name="login" placeholder="Имя пользователя" required>
      <input type="password" name="password" placeholder="Пароль" required>
      <button type="submit">Войти</button>
    </form>
  </main>

  <footer class="footer">
    <p>&copy; 2025 Filmoteka. Все права защищены.</p>
  </footer>
</body>
</html>
