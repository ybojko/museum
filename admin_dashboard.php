<?php
include 'connectionString.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff_manager' && $_SESSION['role'] !== 'content_manager' && $_SESSION['role'] !== 'visitor_manager')) {
    header('Location: index.php');
    exit;
}

$role = $_SESSION['role'];

$create_logs_table = "
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action_type ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT,
    user_id INT NOT NULL,
    username VARCHAR(100) NOT NULL,
    user_role VARCHAR(50) NOT NULL,
    action_details TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_table_name (table_name),
    INDEX idx_user_role (user_role),
    INDEX idx_timestamp (timestamp)
)";
$conn->query($create_logs_table);

function limitLogsPerTable($conn) {
    $tables = ['employees', 'exhibits', 'exhibitions', 'halls', 'restorations', 'visitors', 'tickets', 'reviews'];
    
    foreach ($tables as $table) {
        $delete_old_logs = "
        DELETE FROM activity_logs 
        WHERE table_name = '$table' 
        AND id NOT IN (
            SELECT id FROM (
                SELECT id FROM activity_logs 
                WHERE table_name = '$table' 
                ORDER BY timestamp DESC 
                LIMIT 10
            ) AS recent_logs
        )";
        $conn->query($delete_old_logs);
    }
}

limitLogsPerTable($conn);

if ($role === 'admin') {
    $sql = "SELECT * FROM activity_logs ORDER BY timestamp DESC LIMIT 100";
    $stmt = $conn->prepare($sql);
} else {
    $allowed_tables = [];
    
    switch ($role) {
        case 'staff_manager':
            $allowed_tables = ['employees'];
            break;
        case 'content_manager':
            $allowed_tables = ['exhibits', 'exhibitions', 'halls'];
            break;
        case 'visitor_manager':
            $allowed_tables = ['visitors', 'tickets', 'reviews'];
            break;
    }
    
    if (!empty($allowed_tables)) {
        $placeholders = str_repeat('?,', count($allowed_tables) - 1) . '?';
        $sql = "SELECT * FROM activity_logs WHERE table_name IN ($placeholders) ORDER BY timestamp DESC LIMIT 50";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat('s', count($allowed_tables)), ...$allowed_tables);
    } else {
        $sql = "SELECT * FROM activity_logs WHERE 1=0";
        $stmt = $conn->prepare($sql);
    }
}

$stmt->execute();
$result = $stmt->get_result();

function getActionIcon($action) {
    switch ($action) {
        case 'INSERT': return 'fas fa-plus-circle text-success';
        case 'UPDATE': return 'fas fa-edit text-warning';
        case 'DELETE': return 'fas fa-trash-alt text-danger';
        default: return 'fas fa-info-circle text-info';
    }
}

function getTableIcon($table) {
    switch ($table) {
        case 'employees': return 'fas fa-users';
        case 'exhibits': return 'fas fa-gem';
        case 'exhibitions': return 'fas fa-images';
        case 'halls': return 'fas fa-building';
        case 'restorations': return 'fas fa-tools';
        case 'visitors': return 'fas fa-user-friends';
        case 'tickets': return 'fas fa-ticket-alt';
        case 'reviews': return 'fas fa-star';
        default: return 'fas fa-database';
    }
}

