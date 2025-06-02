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
    <title>Зали музею - Музей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/museum-theme.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        function confirmDelete(name, id) {
            if (confirm(`Ви впевнені, що хочете видалити зал "${name}"?`)) {
                document.getElementById('delete-form').delete_id.value = id;
                document.getElementById('delete-form').submit();
            }
        }
    </script>
</head>
<body class="museum-bg">
<?php include '../header.php'; ?>
<?php
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
?>

<div class="museum-content">
    <div class="container mt-5">
        <div class="museum-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <i class="fas fa-building museum-icon me-3" style="font-size: 2rem; color: var(--museum-accent);"></i>
                    <h3 class="museum-title mb-0">Зали музею</h3>
                </div>

                <?php if ($role === 'admin'): ?>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <a href="addHall.php" class="btn museum-btn-primary">
                            <i class="fas fa-plus me-2"></i>Додати новий зал
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Форма пошуку та фільтрації -->
                <div class="museum-card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-search me-2"></i>Пошук та фільтрація залів
                        </h5>
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <input type="text" name="search" class="form-control museum-input" 
                                       placeholder="Пошук за назвою або описом" 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="floor" class="form-select museum-input">
                                    <option value="">Всі поверхи</option>
                                    <option value="1" <?php echo $floor_filter === '1' ? 'selected' : ''; ?>>1 поверх</option>
                                    <option value="2" <?php echo $floor_filter === '2' ? 'selected' : ''; ?>>2 поверх</option>
                                    <option value="3" <?php echo $floor_filter === '3' ? 'selected' : ''; ?>>3 поверх</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn museum-btn-secondary w-100">
                                    <i class="fas fa-search me-2"></i>Застосувати
                                </button>
                            </div>
                        </form>
                    </div>
                </div>                <!-- Таблиця з даними -->
                <div class="table-responsive">
                    <table class="table museum-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-2"></i>ID</th>
                                <th><i class="fas fa-building me-2"></i>Назва</th>
                                <th><i class="fas fa-layer-group me-2"></i>Поверх</th>
                                <th><i class="fas fa-align-left me-2"></i>Опис</th>
                                <?php if ($role === 'admin'): ?>
                                    <th><i class="fas fa-cogs me-2"></i>Дії</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                                        <td>
                                            <strong class="text-museum-primary">
                                                <i class="fas fa-door-open me-2"></i>
                                                <?php echo htmlspecialchars($row['name']); ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="badge museum-badge">
                                                <i class="fas fa-arrow-up me-1"></i>
                                                <?php echo htmlspecialchars($row['floor']); ?> поверх
                                            </span>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($row['description']); ?>">
                                                <?php echo htmlspecialchars($row['description']); ?>
                                            </div>
                                        </td>
                                        <?php if ($role === 'admin'): ?>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <form method="POST" action="editHall.php" style="display: inline;">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" class="btn btn-sm museum-btn-secondary" title="Редагувати">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </form>
                                                    <button class="btn btn-sm museum-btn-danger" 
                                                            onclick="confirmDelete('<?php echo htmlspecialchars($row['name']); ?>', <?php echo $row['id']; ?>)"
                                                            title="Видалити">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?php echo $role === 'admin' ? 5 : 4; ?>" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-building fa-3x mb-3"></i>
                                            <h5>Зали не знайдено</h5>
                                            <p>Спробуйте змінити параметри пошуку або фільтри</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="delete-form" method="POST" style="display: none;">
    <input type="hidden" name="delete_id" value="">
</form>

<?php include '../footer.php'; ?>
</body>
</html>