<?php
include '../connectionString.php'; // Підключення до бази даних

if (!isset($_SESSION['role'])) {
    header('Location: ../index.php');
    exit;
}

$role = $_SESSION['role'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Формування SQL-запиту
if ($role === 'admin') {
    // Адміністратор бачить усі квитки
    $sql = "SELECT * FROM ticket_details_view";
    $stmt = $conn->prepare($sql);
} else {
    // Звичайний користувач бачить лише свої квитки
    $sql = "SELECT * FROM ticket_details_view WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Квитки</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../header.php'; ?>

<div class="container mt-5">
    <h3>Квитки</h3>

    <!-- Кнопка для створення нового квитка -->
    <div class="mb-3">
        <a href="addTicket.php" class="btn btn-success">Створити новий квиток</a>
    </div>

    <!-- Таблиця з квитками -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID квитка</th>
                <th>Дата покупки</th>
                <th>Користувач</th>
                <th>Тип користувача</th>
                <th>Назва виставки</th>
                <th>Дата початку</th>
                <th>Дата завершення</th>
                <th>Опис</th>
                <th>Зал</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ticket_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['purchase_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['hall_name']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">Нічого не знайдено</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../footer.php'; ?>
</body>
</html>