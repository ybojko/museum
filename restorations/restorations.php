<?php
include '../connectionString.php'; // Підключення до бази даних

// Отримання ролі користувача
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';

// Отримання параметрів пошуку
$employee_id_filter = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : '';
$description_filter = isset($_GET['description']) ? trim($_GET['description']) : '';
$order = isset($_GET['order']) && in_array($_GET['order'], ['asc', 'desc']) ? $_GET['order'] : 'asc'; // Порядок сортування

// Формування SQL-запиту
$sql = "SELECT * FROM restorations WHERE 1=1";
$params = [];
$types = "";

// Додавання пошуку за employee_id
if (!empty($employee_id_filter)) {
    $sql .= " AND employee_id = ?";
    $params[] = $employee_id_filter;
    $types .= "i";
}

// Додавання пошуку за description
if (!empty($description_filter)) {
    $sql .= " AND description LIKE ?";
    $params[] = "%" . $description_filter . "%";
    $types .= "s";
}

// Додавання сортування
$sql .= " ORDER BY restoration_date $order";

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
    <title>Реставрації</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function confirmDelete(id) {
            if (confirm(`Ви впевнені, що хочете видалити запис реставрації з ID ${id}?`)) {
                document.getElementById('delete-form').delete_id.value = id;
                document.getElementById('delete-form').submit();
            }
        }
    </script>
</head>
<body>
<?php include '../header.php'; ?>

<div class="container mt-5">
    <h3>Реставрації</h3>

    <?php if ($role === 'admin'): ?>
        <a href="addRestoration.php" class="btn btn-success mb-3">Додати нову реставрацію</a>
    <?php endif; ?>

    <!-- Форма пошуку -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="number" name="employee_id" class="form-control" placeholder="Пошук за ID працівника" value="<?php echo htmlspecialchars($employee_id_filter); ?>">
        </div>
        <div class="col-md-6">
            <input type="text" name="description" class="form-control" placeholder="Пошук за описом" value="<?php echo htmlspecialchars($description_filter); ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Знайти</button>
        </div>
    </form>

    <!-- Таблиця з даними -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>ID експоната</th>
                <th>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['order' => $order === 'asc' ? 'desc' : 'asc'])); ?>">
                        Дата реставрації
                        <?php if ($order === 'asc'): ?>
                            ↑
                        <?php else: ?>
                            ↓
                        <?php endif; ?>
                    </a>
                </th>
                <th>ID працівника</th>
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
                        <td><?php echo htmlspecialchars($row['exhibit_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['restoration_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['employee_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <?php if ($role === 'admin'): ?>
                            <td>
                                <form method="POST" action="editRestoration.php" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-warning btn-sm">Редагувати</button>
                                </form>
                                <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $row['id']; ?>)">Видалити</button>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?php echo $role === 'admin' ? 6 : 5; ?>" class="text-center">Нічого не знайдено</td>
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