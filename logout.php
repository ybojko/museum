<?php
session_start();
session_unset(); // Видаляє всі змінні сесії
session_destroy(); // Завершує сесію
header('Location: index.php'); // Переадресація на головну сторінку
exit;
?>