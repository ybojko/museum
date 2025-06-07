<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="#">Краєзнавчий музей</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="/museum/index.php">Головна</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/museum/exhibits/exhibits.php">Експонати</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/museum/exhibitions/exhibitions.php">Виставки</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/museum/tickets/tickets.php">Квитки</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/museum/halls/halls.php">Зали</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/museum/restorations/restorations.php">Реставрації</a>
                </li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/museum/employees/employees.php">Робітники</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/museum/reviews/reviews.php">Відгуки</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/museum/visitors/visitors.php">Відвідувачі</a>
                    </li>
                <?php endif; ?>
                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'visitor_manager' || $_SESSION['role'] === 'admin')): ?>
                    <?php if ($_SESSION['role'] === 'visitor_manager'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/museum/visitors/visitors.php">Відвідувачі</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/museum/reviews/reviews.php">Відгуки</a>
                    </li>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'visitor_manager' || $_SESSION['role'] === 'staff_manager')): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/museum/admin_dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Панель адміністратора
                        </a>
                    </li>
                <?php endif; ?>
                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'staff_manager' || $_SESSION['role'] === 'admin')): ?>
                    <?php if ($_SESSION['role'] === 'staff_manager'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/museum/employees/employees.php">Робітники</a>
                    </li>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/museum/profile.php">
                            <i class="fas fa-user me-1"></i>Профіль
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/museum/logout.php">Вихід (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/museum/register.php">Реєстрація</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/museum/login.php">Вхід</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<style>
    .navbar-nav {
        flex-wrap: wrap;
    }
    .nav-item {
        margin-right: 0.5rem;
    }
    @media (max-width: 991px) {
        .navbar-nav {
            margin-top: 0.5rem;
        }
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>