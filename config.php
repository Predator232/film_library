<?php
// Настройки подключения к базе данных
$servername = "127.0.0.1:3306";
$username = "root";
$password = "";
$dbname = "film_library_new";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set names utf8");
} catch(PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}
?> 