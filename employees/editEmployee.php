<?php
include '../connectionString.php';
include '../log_functions.php';

// Створюємо таблицю логів, якщо вона не існує
createLogsTableIfNotExists($conn);

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: employees.php');
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);

if ($id === 0 || !is_numeric($id)) {
    echo "<script>alert('Невірний запит. ID не передано.'); window.location.href = 'employees.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['last_name'])) {
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $position = trim($_POST['position']);
    $hire_date = trim($_POST['hire_date']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $hall_id = trim($_POST['hall_id']);
    $photo = $_FILES['photo']['name'];
    $salary = trim($_POST['salary']);

    if ($hall_id == "") {
        $hall_id = null;
    }

    if (empty($last_name) || empty($first_name) || empty($position) || empty($hire_date) || empty($email) || empty($phone) || empty($salary)) {
        echo "<script>alert('Будь ласка, заповніть усі обов’язкові поля.');</script>";
    } else {
        if (!empty($photo)) {
            $target_dir = "../employees_uploads/";
            $target_file = $target_dir . basename($photo);
            move_uploaded_file($_FILES['photo']['tmp_name'], $target_file);
        } else {
            $photo = $employee['photo']; // якщо нове не завантажено
        }        $stmt = $conn->prepare("UPDATE employees SET last_name = ?, first_name = ?, position = ?, hire_date = ?, email = ?, phone = ?, hall_id = ?, photo = ?, salary = ? WHERE id = ?");
        $stmt->bind_param("ssssssisis", $last_name, $first_name, $position, $hire_date, $email, $phone, $hall_id, $photo, $salary, $id);
        if ($stmt->execute()) {
            // Логування оновлення
            $action_details = "Оновлено співробітника: $last_name $first_name (ID: $id)\nПосада: $position\nЗарплата: $salary\nEmail: $email";
            logActivity($conn, 'UPDATE', 'employees', $id, $action_details);
            
            echo "<script>alert('Дані робітника успішно оновлено!'); window.location.href = 'employees.php';</script>";
        } else {
            echo "<script>alert('Помилка при оновленні даних робітника.');</script>";
        }
        $stmt->close();
    }
} else {
    $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
    $stmt->close();

    if (!$employee) {
        echo "<script>alert('Робітника не знайдено.'); window.location.href = 'employees.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редагувати робітника - Музей</title>
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
                    <i class="fas fa-user-edit museum-icon me-3" style="font-size: 2rem; color: var(--museum-accent);"></i>
                    <h3 class="museum-title mb-0">Редагувати робітника</h3>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">
                                <i class="fas fa-user me-2"></i>Прізвище
                            </label>
                            <input type="text" class="form-control museum-input" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($employee['last_name']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">
                                <i class="fas fa-user me-2"></i>Ім'я
                            </label>
                            <input type="text" class="form-control museum-input" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($employee['first_name']); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="position" class="form-label">
                                <i class="fas fa-briefcase me-2"></i>Посада
                            </label>
                            <input type="text" class="form-control museum-input" id="position" name="position" 
                                   value="<?php echo htmlspecialchars($employee['position']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="salary" class="form-label">
                                <i class="fas fa-money-bill me-2"></i>Зарплата
                            </label>
                            <input type="number" class="form-control museum-input" id="salary" name="salary" 
                                   value="<?php echo htmlspecialchars($employee['salary']); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="hire_date" class="form-label">
                                <i class="fas fa-calendar me-2"></i>Дата найму
                            </label>
                            <input type="date" class="form-control museum-input" id="hire_date" name="hire_date" 
                                   value="<?php echo htmlspecialchars($employee['hire_date']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-2"></i>Email
                            </label>
                            <input type="email" class="form-control museum-input" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($employee['email']); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">
                                <i class="fas fa-phone me-2"></i>Телефон
                            </label>
                            <input type="number" class="form-control museum-input" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($employee['phone']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="hall_id" class="form-label">
                                <i class="fas fa-building me-2"></i>Зал (опціонально)
                            </label>
                            <input type="number" class="form-control museum-input" id="hall_id" name="hall_id" 
                                   value="<?php echo htmlspecialchars($employee['hall_id']); ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="photo" class="form-label">
                            <i class="fas fa-image me-2"></i>Фото (опціонально)
                        </label>
                        <input type="file" class="form-control museum-input" id="photo" name="photo">
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn museum-btn-primary">
                            <i class="fas fa-save me-2"></i>Оновити
                        </button>
                        <a href="employees.php" class="btn museum-btn-secondary">
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