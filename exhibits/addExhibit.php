<?php
include '../connectionString.php'; // Підключення до бази даних

if ($_SESSION['role'] !== 'admin') {
    header('Location: exhibits.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $year_created = trim($_POST['year_created']);
    $condition_status = trim($_POST['condition_status']);
    $hall_id = trim($_POST['hall_id']);
    $last_restoration = trim($_POST['last_restoration']);
    $photo = $_FILES['photo']['name'];

    // Валідація
    $valid_conditions = ['good', 'medium', 'bad'];
    if (empty($name) || empty($description) || empty($year_created) || empty($condition_status) || !in_array($condition_status, $valid_conditions)) {
        echo "<script>alert('Будь ласка, заповніть усі обов’язкові поля та виберіть коректний стан.');</script>";
    } else {
        // Завантаження фото
        if (!empty($photo)) {
            $target_dir = "../exhibits_uploads/";
            $target_file = $target_dir . basename($photo);
            move_uploaded_file($_FILES['photo']['tmp_name'], $target_file);
        }

        // Додавання нового експонату
        $stmt = $conn->prepare("INSERT INTO exhibits (name, description, year_created, condition_status, hall_id, last_restoration, photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissss", $name, $description, $year_created, $condition_status, $hall_id, $last_restoration, $photo);
        if ($stmt->execute()) {
            echo "<script>alert('Експонат успішно додано!'); window.location.href = 'exhibits.php';</script>";
        } else {
            echo "<script>alert('Помилка при додаванні експонату.');</script>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Додати експонат</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../header.php'; ?>

<div class="container mt-5">
    <h3>Додати новий експонат</h3>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Назва</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Опис</label>
            <textarea class="form-control" id="description" name="description" required></textarea>
        </div>
        <div class="mb-3">
            <label for="year_created" class="form-label">Рік створення</label>
            <input type="number" class="form-control" id="year_created" name="year_created" min="-9999" max="2025" required>        </div>
        <div class="mb-3">
            <label for="condition_status" class="form-label">Стан</label>
            <select class="form-select" id="condition_status" name="condition_status" required>
                <option value="">Оберіть стан</option>
                <option value="good">Good</option>
                <option value="medium">Medium</option>
                <option value="bad">Bad</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="hall_id" class="form-label">Зал (опціонально)</label>
            <input type="number" class="form-control" id="hall_id" name="hall_id">
        </div>
        <div class="mb-3">
            <label for="last_restoration" class="form-label">Дата останньої реставрації (опціонально)</label>
            <input type="date" class="form-control" id="last_restoration" name="last_restoration">
        </div>
        <div class="mb-3">
            <label for="photo" class="form-label">Фото (опціонально)</label>
            <input type="file" class="form-control" id="photo" name="photo">
        </div>
        <button type="submit" class="btn btn-success">Додати</button>
    </form>
</div>

<?php include '../footer.php'; ?>
</body>
</html>