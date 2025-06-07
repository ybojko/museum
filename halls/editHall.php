<?php
include '../connectionString.php';
include '../log_functions.php';

createLogsTableIfNotExists($conn);

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'content_manager')) {
    header('Location: halls.php');
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);

if ($id === 0 || !is_numeric($id)) {
    echo "<script>alert('Невірний запит. ID не передано.'); window.location.href = 'halls.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    $floor = trim($_POST['floor']);
    $description = trim($_POST['description']);
    $photo_path = $hall['photo_path'];
   
    if (empty($name) || empty($floor)) {
        echo "<script>alert('Назва та поверх є обов'язковими!');</script>";
    } else {
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $upload_dir = '../assets/images/halls/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($file_extension, $allowed_types)) {
                if ($hall['photo_path'] && file_exists('../' . $hall['photo_path'])) {
                    unlink('../' . $hall['photo_path']);
                }
                
                $new_filename = 'hall_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                    $photo_path = 'assets/images/halls/' . $new_filename;
                }
            }
        }        
        $stmt = $conn->prepare("UPDATE halls SET name = ?, floor = ?, description = ?, photo_path = ? WHERE id = ?");
        $stmt->bind_param("sissi", $name, $floor, $description, $photo_path, $id);
        if ($stmt->execute()) {
            $action_details = "Оновлено зал: $name (ID: $id)\nПоверх: $floor";
            if (!empty($description)) {
                $action_details .= "\nОпис: $description";
            }
            if ($photo_path) {
                $action_details .= "\nФото: $photo_path";
            }
            logActivity($conn, 'UPDATE', 'halls', $id, $action_details);
            
            echo "<script>alert('Зал успішно оновлено!'); window.location.href = 'halls.php';</script>";
        } else {
            echo "<script>alert('Помилка при оновленні залу.');</script>";
        }
        $stmt->close();
    }
} else {
    $stmt = $conn->prepare("SELECT * FROM halls WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $hall = $result->fetch_assoc();
    $stmt->close();

    if (!$hall) {
        echo "<script>alert('Зал не знайдено.'); window.location.href = 'halls.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редагувати зал</title>
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
                    <i class="fas fa-edit museum-icon me-3" style="font-size: 2rem; color: var(--museum-accent);"></i>
                    <h3 class="museum-title mb-0">Редагувати зал</h3>
                </div>

                <div class="museum-card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label fw-bold">
                                            <i class="fas fa-door-open me-2"></i>Назва
                                        </label>
                                        <input type="text" class="form-control museum-input" id="name" name="name" value="<?php echo htmlspecialchars($hall['name']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="floor" class="form-label fw-bold">
                                            <i class="fas fa-layer-group me-2"></i>Поверх
                                        </label>
                                        <input type="number" class="form-control museum-input" id="floor" name="floor" value="<?php echo htmlspecialchars($hall['floor']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label fw-bold">
                                    <i class="fas fa-align-left me-2"></i>Опис
                                </label>
                                <textarea class="form-control museum-input" id="description" name="description" rows="4"><?php echo htmlspecialchars($hall['description']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="photo" class="form-label fw-bold">
                                    <i class="fas fa-camera me-2"></i>Фото залу
                                </label>
                                <?php if ($hall['photo_path']): ?>
                                    <div class="mb-2">
                                        <img src="../<?php echo htmlspecialchars($hall['photo_path']); ?>" 
                                             alt="Поточне фото" class="img-thumbnail" style="max-width: 200px;">
                                        <p class="text-muted small">Поточне фото</p>
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control museum-input" id="photo" name="photo" accept="image/*">
                                <div class="form-text">Підтримувані формати: JPG, JPEG, PNG, GIF. Залиште порожнім, щоб зберегти поточне фото.</div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn museum-btn-secondary btn-lg">
                                    <i class="fas fa-save me-2"></i>Оновити
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