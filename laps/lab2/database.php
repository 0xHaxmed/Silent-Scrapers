<?php
// إعدادات الجلسة
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Lax');

// تعيين معرف جلسة جديد
session_id(bin2hex(random_bytes(16)));

// بدء الجلسة
session_start();

// إعدادات الاتصال
$servername = "localhost";
$username = "root";
$password = "";

// إنشاء الاتصال
$conn = mysqli_connect($servername, $username, $password);

// التحقق من الاتصال
if (!$conn) {
    die("فشل الاتصال: " . mysqli_connect_error());
}

// إنشاء قاعدة البيانات
$sql = "CREATE DATABASE IF NOT EXISTS ethical_hacking_training";
if (mysqli_query($conn, $sql)) {
    echo "تم إنشاء قاعدة البيانات بنجاح";
} else {
    echo "خطأ في إنشاء قاعدة البيانات: " . mysqli_error($conn);
}

// اختيار قاعدة البيانات
mysqli_select_db($conn, "ethical_hacking_training");

// حذف الجداول الموجودة إذا كانت موجودة
mysqli_query($conn, "DROP TABLE IF EXISTS comments");
mysqli_query($conn, "DROP TABLE IF EXISTS articles");
mysqli_query($conn, "DROP TABLE IF EXISTS uploaded_files");
mysqli_query($conn, "DROP TABLE IF EXISTS users");

// إنشاء جدول المستخدمين مع إضافة حقل is_admin
$sql = "CREATE TABLE users (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "<br>تم إنشاء جدول المستخدمين بنجاح";
} else {
    echo "<br>خطأ في إنشاء جدول المستخدمين: " . mysqli_error($conn);
}

// إنشاء جدول المقالات
$sql = "CREATE TABLE articles (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    author_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id)
)";

if (mysqli_query($conn, $sql)) {
    echo "<br>تم إنشاء جدول المقالات بنجاح";
} else {
    echo "<br>خطأ في إنشاء جدول المقالات: " . mysqli_error($conn);
}

// إنشاء جدول التعليقات
$sql = "CREATE TABLE comments (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    article_id INT,
    user_id INT,
    comment TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if (mysqli_query($conn, $sql)) {
    echo "<br>تم إنشاء جدول التعليقات بنجاح";
} else {
    echo "<br>خطأ في إنشاء جدول التعليقات: " . mysqli_error($conn);
}

// إنشاء جدول الملفات المرفوعة
$sql = "CREATE TABLE uploaded_files (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(100),
    upload_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if (mysqli_query($conn, $sql)) {
    echo "<br>تم إنشاء جدول الملفات المرفوعة بنجاح";
} else {
    echo "<br>خطأ في إنشاء جدول الملفات المرفوعة: " . mysqli_error($conn);
}

// إنشاء المستخدم الأساسي (admin)
$admin_username = "admin";
$admin_password = password_hash("admin123", PASSWORD_DEFAULT);

// التحقق من وجود المستخدم الأساسي
$check_admin = mysqli_query($conn, "SELECT id FROM users WHERE username = 'admin'");
if (mysqli_num_rows($check_admin) == 0) {
    $sql = "INSERT INTO users (username, password, is_admin) VALUES ('$admin_username', '$admin_password', 1)";
    if (mysqli_query($conn, $sql)) {
        echo "<br>تم إنشاء المستخدم الأساسي بنجاح";
    } else {
        echo "<br>خطأ في إنشاء المستخدم الأساسي: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>