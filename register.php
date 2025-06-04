<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $user_type = isset($_POST['user_type']) && in_array($_POST['user_type'], ['default', 'benefitial']) ? $_POST['user_type'] : 'default';

    if (empty($username) || empty($email) || empty($password)) {
        echo "<script>alert('Будь ласка, заповніть усі поля.');</script>";
    } else {
        include 'connectionString.php';

        try {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                echo "<script>alert('Користувач з таким ім\'ям або електронною адресою вже існує.');</script>";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, user_type, created_at) VALUES (?, ?, ?, 'user', ?, NOW())");
                $stmt->bind_param("ssss", $username, $email, $hashed_password, $user_type);
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
    <title>Реєстрація - Краєзнавчий музей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/museum-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    
<?php include 'header.php'; ?>

<div class="auth-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="auth-card">
                    <div class="auth-header">
                        <div class="auth-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h2 class="auth-title">Реєстрація</h2>
                        <p class="auth-subtitle">Створіть акаунт для доступу до музейних послуг</p>
                    </div>
                    
                    <form method="POST" action="" class="auth-form">
                        <div class="form-group">
                            <label for="username" class="form-label">
                                <i class="fas fa-user me-2"></i>Ім'я користувача
                            </label>
                            <input type="text" class="form-control" id="username" name="username" required 
                                   placeholder="Введіть ваше ім'я користувача">
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i>Електронна пошта
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   pattern="^[^@]+@[^@]+\.[a-zA-Z]{2,}$" required 
                                   placeholder="Введіть вашу електронну пошту">
                            <div class="form-text">Електронна адреса повинна містити символ "@"</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Пароль
                            </label>
                            <input type="password" class="form-control" id="password" name="password" required 
                                   placeholder="Створіть надійний пароль">
                        </div>
                        
                        <div class="form-group">
                            <label for="user_type" class="form-label">
                                <i class="fas fa-id-card me-2"></i>Тип акаунта
                            </label>
                            <select class="form-select" id="user_type" name="user_type" required>
                                <option value="default" selected>Звичайний відвідувач</option>
                                <option value="benefitial">Пільговий відвідувач</option>
                            </select>
                            <div class="form-text">Пільговий тип надає знижки на квитки</div>
                        </div>
                        
                        <button type="submit" class="btn btn-museum-primary btn-lg w-100">
                            <i class="fas fa-user-plus me-2"></i>Зареєструватися
                        </button>
                    </form>
                    
                    <div class="auth-footer">
                        <p class="text-center">
                            Вже маєте акаунт? 
                            <a href="login.php" class="auth-link">Увійдіть</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 
</body>
</html>