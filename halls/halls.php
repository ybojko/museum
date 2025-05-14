<?php
include '../connectionString.php'; // Підключення до бази даних

// Отримання параметрів пошуку та фільтрації
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$floor_filter = isset($_GET['floor']) ? trim($_GET['floor']) : '';

// Формування SQL-запиту
$sql = "SELECT * FROM halls WHERE 1=1";
$params = [];
$types = "";

// Додавання пошуку
if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

// Додавання фільтрації за поверхами
if (!empty($floor_filter)) {
    $sql .= " AND floor = ?";
    $params[] = $floor_filter;
    $types .= "s";
}

// Підготовка запиту
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Видалення запису
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    $delete_stmt = $conn->prepare("DELETE FROM halls WHERE id = ?");
    $delete_stmt->bind_param("i", $delete_id);
    if ($delete_stmt->execute()) {
        echo "<script>alert('Запис успішно видалено!'); window.location.href = 'halls.php';</script>";
    } else {
        echo "<script>alert('Помилка при видаленні запису.');</script>";
    }
    $delete_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Зали музею</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function confirmDelete(name, id) {
            if (confirm(`Ви впевнені, що хочете видалити зал "${name}"?`)) {
                document.getElementById('delete-form').delete_id.value = id;
                document.getElementById('delete-form').submit();
            }
        }
    </script>
</head>
<body>
<?php include '../header.php'; ?>
<?php
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
?>

<div class="container mt-5">
    <h3>Зали музею</h3>

    <?php if ($role === 'admin'): ?>
        <a href="addHall.php" class="btn btn-success mb-3">Додати новий зал</a>
    <?php endif; ?>

    <!-- Форма пошуку та фільтрації -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Пошук за назвою або описом" value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-3">
            <select name="floor" class="form-select">
                <option value="">Всі поверхи</option>
                <option value="1" <?php echo $floor_filter === '1' ? 'selected' : ''; ?>>1 поверх</option>
                <option value="2" <?php echo $floor_filter === '2' ? 'selected' : ''; ?>>2 поверх</option>
                <option value="3" <?php echo $floor_filter === '3' ? 'selected' : ''; ?>>3 поверх</option>
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
                <th>Поверх</th>
                <th>Опис</th>
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
                        <td><?php echo htmlspecialchars($row['floor']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <?php if ($role === 'admin'): ?>
                            <td>
                                <form method="POST" action="editHall.php" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-warning btn-sm">Редагувати</button>
                                </form>
                                <button class="btn btn-danger btn-sm" onclick="confirmDelete('<?php echo htmlspecialchars($row['name']); ?>', <?php echo $row['id']; ?>)">Видалити</button>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?php echo $role === 'admin' ? 5 : 4; ?>" class="text-center">Нічого не знайдено</td>
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