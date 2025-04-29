<?php
session_start();
require_once 'config.php';

// Сообщение об успешной покупке
$success = false;
$error = '';

// Обработка покупки подписки
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['subscription_id'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $subscription_id = intval($_POST['subscription_id']);

    try {
        // Проверяем, есть ли у пользователя активная подписка
        $check_query = "SELECT * FROM users 
                       WHERE user_id = ? AND (subscription_end IS NULL OR subscription_end > NOW())";
        $check_stmt = $pdo->prepare($check_query);
        $check_stmt->execute([$user_id]);
        $active_subscription = $check_stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($active_subscription) > 0) {
            $error = "У вас уже есть активная подписка.";
        } else {
            // Получаем информацию о подписке
            $sub_query = "SELECT duration FROM subscriptions WHERE subscription_id = ?";
            $sub_stmt = $pdo->prepare($sub_query);
            $sub_stmt->execute([$subscription_id]);
            $sub_data = $sub_stmt->fetch(PDO::FETCH_ASSOC);

            if ($sub_data) {
                // Рассчитываем дату окончания подписки
                $end_date = date('Y-m-d H:i:s', strtotime('+' . $sub_data['duration'] . ' months'));

                // Обновляем подписку пользователя
                $update_stmt = $pdo->prepare("UPDATE users SET subscription_id = ?, subscription_start = NOW(), subscription_end = ? WHERE user_id = ?");
                if ($update_stmt->execute([$subscription_id, $end_date, $user_id])) {
                    $success = true;
                } else {
                    $error = "Ошибка при покупке подписки.";
                }
            } else {
                $error = "Подписка не найдена.";
            }
        }
    } catch(PDOException $e) {
        $error = "Ошибка при покупке подписки: " . $e->getMessage();
    }
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = $_SESSION['is_admin'] ?? false;

// Получаем список подписок
try {
    $query = "SELECT * FROM subscriptions ORDER BY price ASC";
    $stmt = $pdo->query($query);
    $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Ошибка запроса: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Каталог подписок</title>
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
                        <button type="submit" class="btn">Купить</button>
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
