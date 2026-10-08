<?php
session_start();
require_once 'config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';

// معالجة حذف التذكرة
if (isset($_POST['delete_ticket'])) {
    $ticket_id = $_POST['ticket_id'];
    $user_id = $_SESSION['user_id'];
    $is_admin = $_SESSION['is_admin'];
    
    try {
        // بدء المعاملة
        $conn->begin_transaction();
        
        // حذف المرفقات أولاً
        $stmt = $conn->prepare("SELECT filename FROM uploads WHERE ticket_id = ?");
        if ($stmt === false) {
            throw new Exception("خطأ في إعداد استعلام المرفقات: " . $conn->error);
        }
        $stmt->bind_param("i", $ticket_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($file = $result->fetch_assoc()) {
            $file_path = 'uploads/' . $file['filename'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // حذف سجلات المرفقات
        $stmt = $conn->prepare("DELETE FROM uploads WHERE ticket_id = ?");
        if ($stmt === false) {
            throw new Exception("خطأ في إعداد استعلام حذف المرفقات: " . $conn->error);
        }
        $stmt->bind_param("i", $ticket_id);
        $stmt->execute();
        
        // حذف الرسائل
        $stmt = $conn->prepare("DELETE FROM messages WHERE ticket_id = ?");
        if ($stmt === false) {
            throw new Exception("خطأ في إعداد استعلام حذف الرسائل: " . $conn->error);
        }
        $stmt->bind_param("i", $ticket_id);
        $stmt->execute();
        
        // حذف التذكرة
        if ($is_admin) {
            $stmt = $conn->prepare("DELETE FROM tickets WHERE id = ?");
            if ($stmt === false) {
                throw new Exception("خطأ في إعداد استعلام حذف التذكرة: " . $conn->error);
            }
            $stmt->bind_param("i", $ticket_id);
        } else {
            $stmt = $conn->prepare("DELETE FROM tickets WHERE id = ? AND user_id = ?");
            if ($stmt === false) {
                throw new Exception("خطأ في إعداد استعلام حذف التذكرة: " . $conn->error);
            }
            $stmt->bind_param("ii", $ticket_id, $user_id);
        }
        
        if ($stmt->execute()) {
            // تأكيد المعاملة
            $conn->commit();
            $message = '<div class="alert alert-success">تم حذف التذكرة بنجاح</div>';
        } else {
            throw new Exception("فشل في حذف التذكرة: " . $stmt->error);
        }
    } catch (Exception $e) {
        // التراجع عن المعاملة في حالة حدوث خطأ
        $conn->rollback();
        $message = '<div class="alert alert-danger">حدث خطأ أثناء حذف التذكرة: ' . $e->getMessage() . '</div>';
    }
}

// جلب التذاكر
$user_id = $_SESSION['user_id'];
$is_admin = $_SESSION['is_admin'];

if ($is_admin) {
    $stmt = $conn->prepare("SELECT t.*, u.username, COUNT(m.id) as message_count 
                           FROM tickets t 
                           JOIN users u ON t.user_id = u.id 
                           LEFT JOIN messages m ON t.id = m.ticket_id 
                           GROUP BY t.id 
                           ORDER BY t.created_at DESC");
} else {
    $stmt = $conn->prepare("SELECT t.*, u.username, COUNT(m.id) as message_count 
                           FROM tickets t 
                           JOIN users u ON t.user_id = u.id 
                           LEFT JOIN messages m ON t.id = m.ticket_id 
                           WHERE t.user_id = ? 
                           GROUP BY t.id 
                           ORDER BY t.created_at DESC");
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
    <title>عرض التذاكر</title>
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
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9em;
        }
        .status-open {
            background-color: #28a745;
            color: white;
        }
        .status-closed {
            background-color: #dc3545;
            color: white;
        }
        .btn-primary {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: bold;
        }
        .btn-danger {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: bold;
        }
        .ticket-info {
            color: #6c757d;
            font-size: 0.9em;
        }
        .delete-form {
            display: inline;
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
            <h2><i class="fas fa-ticket-alt me-2"></i>التذاكر</h2>
            <div>
                <a href="create_ticket.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>تذكرة جديدة
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-right me-2"></i>العودة للرئيسية
                </a>
            </div>
        </div>

        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <?php if ($result->num_rows === 0): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>لا توجد تذاكر حالياً
            </div>
        <?php else: ?>
            <?php while ($ticket = $result->fetch_assoc()): ?>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?php echo htmlspecialchars($ticket['subject']); ?></h5>
                        <span class="status-badge <?php echo $ticket['status'] === 'open' ? 'status-open' : 'status-closed'; ?>">
                            <?php echo $ticket['status'] === 'open' ? 'مفتوحة' : 'مغلقة'; ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="ticket-info mb-3">
                            <i class="fas fa-user me-2"></i>بواسطة: <?php echo htmlspecialchars($ticket['username']); ?>
                            <i class="fas fa-comments me-2 ms-3"></i>عدد الرسائل: <?php echo $ticket['message_count']; ?>
                            <i class="fas fa-clock me-2 ms-3"></i>تاريخ الإنشاء: <?php echo date('Y-m-d H:i', strtotime($ticket['created_at'])); ?>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="view_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye me-2"></i>عرض التفاصيل
                                </a>
                                <?php if ($is_admin || $ticket['user_id'] == $user_id): ?>
                                    <form method="POST" class="delete-form" onsubmit="return confirm('هل أنت متأكد من حذف هذه التذكرة؟');">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                        <button type="submit" name="delete_ticket" class="btn btn-danger">
                                            <i class="fas fa-trash me-2"></i>حذف التذكرة
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 