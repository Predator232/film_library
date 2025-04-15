<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование жанров</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            font-family: Times New Roman, sans-serif;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        h1 {
            margin-bottom: 20px;
        }
        .container {
            width: 100%;
            max-width: 600px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .genre-list {
            margin-top: 20px;
        }
        .genre-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .genre-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Редактирование жанров</h1>
        
        <form action="manage_genres.php" method="POST">
            <div class="form-group">
                <label for="name">Название жанра:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="description">Описание:</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>
            
            <button type="submit">Добавить жанр</button>
        </form>
        
        <div class="genre-list">
            <?php
            $servername = "127.0.0.1:3306";
            $username = "root";
            $password = "";
            $dbname = "film_library";
            
            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Ошибка подключения: " . $conn->connect_error);
            }
            
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $name = $_POST['name'];
                $description = $_POST['description'];
                
                $sql = "INSERT INTO genres (name, description) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $name, $description);
                
                if ($stmt->execute()) {
                    echo "<p style='color: green;'>Жанр успешно добавлен!</p>";
                } else {
                    echo "<p style='color: red;'>Ошибка: " . $stmt->error . "</p>";
                }
            }
            
            $sql = "SELECT * FROM genres ORDER BY name";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<div class='genre-item'>";
                    echo "<div>";
                    echo "<strong>" . $row['name'] . "</strong><br>";
                    echo $row['description'];
                    echo "</div>";
                    echo "<button onclick='deleteGenre(" . $row['genre_id'] . ")'>Удалить</button>";
                    echo "</div>";
                }
            } else {
                echo "<p>Жанры не найдены</p>";
            }
            
            $conn->close();
            ?>
        </div>
    </div>
    
    <script>
    function deleteGenre(genreId) {
        if (confirm('Вы уверены, что хотите удалить этот жанр?')) {
            window.location.href = 'delete_genre.php?id=' + genreId;
        }
    }
    </script>
</body>
</html> 