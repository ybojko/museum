<?php
include '../connectionString.php'; // Підключення до бази даних

// Отримання ролі користувача
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';

// Отримання параметрів пошуку та фільтрації
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$condition_filter = isset($_GET['condition_status']) ? trim($_GET['condition_status']) : '';

// Формування SQL-запиту
$sql = "SELECT * FROM exhibit_view WHERE 1=1";
$params = [];
$types = "";

// Додавання пошуку
if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR description LIKE ? OR year_created LIKE ? OR hall_name LIKE ? OR last_restoration LIKE ?)";
    $search_param = "%$search%";
    $params = array_fill(0, 5, $search_param);
    $types = str_repeat("s", 5);
}

// Додавання фільтрації за станом
if (!empty($condition_filter)) {
    $sql .= " AND condition_status = ?";
    $params[] = $condition_filter;
    $types .= "s";
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
    <title>Експонати</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function confirmDelete(name, id) {
            if (confirm(`Ви впевнені, що хочете видалити експонат "${name}"?`)) {
                document.getElementById('delete-form').delete_id.value = id;
                document.getElementById('delete-form').submit();
            }
        }
    </script>
</head>
<body>
<?php include '../header.php'; ?>

<div class="container mt-5">
    <h3>Експонати</h3>

    <?php if ($role === 'admin'): ?>
        <a href="addExhibit.php" class="btn btn-success mb-3">Додати новий експонат</a>
    <?php endif; ?>

    <!-- Форма пошуку та фільтрації -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Пошук за всіма полями, окрім стану та фото" value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-3">
            <select name="condition_status" class="form-select">
                <option value="">Всі стани</option>
                <option value="good" <?php echo $condition_filter === 'good' ? 'selected' : ''; ?>>Good</option>
                <option value="medium" <?php echo $condition_filter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                <option value="bad" <?php echo $condition_filter === 'bad' ? 'selected' : ''; ?>>Bad</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Застосувати</button>
        </div>
    </form>

    <!-- Таблиця з даними -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Назва</th>
                <th>Опис</th>
                <th>Рік створення</th>
                <th>Зал</th>
                <th>Дата останньої реставрації</th>
                <th>Стан</th>
                <th>Фото</th>
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
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['year_created']); ?></td>
                        <td><?php echo htmlspecialchars($row['hall_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['last_restoration']); ?></td>
                        <td><?php echo htmlspecialchars($row['condition_status']); ?></td>
                        <td>
                            <?php if (!empty($row['photo'])): ?>
                                <img src="/museum/exhibits_uploads/<?php echo htmlspecialchars($row['photo']); ?>" alt="Фото" width="50">
                            <?php endif; ?>
                        </td>
                        <?php if ($role === 'admin'): ?>
                            <td>
                                <a href="editExhibit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Редагувати</a>
                                <button class="btn btn-danger btn-sm" onclick="confirmDelete('<?php echo htmlspecialchars($row['name']); ?>', <?php echo $row['id']; ?>)">Видалити</button>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?php echo $role === 'admin' ? 9 : 8; ?>" class="text-center">Нічого не знайдено</td>
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