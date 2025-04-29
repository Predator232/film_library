<?php
session_start();
require_once 'config.php';

// Проверка авторизации и прав администратора
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

// Получаем ID фильма из URL
$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($movie_id === 0) {
    header("Location: gallery.php");
    exit();
}

try {
    // Удаляем фильм
    $stmt = $pdo->prepare("DELETE FROM movies WHERE movie_id = ?");
    $stmt->execute([$movie_id]);
    
    $_SESSION['success'] = "Фильм успешно удален";
} catch(PDOException $e) {
    $_SESSION['error'] = "Ошибка при удалении фильма: " . $e->getMessage();
}

header("Location: gallery.php");
exit();
?> 