<?php
ob_start(); // بدء buffer للمخرجات لحل مشكلة headers

// ======== إعداد قاعدة البيانات ========
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "noon_clone";
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
$conn->query("CREATE DATABASE IF NOT EXISTS $db_name");
$conn->select_db($db_name);

// إنشاء الجداول (الصيغة الصحيحة) فقط إذا لم تكن موجودة
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

$conn->query("CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// إضافة بيانات أولية فقط إذا كانت الجداول فارغة
if ($conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0] == 0) {
    $conn->query("INSERT INTO users (username, password, email, is_admin) VALUES 
        ('admin', '1234', 'admin@noon.com', TRUE),
        ('user1', 'pass1', 'user1@example.com', FALSE),
        ('user2', 'pass2', 'user2@example.com', FALSE)");
}

if ($conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0] == 0) {
    $conn->query("INSERT INTO products (name, price, image_url, category, description, stock) VALUES
        ('iPhone 15 Pro', 3500, 'https://i.imgur.com/J5Q8X0p.jpg', 'electronics', 'أحدث هاتف آيفون مع كاميرا متطورة', 10),
        ('Samsung TV', 4200, 'https://i.imgur.com/L4m3Y0q.jpg', 'electronics', 'تلفاز سامسونج ذكي 4K', 5),
        ('Laptop Dell XPS', 5500, 'https://i.imgur.com/K8m5J0p.jpg', 'electronics', 'لابتوب ديل خفيف وقوي', 8),
        ('Sofa Set', 2800, 'https://i.imgur.com/L3n4Y0q.jpg', 'furniture', 'مجموعة كنب فاخرة', 3),
        ('Coffee Table', 1200, 'https://i.imgur.com/M5n8Y0q.jpg', 'furniture', 'طاولة قهوة خشبية', 7)");
}

// ======== معالجة الطلبات ========
session_start();
$page = $_GET['page'] ?? 'home';

// معالجة تسجيل الدخول
$login_message = "";
$leaked_data = "";

if ($page == 'login' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        if (strpos($username, "' OR '1'='1") !== false || strpos($username, "' OR 1=1--") !== false) {
            $leaked_data .= "<div class='leaked-data-container'>";
            $leaked_data .= "<h3 class='leaked-title'>بيانات المستخدمين المسربة:</h3>";
            $leaked_data .= "<table class='leaked-table'>";
            $leaked_data .= "<tr><th>ID</th><th>Username</th><th>Password</th><th>Email</th><th>Admin</th></tr>";
            
            while($user = $result->fetch_assoc()) {
                $leaked_data .= "<tr>";
                $leaked_data .= "<td>".htmlspecialchars($user['id'])."</td>";
                $leaked_data .= "<td>".htmlspecialchars($user['username'])."</td>";
                $leaked_data .= "<td>".htmlspecialchars($user['password'])."</td>";
                $leaked_data .= "<td>".htmlspecialchars($user['email'])."</td>";
                $leaked_data .= "<td>".($user['is_admin'] ? 'نعم' : 'لا')."</td>";
                $leaked_data .= "</tr>";
            }
            
            $leaked_data .= "</table>";
            $leaked_data .= "<p class='leaked-warning'>هذه النتيجة بسبب ثغرة SQL Injection</p>";
            $leaked_data .= "</div>";
        } else {
            $user_data = $result->fetch_assoc();
            $_SESSION['user'] = $user_data['username'];
            $_SESSION['is_admin'] = $user_data['is_admin'];
            $login_message = "✅ تم الدخول بنجاح كـ: " . htmlspecialchars($_SESSION['user']);
        }
    } else {
        $login_message = "❌ فشل الدخول! تأكد من اسم المستخدم وكلمة المرور.";
    }
}

// معالجة نموذج الاتصال
$contact_message = "";
if ($page == 'contact' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $contact_message = $_POST['message'];
}

// إضافة منتج جديد
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

// حذف منتج
if ($page == 'admin' && isset($_POST['delete_product'])) {
    $product_id = (int)$_POST['product_id'];
    if ($product_id > 0) {
        $sql = "DELETE FROM products WHERE id = $product_id";
        if ($conn->query($sql)) {
            echo "<script>alert('تم حذف المنتج بنجاح')</script>";
            echo "<script>window.location = window.location.href;</script>";
        }
    }
}

// صفحة المنتج
if ($page == 'product') {
    $product_id = $_GET['id'] ?? 0;
    $sql = "SELECT * FROM products WHERE id = $product_id";
    $result = $conn->query($sql);
}

// صفحة السلة
if ($page == 'cart') {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    $cart = $_SESSION['cart'];

    $action = $_GET['action'] ?? null;
    if ($action == 'add' && isset($_GET['id'])) {
        $product_id = (int)$_GET['id'];
        $cart[$product_id] = ($cart[$product_id] ?? 0) + 1;
        $_SESSION['cart'] = $cart;
    }
    elseif ($action == 'remove' && isset($_GET['id'])) {
        $product_id = (int)$_GET['id'];
        if (isset($cart[$product_id])) unset($cart[$product_id]);
        $_SESSION['cart'] = $cart;
    }
}

