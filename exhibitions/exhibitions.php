<?php
include '../connectionString.php';
include '../log_functions.php';

// Створюємо таблицю логів, якщо вона не існує
createLogsTableIfNotExists($conn);

$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
$can_manage_content = ($role === 'admin' || $role === 'content_manager');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id']) && $role === 'admin') {
    $delete_id = $_POST['delete_id'];

    // Отримуємо інформацію про виставку перед видаленням для логування
    $info_stmt = $conn->prepare("SELECT title, start_date, end_date FROM exhibitions WHERE id = ?");
    $info_stmt->bind_param("i", $delete_id);
    $info_stmt->execute();
    $info_result = $info_stmt->get_result();
    $exhibition_info = $info_result->fetch_assoc();
    $info_stmt->close();

    $delete_stmt = $conn->prepare("DELETE FROM exhibitions WHERE id = ?");
    $delete_stmt->bind_param("i", $delete_id);
    if ($delete_stmt->execute()) {
        // Логування видалення
        if ($exhibition_info) {
            $action_details = "Видалено виставку: {$exhibition_info['title']}\nПочаток: {$exhibition_info['start_date']}\nЗавершення: {$exhibition_info['end_date']}";
            logActivity($conn, 'DELETE', 'exhibitions', $delete_id, $action_details);
        }
        
        echo "<script>alert('Виставку успішно видалено!'); window.location.href = 'exhibitions.php';</script>";
    } else {
        echo "<script>alert('Помилка при видаленні виставки.');</script>";
    }
    $delete_stmt->close();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT * FROM exhibition_view WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR start_date LIKE ? OR end_date LIKE ? OR description LIKE ? OR hall_name LIKE ?)";
    $search_param = "%$search%";
    $params = array_fill(0, 5, $search_param);
    $types = str_repeat("s", 5);
}

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
    <title>Виставки - Музей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/museum-theme.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        function confirmDelete(title, id) {
            if (confirm(`Ви впевнені, що хочете видалити виставку "${title}"?`)) {
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
                    <i class="fas fa-palette museum-icon me-3" style="font-size: 2rem; color: var(--museum-accent);"></i>
                    <h3 class="museum-title mb-0">Виставки музею</h3>
                </div>

                <?php if ($can_manage_content): ?>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <a href="addExhibition.php" class="btn museum-btn-primary">
                            <i class="fas fa-plus me-2"></i>Додати нову виставку
                        </a>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="../export_excel.php?table=exhibitions" class="btn museum-btn-secondary">
                            <i class="fas fa-file-excel me-2"></i>Експорт в Excel
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <div class="museum-card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-search me-2"></i>Пошук виставок
                        </h5>
                        <form method="GET" class="row g-3">
                            <div class="col-md-9">
                                <input type="text" name="search" class="form-control museum-input" 
                                       placeholder="Пошук за назвою, датами, описом або залом" 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
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
                                <th><i class="fas fa-palette me-2"></i>Назва</th>
                                <th><i class="fas fa-calendar-start me-2"></i>Дата початку</th>
                                <th><i class="fas fa-calendar-times me-2"></i>Дата завершення</th>
                                <th><i class="fas fa-align-left me-2"></i>Опис</th>
                                <th><i class="fas fa-building me-2"></i>Зал</th>
                                <?php if ($can_manage_content): ?>
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
                                                <?php echo htmlspecialchars($row['title']); ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="badge-success">
                                                <i class="fas fa-play me-1"></i>
                                                <?php echo date('d.m.Y', strtotime($row['start_date'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge-danger">
                                                <i class="fas fa-stop me-1"></i>
                                                <?php echo date('d.m.Y', strtotime($row['end_date'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($row['description']); ?>">
                                                <?php echo htmlspecialchars($row['description']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="museum-badge">
                                                <i class="fas fa-door-open me-1"></i>
                                                <?php echo htmlspecialchars($row['hall_name']); ?>
                                            </span>
                                        </td>
                                        <?php if ($can_manage_content): ?>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <form method="POST" action="editExhibition.php" style="display: inline;">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" class="btn btn-sm museum-btn-secondary" title="Редагувати">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </form>                                
                                                    <?php if ($role === 'admin'): ?>
                                                    <button class="btn btn-sm museum-btn-danger" 
                                                            onclick="confirmDelete('<?php echo htmlspecialchars($row['title']); ?>', <?php echo $row['id']; ?>)"
                                                            title="Видалити">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?php echo $can_manage_content ? 7 : 6; ?>" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-palette fa-3x mb-3"></i>
                                            <h5>Виставки не знайдено</h5>
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