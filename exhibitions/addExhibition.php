<?php
include '../connectionString.php'; // Підключення до бази даних

if ($_SESSION['role'] !== 'admin') {
    header('Location: exhibitions.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);
    $hall_id = trim($_POST['hall_id']);
    $description = trim($_POST['description']);

    if (empty($title) || empty($start_date) || empty($hall_id)) {
        echo "<script>alert('Будь ласка, заповніть усі обов’язкові поля.');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO exhibitions (title, start_date, end_date, hall_id, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssds", $title, $start_date, $end_date, $hall_id, $description);
        if ($stmt->execute()) {
            echo "<script>alert('Виставку успішно додано!'); window.location.href = 'exhibitions.php';</script>";
        } else {
            echo "<script>alert('Помилка при додаванні виставки.');</script>";
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
    <title>Додати виставку</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../header.php'; ?>

<div class="container mt-5">
    <h3>Додати нову виставку</h3>
    <form method="POST">
        <div class="mb-3">
            <label for="title" class="form-label">Назва</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Дата початку</label>
            <input type="date" class="form-control" id="start_date" name="start_date" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">Дата завершення (опціонально)</label>
            <input type="date" class="form-control" id="end_date" name="end_date">
        </div>
        <div class="mb-3">
            <label for="hall_id" class="form-label">Зал</label>
            <input type="number" class="form-control" id="hall_id" name="hall_id" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Опис (опціонально)</label>
            <textarea class="form-control" id="description" name="description"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Додати</button>
    </form>
</div>

<?php include '../footer.php'; ?>
</body>
</html>