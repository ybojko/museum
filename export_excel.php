<?php
include 'connectionString.php';

if (!isset($_SESSION['role'])) {
    header('Location: index.php');
    exit;
}

$role = $_SESSION['role'];
$table = isset($_GET['table']) ? $_GET['table'] : '';
$allowed_tables = ['restorations', 'visitors', 'tickets', 'reviews', 'employees', 'exhibitions', 'exhibits', 'halls'];

if (!in_array($table, $allowed_tables)) {
    header('Location: index.php');
    exit;
}

$has_permission = false;
switch ($table) {
    case 'restorations':
    case 'exhibitions':
    case 'exhibits':
    case 'halls':
        $has_permission = ($role === 'admin' || $role === 'content_manager');
        break;
    case 'visitors':
    case 'reviews':
    case 'tickets':
        $has_permission = ($role === 'admin' || $role === 'visitor_manager');
        break;
    case 'employees':
        $has_permission = ($role === 'admin' || $role === 'staff_manager');
        break;
}

if (!$has_permission) {
    header('Location: index.php');
    exit;
}

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $table . '_export_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

echo "\xEF\xBB\xBF";

switch ($table) {
    case 'restorations':
        $sql = "SELECT * FROM restorations ORDER BY restoration_date DESC";
        break;
    case 'visitors':
        $sql = "SELECT * FROM visitors ORDER BY id";
        break;
    case 'tickets':
        $sql = "SELECT * FROM ticket_details_view ORDER BY purchase_date DESC";
        break;
    case 'reviews':
        $sql = "SELECT * FROM reviews ORDER BY review_date DESC";
        break;
    case 'employees':
        $sql = "SELECT * FROM employees ORDER BY last_name, first_name";
        break;
    case 'exhibitions':
        $sql = "SELECT * FROM exhibition_view ORDER BY start_date DESC";
        break;
    case 'exhibits':
        $sql = "SELECT * FROM exhibit_view ORDER BY name";
        break;
    case 'halls':
        $sql = "SELECT * FROM halls ORDER BY floor, name";
        break;
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $fields = $result->fetch_fields();
    
    echo '<table border="1">';
    echo '<tr>';
    foreach ($fields as $field) {
        echo '<th>' . htmlspecialchars($field->name) . '</th>';
    }
    echo '</tr>';
    
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        foreach ($row as $value) {
            echo '<td>' . htmlspecialchars($value) . '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
}

$conn->close();
?>
