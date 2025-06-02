<?php
include '../connectionString.php'; // Підключення до бази даних

// Перевірка авторизації
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Отримання всіх відгуків
$sql = "SELECT * FROM reviews ORDER BY review_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Всі відгуки</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../header.php'; ?>

<div class="container mt-5">
    <h3>Всі відгуки</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Користувач</th>
                <th>Тип користувача</th>
                <th>Назва виставки</th>
                <th>Відгук</th>
                <th>Дата</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['exhibition_title']); ?></td>
                        <td><?php echo htmlspecialchars($row['review_text']); ?></td>
                        <td><?php echo htmlspecialchars($row['review_date']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">Немає відгуків</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../footer.php'; ?>
</body>
</html>