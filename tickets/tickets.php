<?php
include '../connectionString.php'; 

if (!isset($_SESSION['role'])) {
    header('Location: ../index.php');
    exit;
}

$role = $_SESSION['role'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

if ($role === 'admin' || $role === 'visitor_manager') {
    $sql = "SELECT * FROM ticket_details_view";
    $stmt = $conn->prepare($sql);
} else {
    $sql = "SELECT * FROM ticket_details_view WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Квитки - Музей</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/museum-theme.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        function sortTable(tableId, column, direction) {
            const table = document.getElementById(tableId);
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            rows.sort((a, b) => {
                const aText = a.cells[column].textContent.trim();
                const bText = b.cells[column].textContent.trim();
                
                if (!isNaN(aText) && !isNaN(bText)) {
                    return direction === 'asc' ? aText - bText : bText - aText;
                }
                
                return direction === 'asc' 
                    ? aText.localeCompare(bText, 'uk') 
                    : bText.localeCompare(aText, 'uk');
            });
            
            rows.forEach(row => tbody.appendChild(row));
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.sortable').forEach(header => {
                header.addEventListener('click', function() {
                    const table = this.closest('table');
                    const column = parseInt(this.dataset.column);
                    
                    document.querySelectorAll(`#${table.id} .sortable`).forEach(h => {
                        if (h !== this) {
                            h.classList.remove('asc', 'desc');
                        }
                    });
                    
                    let direction = 'asc';
                    if (this.classList.contains('asc')) {
                        direction = 'desc';
                        this.classList.remove('asc');
                        this.classList.add('desc');
                    } else {
                        this.classList.remove('desc');
                        this.classList.add('asc');
                    }
                    
                    sortTable(table.id, column, direction);
                });
            });
        });
    </script>
</head>
<body class="museum-bg">
<?php include '../header.php'; ?>

<div class="museum-content">
    <div class="container mt-5">
        <div class="museum-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <i class="fas fa-ticket-alt museum-icon me-3" style="font-size: 2rem; color: var(--museum-accent);"></i>
                    <h3 class="museum-title mb-0">Квитки</h3>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <a href="addTicket.php" class="btn museum-btn-primary">
                            <i class="fas fa-plus me-2"></i>Створити новий квиток
                        </a>
                    </div>
                    <?php if ($role === 'admin' || $role === 'visitor_manager'): ?>
                    <div class="col-md-6 text-end">
                        <a href="../export_excel.php?table=tickets" class="btn museum-btn-secondary">
                            <i class="fas fa-file-excel me-2"></i>Експорт в Excel
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="table-responsive">
                    <table class="table museum-table" id="ticketsTable">
                        <thead>
                            <tr>
                                <th class="sortable" data-column="0"><i class="fas fa-hashtag me-2"></i>ID квитка</th>
                                <th class="sortable" data-column="1"><i class="fas fa-calendar me-2"></i>Дата покупки</th>
                                <th class="sortable" data-column="2"><i class="fas fa-user me-2"></i>Користувач</th>
                                <th class="sortable" data-column="3"><i class="fas fa-tag me-2"></i>Тип користувача</th>
                                <th class="sortable" data-column="4"><i class="fas fa-palette me-2"></i>Назва виставки</th>
                                <th class="sortable" data-column="5"><i class="fas fa-calendar-alt me-2"></i>Дата початку</th>
                                <th class="sortable" data-column="6"><i class="fas fa-calendar-times me-2"></i>Дата завершення</th>
                                <th class="sortable" data-column="7"><i class="fas fa-info-circle me-2"></i>Опис</th>
                                <th class="sortable" data-column="8"><i class="fas fa-building me-2"></i>Зал</th>
                                <th><i class="fas fa-cogs me-2"></i>Дії</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['ticket_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['purchase_date']); ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td>
                                            <span class="badge museum-badge">
                                                <?php echo htmlspecialchars($row['user_type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                                        <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td><?php echo htmlspecialchars($row['hall_name']); ?></td>
                                        <td>
                                            <?php if ($role !== 'admin' && $role !== 'visitor_manager' && $row['username'] === $username): ?>
                                                <form method="POST" action="../reviews/addReview.php" style="display: inline;">
                                                    <input type="hidden" name="exhibition_title" value="<?php echo htmlspecialchars($row['title']); ?>">
                                                    <button type="submit" class="btn btn-sm museum-btn-secondary" title="Написати відгук">
                                                        <i class="fas fa-star me-1"></i>Відгук
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-search fa-3x mb-3"></i>
                                            <h5>Нічого не знайдено</h5>
                                            <p>Спробуйте змінити параметри пошуку</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
</body>
</html>