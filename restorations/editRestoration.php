<?php
include '../connectionString.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: restorations.php');
    exit;
}

// Отримання id через POST або GET
$id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);

if ($id === 0) {
    echo "<script>alert('Невірний запит. ID не передано.'); window.location.href = 'restorations.php';</script>";
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
        // Оновлення реставрації
        $stmt = $conn->prepare("UPDATE restorations SET exhibit_id = ?, restoration_date = ?, employee_id = ?, description = ? WHERE id = ?");
        $stmt->bind_param("isisi", $exhibit_id, $restoration_date, $employee_id, $description, $id);
        if ($stmt->execute()) {
            echo "<script>alert('Реставрацію успішно оновлено!'); window.location.href = 'restorations.php';</script>";
        } else {
            echo "<script>alert('Помилка при оновленні реставрації.');</script>";
        }
        $stmt->close();
    }
} else {
    // Завантаження даних реставрації
    $stmt = $conn->prepare("SELECT * FROM restorations WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $restoration = $result->fetch_assoc();
    $stmt->close();

    if (!$restoration) {
        echo "<script>alert('Реставрацію не знайдено.'); window.location.href = 'restorations.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редагувати реставрацію</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../header.php'; ?>

<div class="container mt-5">
    <h3>Редагувати реставрацію</h3>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div class="mb-3">
            <label for="exhibit_id" class="form-label">ID експоната</label>
            <input type="number" class="form-control" id="exhibit_id" name="exhibit_id" value="<?php echo htmlspecialchars($restoration['exhibit_id']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="restoration_date" class="form-label">Дата реставрації</label>
            <input type="date" class="form-control" id="restoration_date" name="restoration_date" value="<?php echo htmlspecialchars($restoration['restoration_date']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="employee_id" class="form-label">ID працівника (опціонально)</label>
            <input type="number" class="form-control" id="employee_id" name="employee_id" value="<?php echo htmlspecialchars($restoration['employee_id']); ?>">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Опис (опціонально)</label>
            <textarea class="form-control" id="description" name="description"><?php echo htmlspecialchars($restoration['description']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-warning">Оновити</button>
    </form>
</div>

<?php include '../footer.php'; ?>
</body>
</html>