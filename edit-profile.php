<?php
include 'connectionString.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

$stmt = $conn->prepare("SELECT username, email, user_type FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header('Location: logout.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $user_type = $_POST['user_type'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($email)) {
        $error_message = "Ім'я користувача та email є обов'язковими полями.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Введіть коректний email адрес.";
    } else {
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $check_stmt->bind_param("ssi", $username, $email, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error_message = "Ім'я користувача або email вже використовуються іншим користувачем.";
        } else {
            if (!empty($new_password)) {
                if (empty($current_password)) {
                    $error_message = "Введіть поточний пароль для зміни.";
                } elseif ($new_password !== $confirm_password) {
                    $error_message = "Нові паролі не співпадають.";
                } elseif (strlen($new_password) < 6) {
                    $error_message = "Новий пароль повинен містити щонайменше 6 символів.";
                } else {
                    $password_stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
                    $password_stmt->bind_param("i", $user_id);
                    $password_stmt->execute();
                    $password_result = $password_stmt->get_result();
                    $password_data = $password_result->fetch_assoc();
                    
                    if (!password_verify($current_password, $password_data['password'])) {
                        $error_message = "Поточний пароль введено невірно.";
                    } else {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, user_type = ?, password = ? WHERE id = ?");
                        $update_stmt->bind_param("ssssi", $username, $email, $user_type, $hashed_password, $user_id);
                        
                        if ($update_stmt->execute()) {
                            $_SESSION['username'] = $username;
                            $success_message = "Профіль успішно оновлено!";
                            $user['username'] = $username;
                            $user['email'] = $email;
                            $user['user_type'] = $user_type;
                        } else {
                            $error_message = "Помилка при оновленні профілю.";
                        }
                    }
                }
            } else {
                $update_stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, user_type = ? WHERE id = ?");
                $update_stmt->bind_param("sssi", $username, $email, $user_type, $user_id);
                
                if ($update_stmt->execute()) {
                    $_SESSION['username'] = $username;
                    $success_message = "Профіль успішно оновлено!";
                    $user['username'] = $username;
                    $user['email'] = $email;
                    $user['user_type'] = $user_type;
                } else {
                    $error_message = "Помилка при оновленні профілю.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редагування профілю - Музей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/museum-theme.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="museum-bg">
<?php include 'header.php'; ?>

<div class="museum-content">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="museum-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <i class="fas fa-user-edit museum-icon me-3" style="font-size: 2rem; color: var(--museum-accent);"></i>
                            <h3 class="museum-title mb-0">Редагування профілю</h3>
                        </div>

                        <?php if ($success_message): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo htmlspecialchars($success_message); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user me-2"></i>Ім'я користувача
                                </label>
                                <input type="text" class="form-control museum-input" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </label>
                                <input type="email" class="form-control museum-input" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <div class="mb-4">
                                <label for="user_type" class="form-label">
                                    <i class="fas fa-tag me-2"></i>Тип акаунта
                                </label>
                                <select class="form-select museum-input" id="user_type" name="user_type">
                                    <option value="default" <?php echo $user['user_type'] === 'default' ? 'selected' : ''; ?>>
                                        Звичайний користувач
                                    </option>
                                    <option value="benefitial" <?php echo $user['user_type'] === 'benefitial' ? 'selected' : ''; ?>>
                                        Пільговий користувач
                                    </option>
                                </select>
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3">
                                <i class="fas fa-key me-2"></i>Зміна пароля (необов'язково)
                            </h5>

                            <div class="mb-3">
                                <label for="current_password" class="form-label">Поточний пароль</label>
                                <input type="password" class="form-control museum-input" id="current_password" name="current_password">
                                <div class="form-text">Введіть поточний пароль, якщо хочете змінити його</div>
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">Новий пароль</label>
                                <input type="password" class="form-control museum-input" id="new_password" name="new_password">
                                <div class="form-text">Мінімум 6 символів</div>
                            </div>

                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Підтвердіть новий пароль</label>
                                <input type="password" class="form-control museum-input" id="confirm_password" name="confirm_password">
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="profile.php" class="btn btn-outline-secondary me-md-2">
                                    <i class="fas fa-arrow-left me-2"></i>Скасувати
                                </a>
                                <button type="submit" class="btn museum-btn-primary">
                                    <i class="fas fa-save me-2"></i>Зберегти зміни
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const currentPassword = document.getElementById('current_password');

    function checkPasswordMatch() {
        if (newPassword.value && confirmPassword.value) {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Паролі не співпадають');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
    }

    newPassword.addEventListener('input', checkPasswordMatch);
    confirmPassword.addEventListener('input', checkPasswordMatch);

    newPassword.addEventListener('input', function() {
        if (this.value && !currentPassword.value) {
            currentPassword.setCustomValidity('Введіть поточний пароль для зміни');
        } else {
            currentPassword.setCustomValidity('');
        }
    });

    currentPassword.addEventListener('input', function() {
        if (newPassword.value && !this.value) {
            this.setCustomValidity('Введіть поточний пароль для зміни');
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>
</body>
</html>
