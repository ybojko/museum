
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="#">Краєзнавчий музей</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
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
                    <a class="nav-link" href="/museum/halls/halls.php">Зали</a>
                </li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/museum/employees/employees.php">Робітники</a>
                    </li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
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