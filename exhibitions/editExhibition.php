<?php
include '../connectionString.php';
include '../log_functions.php';

createLogsTableIfNotExists($conn);

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'content_manager') {
    header('Location: exhibitions.php');
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);

if ($id === 0 || !is_numeric($id)) {
    echo "<script>alert('Невірний запит. ID не передано.'); window.location.href = 'exhibitions.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = trim($_POST['title']);
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);
    $hall_id = trim($_POST['hall_id']);
    $description = trim($_POST['description']);

    if (empty($title) || empty($start_date) || empty($hall_id)) {
        echo "<script>alert('Будь ласка, заповніть усі обов'язкові поля.');</script>";
    } else {
        $stmt = $conn->prepare("UPDATE exhibitions SET title = ?, start_date = ?, end_date = ?, hall_id = ?, description = ? WHERE id = ?");
        $stmt->bind_param("sssdsd", $title, $start_date, $end_date, $hall_id, $description, $id);
        if ($stmt->execute()) {
            $action_details = "Оновлено виставку: $title (ID: $id)\nПочаток: $start_date\nЗавершення: $end_date\nЗал ID: $hall_id";
            if (!empty($description)) {
                $action_details .= "\nОпис: $description";
            }
            logActivity($conn, 'UPDATE', 'exhibitions', $id, $action_details);
            
            echo "<script>alert('Виставку успішно оновлено!'); window.location.href = 'exhibitions.php';</script>";
        } else {
            echo "<script>alert('Помилка при оновленні виставки.');</script>";
        }
        $stmt->close();
    }
} else {
    $stmt = $conn->prepare("SELECT * FROM exhibitions WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $exhibition = $result->fetch_assoc();
    $stmt->close();

    if (!$exhibition) {
        echo "<script>alert('Виставку не знайдено.'); window.location.href = 'exhibitions.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редагувати виставку</title>
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
                    <i class="fas fa-edit museum-icon me-3" style="font-size: 2rem; color: var(--museum-accent);"></i>
                    <h3 class="museum-title mb-0">Редагувати виставку</h3>
                </div>

                <div class="museum-card">
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label fw-bold">
                                            <i class="fas fa-palette me-2"></i>Назва
                                        </label>
                                        <input type="text" class="form-control museum-input" id="title" name="title" value="<?php echo htmlspecialchars($exhibition['title']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="hall_id" class="form-label fw-bold">
                                            <i class="fas fa-building me-2"></i>Зал
                                        </label>
                                        <input type="number" class="form-control museum-input" id="hall_id" name="hall_id" value="<?php echo htmlspecialchars($exhibition['hall_id']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="start_date" class="form-label fw-bold">
                                            <i class="fas fa-calendar-check me-2"></i>Дата початку
                                        </label>
                                        <input type="date" class="form-control museum-input" id="start_date" name="start_date" value="<?php echo htmlspecialchars($exhibition['start_date']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="end_date" class="form-label fw-bold">
                                            <i class="fas fa-calendar-times me-2"></i>Дата завершення (опціонально)
                                        </label>
                                        <input type="date" class="form-control museum-input" id="end_date" name="end_date" value="<?php echo htmlspecialchars($exhibition['end_date']); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label fw-bold">
                                    <i class="fas fa-align-left me-2"></i>Опис (опціонально)
                                </label>
                                <textarea class="form-control museum-input" id="description" name="description" rows="4"><?php echo htmlspecialchars($exhibition['description']); ?></textarea>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn museum-btn-secondary btn-lg">
                                    <i class="fas fa-save me-2"></i>Оновити
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
</body>
</html>