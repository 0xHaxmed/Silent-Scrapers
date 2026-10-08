<?php
// ======== إعداد قاعدة البيانات ========
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "noon_clone";
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->query("CREATE DATABASE IF NOT EXISTS $db_name");
$conn->select_db($db_name);

// إنشاء الجداول (الصيغة الصحيحة)
$conn->query("DROP TABLE IF EXISTS products"); // حذف الجدول القديم إذا كان موجوداً
$conn->query("CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    stock INT DEFAULT 0
)");

$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    is_admin BOOLEAN DEFAULT FALSE
)");

// إضافة بيانات أولية
if ($conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0] == 0) {
    $conn->query("INSERT INTO users (username, password, email, is_admin) VALUES 
        ('admin', '1234', 'admin@noon.com', TRUE),
        ('user1', 'pass1', 'user1@example.com', FALSE),
        ('user2', 'pass2', 'user2@example.com', FALSE),
        ('user3', 'pass3', 'user3@example.com', FALSE),
        ('user4', 'pass4', 'user4@example.com', FALSE),
        ('user5', 'pass5', 'user5@example.com', FALSE),
        ('user6', 'pass6', 'user6@example.com', FALSE),
        ('user7', 'pass7', 'user7@example.com', FALSE),
        ('user8', 'pass8', 'user8@example.com', FALSE),
        ('user9', 'pass9', 'user9@example.com', FALSE),
        ('user10', 'pass10', 'user10@example.com', FALSE),
        ('user11', 'pass11', 'user11@example.com', FALSE),
        ('user12', 'pass12', 'user12@example.com', FALSE),
        ('user13', 'pass13', 'user13@example.com', FALSE),
        ('user14', 'pass14', 'user14@example.com', FALSE),
        ('user15', 'pass15', 'user15@example.com', FALSE),
        ('user16', 'pass16', 'user16@example.com', FALSE),
        ('user17', 'pass17', 'user17@example.com', FALSE),
        ('user18', 'pass18', 'user18@example.com', FALSE),
        ('user19', 'pass19', 'user19@example.com', FALSE),
        ('user20', 'pass20', 'user20@example.com', FALSE)");
}

// إضافة بيانات أولية للمنتجات
if ($conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0] == 0) {
    $conn->query("INSERT INTO products (name, price, image_url, category, description, stock) VALUES
        ('iPhone 15', 3500, 'https://i.imgur.com/J5Q8X0p.jpg', 'electronics', 'أحدث هاتف آيفون مع كاميرا متطورة', 10),
        ('Samsung TV', 4200, 'https://i.imgur.com/L4m3Y0q.jpg', 'electronics', 'تلفاز سامسونج ذكي 4K', 5),
        ('Nike Shoes', 899, 'https://i.imgur.com/9Z7W1Fb.jpg', 'fashion', 'حذاء نايك رياضي مريح', 20),
        ('MacBook Pro', 5500, 'https://i.imgur.com/2X8Y5Zq.jpg', 'electronics', 'لابتوب ماك بوك برو M2', 8),
        ('Sony Headphones', 1200, 'https://i.imgur.com/7K9L3Mn.jpg', 'electronics', 'سماعات سوني لاسلكية', 15),
        ('Rolex Watch', 15000, 'https://i.imgur.com/4P5Q6R7.jpg', 'fashion', 'ساعة رولكس كلاسيكية', 3),
        ('Gaming PC', 8000, 'https://i.imgur.com/1B2C3D4.jpg', 'electronics', 'كمبيوتر للألعاب عالي الأداء', 6),
        ('Leather Wallet', 299, 'https://i.imgur.com/8E9F0G1.jpg', 'fashion', 'محفظة جلدية فاخرة', 30),
        ('Smart Watch', 1500, 'https://i.imgur.com/5H6I7J8.jpg', 'electronics', 'ساعة ذكية متعددة الوظائف', 12),
        ('Camera DSLR', 4500, 'https://i.imgur.com/9K0L1M2.jpg', 'electronics', 'كاميرا احترافية', 7),
        ('Designer Bag', 2500, 'https://i.imgur.com/3N4O5P6.jpg', 'fashion', 'حقيبة يد مصممة', 10),
        ('Wireless Mouse', 199, 'https://i.imgur.com/7Q8R9S0.jpg', 'electronics', 'ماوس لاسلكي', 25),
        ('Coffee Maker', 800, 'https://i.imgur.com/1T2U3V4.jpg', 'home', 'ماكينة قهوة أوتوماتيكية', 15),
        ('Smart Home Hub', 1200, 'https://i.imgur.com/5W6X7Y8.jpg', 'electronics', 'مركز تحكم المنزل الذكي', 8),
        ('Yoga Mat', 150, 'https://i.imgur.com/9A0B1C2.jpg', 'sports', 'سجادة يوغا', 40),
        ('Blender', 600, 'https://i.imgur.com/3D4E5F6.jpg', 'home', 'خلاط كهربائي', 18),
        ('Running Shoes', 450, 'https://i.imgur.com/7G8H9I0.jpg', 'sports', 'حذاء رياضي للجري', 22),
        ('Smart Doorbell', 900, 'https://i.imgur.com/1J2K3L4.jpg', 'electronics', 'جرس باب ذكي', 10),
        ('Air Purifier', 1200, 'https://i.imgur.com/5M6N7O8.jpg', 'home', 'منقي هواء', 12),
        ('Fitness Tracker', 800, 'https://i.imgur.com/9P0Q1R2.jpg', 'sports', 'جهاز تتبع اللياقة البدنية', 15)");
}

// ======== معالجة الطلبات ========
session_start();
$page = $_GET['page'] ?? 'home';

// معالجة تسجيل الدخول مع ثغرة SQL Injection
$login_message = "";
if ($page == 'login' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // استعلام SQL مع ثغرة متعمدة
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password' LIMIT 1";
    
    try {
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
            $_SESSION['user'] = $user_data['username'];
            $_SESSION['is_admin'] = $user_data['is_admin'] ?? false;
            $login_message = "✅ تم الدخول بنجاح كـ: " . htmlspecialchars($_SESSION['user']);
        } else {
            $login_message = "❌ فشل الدخول! جرب: <code>admin' -- </code> (مع مسافة بعد --)";
        }
    } catch (Exception $e) {
        $login_message = "⚠️ حدث خطأ تقني: " . htmlspecialchars(str_replace([$username, $password], '****', $e->getMessage()));
    }
}

