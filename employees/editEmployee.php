<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: employees.php');
    exit;
}

include '../connectionString.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $position = trim($_POST['position']);
    $hire_date = trim($_POST['hire_date']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $hall_id = trim($_POST['hall_id']);
    $photo = $_FILES['photo']['name'];
    $salary = trim($_POST['salary']);

    if ($hall_id == ""):
        $hall_id = null;
    endif;

    if (empty($last_name) || empty($first_name) || empty($position) || empty($hire_date) || empty($email) || empty($phone) || empty($salary)) {
        echo "<script>alert('Будь ласка, заповніть усі обов’язкові поля.');</script>";
    } else {
        // Завантаження фото
        if (!empty($photo)) {
            $target_dir = "../uploads/";
            $target_file = $target_dir . basename($photo);
            move_uploaded_file($_FILES['photo']['tmp_name'], $target_file);
        }

        // Оновлення даних робітника
        $stmt = $conn->prepare("UPDATE employees SET last_name = ?, first_name = ?, position = ?, hire_date = ?, email = ?, phone = ?, hall_id = ?, photo = ?, salary = ? WHERE id = ?");
        $stmt->bind_param("ssssssisis", $last_name, $first_name, $position, $hire_date, $email, $phone, $hall_id, $photo, $salary, $id);
        if ($stmt->execute()) {
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
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редагувати робітника</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../header.php'; ?>

<div class="container mt-5">
    <h3>Редагувати робітника</h3>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="last_name" class="form-label">Прізвище</label>
            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($employee['last_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="first_name" class="form-label">Ім'я</label>
            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($employee['first_name']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="position" class="form-label">Посада</label>
            <input type="text" class="form-control" id="position" name="position" value="<?php echo htmlspecialchars($employee['position']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="salary" class="form-label">Зарплата</label>
            <input type="number" class="form-control" id="position" name="salary" value="<?php echo htmlspecialchars($employee['salary']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="hire_date" class="form-label">Дата найму</label>
            <input type="date" class="form-control" id="hire_date" name="hire_date" value="<?php echo htmlspecialchars($employee['hire_date']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($employee['email']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Телефон</label>
            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($employee['phone']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="hall_id" class="form-label">Зал (опціонально)</label>
            <input type="number" class="form-control" id="hall_id" name="hall_id" value="<?php echo htmlspecialchars($employee['hall_id']); ?>">
        </div>
        <div class="mb-3">
            <label for="photo" class="form-label">Фото (опціонально)</label>
            <input type="file" class="form-control" id="photo" name="photo">
        </div>
        <button type="submit" class="btn btn-warning">Оновити</button>
    </form>
</div>

<?php include '../footer.php'; ?>
</body>
</html>