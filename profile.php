<?php
include 'connectionString.php';

// Перевірка авторизації
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Отримання інформації користувача
$stmt = $conn->prepare("SELECT username, email, user_type, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header('Location: logout.php');
    exit;
}

// Отримання статистики користувача
$stats = [
    'account_age' => floor((time() - strtotime($user['created_at'])) / (60 * 60 * 24)),
    'user_type_label' => $user['user_type'] === 'benefitial' ? 'Пільговий користувач' : 'Звичайний користувач'
];
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мій профіль - Музей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/museum-theme.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="museum-bg">
<?php include 'header.php'; ?>

<div class="museum-content">
    <div class="container mt-5">
        <div class="row">
            <!-- Основна інформація профілю -->
            <div class="col-md-8">
                <div class="museum-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-circle museum-icon me-3" style="font-size: 3rem; color: var(--museum-accent);"></i>
                                <div>
                                    <h3 class="museum-title mb-1"><?php echo htmlspecialchars($user['username']); ?></h3>
                                    <p class="text-muted mb-0"><?php echo $stats['user_type_label']; ?></p>
                                </div>
                            </div>
                            <a href="edit-profile.php" class="btn museum-btn-primary">
                                <i class="fas fa-edit me-2"></i>Редагувати профіль
                            </a>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-envelope text-muted me-3"></i>
                                    <div>
                                        <small class="text-muted d-block">Email</small>
                                        <span><?php echo htmlspecialchars($user['email']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-calendar-alt text-muted me-3"></i>
                                    <div>
                                        <small class="text-muted d-block">Дата реєстрації</small>
                                        <span><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-tag text-muted me-3"></i>
                                    <div>
                                        <small class="text-muted d-block">Тип акаунта</small>
                                        <span class="badge museum-badge"><?php echo $stats['user_type_label']; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-clock text-muted me-3"></i>
                                    <div>
                                        <small class="text-muted d-block">Днів в системі</small>
                                        <span><?php echo $stats['account_age']; ?> днів</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Бічна панель з додатковою інформацією -->
            <div class="col-md-4">
                <div class="museum-card mb-4">
                    <div class="card-body text-center">
                        <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                        <h5>Безпека акаунта</h5>
                        <p class="text-muted mb-3">Ваш акаунт захищено</p>
                        <a href="edit-profile.php" class="btn museum-btn-secondary btn-sm">
                            <i class="fas fa-key me-1"></i>Змінити пароль
                        </a>
                    </div>
                </div>

                <div class="museum-card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-info-circle me-2"></i>Інформація
                        </h6>
                        <small class="text-muted">
                            <?php if ($user['user_type'] === 'benefitial'): ?>
                                <i class="fas fa-star text-warning me-1"></i>
                                Як пільговий користувач, ви маєте право на знижки при відвідуванні музею.
                            <?php else: ?>
                                <i class="fas fa-user me-1"></i>
                                Звичайний користувач музею. Ви можете змінити тип акаунта в налаштуваннях профілю.
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Швидкі дії -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="museum-card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-bolt me-2"></i>Швидкі дії
                        </h5>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="tickets/tickets.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-ticket-alt d-block mb-2"></i>
                                    Мої квитки
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="exhibitions/exhibitions.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-eye d-block mb-2"></i>
                                    Виставки
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="exhibits/exhibits.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-gem d-block mb-2"></i>
                                    Експонати
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="edit-profile.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-cog d-block mb-2"></i>
                                    Налаштування
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