// معالجة نموذج الاتصال مع ثغرة XSS
$contact_message = "";
if ($page == 'contact' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $contact_message = $_POST['message']; // لا يوجد تعقيم عمداً لإظهار ثغرة XSS
}

// إضافة منتج جديد (للوحة الأدمن)
if ($page == 'admin' && isset($_POST['add_product'])) {
    $name = $_POST['product_name'];
    $price = $_POST['product_price'];
    $image = $_POST['product_image'];
    $category = $_POST['product_category'];
    $description = $_POST['product_description'];
    $stock = $_POST['product_stock'];
    
    $sql = "INSERT INTO products (name, price, image_url, category, description, stock) 
            VALUES ('$name', $price, '$image', '$category', '$description', $stock)";
    $conn->query($sql);
}

// حذف منتج (للوحة الأدمن)
if ($page == 'admin' && isset($_POST['delete_product'])) {
    $product_id = (int)$_POST['product_id'];
    $sql = "DELETE FROM products WHERE id = $product_id";
    $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>متجر نون</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f5f5f5; }
        header { background: #FF6B6B; color: white; padding: 15px; text-align: center; }
        nav { background: #333; overflow: hidden; }
        nav a { color: white; padding: 14px 16px; display: inline-block; text-decoration: none; }
        .product { background: white; margin: 15px; padding: 15px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: inline-block; width: 250px; }
        .product img { width: 100%; height: 200px; object-fit: cover; border-radius: 5px; }
        form { margin: 20px auto; padding: 20px; background: white; border-radius: 5px; max-width: 500px; }
        input, textarea { width: 100%; padding: 10px; margin: 8px 0; box-sizing: border-box; }
        button { background: #FF6B6B; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; }
        .admin-panel { background: white; padding: 20px; border-radius: 5px; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: right; border-bottom: 1px solid #ddd; }
        th { background: #f0f0f0; }
        .message { padding: 15px; margin: 20px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        code { background: #333; color: white; padding: 2px 5px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <header>
        <h1>متجر نون</h1>
    </header>
    <nav>
        <a href="?page=home">الرئيسية</a>
        <a href="?page=products">المنتجات</a>
        <a href="?page=login">تسجيل الدخول</a>
        <a href="?page=contact">اتصل بنا</a>
        <?php if (isset($_SESSION['user'])): ?>
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                <a href="?page=admin">لوحة التحكم</a>
            <?php endif; ?>
            <a href="?page=logout">تسجيل الخروج</a>
        <?php endif; ?>
    </nav>

    <div style="padding: 20px;">
        <?php if ($page == 'home'): ?>
            <h2>أهلاً بك في متجر نون!</h2>
            <p>تصفح أفضل المنتجات لدينا</p>

        <?php elseif ($page == 'products'): ?>
            <h2>منتجاتنا</h2>
            <div style="text-align: center;">
                <?php
                $result = $conn->query("SELECT * FROM products");
                if ($result) {
                    while ($row = $result->fetch_assoc()): ?>
                        <div class="product">
                            <img src="<?= htmlspecialchars($row['image_url'] ?? '') ?>" alt="<?= htmlspecialchars($row['name'] ?? '') ?>">
                            <h3><?= htmlspecialchars($row['name'] ?? '') ?></h3>
                            <p>السعر: <?= htmlspecialchars($row['price'] ?? 0) ?> ر.س</p>
                            <p>القسم: <?= htmlspecialchars($row['category'] ?? '') ?></p>
                            <p>المخزون: <?= htmlspecialchars($row['stock'] ?? 0) ?></p>
                            <?php if (!empty($row['description'])): ?>
                                <p><?= htmlspecialchars($row['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endwhile;
                    $result->free();
                }
                ?>
            </div>

        <?php elseif ($page == 'login'): ?>
            <div class="login-box" style="background: white; max-width: 400px; margin: 50px auto; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
                <h2 style="text-align:center;color:#e67e22;">تسجيل الدخول</h2>
                
                <?php if (!empty($login_message)): ?>
                    <div class="message <?= strpos($login_message, '✅') !== false ? 'success' : 'error' ?>">
                        <?= $login_message ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="text" name="username" placeholder="اسم المستخدم" required>
                    <input type="password" name="password" placeholder="كلمة المرور">
                    <button type="submit" name="login">دخول</button>
                </form>
                
                <div style="margin-top:30px;padding:15px;background:#f0f7ff;border-radius:5px;">
                    <h3 style="margin-top:0;">كيفية اختبار الثغرة:</h3>
                    <ol>
                        <li>في حقل <strong>اسم المستخدم</strong> أدخل بالضبط:
                            <code>admin' -- </code>
                            (لاحظ المسافة بعد --)
                        </li>
                        <li><strong>اترك كلمة المرور فارغة</strong></li>
                        <li>اضغط على زر <strong>دخول</strong></li>
                    </ol>
                </div>
            </div>

        <?php elseif ($page == 'contact'): ?>
            <form method="POST">
                <h2>اتصل بنا</h2>
                <textarea name="message" placeholder="رسالتك..." required></textarea>
                <button type="submit">إرسال</button>
                <?php if (!empty($contact_message)): ?>
                    <div style="margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 5px;">
                        <h3>تم استلام رسالتك:</h3>
                        <p><?= $contact_message ?></p> <!-- ثغرة XSS هنا -->
                        <div style="margin-top: 15px; font-size: 0.9em; color: #666;">
                            <p>لاختبار ثغرة XSS، جرب إدخال:</p>
                            <code>&lt;script&gt;alert('XSS!');&lt;/script&gt;</code>
                        </div>
                    </div>
                <?php endif; ?>
            </form>

        <?php elseif ($page == 'admin' && isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <div class="admin-panel">
                <h2>لوحة تحكم الأدمن</h2>
                
                <!-- إضافة قسم جديد لعرض وحذف المنتجات -->
                <div class="admin-products-list">
                    <h3>إدارة المنتجات</h3>
                    <table>
                        <tr>
                            <th>المنتج</th>
                            <th>السعر</th>
                            <th>القسم</th>
                            <th>المخزون</th>
                            <th>الإجراءات</th>
                        </tr>
                        <?php
                        $products = $conn->query("SELECT * FROM products ORDER BY id DESC");
                        while ($product = $products->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($product['name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($product['price'] ?? '0') ?> ر.س</td>
                            <td><?= htmlspecialchars($product['category'] ?? '') ?></td>
                            <td><?= htmlspecialchars($product['stock'] ?? '0') ?></td>
                            <td>
                                <form method="POST" style="margin: 0; padding: 0; display: inline;">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <button type="submit" name="delete_product" style="background: #dc3545;" onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">حذف</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>

                <div class="admin-stats">
                    <h3>إحصائيات</h3>
                    <?php
                    $total_users = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
                    $total_products = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
                    $total_admins = $conn->query("SELECT COUNT(*) FROM users WHERE is_admin = TRUE")->fetch_row()[0];
                    ?>
                    <p>إجمالي المستخدمين: <?= $total_users ?></p>
                    <p>إجمالي المنتجات: <?= $total_products ?></p>
                    <p>عدد الأدمن: <?= $total_admins ?></p>
                </div>
                
                <div class="admin-users">
                    <h3>المستخدمين</h3>
                    <table>
                        <tr>
                            <th>المستخدم</th>
                            <th>البريد الإلكتروني</th>
                            <th>الصلاحيات</th>
                        </tr>
                        <?php
                        $users = $conn->query("SELECT * FROM users");
                        while ($user = $users->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($user['username'] ?? '') ?></td>
                            <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                            <td><?= ($user['is_admin'] ?? false) ? 'أدمن' : 'مستخدم عادي' ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>

                <div class="admin-products">
                    <h3>إضافة منتج جديد</h3>
                    <form method="POST" action="?page=admin">
                        <input type="text" name="product_name" placeholder="اسم المنتج" required>
                        <input type="number" name="product_price" placeholder="السعر" required>
                        <input type="text" name="product_image" placeholder="رابط الصورة" required>
                        <input type="text" name="product_category" placeholder="القسم" required>
                        <textarea name="product_description" placeholder="وصف المنتج" required></textarea>
                        <input type="number" name="product_stock" placeholder="المخزون" required>
                        <button type="submit" name="add_product">إضافة منتج</button>
                    </form>
                </div>
            </div>

        <?php elseif ($page == 'logout'): ?>
            <?php
            session_destroy();
            header("Location: ?page=home");
            exit();
            ?>
        <?php endif; ?>
    </div>
</body>
</html> 