<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'support_system';

// First connect without database
$conn = new mysqli($db_host, $db_user, $db_pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if ($conn->query($sql) === TRUE) {
    $conn->select_db($db_name);
} else {
    die("Error creating database: " . $conn->error);
}

// Create tables
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE,
        password VARCHAR(255),
        email VARCHAR(100),
        is_admin BOOLEAN DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS tickets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        subject VARCHAR(200),
        description TEXT,
        status ENUM('open', 'closed', 'pending') DEFAULT 'open',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",
    "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ticket_id INT,
        user_id INT,
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (ticket_id) REFERENCES tickets(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",
    "CREATE TABLE IF NOT EXISTS uploads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ticket_id INT,
        filename VARCHAR(255),
        filepath VARCHAR(255),
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (ticket_id) REFERENCES tickets(id)
    )"
];

foreach ($tables as $sql) {
    if (!$conn->query($sql)) {
        die("Error creating table: " . $conn->error);
    }
}

// Create uploads directory if it doesn't exist
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'register':
                $username = $_POST['username'];
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $email = $_POST['email'];
                $is_admin = isset($_POST['is_admin']) ? 1 : 0;

                $sql = "INSERT INTO users (username, password, email, is_admin) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $username, $password, $email, $is_admin);
                $stmt->execute();
                break;

            case 'login':
                $username = $_POST['username'];
                $password = $_POST['password'];

                // Vulnerable SQL Query for testing
                $sql = "SELECT * FROM users WHERE username = '$username'";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    if (password_verify($password, $user['password']) || $password === "admin' OR '1'='1") {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['is_admin'] = $user['is_admin'];
                        $_SESSION['username'] = $user['username'];
                    }
                }
                break;

            case 'create_ticket':
                if (isset($_SESSION['user_id'])) {
                    $subject = $_POST['subject'];
                    $description = $_POST['description'];
                    $user_id = $_SESSION['user_id'];

                    $sql = "INSERT INTO tickets (user_id, subject, description) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iss", $user_id, $subject, $description);
                    $stmt->execute();
                    $ticket_id = $conn->insert_id;

                    // Handle file upload
                    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
                        $file = $_FILES['attachment'];
                        $filename = time() . '_' . basename($file['name']);
                        $filepath = "uploads/" . $filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $filepath)) {
                            // Vulnerable Command Execution for testing
                            if (strpos($filename, '.jpg') !== false) {
                                exec("convert $filepath -resize 800x600 $filepath");
                            }

                            $sql = "INSERT INTO uploads (ticket_id, filename, filepath) VALUES (?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("iss", $ticket_id, $filename, $filepath);
                            $stmt->execute();
                        }
                    }
                }
                break;

            case 'send_message':
                if (isset($_SESSION['user_id'])) {
                    $ticket_id = $_POST['ticket_id'];
                    $message = $_POST['message'];
                    $user_id = $_SESSION['user_id'];

                    // XXE Vulnerability for testing
                    if (isset($_FILES['xml_file']) && $_FILES['xml_file']['error'] == 0) {
                        $xml = file_get_contents($_FILES['xml_file']['tmp_name']);
                        $dom = new DOMDocument();
                        $dom->loadXML($xml, LIBXML_NOENT | LIBXML_DTDLOAD);
                    }

                    $sql = "INSERT INTO messages (ticket_id, user_id, message) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iis", $ticket_id, $user_id, $message);
                    $stmt->execute();
                }
                break;
        }
    }
}

// Get tickets for display
$tickets = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $is_admin = $_SESSION['is_admin'] ?? 0;
    
    // Vulnerable to Broken Access Control
    $sql = "SELECT t.*, u.username FROM tickets t 
            JOIN users u ON t.user_id = u.id 
            WHERE t.user_id = $user_id";
    if ($is_admin) {
        $sql = "SELECT t.*, u.username FROM tickets t 
                JOIN users u ON t.user_id = u.id";
    }
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $tickets[] = $row;
    }
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام الدعم الفني - الرئيسية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .menu-card {
            transition: transform 0.3s;
            cursor: pointer;
        }
        .menu-card:hover {
            transform: translateY(-5px);
        }
        .menu-icon {
            font-size: 3em;
            margin-bottom: 15px;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>نظام الدعم الفني</h1>
            <a href="logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
            </a>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <a href="create_ticket.php" class="text-decoration-none">
                    <div class="card menu-card text-center p-4">
                        <i class="fas fa-ticket-alt menu-icon"></i>
                        <h3>إنشاء تذكرة جديدة</h3>
                        <p class="text-muted">إنشاء تذكرة دعم فني جديدة</p>
                    </div>
                </a>
            </div>

            <div class="col-md-4 mb-4">
                <a href="tickets.php" class="text-decoration-none">
                    <div class="card menu-card text-center p-4">
                        <i class="fas fa-list menu-icon"></i>
                        <h3>عرض التذاكر</h3>
                        <p class="text-muted">عرض وإدارة التذاكر المفتوحة</p>
                    </div>
                </a>
            </div>

            <div class="col-md-4 mb-4">
                <a href="uploads.php" class="text-decoration-none">
                    <div class="card menu-card text-center p-4">
                        <i class="fas fa-file-upload menu-icon"></i>
                        <h3>الملفات المرفوعة</h3>
                        <p class="text-muted">عرض وتحميل الملفات المرفوعة</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 