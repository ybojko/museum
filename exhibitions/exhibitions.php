<?php
include '../connectionString.php'; // Підключення до бази даних

// Отримання ролі користувача
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';

// Отримання параметрів пошуку
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Формування SQL-запиту
$sql = "SELECT * FROM exhibition_view WHERE 1=1";
$params = [];
$types = "";

// Додавання пошуку
if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR start_date LIKE ? OR end_date LIKE ? OR description LIKE ? OR hall_name LIKE ?)";
    $search_param = "%$search%";
    $params = array_fill(0, 5, $search_param);
    $types = str_repeat("s", 5);
}

// Підготовка запиту
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Виставки</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function confirmDelete(title, id) {
            if (confirm(`Ви впевнені, що хочете видалити виставку "${title}"?`)) {
                document.getElementById('delete-form').delete_id.value = id;
                document.getElementById('delete-form').submit();
            }
        }
    </script>
</head>
<body>
<?php include '../header.php'; ?>

<div class="container mt-5">
    <h3>Виставки</h3>

    <?php if ($role === 'admin'): ?>
        <a href="addExhibition.php" class="btn btn-success mb-3">Додати нову виставку</a>
    <?php endif; ?>

    <!-- Форма пошуку -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-9">
            <input type="text" name="search" class="form-control" placeholder="Пошук за всіма полями" value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Знайти</button>
        </div>
    </form>

    <!-- Таблиця з даними -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Назва</th>
                <th>Дата початку</th>
                <th>Дата завершення</th>
                <th>Опис</th>
                <th>Зал</th>
                <?php if ($role === 'admin'): ?>
                    <th>Дії</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['hall_name']); ?></td>
                        <?php if ($role === 'admin'): ?>
                            <td>
                                <a href="editExhibition.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Редагувати</a>
                                <button class="btn btn-danger btn-sm" onclick="confirmDelete('<?php echo htmlspecialchars($row['title']); ?>', <?php echo $row['id']; ?>)">Видалити</button>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?php echo $role === 'admin' ? 7 : 6; ?>" class="text-center">Нічого не знайдено</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<form id="delete-form" method="POST" style="display: none;">
    <input type="hidden" name="delete_id" value="">
</form>

<?php include '../footer.php'; ?>
</body>
</html>