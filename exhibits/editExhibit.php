<?php
include '../connectionString.php'; // Підключення до бази даних

if ($_SESSION['role'] !== 'admin') {
    header('Location: exhibits.php');
    exit;
}

// Отримання id через POST або GET
$id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);

if ($id === 0 || !is_numeric($id)) {
    echo "<script>alert('Невірний запит. ID не передано.'); window.location.href = 'exhibits.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $year_created = trim($_POST['year_created']);
    $condition_status = trim($_POST['condition_status']);
    $hall_id = trim($_POST['hall_id']);
    $last_restoration = trim($_POST['last_restoration']);
    $photo = $_FILES['photo']['name'];

    // Валідація
    $valid_conditions = ['good', 'medium', 'bad'];
    if (empty($name) || empty($description) || empty($year_created) || empty($condition_status) || !in_array($condition_status, $valid_conditions) || !is_numeric($hall_id)) {
        echo "<script>alert('Будь ласка, заповніть усі обов’язкові поля та виберіть коректний стан.');</script>";
    } else {
        // Завантаження фото
        if (!empty($photo)) {
            $target_dir = "../exhibits_uploads/";
            $target_file = $target_dir . basename($photo);
            move_uploaded_file($_FILES['photo']['tmp_name'], $target_file);
        } else {
            $photo = $exhibit['photo']; // Використовуємо старе фото, якщо нове не завантажено
        }

        // Оновлення експонату
        if (!empty($photo)) {
            $stmt = $conn->prepare("UPDATE exhibits SET name = ?, description = ?, year_created = ?, condition_status = ?, hall_id = ?, last_restoration = ?, photo = ? WHERE id = ?");
            $stmt->bind_param("ssissssi", $name, $description, $year_created, $condition_status, $hall_id, $last_restoration, $photo, $id);
        } else {
            $stmt = $conn->prepare("UPDATE exhibits SET name = ?, description = ?, year_created = ?, condition_status = ?, hall_id = ?, last_restoration = ? WHERE id = ?");
            $stmt->bind_param("ssisssi", $name, $description, $year_created, $condition_status, $hall_id, $last_restoration, $id);
        }

        if ($stmt->execute()) {
            echo "<script>alert('Експонат успішно оновлено!'); window.location.href = 'exhibits.php';</script>";
        } else {
            echo "<script>alert('Помилка при оновленні експонату.');</script>";
        }
        $stmt->close();
    }
} else {
    $stmt = $conn->prepare("SELECT * FROM exhibits WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $exhibit = $result->fetch_assoc();
    $stmt->close();

    if (!$exhibit) {
        echo "<script>alert('Експонат не знайдено.'); window.location.href = 'exhibits.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редагувати експонат</title>
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
                    <h3 class="museum-title mb-0">Редагувати експонат</h3>
                </div>

                <div class="museum-card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label fw-bold">
                                            <i class="fas fa-gem me-2"></i>Назва
                                        </label>
                                        <input type="text" class="form-control museum-input" id="name" name="name" value="<?php echo htmlspecialchars($exhibit['name']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="year_created" class="form-label fw-bold">
                                            <i class="fas fa-calendar me-2"></i>Рік створення
                                        </label>
                                        <input type="number" class="form-control museum-input" id="year_created" name="year_created" min="-9999" max="2025" value="<?php echo htmlspecialchars($exhibit['year_created']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label fw-bold">
                                    <i class="fas fa-align-left me-2"></i>Опис
                                </label>
                                <textarea class="form-control museum-input" id="description" name="description" rows="4" required><?php echo htmlspecialchars($exhibit['description']); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="condition_status" class="form-label fw-bold">
                                            <i class="fas fa-heart me-2"></i>Стан
                                        </label>
                                        <select class="form-select museum-input" id="condition_status" name="condition_status" required>
                                            <option value="good" <?php echo $exhibit['condition_status'] === 'good' ? 'selected' : ''; ?>>Відмінний</option>
                                            <option value="medium" <?php echo $exhibit['condition_status'] === 'medium' ? 'selected' : ''; ?>>Середній</option>
                                            <option value="bad" <?php echo $exhibit['condition_status'] === 'bad' ? 'selected' : ''; ?>>Потребує реставрації</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="hall_id" class="form-label fw-bold">
                                            <i class="fas fa-building me-2"></i>Зал (опціонально)
                                        </label>
                                        <input type="number" class="form-control museum-input" id="hall_id" name="hall_id" value="<?php echo htmlspecialchars($exhibit['hall_id']); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="last_restoration" class="form-label fw-bold">
                                            <i class="fas fa-tools me-2"></i>Дата останньої реставрації (опціонально)
                                        </label>
                                        <input type="date" class="form-control museum-input" id="last_restoration" name="last_restoration" value="<?php echo htmlspecialchars($exhibit['last_restoration']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="photo" class="form-label fw-bold">
                                            <i class="fas fa-image me-2"></i>Фото (опціонально)
                                        </label>
                                        <input type="file" class="form-control museum-input" id="photo" name="photo">
                                    </div>
                                </div>
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