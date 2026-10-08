<?php
require_once 'config.php';

// إنشاء جدول uploads
$sql = "CREATE TABLE IF NOT EXISTS uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "تم إنشاء جدول uploads بنجاح<br>";
} else {
    echo "خطأ في إنشاء جدول uploads: " . $conn->error . "<br>";
}

// إنشاء مجلد uploads إذا لم يكن موجوداً
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
    echo "تم إنشاء مجلد uploads بنجاح<br>";
}

echo "تم الانتهاء من إعداد قاعدة البيانات";
?> 