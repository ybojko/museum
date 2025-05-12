<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Перевірка, чи всі поля заповнені
    if (empty($username) || empty($email) || empty($password)) {
        echo "<script>alert('Будь ласка, заповніть усі поля.');</script>";
    } else {
        include 'connectionString.php';

        try {
            // Перевірка, чи існує користувач з таким ім'ям або електронною адресою
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                echo "<script>alert('Користувач з таким ім\'ям або електронною адресою вже існує.');</script>";
            } else {
                // Хешування пароля
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                // Додавання нового користувача
                $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $email, $hashed_password);
                if ($stmt->execute()) {
                    echo "<script>
                            alert('Реєстрація успішна!');
                            window.location.href = 'index.php';
                          </script>";
                } else {
                    echo "<script>alert('Помилка при реєстрації. Спробуйте ще раз.');</script>";
                }
            }

            $stmt->close();
        } catch (Exception $e) {
            echo "<script>alert('Виникла помилка: " . $e->getMessage() . "');</script>";
        }

        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Реєстрація</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Реєстрація</h2>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="username" class="form-label">Ім'я користувача</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Електронна пошта</label>
            <input type="email" class="form-control" id="email" name="email" pattern="^[^@]+@[^@]+\.[a-zA-Z]{2,}$" required>
            <div class="form-text">Електронна адреса повинна містити символ "@".</div>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Пароль</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Зареєструватися</button>
    </form>
</div>
<?php include 'footer.php'; ?> 
</body>
</html>