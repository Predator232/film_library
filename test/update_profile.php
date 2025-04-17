<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Проверка текущего пароля
    $conn = new mysqli("127.0.0.1:3306", "root", "", "film_library");
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!password_verify($current_password, $user['password'])) {
        $error = "Неверный текущий пароль";
    } else {
        // Проверка нового пароля, если он указан
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $error = "Новые пароли не совпадают";
            } elseif (strlen($new_password) < 6) {
                $error = "Новый пароль должен содержать минимум 6 символов";
            }
        }

        if (empty($error)) {
            // Обновление данных пользователя
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE user_id = ?");
                $stmt->bind_param("sssi", $username, $email, $hashed_password, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
                $stmt->bind_param("ssi", $username, $email, $user_id);
            }

            if ($stmt->execute()) {
                $success = "Данные успешно обновлены";
            } else {
                $error = "Ошибка при обновлении данных: " . $conn->error;
            }
        }
    }

    $conn->close();
}

// Перенаправление обратно в личный кабинет с сообщением
if (!empty($error)) {
    $_SESSION['error'] = $error;
} elseif (!empty($success)) {
    $_SESSION['success'] = $success;
}
header("Location: lk.php");
exit();
?> 