// صفحة تسجيل الخروج
if ($page == 'logout') {
    session_destroy();
    header("Location: ?page=home");
    exit();
}

ob_end_flush(); // إرسال المحتوى المخزن في الـ buffer
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>متجر نون</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f5f5f5; }
        header { background: #0000ff; color: white; padding: 15px; text-align: center; }
        nav { 
            background: #333; 
            overflow: hidden;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        nav a { color: white; padding: 14px 16px; display: inline-block; text-decoration: none; }
        nav a:hover { background: #555; }
        .product { 
            background: white; 
            margin: 15px; 
            padding: 15px; 
            border-radius: 5px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
            display: inline-block; 
            width: 250px; 
            vertical-align: top;
        }
        .product img { width: 100%; height: 200px; object-fit: cover; border-radius: 5px; }
        .product h3 { color: #333; margin-top: 10px; }
        .product p { color: #666; margin: 5px 0; }
        .product a { 
            display: inline-block; 
            margin-top: 10px; 
            padding: 8px 12px; 
            background: #0000ff; 
            color: white; 
            text-decoration: none; 
            border-radius: 4px; 
        }
        .product a:hover { background: #3333ff; }
        main { padding: 20px; }
        h2 { margin-top: 0; color: #333; }
        form { margin: 20px 0; max-width: 500px; }
        input, textarea, select { 
            width: 100%; 
            padding: 10px; 
            margin: 8px 0; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            box-sizing: border-box;
        }
        button { 
            background: #0000ff; 
            color: white; 
            padding: 10px 15px; 
            border: none; 
            cursor: pointer; 
            border-radius: 4px; 
            font-size: 16px;
        }
        button:hover { background: #3333ff; }
        .admin-panel { background: white; padding: 20px; border-radius: 5px; }
        .message { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .cart-item { background: white; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
        .product-details { display: flex; margin-bottom: 20px; }
        .product-image { flex: 1; }
        .product-info { flex: 2; padding: 0 20px; }
        .leaked-data-container {
            background: #fff3f3;
            padding: 20px;
            margin: 30px 0;
            border: 2px dashed red;
            border-radius: 5px;
            direction: ltr;
            text-align: left;
        }
        .leaked-title {
            color: red;
            text-align: center;
            margin-top: 0;
        }
        .leaked-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .leaked-table th {
            background: #333;
            color: white;
            padding: 10px;
            text-align: left;
        }
        .leaked-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            background: white;
        }
        .leaked-table tr:nth-child(even) td {
            background: #f9f9f9;
        }
        .leaked-warning {
            color: red;
            font-weight: bold;
            text-align: center;
            margin-bottom: 0;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .test-instructions {
            margin-top:20px;
            padding:15px;
            background:#f0f7ff;
            border-radius:5px;
        }
    </style>
    <script>
        function confirmDelete() {
            return confirm('هل أنت متأكد من حذف هذا المنتج؟');
        }
    </script>
</head>
<body>
    <header>
        <h1>متجر نون</h1>
    </header>
    <nav>
        <a href="?page=home">الرئيسية</a>
        <a href="?page=products">المنتجات</a>
        <a href="?page=cart">السلة</a>
        <a href="?page=contact">اتصل بنا</a>
        <?php if (isset($_SESSION['user'])): ?>
            <?php if ($_SESSION['is_admin']): ?>
                <a href="?page=admin">لوحة التحكم</a>
            <?php endif; ?>
            <a href="?page=logout">تسجيل الخروج</a>
        <?php else: ?>
            <a href="?page=login">تسجيل الدخول</a>
        <?php endif; ?>
    </nav>

    <main>
        <?php if ($page == 'home'): ?>
            <h2>أهلاً بك في متجر نون!</h2>
            <p>تصفح أفضل المنتجات لدينا</p>
            
            <h3>أحدث المنتجات</h3>
            <div class="products-grid">
                <?php
                $result = $conn->query("SELECT * FROM products LIMIT 3");
                while ($row = $result->fetch_assoc()): ?>
                    <div class="product">
                        <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                        <h3><?= htmlspecialchars($row['name']) ?></h3>
                        <p>السعر: <?= htmlspecialchars($row['price']) ?> ر.س</p>
                        <p>المخزون: <?= htmlspecialchars($row['stock']) ?></p>
                        <a href="?page=product&id=<?= $row['id'] ?>">التفاصيل</a>
                        <a href="?page=cart&action=add&id=<?= $row['id'] ?>">أضف للسلة</a>
                    </div>
                <?php endwhile; ?>
            </div>

        <?php elseif ($page == 'products'): ?>
            <h2>منتجاتنا</h2>
            <div class="products-grid">
                <?php
                $result = $conn->query("SELECT * FROM products");
                while ($row = $result->fetch_assoc()): ?>
                    <div class="product">
                        <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                        <h3><?= htmlspecialchars($row['name']) ?></h3>
                        <p>السعر: <?= htmlspecialchars($row['price']) ?> ر.س</p>
                        <p>المخزون: <?= htmlspecialchars($row['stock']) ?></p>
                        <a href="?page=product&id=<?= $row['id'] ?>">التفاصيل</a>
                        <a href="?page=cart&action=add&id=<?= $row['id'] ?>">أضف للسلة</a>
                    </div>
                <?php endwhile; ?>
            </div>

        <?php elseif ($page == 'product' && isset($result) && $result->num_rows > 0): ?>
            <?php $product = $result->fetch_assoc(); ?>
            <h2><?= htmlspecialchars($product['name']) ?></h2>
            <div class="product-details">
                <div class="product-image">
                    <img src="<?= htmlspecialchars($product['image_url']) ?>" style="max-width: 400px;">
                </div>
                <div class="product-info">
                    <p><strong>السعر:</strong> <?= htmlspecialchars($product['price']) ?> ر.س</p>
                    <p><strong>الوصف:</strong> <?= htmlspecialchars($product['description']) ?></p>
                    <p><strong>المخزون:</strong> <?= htmlspecialchars($product['stock']) ?></p>
                    <p><strong>الفئة:</strong> <?= htmlspecialchars($product['category']) ?></p>
                    <a href="?page=cart&action=add&id=<?= $product['id'] ?>" style="background: #0000ff; color: white; padding: 10px; display: inline-block;">أضف إلى السلة</a>
                </div>
            </div>

        <?php elseif ($page == 'cart'): ?>
            <h2>سلة التسوق</h2>
            <?php if (empty($_SESSION['cart'])): ?>
                <p>سلة التسوق فارغة</p>
                <a href="?page=products">تصفح المنتجات</a>
            <?php else: ?>
                <?php 
                $total = 0;
                foreach ($_SESSION['cart'] as $id => $quantity): 
                    $product = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();
                    $subtotal = $quantity * $product['price'];
                    $total += $subtotal;
                ?>
                    <div class="cart-item">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <p>الكمية: <?= $quantity ?></p>
                        <p>سعر الوحدة: <?= $product['price'] ?> ر.س</p>
                        <p>الإجمالي: <?= $subtotal ?> ر.س</p>
                        <a href="?page=cart&action=remove&id=<?= $id ?>" style="color: red;">حذف من السلة</a>
                    </div>
                <?php endforeach; ?>
                
                <div style="background: white; padding: 15px; border-radius: 5px; margin-top: 20px;">
                    <h3>إجمالي الطلب: <?= $total ?> ر.س</h3>
                    <button style="background: green;">إتمام الشراء</button>
                </div>
            <?php endif; ?>

        <?php elseif ($page == 'login'): ?>
            <h2>تسجيل الدخول</h2>
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
            
            <div class="test-instructions">
                <h3 style="margin-top:0;">كيفية اختبار الثغرة:</h3>
                <p>في حقل اسم المستخدم أدخل:</p>
                <code>' OR '1'='1</code>
                <p>واترك كلمة المرور فارغة</p>
            </div>

        <?php elseif ($page == 'contact'): ?>
            <h2>اتصل بنا</h2>
            <form method="POST">
                <input type="text" name="name" placeholder="اسمك" required>
                <input type="email" name="email" placeholder="بريدك الإلكتروني" required>
                <textarea name="message" placeholder="رسالتك..." required></textarea>
                <button type="submit">إرسال</button>
                <?php if (!empty($contact_message)): ?>
                    <div class="message">
                        <h3>تم استلام رسالتك:</h3>
                        <p><?= $contact_message ?></p>
                    </div>
                <?php endif; ?>
            </form>

        <?php elseif ($page == 'admin' && isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <div class="admin-panel">
                <h2>لوحة التحكم</h2>
                
                <h3>إضافة منتج جديد</h3>
                <form method="POST">
                    <input type="text" name="product_name" placeholder="اسم المنتج" required>
                    <input type="number" name="product_price" placeholder="السعر" step="0.01" required>
                    <input type="text" name="product_image" placeholder="رابط الصورة" required>
                    <input type="text" name="product_category" placeholder="القسم" required>
                    <textarea name="product_description" placeholder="الوصف" required></textarea>
                    <input type="number" name="product_stock" placeholder="المخزون" required>
                    <button type="submit" name="add_product">إضافة</button>
                </form>
                
                <h3>قائمة المنتجات</h3>
                <div class="products-grid">
                    <?php
                    $products = $conn->query("SELECT * FROM products");
                    while ($product = $products->fetch_assoc()): ?>
                        <div class="product">
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <p>السعر: <?= htmlspecialchars($product['price']) ?> ر.س</p>
                            <p>المخزون: <?= htmlspecialchars($product['stock']) ?></p>
                            <form method="POST" onsubmit="return confirmDelete()">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <button type="submit" name="delete_product" style="background: red;">حذف</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

        <?php else: ?>
            <h2>الصفحة غير موجودة</h2>
            <p>عذراً، الصفحة التي تبحث عنها غير موجودة.</p>
            <a href="?page=home">العودة إلى الصفحة الرئيسية</a>
        <?php endif; ?>
        
        <?php if (!empty($leaked_data)): ?>
            <?= $leaked_data ?>
        <?php endif; ?>
    </main>
</body>
</html>