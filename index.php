<?php session_start(); ?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Краєзнавчий музей - Головна</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/museum-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<?php include 'header.php'; ?>
<?php
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
		$role = 'admin';
	} elseif (isset($_SESSION['user_id'])) {
		$role = 'user';
	} else {
		$role = 'guest';
	}
?>

<div class="hero-section">
    <div class="container">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="hero-title fade-in">Ласкаво просимо до Краєзнавчого музею!</h1>
                    <p class="hero-subtitle slide-up">Відкрийте для себе багату історію та культурну спадщину нашого краю</p>
                    <div class="hero-buttons">
                        <?php if ($role === 'guest'): ?>
                            <a href="register.php" class="btn btn-museum-primary btn-lg me-3">
                                <i class="fas fa-user-plus me-2"></i>Приєднатися
                            </a>
                            <a href="login.php" class="btn btn-museum-outline btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Увійти
                            </a>
                        <?php elseif ($role === 'admin'): ?>
                            <a href="admin_dashboard.php" class="btn btn-museum-primary btn-lg">
                                <i class="fas fa-cog me-2"></i>Панель адміністратора
                            </a>
                        <?php else: ?>
                            <a href="exhibits/exhibits.php" class="btn btn-museum-primary btn-lg me-3">
                                <i class="fas fa-gem me-2"></i>Переглянути експонати
                            </a>
                            <a href="exhibitions/exhibitions.php" class="btn btn-museum-outline btn-lg">
                                <i class="fas fa-images me-2"></i>Поточні виставки
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image">
                    <div class="museum-showcase">
                        <div class="showcase-item">
                            <i class="fas fa-landmark showcase-icon"></i>
                            <h3>Історичні артефакти</h3>
                            <p>Унікальні експонати з багатовікової історії</p>
                        </div>
                        <div class="showcase-item">
                            <i class="fas fa-palette showcase-icon"></i>
                            <h3>Мистецькі колекції</h3>
                            <p>Шедеври місцевих та відомих художників</p>
                        </div>
                        <div class="showcase-item">
                            <i class="fas fa-globe showcase-icon"></i>
                            <h3>Культурна спадщина</h3>
                            <p>Традиції та звичаї нашого народу</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <?php if ($role === 'admin'): ?>
        <div class="alert alert-museum-success alert-with-icon">
            <i class="fas fa-crown me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">Ласкаво просимо, адміністратор <?php echo htmlspecialchars($_SESSION['username']); ?>!</h5>
                <p class="mb-0">У вас є повний доступ до всіх функцій музейної системи</p>
            </div>
        </div>
    <?php elseif ($role === 'user'): ?>
        <div class="alert alert-museum-info alert-with-icon">
            <i class="fas fa-user me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">Ласкаво просимо, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h5>
                <p class="mb-0">Насолоджуйтесь вивченням нашої колекції та відкривайте нові експонати</p>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-museum-warning alert-with-icon">
            <i class="fas fa-info-circle me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">Ласкаво просимо, гість!</h5>
                <p class="mb-0">
                    <a href="login.php" class="alert-link">Увійдіть в систему</a> або 
                    <a href="register.php" class="alert-link">зареєструйтесь</a>, щоб отримати більше можливостей.
                </p>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <h3 class="section-title">Відкрийте музей разом з нами</h3>
                <p class="text-muted">Перегляньте відео-екскурсію нашим музеєм</p>
            </div>
            <div class="ratio ratio-16x9 shadow rounded overflow-hidden">
                <iframe 
                    src="https://www.youtube.com/embed/vlPj4MKYJJI" 
                    title="Museum Video Tour" 
                    allowfullscreen
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share">
                </iframe>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-gem"></i>
                </div>
                <h4>Експонати</h4>
                <p>Перегляньте нашу унікальну колекцію історичних експонатів та артефактів</p>
                <a href="exhibits/exhibits.php" class="btn btn-museum-outline btn-sm">Переглянути</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-images"></i>
                </div>
                <h4>Виставки</h4>
                <p>Відвідайте поточні виставки та дізнайтесь про майбутні події</p>
                <a href="exhibitions/exhibitions.php" class="btn btn-museum-outline btn-sm">Дізнатися більше</a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <h4>Квитки</h4>
                <p>Придбайте квитки онлайн та заплануйте свій візит до музею</p>
                <a href="tickets/tickets.php" class="btn btn-museum-outline btn-sm">Купити квитки</a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 
</body>
</html>