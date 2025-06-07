<?php
include '../connectionString.php';
include '../log_functions.php';

createLogsTableIfNotExists($conn);

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: visitors.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $email = trim($_POST['email']);
    $visitor_type = isset($_POST['visitor_type']) && in_array($_POST['visitor_type'], ['default', 'benefitial']) ? $_POST['visitor_type'] : 'default';

    if (empty($last_name) || empty($first_name) || empty($email) || ctype_space($last_name) || ctype_space($first_name) || ctype_space($email)) {
        echo "<script>alert('Будь ласка, заповніть усі поля коректно.');</script>";
    } else {        
        $stmt = $conn->prepare("INSERT INTO visitors (last_name, first_name, email, visitor_type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $last_name, $first_name, $email, $visitor_type);
        if ($stmt->execute()) {
            $new_visitor_id = $conn->insert_id;
            
            $action_details = "Додано нового відвідувача: $last_name $first_name\nEmail: $email\nТип: $visitor_type";
            logActivity($conn, 'INSERT', 'visitors', $new_visitor_id, $action_details);
            
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
    <title>Додати відвідувача - Музей</title>
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
                    <i class="fas fa-user-plus museum-icon me-3" style="font-size: 2rem; color: var(--museum-accent);"></i>
                    <h3 class="museum-title mb-0">Додати нового відвідувача</h3>
                </div>

                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">
                                <i class="fas fa-user me-2"></i>Прізвище
                            </label>
                            <input type="text" class="form-control museum-input" id="last_name" name="last_name" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">
                                <i class="fas fa-user me-2"></i>Ім'я
                            </label>
                            <input type="text" class="form-control museum-input" id="first_name" name="first_name" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-2"></i>Email
                        </label>
                        <input type="email" class="form-control museum-input" id="email" name="email" required>
                    </div>

                    <div class="mb-4">
                        <label for="visitor_type" class="form-label">
                            <i class="fas fa-tag me-2"></i>Тип відвідувача
                        </label>
                        <select class="form-select museum-input" id="visitor_type" name="visitor_type" required>
                            <option value="default" selected>Default</option>
                            <option value="benefitial">Benefitial</option>
                        </select>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn museum-btn-primary">
                            <i class="fas fa-save me-2"></i>Додати
                        </button>
                        <a href="visitors.php" class="btn museum-btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Повернутися
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
</body>
</html>