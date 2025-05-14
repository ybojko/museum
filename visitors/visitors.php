<?php
include '../connectionString.php'; // Підключення до бази даних

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Отримання параметрів пошуку та фільтрації
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$visitor_type_filter = isset($_GET['visitor_type']) && in_array($_GET['visitor_type'], ['default', 'benefitial']) ? $_GET['visitor_type'] : '';

// Формування SQL-запиту для таблиці visitors
$sql_visitors = "SELECT * FROM visitors WHERE 1=1";
$params_visitors = [];
$types_visitors = "";

// Додавання пошуку для visitors
if (!empty($search)) {
    $sql_visitors .= " AND (last_name LIKE ? OR first_name LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params_visitors = array_fill(0, 3, $search_param);
    $types_visitors .= str_repeat("s", 3);
}

// Додавання фільтрації за visitor_type
if (!empty($visitor_type_filter)) {
    $sql_visitors .= " AND visitor_type = ?";
    $params_visitors[] = $visitor_type_filter;
    $types_visitors .= "s";
}

// Підготовка запиту для visitors
$stmt_visitors = $conn->prepare($sql_visitors);
if (!empty($params_visitors)) {
    $stmt_visitors->bind_param($types_visitors, ...$params_visitors);
}
$stmt_visitors->execute();
$result_visitors = $stmt_visitors->get_result();

// Формування SQL-запиту для таблиці users
$sql_users = "SELECT id, username, email, created_at, user_type FROM users WHERE role = 'user'";
$params_users = [];
$types_users = "";

// Додавання пошуку для users
if (!empty($search)) {
    $sql_users .= " AND (username LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params_users = array_fill(0, 2, $search_param);
    $types_users .= str_repeat("s", 2);
}

// Додавання фільтрації за user_type
if (!empty($visitor_type_filter)) {
    $sql_users .= " AND user_type = ?";
    $params_users[] = $visitor_type_filter;
    $types_users .= "s";
}

// Підготовка запиту для users
$stmt_users = $conn->prepare($sql_users);
if (!empty($params_users)) {
    $stmt_users->bind_param($types_users, ...$params_users);
}
$stmt_users->execute();
$result_users = $stmt_users->get_result();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Відвідувачі</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../header.php'; ?>

<div class="container mt-5">
    <h3>Відвідувачі</h3>

    <!-- Форма пошуку та фільтрації -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Пошук за прізвищем, ім'ям або email" value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-4">
            <select name="visitor_type" class="form-select">
                <option value="">Усі типи</option>
                <option value="default" <?php echo $visitor_type_filter === 'default' ? 'selected' : ''; ?>>Default</option>
                <option value="benefitial" <?php echo $visitor_type_filter === 'benefitial' ? 'selected' : ''; ?>>Benefitial</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Знайти</button>
        </div>
    </form>
    <div class="mb-3">
        <a href="addVisitor.php" class="btn btn-success">Додати нового відвідувача</a>
    </div>
    <!-- Таблиця "Офлайн-відвідувачі" -->
    <h4>Офлайн-відвідувачі</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Прізвище</th>
                <th>Ім'я</th>
                <th>Email</th>
                <th>Тип відвідувача</th>
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_visitors->num_rows > 0): ?>
                <?php while ($row = $result_visitors->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['visitor_type']); ?></td>
                        <td>
                            <form method="POST" action="editVisitor.php" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-warning btn-sm">Редагувати</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">Нічого не знайдено</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Таблиця "Онлайн-відвідувачі" -->
    <h4>Онлайн-відвідувачі</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ім'я користувача</th>
                <th>Email</th>
                <th>Дата створення</th>
                <th>Тип акаунта</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_users->num_rows > 0): ?>
                <?php while ($row = $result_users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_type']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">Нічого не знайдено</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../footer.php'; ?>
</body>
</html>