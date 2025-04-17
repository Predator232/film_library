<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['movie_id'])) {
    $user_id = $_SESSION['user_id'];
    $movie_id = $_POST['movie_id'];
    $action = $_POST['action'] ?? 'add';

    $conn = new mysqli("127.0.0.1:3306", "root", "", "film_library");
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }

    if ($action === 'add') {
        // Проверяем, не добавлен ли уже фильм в избранное
        $check_stmt = $conn->prepare("SELECT favorite_id FROM favorite_movies WHERE user_id = ? AND movie_id = ?");
        $check_stmt->bind_param("ii", $user_id, $movie_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO favorite_movies (user_id, movie_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $movie_id);
            $stmt->execute();
        }
    } else {
        $stmt = $conn->prepare("DELETE FROM favorite_movies WHERE user_id = ? AND movie_id = ?");
        $stmt->bind_param("ii", $user_id, $movie_id);
        $stmt->execute();
    }

    $conn->close();
    
    // Возвращаемся на предыдущую страницу
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
?> 