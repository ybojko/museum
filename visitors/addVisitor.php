<?php
include '../connectionString.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: visitors.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $email = trim($_POST['email']);
    $visitor_type = isset($_POST['visitor_type']) && in_array($_POST['visitor_type'], ['default', 'benefitial']) ? $_POST['visitor_type'] : 'default';

    // Валідація
    if (empty($last_name) || empty($first_name) || empty($email) || ctype_space($last_name) || ctype_space($first_name) || ctype_space($email)) {
        echo "<script>alert('Будь ласка, заповніть усі поля коректно.');</script>";
    } else {
        // Додавання нового відвідувача
        $stmt = $conn->prepare("INSERT INTO visitors (last_name, first_name, email, visitor_type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $last_name, $first_name, $email, $visitor_type);
        if ($stmt->execute()) {
            echo "<script>alert('Відвідувача успішно додано!'); window.location.href = 'visitors.php';</script>";
        } else {
            echo "<script>alert('Помилка при додаванні відвідувача.');</script>";
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
    <title>Додати відвідувача</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../header.php'; ?>

<div class="container mt-5">
    <h3>Додати нового відвідувача</h3>
    <form method="POST">
        <div class="mb-3">
            <label for="last_name" class="form-label">Прізвище</label>
            <input type="text" class="form-control" id="last_name" name="last_name" required>
        </div>
        <div class="mb-3">
            <label for="first_name" class="form-label">Ім'я</label>
            <input type="text" class="form-control" id="first_name" name="first_name" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="visitor_type" class="form-label">Тип відвідувача</label>
            <select class="form-select" id="visitor_type" name="visitor_type" required>
                <option value="default" selected>Default</option>
                <option value="benefitial">Benefitial</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Додати</button>
    </form>
</div>

<?php include '../footer.php'; ?>
</body>
</html>