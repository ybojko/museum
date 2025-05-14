<?php
include '../connectionString.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: halls.php');
    exit;
}

// Отримання id через POST або GET
$id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);

if ($id === 0) {
    echo "<script>alert('Невірний запит. ID не передано.'); window.location.href = 'halls.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    $floor = trim($_POST['floor']);
    $description = trim($_POST['description']);

    // Валідація
    if (empty($name) || empty($floor)) {
        echo "<script>alert('Назва та поверх є обов’язковими!');</script>";
    } else {
        // Оновлення залу
        $stmt = $conn->prepare("UPDATE halls SET name = ?, floor = ?, description = ? WHERE id = ?");
        $stmt->bind_param("sisi", $name, $floor, $description, $id);
        if ($stmt->execute()) {
            echo "<script>alert('Зал успішно оновлено!'); window.location.href = 'halls.php';</script>";
        } else {
            echo "<script>alert('Помилка при оновленні залу.');</script>";
        }
        $stmt->close();
    }
} else {
    // Завантаження даних залу
    $stmt = $conn->prepare("SELECT * FROM halls WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $hall = $result->fetch_assoc();
    $stmt->close();

    if (!$hall) {
        echo "<script>alert('Зал не знайдено.'); window.location.href = 'halls.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редагувати зал</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../header.php'; ?>

<div class="container mt-5">
    <h3>Редагувати зал</h3>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div class="mb-3">
            <label for="name" class="form-label">Назва</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($hall['name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="floor" class="form-label">Поверх</label>
            <input type="number" class="form-control" id="floor" name="floor" value="<?php echo htmlspecialchars($hall['floor']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Опис</label>
            <textarea class="form-control" id="description" name="description"><?php echo htmlspecialchars($hall['description']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-warning">Оновити</button>
    </form>
</div>

<?php include '../footer.php'; ?>
</body>
</html>