<?php
session_start();
$success_message = ''; // Сообщение об успешной регистрации
$error_message = ''; // Сообщение об ошибке регистрации

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Подключение к базе данных
    $conn = new mysqli("127.0.0.1:3306", "root", "", "film_library");

    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }

    // Получаем данные из формы
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Проверка, существует ли уже пользователь с таким email
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error_message = "Пользователь с таким email уже существует!";
    } else {
        // Хешируем пароль перед сохранением
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Вставка нового пользователя в базу данных
        $query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        if ($stmt->execute()) {
            $success_message = "Вы успешно зарегистрировались!";
            // Автоматический вход после регистрации
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['username'] = $username;
            header("Location: lk.php");
            exit();
        } else {
            $error_message = "Произошла ошибка при регистрации!";
        }
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Filmoteka</title>
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
    }
    .btn:hover {
      background-color: #e64a00;
    }
    .message {
      font-size: 1.2em;
      margin-bottom: 20px;
      color: green;
    }
    .error-message {
      font-size: 1.2em;
      margin-bottom: 20px;
      color: red;
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
  <h1>Регистрация</h1>
  
  <!-- Сообщения об ошибке и успехе -->
  <?php if ($success_message): ?>
    <div class="message"><?php echo $success_message; ?></div>
  <?php elseif ($error_message): ?>
    <div class="error-message"><?php echo $error_message; ?></div>
  <?php endif; ?>
  
  <form method="POST" style="max-width: 400px; margin: 0 auto; display: flex; flex-direction: column; gap: 15px;">
    <input type="text" name="username" placeholder="Имя пользователя" required style="padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
    <input type="email" name="email" placeholder="Email" required style="padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
    <input type="password" name="password" placeholder="Пароль" required style="padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
    <button type="submit" class="btn">Зарегистрироваться</button>
  </form>

</main>
<footer class="footer">
  <p>&copy; 2025 Filmoteka. Все права защищены.</p>
</footer>
</body>
</html>
