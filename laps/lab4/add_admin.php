<?php
require_once 'config.php';

// إضافة مستخدم مسؤول
$username = 'admin';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$email = 'admin@example.com';
$is_admin = 1;

$stmt = $conn->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $username, $email, $password, $is_admin);

if ($stmt->execute()) {
    echo "تم إضافة المستخدم المسؤول بنجاح!";
} else {
    echo "حدث خطأ أثناء إضافة المستخدم المسؤول.";
}
?> 