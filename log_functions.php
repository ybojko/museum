<?php
/**
 * Функція для логування дій в системі
 */
function logActivity($conn, $action_type, $table_name, $record_id = null, $action_details = null) {
    // Перевіряємо, чи є активна сесія
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
        return false;
    }
    
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $user_role = $_SESSION['role'];
    
    try {
        // Вставляємо новий лог
        $stmt = $conn->prepare("INSERT INTO activity_logs (action_type, table_name, record_id, user_id, username, user_role, action_details) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiisss", $action_type, $table_name, $record_id, $user_id, $username, $user_role, $action_details);
        $stmt->execute();
        $stmt->close();
        
        // Обмежуємо кількість логів для цієї таблиці до 10
        limitLogsForTable($conn, $table_name);
        
        return true;
    } catch (Exception $e) {
        error_log("Помилка логування: " . $e->getMessage());
        return false;
    }
}

/**
 * Функція для обмеження кількості логів до 10 для кожної таблиці
 */
function limitLogsForTable($conn, $table_name) {
    try {
        $delete_old_logs = "
        DELETE FROM activity_logs 
        WHERE table_name = ? 
        AND id NOT IN (
            SELECT id FROM (
                SELECT id FROM activity_logs 
                WHERE table_name = ? 
                ORDER BY timestamp DESC 
                LIMIT 10
            ) AS recent_logs
        )";
        $stmt = $conn->prepare($delete_old_logs);
        $stmt->bind_param("ss", $table_name, $table_name);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Помилка обмеження логів: " . $e->getMessage());
    }
}

/**
 * Створення таблиці логів, якщо вона не існує
 */
function createLogsTableIfNotExists($conn) {
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
    
    try {
        $conn->query($create_logs_table);
        return true;
    } catch (Exception $e) {
        error_log("Помилка створення таблиці логів: " . $e->getMessage());
        return false;
    }
}
?>
