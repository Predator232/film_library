<?php
session_start();

// Сообщение об успешной покупке
$success = false;
$error = '';

// Подключение к БД
$servername = "127.0.0.1:3306";
$username = "root";
$password = "";
$dbname = "film_library";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Обработка покупки подписки
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['subscription_id'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $subscription_id = intval($_POST['subscription_id']);

    // Проверяем, есть ли у пользователя активная подписка
    $check_query = "SELECT * FROM user_subscriptions 
                   WHERE user_id = ? AND (end_date IS NULL OR end_date > NOW())";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $active_subscription = $check_stmt->get_result();

    if ($active_subscription->num_rows > 0) {
        $error = "У вас уже есть активная подписка.";
    } else {
        // Получаем информацию о подписке
        $sub_query = "SELECT duration FROM subscriptions WHERE subscription_id = ?";
        $sub_stmt = $conn->prepare($sub_query);
        $sub_stmt->bind_param("i", $subscription_id);
        $sub_stmt->execute();
        $sub_result = $sub_stmt->get_result();
        $sub_data = $sub_result->fetch_assoc();

        if ($sub_data) {
            // Рассчитываем дату окончания подписки
            $end_date = date('Y-m-d H:i:s', strtotime('+' . $sub_data['duration'] . ' months'));

            // Добавляем подписку
            $stmt = $conn->prepare("INSERT INTO user_subscriptions (user_id, subscription_id, end_date) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $user_id, $subscription_id, $end_date);
            
            if ($stmt->execute()) {
                $success = true;
            } else {
                $error = "Ошибка при покупке подписки: " . $conn->error;
            }
            $stmt->close();
        } else {
            $error = "Подписка не найдена.";
        }
        $sub_stmt->close();
    }
    $check_stmt->close();
}

// Получаем список подписок
$query = "SELECT * FROM subscriptions ORDER BY price ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Ошибка запроса: " . mysqli_error($conn));
}

$subscriptions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $subscriptions[] = $row;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Каталог подписок</title>
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
      padding: 40px 20px;
    }
    .subscription-group {
      margin-bottom: 50px;
    }
    .subscription-group h2 {
      color: #ff5500;
      margin-bottom: 20px;
    }
    .catalog {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 20px;
    }
    .plan {
      border: 2px solid #ff5500;
      border-radius: 10px;
      padding: 20px;
      width: 250px;
      background-color: #fff;
      transition: transform 0.3s;
    }
    .plan:hover {
      transform: scale(1.03);
    }
    .plan h3 {
      margin-bottom: 10px;
      color: #ff5500;
    }
    .plan p {
      margin-bottom: 10px;
    }
    .plan button {
      background-color: #ff5500;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 25px;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    .plan button:hover {
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
    .success-message {
      background: #d4edda;
      color: #155724;
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
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
      <a href="movies.php">Фильмы</a>
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
    <h1>Каталог подписок</h1>

    <!-- Сообщения об ошибке и успехе -->
    <?php if ($success): ?>
      <div class="success-message">
        Вы успешно приобрели подписку! Спасибо за покупку!
      </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
      <div class="error-message">
        <?php echo $error; ?>
      </div>
    <?php endif; ?>

    <div class="catalog">
        <?php foreach ($subscriptions as $subscription): ?>
            <div class="plan">
                <h3><?php echo $subscription['name']; ?></h3>
                <p>Длительность: <?php echo $subscription['duration']; ?> мес.</p>
                <p>Цена: <?php echo number_format($subscription['price'], 2, '.', ' '); ?> ₽</p>
                <p><?php echo $subscription['description']; ?></p>
                <p><small><?php echo $subscription['features']; ?></small></p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="POST" action="">
                        <input type="hidden" name="subscription_id" value="<?php echo $subscription['subscription_id']; ?>">
                        <button type="submit">Купить</button>
                    </form>
                <?php else: ?>
                    <p><a href="login.php">Войдите</a> для покупки подписки</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
  </main>

  <footer class="footer">
    <p>&copy; 2025 Filmoteka. Все права защищены.</p>
  </footer>
</body>
</html>
