<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$ticket_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$is_admin = $_SESSION['is_admin'];

// جلب معلومات التذكرة
$stmt = $conn->prepare("SELECT t.*, u.username 
                       FROM tickets t 
                       JOIN users u ON t.user_id = u.id 
                       WHERE t.id = ?");
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket) {
    header('Location: tickets.php');
    exit();
}

// جلب الرسائل
$stmt = $conn->prepare("SELECT m.*, u.username 
                       FROM messages m 
                       JOIN users u ON m.user_id = u.id 
                       WHERE m.ticket_id = ? 
                       ORDER BY m.created_at ASC");
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$messages = $stmt->get_result();

// جلب المرفقات
$stmt = $conn->prepare("SELECT * FROM uploads WHERE ticket_id = ?");
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$uploads = $stmt->get_result();

// معالجة إرسال رسالة جديدة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];
    
    // إدخال الرسالة
    $stmt = $conn->prepare("INSERT INTO messages (ticket_id, user_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $ticket_id, $user_id, $message);
    $stmt->execute();
    
    // معالجة الملفات المرفقة
    if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
        $upload_dir = 'uploads/';
        
        foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
                $filename = $_FILES['attachments']['name'][$key];
                $filepath = $upload_dir . $filename;
                
                if (move_uploaded_file($tmp_name, $filepath)) {
                    $stmt = $conn->prepare("INSERT INTO uploads (ticket_id, filename) VALUES (?, ?)");
                    $stmt->bind_param("is", $ticket_id, $filename);
                    $stmt->execute();
                }
            }
        }
    }
    
    header("Location: view_ticket.php?id=" . $ticket_id);
    exit();
}
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>عرض التذكرة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 1000px;
            margin-top: 50px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px 20px;
        }
        .message {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
        }
        .message-header {
            color: #6c757d;
            font-size: 0.9em;
            margin-bottom: 10px;
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
        .attachment-card {
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-ticket-alt me-2"></i>عرض التذكرة</h2>
            <div>
                <a href="tickets.php" class="btn btn-primary">
                    <i class="fas fa-arrow-right me-2"></i>العودة للتذاكر
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?php echo htmlspecialchars($ticket['subject']); ?></h5>
            </div>
            <div class="card-body">
                <div class="message">
                    <div class="message-header">
                        <i class="fas fa-user me-2"></i>بواسطة: <?php echo htmlspecialchars($ticket['username']); ?>
                        <i class="fas fa-clock me-2 ms-3"></i>تاريخ الإنشاء: <?php echo date('Y-m-d H:i', strtotime($ticket['created_at'])); ?>
                        <span class="badge <?php echo $ticket['status'] === 'open' ? 'bg-success' : 'bg-danger'; ?> ms-2">
                            <?php echo $ticket['status'] === 'open' ? 'مفتوحة' : 'مغلقة'; ?>
                        </span>
                    </div>
                    <div class="message-content">
                        <?php 
                        // عرض الرسالة الأولى
                        $first_message = $messages->fetch_assoc();
                        echo nl2br(htmlspecialchars($first_message['message'])); 
                        ?>
                    </div>
                </div>

                <?php while ($message = $messages->fetch_assoc()): ?>
                    <div class="message">
                        <div class="message-header">
                            <i class="fas fa-user me-2"></i>بواسطة: <?php echo htmlspecialchars($message['username']); ?>
                            <i class="fas fa-clock me-2 ms-3"></i>تاريخ الإرسال: <?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?>
                        </div>
                        <div class="message-content">
                            <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                        </div>
                    </div>
                <?php endwhile; ?>

                <?php if ($uploads->num_rows > 0): ?>
                    <div class="mt-4">
                        <h5><i class="fas fa-paperclip me-2"></i>المرفقات</h5>
                        <div class="row">
                            <?php while ($upload = $uploads->fetch_assoc()): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="attachment-card">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-file me-2"></i>
                                                <?php echo htmlspecialchars($upload['filename']); ?>
                                            </div>
                                            <div>
                                                <a href="uploads/<?php echo htmlspecialchars($upload['filename']); ?>" class="btn btn-sm btn-primary" target="_blank">
                                                    <i class="fas fa-eye me-1"></i>فتح
                                                </a>
                                                <a href="uploads/<?php echo htmlspecialchars($upload['filename']); ?>" class="btn btn-sm btn-secondary" download>
                                                    <i class="fas fa-download me-1"></i>تحميل
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($ticket['status'] === 'open'): ?>
                    <form method="POST" enctype="multipart/form-data" class="mt-4">
                        <div class="mb-3">
                            <label for="message" class="form-label">إضافة رد</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="attachments" class="form-label">المرفقات</label>
                            <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>إرسال الرد
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 