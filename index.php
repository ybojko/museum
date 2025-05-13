<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>duzhe silniy musei</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
<div class="container mt-5">
    <h3>Ласкаво просимо до нашого музею!</h3>
</div>

<div class="container mt-5">
    <?php if ($role === 'admin'): ?>
        <div class="alert alert-success">
            Ласкаво просимо, адміністратор <?php echo htmlspecialchars($_SESSION['username']); ?>!
        </div>
        <div>
            <a href="admin_dashboard.php" class="btn btn-primary">Перейти до панелі адміністратора</a>
        </div>
    <?php elseif ($role === 'user'): ?>
        <div class="alert alert-info">
            Ласкаво просимо, <?php echo htmlspecialchars($_SESSION['username']); ?>!
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            Ласкаво просимо, гість! <a href="login.php">Увійдіть</a>, щоб отримати більше можливостей.
        </div>
    <?php endif; ?>

</div>

<?php include 'footer.php'; ?> 
</body>
</html>