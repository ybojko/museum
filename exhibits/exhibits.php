<?php
include '../connectionString.php'; // Підключення до бази даних

// Отримання ролі користувача
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';

// Видалення запису (тільки для адміна)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id']) && $role === 'admin') {
    $delete_id = $_POST['delete_id'];

    $delete_stmt = $conn->prepare("DELETE FROM exhibits WHERE id = ?");
    $delete_stmt->bind_param("i", $delete_id);
    if ($delete_stmt->execute()) {
        echo "<script>alert('Експонат успішно видалено!'); window.location.href = 'exhibits.php';</script>";
    } else {
        echo "<script>alert('Помилка при видаленні експоната.');</script>";
    }
    $delete_stmt->close();
}

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
    <title>Експонати - Музей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/museum-theme.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        function confirmDelete(name, id) {
            if (confirm(`Ви впевнені, що хочете видалити експонат "${name}"?`)) {
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
                    <i class="fas fa-gem museum-icon me-3" style="font-size: 2rem; color: var(--museum-accent);"></i>
                    <h3 class="museum-title mb-0">Експонати музею</h3>
                </div>

                <?php if ($role === 'admin'): ?>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <a href="addExhibit.php" class="btn museum-btn-primary">
                            <i class="fas fa-plus me-2"></i>Додати новий експонат
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Форма пошуку та фільтрації -->
                <div class="museum-card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-search me-2"></i>Пошук та фільтрація експонатів
                        </h5>
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <input type="text" name="search" class="form-control museum-input" 
                                       placeholder="Пошук за назвою, описом, роком або залом" 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="condition_status" class="form-select museum-input">
                                    <option value="">Всі стани</option>
                                    <option value="good" <?php echo $condition_filter === 'good' ? 'selected' : ''; ?>>Відмінний</option>
                                    <option value="medium" <?php echo $condition_filter === 'medium' ? 'selected' : ''; ?>>Середній</option>
                                    <option value="bad" <?php echo $condition_filter === 'bad' ? 'selected' : ''; ?>>Потребує реставрації</option>
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
                                <th><i class="fas fa-gem me-2"></i>Назва</th>
                                <th><i class="fas fa-align-left me-2"></i>Опис</th>
                                <th><i class="fas fa-calendar me-2"></i>Рік створення</th>
                                <th><i class="fas fa-building me-2"></i>Зал</th>
                                <th><i class="fas fa-tools me-2"></i>Остання реставрація</th>
                                <th><i class="fas fa-heart me-2"></i>Стан</th>
                                <th><i class="fas fa-image me-2"></i>Фото</th>
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
                                                <?php echo htmlspecialchars($row['name']); ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($row['description']); ?>">
                                                <?php echo htmlspecialchars($row['description']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge museum-badge">
                                                <?php echo htmlspecialchars($row['year_created']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge museum-badge">
                                                <i class="fas fa-door-open me-1"></i>
                                                <?php echo htmlspecialchars($row['hall_name']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($row['last_restoration']): ?>
                                                <span class="badge badge-info">
                                                    <?php echo date('d.m.Y', strtotime($row['last_restoration'])); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Не реставрувався</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $condition = $row['condition_status'];
                                            $badge_class = '';
                                            $icon = '';
                                            switch($condition) {
                                                case 'good':
                                                    $badge_class = 'badge-success';
                                                    $icon = 'fas fa-check-circle';
                                                    $text = 'Відмінний';
                                                    break;
                                                case 'medium':
                                                    $badge_class = 'badge-warning';
                                                    $icon = 'fas fa-exclamation-triangle';
                                                    $text = 'Середній';
                                                    break;
                                                case 'bad':
                                                    $badge_class = 'badge-danger';
                                                    $icon = 'fas fa-times-circle';
                                                    $text = 'Потребує реставрації';
                                                    break;
                                                default:
                                                    $badge_class = 'badge-secondary';
                                                    $icon = 'fas fa-question';
                                                    $text = $condition;
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>">
                                                <i class="<?php echo $icon; ?> me-1"></i><?php echo $text; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['photo'])): ?>
                                                <img src="/museum/exhibits_uploads/<?php echo htmlspecialchars($row['photo']); ?>" 
                                                     alt="Фото" class="rounded" width="50" height="50" style="object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($role === 'admin'): ?>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <form method="POST" action="editExhibit.php" style="display: inline;">
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
                                    <td colspan="<?php echo $role === 'admin' ? 9 : 8; ?>" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-gem fa-3x mb-3"></i>
                                            <h5>Експонати не знайдено</h5>
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