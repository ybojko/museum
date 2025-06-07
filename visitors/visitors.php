<?php
include '../connectionString.php';
include '../log_functions.php';

createLogsTableIfNotExists($conn);

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'visitor_manager')) {
    header('Location: ../index.php');
    exit;
}

$role = $_SESSION['role'];

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$visitor_type_filter = isset($_GET['visitor_type']) && in_array($_GET['visitor_type'], ['default', 'benefitial']) ? $_GET['visitor_type'] : '';

$sql_visitors = "SELECT * FROM visitors WHERE 1=1";
$params_visitors = [];
$types_visitors = "";

if (!empty($search)) {
    $sql_visitors .= " AND (last_name LIKE ? OR first_name LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params_visitors = array_fill(0, 3, $search_param);
    $types_visitors .= str_repeat("s", 3);
}

if (!empty($visitor_type_filter)) {
    $sql_visitors .= " AND visitor_type = ?";
    $params_visitors[] = $visitor_type_filter;
    $types_visitors .= "s";
}

$stmt_visitors = $conn->prepare($sql_visitors);
if (!empty($params_visitors)) {
    $stmt_visitors->bind_param($types_visitors, ...$params_visitors);
}
$stmt_visitors->execute();
$result_visitors = $stmt_visitors->get_result();

$sql_users = "SELECT id, username, email, created_at, user_type FROM users WHERE role = 'user'";
$params_users = [];
$types_users = "";

if (!empty($search)) {
    $sql_users .= " AND (username LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params_users = array_fill(0, 2, $search_param);
    $types_users .= str_repeat("s", 2);
}

if (!empty($visitor_type_filter)) {
    $sql_users .= " AND user_type = ?";
    $params_users[] = $visitor_type_filter;
    $types_users .= "s";
}

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
    <title>Відвідувачі - Музей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/museum-theme.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="museum-bg">
<?php include '../header.php'; ?>

<div class="museum-content">
    <div class="container mt-5">
        <div class="museum-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <i class="fas fa-users museum-icon me-3" style="font-size: 2rem; color: var(--museum-accent);"></i>
                    <h3 class="museum-title mb-0">Відвідувачі музею</h3>
                </div>

                <?php if ($role === 'admin'): ?>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <a href="addVisitor.php" class="btn museum-btn-primary">
                            <i class="fas fa-plus me-2"></i>Додати нового відвідувача
                        </a>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="../export_excel.php?table=visitors" class="btn museum-btn-secondary">
                            <i class="fas fa-file-excel me-2"></i>Експорт в Excel
                        </a>
                    </div>
                </div>
                <?php elseif ($role === 'visitor_manager'): ?>
                <div class="row mb-4">
                    <div class="col-md-12 text-end">
                        <a href="../export_excel.php?table=visitors" class="btn museum-btn-secondary">
                            <i class="fas fa-file-excel me-2"></i>Експорт в Excel
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <div class="museum-card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-search me-2"></i>Пошук відвідувачів
                        </h5>
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <input type="text" name="search" class="form-control museum-input" 
                                       placeholder="Пошук за прізвищем, ім'ям або email" 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="visitor_type" class="form-select museum-input">
                                    <option value="">Усі типи</option>
                                    <option value="default" <?php echo $visitor_type_filter === 'default' ? 'selected' : ''; ?>>Default</option>
                                    <option value="benefitial" <?php echo $visitor_type_filter === 'benefitial' ? 'selected' : ''; ?>>Benefitial</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn museum-btn-secondary w-100">
                                    <i class="fas fa-search me-2"></i>Знайти
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="museum-card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-store me-2"></i>Офлайн-відвідувачі
                        </h5>
                        <div class="table-responsive">
                            <table class="table museum-table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag me-2"></i>ID</th>
                                        <th><i class="fas fa-user me-2"></i>Прізвище</th>
                                        <th><i class="fas fa-user me-2"></i>Ім'я</th>
                                        <th><i class="fas fa-envelope me-2"></i>Email</th>
                                        <th><i class="fas fa-tag me-2"></i>Тип відвідувача</th>
                                        <th><i class="fas fa-cogs me-2"></i>Дії</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result_visitors->num_rows > 0): ?>
                                        <?php while ($row = $result_visitors->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                                <td>
                                                    <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($row['email']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge museum-badge">
                                                        <?php echo htmlspecialchars($row['visitor_type']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($role === 'admin'): ?>
                                                    <form method="POST" action="editVisitor.php" style="display: inline;">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" class="btn btn-sm museum-btn-secondary" title="Редагувати">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </form>
                                                    <?php else: ?>
                                                    <span class="text-muted">Тільки перегляд</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
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

                <div class="museum-card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-globe me-2"></i>Онлайн-відвідувачі
                        </h5>
                        <div class="table-responsive">
                            <table class="table museum-table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag me-2"></i>ID</th>
                                        <th><i class="fas fa-user me-2"></i>Ім'я користувача</th>
                                        <th><i class="fas fa-envelope me-2"></i>Email</th>
                                        <th><i class="fas fa-calendar me-2"></i>Дата створення</th>
                                        <th><i class="fas fa-tag me-2"></i>Тип акаунта</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result_users->num_rows > 0): ?>
                                        <?php while ($row = $result_users->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                <td>
                                                    <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($row['email']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                                <td>
                                                    <span class="badge museum-badge">
                                                        <?php echo htmlspecialchars($row['user_type']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
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
    </div>
</div>

<?php include '../footer.php'; ?>
</body>
</html>