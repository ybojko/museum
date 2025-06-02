<?php
include '../connectionString.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /museum/index.php');
    exit;
}


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
    <title>Робітники - Музей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/museum-theme.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
      <script>
        function confirmDelete(name, id) {
            if (confirm(`Ви впевнені, що хочете видалити робітника "${name}"?`)) {
                document.getElementById('delete-form').delete_id.value = id;
                document.getElementById('delete-form').submit();
            }
        }
    </script>
</head>
<body class="museum-bg">
<?php include '../header.php'; ?>

<div class="museum-content">
    <div class="container mt-5">
        <div class="museum-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <i class="fas fa-users museum-icon me-3" style="font-size: 2rem; color: var(--museum-accent);"></i>
                    <h3 class="museum-title mb-0">Робітники музею</h3>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <a href="addEmployee.php" class="btn museum-btn-primary">
                            <i class="fas fa-plus me-2"></i>Додати нового робітника
                        </a>
                    </div>
                </div>

                <!-- Форма пошуку -->
                <div class="museum-card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-search me-2"></i>Пошук робітників
                        </h5>
                        <form method="GET" class="row g-3">
                            <div class="col-md-9">
                                <input type="text" name="search" class="form-control museum-input" 
                                       placeholder="Пошук за прізвищем, ім'ям, посадою, email або телефоном" 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn museum-btn-secondary w-100">
                                    <i class="fas fa-search me-2"></i>Знайти
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
                                <th><i class="fas fa-user me-2"></i>Прізвище</th>
                                <th><i class="fas fa-user me-2"></i>Ім'я</th>
                                <th><i class="fas fa-briefcase me-2"></i>Посада</th>
                                <th><i class="fas fa-money-bill me-2"></i>Зарплата</th>
                                <th><i class="fas fa-calendar me-2"></i>Дата найму</th>
                                <th><i class="fas fa-envelope me-2"></i>Email</th>
                                <th><i class="fas fa-phone me-2"></i>Телефон</th>
                                <th><i class="fas fa-building me-2"></i>Зал</th>
                                <th><i class="fas fa-image me-2"></i>Фото</th>
                                <th><i class="fas fa-cogs me-2"></i>Дії</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                        <td>
                                            <span class="badge museum-badge">
                                                <?php echo htmlspecialchars($row['position']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-success fw-bold">
                                                <?php echo number_format($row['salary'], 0, ',', ' '); ?> грн
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['hire_date']); ?></td>
                                        <td>
                                            <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($row['email']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="tel:<?php echo htmlspecialchars($row['phone']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($row['phone']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['hall_id']); ?></td>
                                        <td>
                                            <?php if (!empty($row['photo'])): ?>
                                                <img src="/museum/employees_uploads/<?php echo htmlspecialchars($row['photo']); ?>" 
                                                     alt="Фото" class="rounded-circle" width="50" height="50" style="object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fas fa-user text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <form method="POST" action="editEmployee.php" style="display: inline;">
                                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" class="btn btn-sm museum-btn-secondary" title="Редагувати">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </form>
                                                <button class="btn btn-sm museum-btn-danger" 
                                                        onclick="confirmDelete('<?php echo htmlspecialchars($row['last_name'] . ' ' . $row['first_name']); ?>', <?php echo $row['id']; ?>)"
                                                        title="Видалити">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-search fa-3x mb-3"></i>
                                            <h5>Нічого не знайдено</h5>
                                            <p>Спробуйте змінити параметри пошуку</p>
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