function getTableNameUkr($table) {
    switch ($table) {
        case 'employees': return 'Співробітники';
        case 'exhibits': return 'Експонати';
        case 'exhibitions': return 'Виставки';
        case 'halls': return 'Зали';
        case 'restorations': return 'Реставрації';
        case 'visitors': return 'Відвідувачі';
        case 'tickets': return 'Квитки';
        case 'reviews': return 'Відгуки';
        default: return $table;
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель адміністратора - Музей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/museum-theme.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .activity-card {
            border-left: 4px solid var(--museum-accent);
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        .activity-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .action-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
        }
        .timestamp {
            font-size: 0.875rem;
            opacity: 0.7;
        }
        .log-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 0.75rem;
            margin-top: 0.5rem;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
        }
        .stats-card {
            background: linear-gradient(135deg, var(--museum-primary) 0%, var(--museum-accent) 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .stats-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
    </style>
</head>
<body class="museum-bg">
<?php include 'header.php'; ?>

<div class="museum-content">
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="d-flex align-items-center mb-4">
                    <i class="fas fa-cog museum-icon me-3" style="font-size: 2rem; color: var(--museum-accent);"></i>
                    <div>
                        <h3 class="museum-title mb-0">Панель адміністратора</h3>
                        <p class="text-muted mb-0">
                            Роль: <strong><?php echo htmlspecialchars($role); ?></strong> | 
                            Користувач: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-list stats-icon"></i>
                    <h4 class="mt-2 mb-1"><?php echo $result->num_rows; ?></h4>
                    <p class="mb-0">Логів активності</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-users stats-icon"></i>
                    <h4 class="mt-2 mb-1">
                        <?php 
                        $count_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
                        echo $count_users;
                        ?>
                    </h4>
                    <p class="mb-0">Користувачів</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-gem stats-icon"></i>
                    <h4 class="mt-2 mb-1">
                        <?php 
                        $count_exhibits = $conn->query("SELECT COUNT(*) as count FROM exhibits")->fetch_assoc()['count'];
                        echo $count_exhibits;
                        ?>
                    </h4>
                    <p class="mb-0">Експонатів</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card text-center">
                    <i class="fas fa-building stats-icon"></i>
                    <h4 class="mt-2 mb-1">
                        <?php 
                        $count_halls = $conn->query("SELECT COUNT(*) as count FROM halls")->fetch_assoc()['count'];
                        echo $count_halls;
                        ?>
                    </h4>
                    <p class="mb-0">Залів</p>
                </div>
            </div>
        </div>

        <div class="museum-card mb-4">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-bolt me-2"></i>Швидкі дії
                </h5>
                <div class="row">
                    <?php if ($role === 'admin' || $role === 'staff_manager'): ?>
                    <div class="col-md-2 mb-2">
                        <a href="employees/employees.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-users d-block mb-1"></i>
                            Співробітники
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($role === 'admin' || $role === 'content_manager'): ?>
                    <div class="col-md-2 mb-2">
                        <a href="exhibits/exhibits.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-gem d-block mb-1"></i>
                            Експонати
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="exhibitions/exhibitions.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-images d-block mb-1"></i>
                            Виставки
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="halls/halls.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-building d-block mb-1"></i>
                            Зали
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($role === 'admin' || $role === 'visitor_manager'): ?>
                    <div class="col-md-2 mb-2">
                        <a href="visitors/visitors.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-user-friends d-block mb-1"></i>
                            Відвідувачі
                        </a>
                    </div>
                    <div class="col-md-2 mb-2">
                        <a href="tickets/tickets.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-ticket-alt d-block mb-1"></i>
                            Квитки
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="museum-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Логи активності
                    </h5>
                    <small class="text-muted">
                        <?php if ($role === 'admin'): ?>
                            Показано всі логи системи
                        <?php else: ?>
                            Показано логи для ваших дозволених таблиць
                        <?php endif; ?>
                    </small>
                </div>

                <?php if ($result->num_rows > 0): ?>
                    <div class="row">
                        <?php while ($log = $result->fetch_assoc()): ?>
                        <div class="col-md-6 mb-3">
                            <div class="activity-card border rounded p-3 h-100">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="<?php echo getActionIcon($log['action_type']); ?> me-2" style="font-size: 1.25rem;"></i>
                                        <div>
                                            <span class="action-badge bg-light text-dark">
                                                <i class="<?php echo getTableIcon($log['table_name']); ?> me-1"></i>
                                                <?php echo getTableNameUkr($log['table_name']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <span class="badge bg-<?php echo $log['action_type'] === 'INSERT' ? 'success' : ($log['action_type'] === 'UPDATE' ? 'warning' : 'danger'); ?>">
                                        <?php echo $log['action_type']; ?>
                                    </span>
                                </div>
                                
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>
                                        <strong><?php echo htmlspecialchars($log['username']); ?></strong>
                                        (<?php echo htmlspecialchars($log['user_role']); ?>)
                                    </small>
                                </div>
                                
                                <div class="timestamp mt-1">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo date('d.m.Y H:i:s', strtotime($log['timestamp'])); ?>
                                </div>
                                
                                <?php if ($log['record_id']): ?>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        <i class="fas fa-hashtag me-1"></i>
                                        ID запису: <?php echo htmlspecialchars($log['record_id']); ?>
                                    </small>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($log['action_details']): ?>
                                <div class="log-details">
                                    <small>
                                        <strong>Деталі:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($log['action_details'])); ?>
                                    </small>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Логи активності відсутні</h5>
                        <p class="text-muted">Ще немає записів про дії в системі</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
