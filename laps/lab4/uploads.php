<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = $_SESSION['is_admin'];

// جلب الملفات المرفقة
if ($is_admin) {
    $stmt = $conn->prepare("SELECT u.*, t.subject as ticket_subject, us.username 
                           FROM uploads u 
                           JOIN tickets t ON u.ticket_id = t.id 
                           JOIN users us ON t.user_id = us.id 
                           ORDER BY u.uploaded_at DESC");
} else {
    $stmt = $conn->prepare("SELECT u.*, t.subject as ticket_subject, us.username 
                           FROM uploads u 
                           JOIN tickets t ON u.ticket_id = t.id 
                           JOIN users us ON t.user_id = us.id 
                           WHERE t.user_id = ? 
                           ORDER BY u.uploaded_at DESC");
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملفات المرفوعة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 1200px;
            margin-top: 50px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px 20px;
        }
        .btn-primary {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: bold;
        }
        .btn-success {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: bold;
        }
        .file-info {
            color: #6c757d;
            font-size: 0.9em;
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-file-alt me-2"></i>الملفات المرفوعة</h2>
            <div>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-arrow-right me-2"></i>العودة للرئيسية
                </a>
            </div>
        </div>

        <?php if ($result->num_rows === 0): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>لا توجد ملفات مرفقة حالياً
            </div>
        <?php else: ?>
            <div class="row">
                <?php while ($file = $result->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-file me-2"></i>
                                    <?php echo htmlspecialchars($file['filename']); ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="file-info mb-3">
                                    <p><i class="fas fa-ticket-alt me-2"></i>التذكرة: <?php echo htmlspecialchars($file['ticket_subject']); ?></p>
                                    <p><i class="fas fa-user me-2"></i>بواسطة: <?php echo htmlspecialchars($file['username']); ?></p>
                                    <p><i class="fas fa-clock me-2"></i>تاريخ الرفع: <?php echo date('Y-m-d H:i', strtotime($file['uploaded_at'])); ?></p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <a href="uploads/<?php echo htmlspecialchars($file['filename']); ?>" class="btn btn-primary" target="_blank">
                                        <i class="fas fa-eye me-2"></i>فتح الملف
                                    </a>
                                    <a href="uploads/<?php echo htmlspecialchars($file['filename']); ?>" class="btn btn-success" download>
                                        <i class="fas fa-download me-2"></i>تحميل
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 