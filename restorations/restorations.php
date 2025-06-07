<?php
include '../connectionString.php';
include '../log_functions.php';

// Створюємо таблицю логів, якщо вона не існує
createLogsTableIfNotExists($conn);

$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id']) && $role === 'admin') {
    $delete_id = $_POST['delete_id'];

    // Отримуємо інформацію про реставрацію перед видаленням для логування
    $info_stmt = $conn->prepare("SELECT exhibit_id, restoration_date, employee_id, description FROM restorations WHERE id = ?");
    $info_stmt->bind_param("i", $delete_id);
    $info_stmt->execute();
    $info_result = $info_stmt->get_result();
    $restoration_info = $info_result->fetch_assoc();
    $info_stmt->close();

    $delete_stmt = $conn->prepare("DELETE FROM restorations WHERE id = ?");
    $delete_stmt->bind_param("i", $delete_id);
    if ($delete_stmt->execute()) {
        // Логування видалення
        if ($restoration_info) {
            $action_details = "Видалено реставрацію\nЕкспонат ID: {$restoration_info['exhibit_id']}\nДата реставрації: {$restoration_info['restoration_date']}";
            if (!empty($restoration_info['employee_id'])) {
                $action_details .= "\nСпівробітник ID: {$restoration_info['employee_id']}";
            }
            if (!empty($restoration_info['description'])) {
                $action_details .= "\nОпис: {$restoration_info['description']}";
            }
            logActivity($conn, 'DELETE', 'restorations', $delete_id, $action_details);
        }
        
        echo "<script>alert('Реставрацію успішно видалено!'); window.location.href = 'restorations.php';</script>";
    } else {
        echo "<script>alert('Помилка при видаленні реставрації.');</script>";
    }
    $delete_stmt->close();
}

$employee_id_filter = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : '';
$description_filter = isset($_GET['description']) ? trim($_GET['description']) : '';
$order = isset($_GET['order']) && in_array($_GET['order'], ['asc', 'desc']) ? $_GET['order'] : 'asc'; // Порядок сортування

$sql = "SELECT * FROM restorations WHERE 1=1";
$params = [];
$types = "";

if (!empty($employee_id_filter)) {
    $sql .= " AND employee_id = ?";
    $params[] = $employee_id_filter;
    $types .= "i";
}

if (!empty($description_filter)) {
    $sql .= " AND description LIKE ?";
    $params[] = "%" . $description_filter . "%";
    $types .= "s";
}

$sql .= " ORDER BY restoration_date $order";

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
    <title>Реставрації - Музей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/museum-theme.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        function confirmDelete(id) {
            if (confirm(`Ви впевнені, що хочете видалити запис реставрації з ID ${id}?`)) {
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
                    <i class="fas fa-tools museum-icon me-3" style="font-size: 2rem; color: var(--museum-accent);"></i>
                    <h3 class="museum-title mb-0">Реставрації</h3>
                </div>

                <?php if ($role === 'admin'): ?>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <a href="addRestoration.php" class="btn museum-btn-primary">
                            <i class="fas fa-plus me-2"></i>Додати нову реставрацію
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <div class="museum-card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-search me-2"></i>Пошук реставрацій
                        </h5>
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="number" name="employee_id" class="form-control museum-input" 
                                       placeholder="Пошук за ID працівника" 
                                       value="<?php echo htmlspecialchars($employee_id_filter); ?>">
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="description" class="form-control museum-input" 
                                       placeholder="Пошук за описом" 
                                       value="<?php echo htmlspecialchars($description_filter); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn museum-btn-secondary w-100">
                                    <i class="fas fa-search me-2"></i>Знайти
                                </button>
                            </div>
                        </form>
                    </div>
                </div>            
                <div class="table-responsive">
                    <table class="table museum-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-2"></i>ID</th>
                                <th><i class="fas fa-gem me-2"></i>ID експоната</th>
                                <th>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['order' => $order === 'asc' ? 'desc' : 'asc'])); ?>" 
                                       class="text-decoration-none text-dark">
                                        <i class="fas fa-calendar me-2"></i>Дата реставрації
                                        <?php if ($order === 'asc'): ?>
                                            <i class="fas fa-sort-up"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort-down"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th><i class="fas fa-user me-2"></i>ID працівника</th>
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
                                            <span >
                                                <i class="fas fa-gem me-1"></i>
                                                <?php echo htmlspecialchars($row['exhibit_id']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge-info">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo date('d.m.Y', strtotime($row['restoration_date'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($row['employee_id']): ?>
                                                <span class="museum-badge">
                                                    <i class="fas fa-user me-1"></i>
                                                    <?php echo htmlspecialchars($row['employee_id']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Не вказано</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 250px;" title="<?php echo htmlspecialchars($row['description']); ?>">
                                                <?php echo htmlspecialchars($row['description'] ?: 'Без опису'); ?>
                                            </div>
                                        </td>
                                        <?php if ($role === 'admin'): ?>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <form method="POST" action="editRestoration.php" style="display: inline;">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" class="btn btn-sm museum-btn-secondary" title="Редагувати">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </form>
                                                    <button class="btn btn-sm museum-btn-danger" 
                                                            onclick="confirmDelete(<?php echo $row['id']; ?>)"
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
                                    <td colspan="<?php echo $role === 'admin' ? 6 : 5; ?>" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-tools fa-3x mb-3"></i>
                                            <h5>Реставрації не знайдено</h5>
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