<?php
session_start();
require_once 'config.php';

// Проверка авторизации и прав администратора
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $movie_id = $_GET['id'];
    
    try {
        // Проверяем, существует ли фильм
        $check_stmt = $pdo->prepare("SELECT movie_id FROM movies WHERE movie_id = ?");
        $check_stmt->execute([$movie_id]);
        
        if ($check_stmt->fetch()) {
            // Удаляем связанные записи из movie_genres
            $delete_genres_stmt = $pdo->prepare("DELETE FROM movie_genres WHERE movie_id = ?");
            $delete_genres_stmt->execute([$movie_id]);
            
            // Удаляем сам фильм
            $delete_stmt = $pdo->prepare("DELETE FROM movies WHERE movie_id = ?");
            $delete_stmt->execute([$movie_id]);
            
            $_SESSION['success'] = 'Фильм успешно удален';
        } else {
            $_SESSION['error'] = 'Фильм не найден';
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Ошибка при удалении фильма: ' . $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'ID фильма не указан';
}

header('Location: gallery.php');
exit();
?> 