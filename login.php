<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    include 'connectionString.php';

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $username, $hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            if ($role === 'admin') {
                header('Location: index.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            echo "Невірний пароль.";
        }
    } else {
        echo "Користувача з такою електронною поштою не знайдено.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід - Краєзнавчий музей</title>
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
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <h2 class="auth-title">Вхід до музею</h2>
                        <p class="auth-subtitle">Увійдіть до свого акаунта, щоб отримати доступ до всіх можливостей</p>
                    </div>
                    
                    <form method="POST" action="" class="auth-form">
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i>Електронна пошта
                            </label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   placeholder="Введіть вашу електронну пошту">
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-2"></i>Пароль
                            </label>
                            <input type="password" class="form-control" id="password" name="password" required 
                                   placeholder="Введіть ваш пароль">
                        </div>
                        
                        <button type="submit" class="btn btn-museum-primary btn-lg w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Увійти
                        </button>
                    </form>
                    
                    <div class="auth-footer">
                        <p class="text-center">
                            Ще не маєте акаунта? 
                            <a href="register.php" class="auth-link">Зареєструватися</a>
                        </p>
                        <div class="text-center">
                            <a href="index.php" class="back-link">
                                <i class="fas fa-arrow-left me-1"></i>Повернутися на головну
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>