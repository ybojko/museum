<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /museum/index.php');
    exit;
}

include '../connectionString.php';

// Отримання параметрів пошуку
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Формування SQL-запиту
$sql = "SELECT * FROM employees WHERE 1=1";
$params = [];
$types = "";

// Додавання пошуку
if (!empty($search)) {
    $sql .= " AND (last_name LIKE ? OR salary LIKE ? OR first_name LIKE ? OR position LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search_param = "%$search%";
    $params = array_fill(0, 6, $search_param);
    $types = str_repeat("s", 6);
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

    $delete_stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
    $delete_stmt->bind_param("i", $delete_id);
    if ($delete_stmt->execute()) {
        echo "<script>alert('Запис успішно видалено!'); window.location.href = 'employees.php';</script>";
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
    <title>Робітники</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function confirmDelete(name, id) {
            if (confirm(`Ви впевнені, що хочете видалити робітника "${name}"?`)) {
                document.getElementById('delete-form').delete_id.value = id;
                document.getElementById('delete-form').submit();
            }
        }
    </script>
</head>
<body>
<?php include '../header.php'; ?>

<div class="container mt-5">
    <h3>Робітники</h3>

    <a href="addEmployee.php" class="btn btn-success mb-3">Додати нового робітника</a>

    <!-- Форма пошуку -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-9">
            <input type="text" name="search" class="form-control" placeholder="Пошук за полями" value="<?php echo htmlspecialchars($search); ?>">
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
                <th>Прізвище</th>
                <th>Ім'я</th>
                <th>Посада</th>
                <th>Зарплата</th>
                <th>Дата найму</th>
                <th>Email</th>
                <th>Телефон</th>
                <th>Зал</th>
                <th>Фото</th>
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['position']); ?></td>
                        <td><?php echo htmlspecialchars($row['salary']); ?></td>
                        <td><?php echo htmlspecialchars($row['hire_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['hall_id']); ?></td>
                        <td>
                            <?php if (!empty($row['photo'])): ?>
                                <img src="/museum/uploads/<?php echo htmlspecialchars($row['photo']); ?>" alt="Фото" width="50">
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="editEmployee.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Редагувати</a>
                            <button class="btn btn-danger btn-sm" onclick="confirmDelete('<?php echo htmlspecialchars($row['last_name'] . ' ' . $row['first_name']); ?>', <?php echo $row['id']; ?>)">Видалити</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="text-center">Нічого не знайдено</td>
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