<?php
include '../connectionString.php'; // Підключення до бази даних

// Перевірка авторизації
if (!isset($_SESSION['role']) || !isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$user_id = intval($_SESSION['user_id']);
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : '';

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
    <link href="../assets/css/museum-theme.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="museum-bg">
<?php include '../header.php'; ?>

<div class="museum-content">
    <div class="container mt-5">
        <div class="museum-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <i class="fas fa-ticket-alt museum-icon me-3" style="font-size: 2rem; color: var(--museum-accent);"></i>
                    <h3 class="museum-title mb-0">Створити квиток</h3>
                </div>

                <div class="museum-card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-4">
                                <label for="exhibition_id" class="form-label fw-bold">
                                    <i class="fas fa-calendar-alt me-2"></i>Виберіть виставку
                                </label>
                                <select class="form-select museum-input form-select-lg" id="exhibition_id" name="exhibition_id" required>
                                    <option value="">-- Виберіть виставку --</option>
                                    <?php while ($row = $result_exhibitions->fetch_assoc()): ?>
                                        <option value="<?php echo $row['id']; ?>">
                                            <?php echo htmlspecialchars($row['title'] . " (" . $row['start_date'] . " - " . $row['end_date'] . ", Зал: " . $row['hall_name'] . ")"); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn museum-btn-primary btn-lg">
                                    <i class="fas fa-ticket-alt me-2"></i>Придбати квиток
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
</body>
</html>