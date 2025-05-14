<?php
include '../connectionString.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: visitors.php');
    exit;
}

// Отримання id через POST
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id === 0) {
    echo "<script>alert('Невірний запит. ID не передано.'); window.location.href = 'visitors.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['last_name'])) {
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $email = trim($_POST['email']);
    $visitor_type = isset($_POST['visitor_type']) && in_array($_POST['visitor_type'], ['default', 'benefitial']) ? $_POST['visitor_type'] : 'default';

    // Валідація
    if (empty($last_name) || empty($first_name) || empty($email) || ctype_space($last_name) || ctype_space($first_name) || ctype_space($email)) {
        echo "<script>alert('Будь ласка, заповніть усі поля коректно.');</script>";
    } else {
        // Оновлення даних відвідувача
        $stmt = $conn->prepare("UPDATE visitors SET last_name = ?, first_name = ?, email = ?, visitor_type = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $last_name, $first_name, $email, $visitor_type, $id);
        if ($stmt->execute()) {
            echo "<script>alert('Дані відвідувача успішно оновлено!'); window.location.href = 'visitors.php';</script>";
        } else {
            echo "<script>alert('Помилка при оновленні даних відвідувача.');</script>";
        }
        $stmt->close();
    }
} else {
    // Завантаження даних відвідувача
    $stmt = $conn->prepare("SELECT * FROM visitors WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $visitor = $result->fetch_assoc();
    $stmt->close();

    if (!$visitor) {
        echo "<script>alert('Відвідувача не знайдено.'); window.location.href = 'visitors.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редагувати відвідувача</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../header.php'; ?>

<div class="container mt-5">
    <h3>Редагувати відвідувача</h3>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div class="mb-3">
            <label for="last_name" class="form-label">Прізвище</label>
            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($visitor['last_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="first_name" class="form-label">Ім'я</label>
            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($visitor['first_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($visitor['email']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="visitor_type" class="form-label">Тип відвідувача</label>
            <select class="form-select" id="visitor_type" name="visitor_type" required>
                <option value="default" <?php echo $visitor['visitor_type'] === 'default' ? 'selected' : ''; ?>>Default</option>
                <option value="benefitial" <?php echo $visitor['visitor_type'] === 'benefitial' ? 'selected' : ''; ?>>Benefitial</option>
            </select>
        </div>
        <button type="submit" class="btn btn-warning">Оновити</button>
    </form>
</div>

<?php include '../footer.php'; ?>
</body>
</html>