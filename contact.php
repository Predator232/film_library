<?php
session_start();
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $message = htmlspecialchars(trim($_POST['message']));

    // Здесь может быть сохранение в БД или лог
    $success = true;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Контакты</title>
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
      max-width: 600px;
      margin: 50px auto;
      text-align: center;
    }
    form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    input, textarea {
      padding: 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
      font-size: 1em;
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
    .footer {
      background-color: #f2f2f2;
      padding: 20px 30px;
      text-align: center;
      font-size: 0.9em;
      color: #777;
      margin-top: 60px;
    }
    .contact-info {
      margin-top: 30px;
      text-align: left;
      padding: 20px;
      background-color: #f9f9f9;
      border-radius: 10px;
    }
    .contact-info h3 {
      color: #ff5500;
      margin-bottom: 15px;
    }
    .contact-info p {
      margin: 10px 0;
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
    <h1>Связаться с нами</h1>

    <?php if ($success): ?>
      <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        Спасибо за сообщение! Мы свяжемся с вами по электронной почте.
      </div>
    <?php endif; ?>

    <form method="POST">
      <input type="text" name="name" placeholder="Ваше имя" required>
      <input type="email" name="email" placeholder="Ваш email" required>
      <textarea name="message" rows="5" placeholder="Ваше сообщение..." required></textarea>
      <button type="submit">Отправить</button>
    </form>

    <div class="contact-info">
      <h3>Контактная информация</h3>
      <p><strong>Адрес:</strong> г. Москва, ул. Кинотеатральная, д. 1</p>
      <p><strong>Телефон:</strong> +7 (495) 123-45-67</p>
      <p><strong>Email:</strong> info@filmoteka.ru</p>
      <p><strong>Режим работы:</strong> Пн-Пт: 9:00 - 21:00, Сб-Вс: 10:00 - 22:00</p>
    </div>
  </main>

  <footer class="footer">
    <p>&copy; 2025 Filmoteka. Все права защищены.</p>
  </footer>
</body>
</html>
