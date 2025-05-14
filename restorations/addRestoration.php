<?php
include '../connectionString.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: restorations.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exhibit_id = trim($_POST['exhibit_id']);
    $restoration_date = trim($_POST['restoration_date']);
    $employee_id = trim($_POST['employee_id']);
    $description = trim($_POST['description']);

    // Валідація
    if (empty($exhibit_id) || empty($restoration_date)) {
        echo "<script>alert('ID експоната та дата реставрації є обов’язковими!');</script>";
    } else {
        // Додавання нової реставрації
        $stmt = $conn->prepare("INSERT INTO restorations (exhibit_id, restoration_date, employee_id, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $exhibit_id, $restoration_date, $employee_id, $description);
        if ($stmt->execute()) {
            echo "<script>alert('Реставрацію успішно додано!'); window.location.href = 'restorations.php';</script>";
        } else {
            echo "<script>alert('Помилка при додаванні реставрації.');</script>";
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
    <title>Додати реставрацію</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../header.php'; ?>

<div class="container mt-5">
    <h3>Додати нову реставрацію</h3>
    <form method="POST">
        <div class="mb-3">
            <label for="exhibit_id" class="form-label">ID експоната</label>
            <input type="number" class="form-control" id="exhibit_id" name="exhibit_id" required>
        </div>
        <div class="mb-3">
            <label for="restoration_date" class="form-label">Дата реставрації</label>
            <input type="date" class="form-control" id="restoration_date" name="restoration_date" required>
        </div>
        <div class="mb-3">
            <label for="employee_id" class="form-label">ID працівника (опціонально)</label>
            <input type="number" class="form-control" id="employee_id" name="employee_id">
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