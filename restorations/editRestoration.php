<?php
include '../connectionString.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: restorations.php');
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);

if ($id === 0 || !is_numeric($id)) {
    echo "<script>alert('Невірний запит. ID не передано.'); window.location.href = 'restorations.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exhibit_id'])) {
    $exhibit_id = trim($_POST['exhibit_id']);
    $restoration_date = trim($_POST['restoration_date']);
    $employee_id = trim($_POST['employee_id']);
    $description = trim($_POST['description']);

    if (empty($exhibit_id) || empty($restoration_date)) {
        echo "<script>alert('ID експоната та дата реставрації є обов'язковими!');</script>";
    } else {
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
    <title>Редагувати реставрацію - Музей</title>
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
                    <i class="fas fa-tools museum-icon me-3" style="font-size: 2rem; color: var(--museum-accent);"></i>
                    <h3 class="museum-title mb-0">Редагувати реставрацію</h3>
                </div>

                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="exhibit_id" class="form-label">
                                <i class="fas fa-gem me-2"></i>ID експоната
                            </label>
                            <input type="number" class="form-control museum-input" id="exhibit_id" name="exhibit_id" 
                                   value="<?php echo htmlspecialchars($restoration['exhibit_id']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="restoration_date" class="form-label">
                                <i class="fas fa-calendar me-2"></i>Дата реставрації
                            </label>
                            <input type="date" class="form-control museum-input" id="restoration_date" name="restoration_date" 
                                   value="<?php echo htmlspecialchars($restoration['restoration_date']); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="employee_id" class="form-label">
                            <i class="fas fa-user me-2"></i>ID працівника (опціонально)
                        </label>
                        <input type="number" class="form-control museum-input" id="employee_id" name="employee_id" 
                               value="<?php echo htmlspecialchars($restoration['employee_id']); ?>">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left me-2"></i>Опис (опціонально)
                        </label>
                        <textarea class="form-control museum-input" id="description" name="description" rows="4"><?php echo htmlspecialchars($restoration['description']); ?></textarea>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn museum-btn-primary">
                            <i class="fas fa-save me-2"></i>Оновити
                        </button>
                        <a href="restorations.php" class="btn museum-btn-secondary">
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