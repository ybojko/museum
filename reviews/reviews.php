<?php
include '../connectionString.php'; 
include '../log_functions.php';

createLogsTableIfNotExists($conn);

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'visitor_manager')) {
    header('Location: ../index.php');
    exit;
}

$sql = "SELECT * FROM reviews ORDER BY review_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Відгуки - Музей</title>
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
                    <i class="fas fa-comments museum-icon me-3" style="font-size: 2rem; color: var(--museum-accent);"></i>
                    <h3 class="museum-title mb-0">Всі відгуки</h3>
                    <div class="ms-auto">
                        <a href="../export_excel.php?table=reviews" class="btn museum-btn-secondary">
                            <i class="fas fa-file-excel me-2"></i>Експорт в Excel
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table museum-table" id="reviewsTable">
                        <thead>
                            <tr>
                                <th class="sortable" data-column="0"><i class="fas fa-hashtag me-2"></i>ID</th>
                                <th class="sortable" data-column="1"><i class="fas fa-user me-2"></i>Користувач</th>
                                <th class="sortable" data-column="2"><i class="fas fa-tag me-2"></i>Тип користувача</th>
                                <th class="sortable" data-column="3"><i class="fas fa-palette me-2"></i>Назва виставки</th>
                                <th class="sortable" data-column="4"><i class="fas fa-comment me-2"></i>Відгук</th>
                                <th class="sortable" data-column="5"><i class="fas fa-calendar me-2"></i>Дата</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td>
                                            <span class="badge museum-badge">
                                                <?php echo htmlspecialchars($row['user_type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['exhibition_title']); ?></strong>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($row['review_text']); ?>">
                                                <?php echo htmlspecialchars($row['review_text']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['review_date']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-search fa-3x mb-3"></i>
                                            <h5>Немає відгуків</h5>
                                            <p>Поки що немає жодного відгуку</p>
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