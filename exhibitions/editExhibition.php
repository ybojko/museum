<?php
include '../connectionString.php'; // Підключення до бази даних

if ($_SESSION['role'] !== 'admin') {
    header('Location: exhibitions.php');
    exit;
}

// Отримання id через POST або GET
$id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);

if ($id === 0) {
    echo "<script>alert('Невірний запит. ID не передано.'); window.location.href = 'exhibitions.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = trim($_POST['title']);
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);
    $hall_id = trim($_POST['hall_id']);
    $description = trim($_POST['description']);

    // Валідація
    if (empty($title) || empty($start_date) || empty($hall_id)) {
        echo "<script>alert('Будь ласка, заповніть усі обов’язкові поля.');</script>";
    } else {
        // Оновлення виставки
        $stmt = $conn->prepare("UPDATE exhibitions SET title = ?, start_date = ?, end_date = ?, hall_id = ?, description = ? WHERE id = ?");
        $stmt->bind_param("sssdsd", $title, $start_date, $end_date, $hall_id, $description, $id);
        if ($stmt->execute()) {
            echo "<script>alert('Виставку успішно оновлено!'); window.location.href = 'exhibitions.php';</script>";
        } else {
            echo "<script>alert('Помилка при оновленні виставки.');</script>";
        }
        $stmt->close();
    }
} else {
    // Завантаження даних виставки
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
</head>
<body>
<?php include '../header.php'; ?>

<div class="container mt-5">
    <h3>Редагувати виставку</h3>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div class="mb-3">
            <label for="title" class="form-label">Назва</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($exhibition['title']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Дата початку</label>
            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($exhibition['start_date']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">Дата завершення (опціонально)</label>
            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($exhibition['end_date']); ?>">
        </div>
        <div class="mb-3">
            <label for="hall_id" class="form-label">Зал</label>
            <input type="number" class="form-control" id="hall_id" name="hall_id" value="<?php echo htmlspecialchars($exhibition['hall_id']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Опис (опціонально)</label>
            <textarea class="form-control" id="description" name="description"><?php echo htmlspecialchars($exhibition['description']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-warning">Оновити</button>
    </form>
</div>

<?php include '../footer.php'; ?>
</body>
</html>