
<!-- Museum Theme CSS -->
<link rel="stylesheet" href="/museum/assets/css/museum-theme.css">
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Source+Sans+Pro:wght@400;600&display=swap" rel="stylesheet">
<!-- Font Awesome Icons -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand museum-brand" href="/museum/index.php">
            Краєзнавчий музей
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Меню, яке ховається/відкривається бургером -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- Ваші кнопки меню -->
                <li class="nav-item">
                    <a class="nav-link" href="/museum/index.php">
                        <i class="fas fa-home me-1"></i>Головна
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/museum/exhibits/exhibits.php">
                        <i class="fas fa-gem me-1"></i>Експонати
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/museum/exhibitions/exhibitions.php">
                        <i class="fas fa-images me-1"></i>Виставки
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/museum/tickets/tickets.php">
                        <i class="fas fa-ticket-alt me-1"></i>Квитки
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/museum/halls/halls.php">
                        <i class="fas fa-building me-1"></i>Зали
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/museum/restorations/restorations.php">
                        <i class="fas fa-tools me-1"></i>Реставрації
                    </a>
                </li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog me-1"></i>Адміністрування
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/museum/employees/employees.php">
                                <i class="fas fa-users me-1"></i>Робітники
                            </a></li>
                            <li><a class="dropdown-item" href="/museum/reviews/reviews.php">
                                <i class="fas fa-star me-1"></i>Відгуки
                            </a></li>
                            <li><a class="dropdown-item" href="/museum/visitors/visitors.php">
                                <i class="fas fa-user-friends me-1"></i>Відвідувачі
                            </a></li>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link user-menu" href="/museum/logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>
                            Вихід (<?php echo htmlspecialchars($_SESSION['username']); ?>)
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/museum/register.php">
                            <i class="fas fa-user-plus me-1"></i>Реєстрація
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/museum/login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Вхід
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>    </div>
</nav>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>