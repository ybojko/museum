<?php
include '../connectionString.php';
include '../log_functions.php';

createLogsTableIfNotExists($conn);

if (!isset($_SESSION['role']) || !isset($_SESSION['username'])) {
    header('Location: ../index.php');
    exit;
}

$username = $_SESSION['username'];
$user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'default';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exhibition_title = isset($_POST['exhibition_title']) ? trim($_POST['exhibition_title']) : '';
    $review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';
    $review_date = date('Y-m-d H:i:s');

    if (empty($exhibition_title) || empty($review_text)) {
        echo "<script>alert('Будь ласка, заповніть усі поля.');</script>";
    } else {
        $stmt_check = $conn->prepare("SELECT id FROM reviews WHERE username = ? AND exhibition_title = ?");
        $stmt_check->bind_param("ss", $username, $exhibition_title);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            echo "<script>alert('Ви вже залишили відгук для цієї виставки.'); window.location.href = '../tickets/tickets.php';</script>";
        } else {            
            $stmt = $conn->prepare("INSERT INTO reviews (username, user_type, exhibition_title, review_text, review_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $username, $user_type, $exhibition_title, $review_text, $review_date);
            if ($stmt->execute()) {
                $new_review_id = $conn->insert_id;
                
                $action_details = "Додано новий відгук\nКористувач: $username\nВиставка: $exhibition_title\nТекст відгуку: " . substr($review_text, 0, 100) . (strlen($review_text) > 100 ? '...' : '');
                logActivity($conn, 'INSERT', 'reviews', $new_review_id, $action_details);
                
                echo "<script>alert('Відгук успішно додано!'); window.location.href = '../tickets/tickets.php';</script>";
            } else {
                echo "<script>alert('Помилка при додаванні відгуку.');</script>";
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Додати відгук</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../header.php'; ?>

<div class="container mt-5">
    <h3>Додати відгук</h3>
    <form method="POST">
        <div class="mb-3">
            <label for="exhibition_title" class="form-label">Виберіть виставку</label>
            <select class="form-select" id="exhibition_title" name="exhibition_title" required>
                <option value="">-- Виберіть виставку --</option>
                <?php
                $stmt_exhibitions = $conn->prepare("SELECT DISTINCT title FROM ticket_details_view WHERE username = ?");
                $stmt_exhibitions->bind_param("s", $username);
                $stmt_exhibitions->execute();
                $result_exhibitions = $stmt_exhibitions->get_result();
                while ($row = $result_exhibitions->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['title']); ?>">
                        <?php echo htmlspecialchars($row['title']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="review_text" class="form-label">Ваш відгук</label>
            <textarea class="form-control" id="review_text" name="review_text" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn btn-success">Додати відгук</button>
    </form>
</div>

<?php include '../footer.php'; ?>
</body>
</html>