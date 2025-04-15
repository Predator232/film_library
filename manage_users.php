<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Работа с Пользователями</title>
    <style>
        body {
            font-family: Times New Roman, sans-serif;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
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
        input[type="text"], input[type="email"], input[type="password"] {
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
        .user-list {
            margin-top: 20px;
        }
        .user-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .user-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Работа с Пользователями</h1>
        
        <form action="manage_users.php" method="POST">
            <div class="form-group">
                <label for="username">Имя пользователя:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Добавить пользователя</button>
        </form>
        
        <div class="user-list">
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
                $username = $_POST['username'];
                $email = $_POST['email'];
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                
                $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $username, $email, $password);
                
                if ($stmt->execute()) {
                    echo "<p style='color: green;'>Пользователь успешно добавлен!</p>";
                } else {
                    echo "<p style='color: red;'>Ошибка: " . $stmt->error . "</p>";
                }
            }
            
            $sql = "SELECT * FROM users ORDER BY username";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<div class='user-item'>";
                    echo "<div>";
                    echo "<strong>" . $row['username'] . "</strong><br>";
                    echo $row['email'];
                    echo "</div>";
                    echo "<button onclick='deleteUser(" . $row['user_id'] . ")'>Удалить</button>";
                    echo "</div>";
                }
            } else {
                echo "<p>Пользователи не найдены</p>";
            }
            
            $conn->close();
            ?>
        </div>
    </div>
    
    <script>
    function deleteUser(userId) {
        if (confirm('Вы уверены, что хотите удалить этого пользователя?')) {
            window.location.href = 'delete_user.php?id=' + userId;
        }
    }
    </script>
</body>
</html> 