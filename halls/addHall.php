<?php
include '../connectionString.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: halls.php');
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $floor = trim($_POST['floor']);
    $description = trim($_POST['description']);

    if (empty($name) || empty($floor)) {
        echo "<script>alert('Назва та поверх є обов’язковими!');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO halls (name, floor, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $name, $floor, $description);
        if ($stmt->execute()) {
            echo "<script>alert('Зал успішно додано!'); window.location.href = 'halls.php';</script>";
        } else {
            echo "<script>alert('Помилка при додаванні залу.');</script>";
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
    <title>Додати зал</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../header.php'; ?>

<div class="container mt-5">
    <h3>Додати новий зал</h3>
    <form method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Назва</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="floor" class="form-label">Поверх</label>
            <input type="number" class="form-control" id="floor" name="floor" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Опис</label>
            <textarea class="form-control" id="description" name="description"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Додати</button>
    </form>
</div>

<?php include '../footer.php'; ?>
</body>
</html>