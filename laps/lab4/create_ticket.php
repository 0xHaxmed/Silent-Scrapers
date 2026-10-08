<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'];
    $message_text = $_POST['message'];
    $user_id = $_SESSION['user_id'];
    
    try {
        // بدء المعاملة
        $conn->begin_transaction();
        
        // إدخال التذكرة
        $stmt = $conn->prepare("INSERT INTO tickets (user_id, subject, status) VALUES (?, ?, 'open')");
        $stmt->bind_param("is", $user_id, $subject);
        
        if ($stmt->execute()) {
            $ticket_id = $conn->insert_id;
            
            // إدخال الرسالة
            $stmt = $conn->prepare("INSERT INTO messages (ticket_id, user_id, message) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $ticket_id, $user_id, $message_text);
            $stmt->execute();
            
            // معالجة الملفات المرفقة
            if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
                $upload_dir = 'uploads/';
                
                // التأكد من وجود مجلد uploads
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
                    if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
                        $filename = $_FILES['attachments']['name'][$key];
                        $filepath = $upload_dir . $filename;
                        
                        // نقل الملف
                        if (move_uploaded_file($tmp_name, $filepath)) {
                            // إدخال معلومات الملف في قاعدة البيانات
                            $stmt = $conn->prepare("INSERT INTO uploads (ticket_id, filename) VALUES (?, ?)");
                            $stmt->bind_param("is", $ticket_id, $filename);
                            $stmt->execute();
                        }
                    }
                }
            }
            
            // تأكيد المعاملة
            $conn->commit();
            header('Location: tickets.php');
            exit();
        } else {
            throw new Exception("فشل في إنشاء التذكرة");
        }
    } catch (Exception $e) {
        // التراجع عن المعاملة في حالة حدوث خطأ
        $conn->rollback();
        $message = '<div class="alert alert-danger">حدث خطأ: ' . $e->getMessage() . '</div>';
    }
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء تذكرة جديدة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 800px;
            margin-top: 50px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
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
        .form-control {
            border-radius: 10px;
            padding: 10px 15px;
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
            <h2><i class="fas fa-plus-circle me-2"></i>إنشاء تذكرة جديدة</h2>
            <div>
                <a href="tickets.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-right me-2"></i>العودة للتذاكر
                </a>
            </div>
        </div>

        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">معلومات التذكرة</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="subject" class="form-label">عنوان التذكرة</label>
                        <input type="text" class="form-control" id="subject" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">الرسالة</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="attachments" class="form-label">المرفقات</label>
                        <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
                        <small class="text-muted">يمكنك اختيار أكثر من ملف</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>إرسال التذكرة
                    </button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 