<?php
include '../connectionString.php'; // Підключення до бази даних

// Перевірка авторизації
if (!isset($_SESSION['role']) || !isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$user_id = intval($_SESSION['user_id']);
$username = $_SESSION['username'];
$user_type = $_SESSION['user_type'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exhibition_id = intval($_POST['exhibition_id']);
    $purchase_date = date('Y-m-d'); // Поточна дата

    // Валідація
    if ($exhibition_id <= 0) {
        echo "<script>alert('Будь ласка, виберіть виставку.');</script>";
    } else {
        // Додавання квитка до таблиці tickets
        $stmt = $conn->prepare("INSERT INTO tickets (user_id, exhibition_id, purchase_date) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $exhibition_id, $purchase_date);
        if ($stmt->execute()) {
            echo "<script>alert('Квиток успішно створено!'); window.location.href = 'tickets.php';</script>";
        } else {
            echo "<script>alert('Помилка при створенні квитка.');</script>";
        }
        $stmt->close();
    }
}

// Отримання списку виставок із `exhibition_view`
$stmt_exhibitions = $conn->prepare("SELECT id, title, start_date, end_date, hall_name FROM exhibition_view");
$stmt_exhibitions->execute();
$result_exhibitions = $stmt_exhibitions->get_result();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Створити квиток</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../header.php'; ?>

<div class="container mt-5">
    <h3>Створити квиток</h3>
    <form method="POST">
        <div class="mb-3">
            <label for="exhibition_id" class="form-label">Виберіть виставку</label>
            <select class="form-select" id="exhibition_id" name="exhibition_id" required>
                <option value="">-- Виберіть виставку --</option>
                <?php while ($row = $result_exhibitions->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>">
                        <?php echo htmlspecialchars($row['title'] . " (" . $row['start_date'] . " - " . $row['end_date'] . ", Зал: " . $row['hall_name'] . ")"); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Придбати квиток</button>
    </form>
</div>

<?php include '../footer.php'; ?>
</body>
</html>