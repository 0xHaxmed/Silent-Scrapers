<?php
session_start();

// إعدادات قاعدة البيانات
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ethical_hacking_training');

// الاتصال بقاعدة البيانات
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// تحديد الصفحة الحالية
$current_page = isset($_GET['page']) ? $_GET['page'] : 'home';

// التحقق من صلاحيات المستخدم
$is_admin = false;
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    $sql = "SELECT is_admin FROM users WHERE id = " . $_SESSION["id"];
    $result = mysqli_query($conn, $sql);
    if($result) {
        $row = mysqli_fetch_assoc($result);
        $is_admin = $row['is_admin'];
    }
}

// إذا لم يكن المستخدم مسجلاً، اجعل الصفحة الحالية هي صفحة تسجيل الدخول
if(!isset($_SESSION["loggedin"]) && $current_page != 'login' && $current_page != 'register') {
    $current_page = 'login';
}

// إذا حاول المستخدم العادي الوصول لصفحة إدارة المستخدمين، قم بتوجيهه للصفحة الرئيسية
if($current_page == 'manage_users' && !$is_admin) {
    header("Location: index.php?page=home");
    exit;
}

// معالجة تسجيل الدخول
if(isset($_POST['login'])) {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    
    // ثغرة SQL Injection متعمدة
    $sql = "SELECT id, username, password FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);
    
    if(mysqli_num_rows($result) == 1){
        $row = mysqli_fetch_array($result);
        if(password_verify($password, $row['password'])){
            $_SESSION["loggedin"] = true;
            $_SESSION["id"] = $row["id"];
            $_SESSION["username"] = $row["username"];
            header("Location: index.php?page=home");
            exit;
        }
    }
}

// معالجة التسجيل
if(isset($_POST['register'])) {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    
    // ثغرة SQL Injection متعمدة
    $sql = "INSERT INTO users (username, password, is_admin) VALUES ('$username', '" . password_hash($password, PASSWORD_DEFAULT) . "', 0)";
    mysqli_query($conn, $sql);
    header("Location: index.php?page=login");
    exit;
}

// معالجة تسجيل الخروج
if(isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php?page=login");
    exit;
}

// معالجة إضافة مقال
if(isset($_POST['add_article']) && isset($_SESSION["loggedin"])) {
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);
    $author_id = $_SESSION["id"];
    
    // ثغرة SQL Injection متعمدة
    $sql = "INSERT INTO articles (title, content, author_id) VALUES ('$title', '$content', $author_id)";
    mysqli_query($conn, $sql);
    header("Location: index.php?page=articles");
    exit;
}

// معالجة إضافة تعليق
if(isset($_POST['add_comment'])) {
    $article_id = $_POST["article_id"];
    $comment = $_POST["comment"];
    $user_id = isset($_SESSION["id"]) ? $_SESSION["id"] : 0;
    
    // ثغرة XSS متعمدة
    $sql = "INSERT INTO comments (article_id, user_id, comment) VALUES ($article_id, $user_id, '$comment')";
    mysqli_query($conn, $sql);
    header("Location: index.php?page=articles");
    exit;
}

// معالجة رفع الملفات
if(isset($_POST['upload']) && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $filename = time() . '_' . $file['name']; // إضافة timestamp لتجنب تكرار الأسماء
    $original_name = $file['name'];
    $file_size = $file['size'];
    $file_type = $file['type'];
    $user_id = $_SESSION['id'];
    
    // ثغرة Command Injection متعمدة
    $command = "move " . $file['tmp_name'] . " uploads/" . $filename;
    exec($command);
    
    // حفظ معلومات الملف في قاعدة البيانات
    $sql = "INSERT INTO uploaded_files (filename, original_name, file_size, file_type, user_id) 
            VALUES ('$filename', '$original_name', $file_size, '$file_type', $user_id)";
    mysqli_query($conn, $sql);
    
    header("Location: index.php?page=upload");
    exit;
}

// معالجة البحث
if(isset($_GET['search'])) {
    $search = $_GET['search'];
    
    // ثغرة SQL Injection متعمدة
    $sql = "SELECT * FROM articles WHERE title LIKE '%$search%' OR content LIKE '%$search%'";
    $search_result = mysqli_query($conn, $sql);
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>موقع تدريب الهكر الأخلاقي</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Arial', sans-serif;
        line-height: 1.6;
        background-color: #f4f4f4;
    }

    header {
        background-color: #333;
        color: #fff;
        padding: 1rem;
        text-align: center;
    }

    nav {
        margin-top: 1rem;
    }

    nav a {
        color: #fff;
        text-decoration: none;
        padding: 0.5rem 1rem;
        margin: 0 0.5rem;
    }

    nav a:hover {
        background-color: #555;
        border-radius: 3px;
    }

    main {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    article {
        background-color: #fff;
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-control {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 3px;
    }

    .btn {
        display: inline-block;
        padding: 0.5rem 1rem;
        background-color: #333;
        color: #fff;
        border: none;
        border-radius: 3px;
        cursor: pointer;
        text-decoration: none;
        margin: 0.5rem;
    }

    .btn:hover {
        background-color: #555;
    }

    .alert {
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 3px;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    footer {
        text-align: center;
        padding: 1rem;
        background-color: #333;
        color: #fff;
        position: fixed;
        bottom: 0;
        width: 100%;
    }

    .section {
        display: none;
        background-color: #fff;
        padding: 2rem;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .section.active {
        display: block;
    }

    .quick-links {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 2rem;
    }

    .profile-info {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 5px;
        margin-bottom: 2rem;
    }

    .comments {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #ddd;
    }

    .comment {
        background-color: #f8f9fa;
        padding: 1rem;
        margin-top: 1rem;
        border-radius: 3px;
    }

    .uploaded-files {
        margin-top: 2rem;
    }

    .files-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        background-color: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .files-table th,
    .files-table td {
        padding: 0.75rem;
        text-align: right;
        border-bottom: 1px solid #ddd;
    }

    .files-table th {
        background-color: #f8f9fa;
        font-weight: bold;
    }

    .files-table tr:hover {
        background-color: #f5f5f5;
    }

    .files-table .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .users-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        background-color: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .users-table th,
    .users-table td {
        padding: 0.75rem;
        text-align: right;
        border-bottom: 1px solid #ddd;
    }

    .users-table th {
        background-color: #f8f9fa;
        font-weight: bold;
    }

    .users-table tr:hover {
        background-color: #f5f5f5;
    }

    .user-info {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 5px;
        margin-bottom: 2rem;
    }

    .user-articles,
    .user-files,
    .user-comments {
        margin-bottom: 2rem;
    }

    .user-articles .article,
    .user-files .file,
    .user-comments .comment {
        background-color: #fff;
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 5px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    </style>
</head>

<body>
    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
    <header>
        <h1>موقع تدريب الهكر الأخلاقي</h1>
        <nav>
            <a href="?page=home">الرئيسية</a>
            <a href="?page=articles">المقالات</a>
            <a href="?page=add_article">إضافة مقال</a>
            <a href="?page=upload">رفع ملف</a>
            <a href="?page=profile">الملف الشخصي</a>
            <?php if($is_admin): ?>
            <a href="?page=manage_users">إدارة المستخدمين</a>
            <?php endif; ?>
            <a href="?logout=1">تسجيل الخروج</a>
        </nav>
    </header>
    <?php endif; ?>

    <main>
        <!-- صفحة تسجيل الدخول -->
        <section id="login" class="section <?php echo $current_page == 'login' ? 'active' : ''; ?>">
            <h2>تسجيل الدخول</h2>
            <form method="post">
                <div class="form-group">
                    <label>اسم المستخدم</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>كلمة المرور</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" name="login" class="btn">تسجيل الدخول</button>
                <a href="?page=register" class="btn">مستخدم جديد؟ سجل هنا</a>
            </form>
        </section>

        <!-- صفحة التسجيل -->
        <section id="register" class="section <?php echo $current_page == 'register' ? 'active' : ''; ?>">
            <h2>تسجيل جديد</h2>
            <form method="post">
                <div class="form-group">
                    <label>اسم المستخدم</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>كلمة المرور</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" name="register" class="btn">تسجيل</button>
                <a href="?page=login" class="btn">لديك حساب؟ سجل دخول</a>
            </form>
        </section>

        <!-- الصفحة الرئيسية -->
        <section id="home" class="section <?php echo $current_page == 'home' ? 'active' : ''; ?>">
            <h2>الصفحة الرئيسية</h2>
            <p>مرحباً <?php echo $_SESSION['username']; ?></p>
            <div class="quick-links">
                <a href="?page=articles" class="btn">عرض المقالات</a>
                <a href="?page=add_article" class="btn">إضافة مقال جديد</a>
                <a href="?page=upload" class="btn">رفع ملف</a>
            </div>
        </section>

        <!-- صفحة المقالات -->
        <section id="articles" class="section <?php echo $current_page == 'articles' ? 'active' : ''; ?>">
            <h2>المقالات</h2>
            <form method="get" class="search-form">
                <div class="form-group">
                    <input type="text" name="search" class="form-control" placeholder="ابحث هنا...">
                </div>
                <button type="submit" class="btn">بحث</button>
            </form>

            <?php
            if(isset($search_result)) {
                $result = $search_result;
            } else {
                $sql = "SELECT articles.*, users.username FROM articles 
                        LEFT JOIN users ON articles.author_id = users.id 
                        ORDER BY created_at DESC";
                $result = mysqli_query($conn, $sql);
            }
            
            while($row = mysqli_fetch_assoc($result)):
            ?>
            <article>
                <h3><?php echo $row['title']; ?></h3>
                <p>بواسطة: <?php echo $row['username']; ?></p>
                <p>تاريخ النشر: <?php echo $row['created_at']; ?></p>
                <div class="content">
                    <?php echo $row['content']; ?>
                </div>

                <div class="comments">
                    <h4>التعليقات</h4>
                    <form method="post">
                        <input type="hidden" name="article_id" value="<?php echo $row['id']; ?>">
                        <div class="form-group">
                            <textarea name="comment" class="form-control" placeholder="اكتب تعليقك هنا..."></textarea>
                        </div>
                        <button type="submit" name="add_comment" class="btn">إضافة تعليق</button>
                    </form>

                    <?php
                    $sql = "SELECT comments.*, users.username FROM comments 
                            LEFT JOIN users ON comments.user_id = users.id 
                            WHERE article_id = " . $row['id'] . " 
                            ORDER BY created_at DESC";
                    $comments = mysqli_query($conn, $sql);
                    while($comment = mysqli_fetch_assoc($comments)):
                    ?>
                    <div class="comment">
                        <p><?php echo $comment['comment']; ?></p>
                        <small>بواسطة: <?php echo $comment['username']; ?></small>
                    </div>
                    <?php endwhile; ?>
                </div>
            </article>
            <?php endwhile; ?>
        </section>

        <!-- صفحة إضافة مقال -->
        <section id="add_article" class="section <?php echo $current_page == 'add_article' ? 'active' : ''; ?>">
            <h2>إضافة مقال جديد</h2>
            <form method="post">
                <div class="form-group">
                    <label>عنوان المقال</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>محتوى المقال</label>
                    <textarea name="content" class="form-control" required></textarea>
                </div>
                <button type="submit" name="add_article" class="btn">إضافة المقال</button>
            </form>
        </section>

        <!-- صفحة رفع الملفات -->
        <section id="upload" class="section <?php echo $current_page == 'upload' ? 'active' : ''; ?>">
            <h2>رفع ملف</h2>
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>اختر الملف</label>
                    <input type="file" name="file" class="form-control" required>
                </div>
                <button type="submit" name="upload" class="btn">رفع الملف</button>
            </form>

            <div class="uploaded-files">
                <h3>الملفات المرفوعة</h3>
                <?php
                $sql = "SELECT uf.*, users.username 
                        FROM uploaded_files uf 
                        LEFT JOIN users ON uf.user_id = users.id 
                        ORDER BY upload_date DESC";
                $result = mysqli_query($conn, $sql);
                
                if(mysqli_num_rows($result) > 0):
                ?>
                <table class="files-table">
                    <thead>
                        <tr>
                            <th>اسم الملف</th>
                            <th>الحجم</th>
                            <th>النوع</th>
                            <th>تاريخ الرفع</th>
                            <th>بواسطة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($file = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($file['original_name']); ?></td>
                            <td><?php echo number_format($file['file_size'] / 1024, 2) . ' KB'; ?></td>
                            <td><?php echo htmlspecialchars($file['file_type']); ?></td>
                            <td><?php echo $file['upload_date']; ?></td>
                            <td><?php echo htmlspecialchars($file['username']); ?></td>
                            <td>
                                <a href="uploads/<?php echo $file['filename']; ?>" class="btn" download>تحميل</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>لا توجد ملفات مرفوعة حالياً</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- صفحة الملف الشخصي -->
        <section id="profile" class="section <?php echo $current_page == 'profile' ? 'active' : ''; ?>">
            <h2>الملف الشخصي</h2>
            <div class="profile-info">
                <p>اسم المستخدم: <?php echo $_SESSION['username']; ?></p>
                <p>تاريخ التسجيل: <?php 
                    $sql = "SELECT created_at FROM users WHERE id = " . $_SESSION['id'];
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                    echo $row['created_at'];
                ?></p>
            </div>

            <h3>مقالاتي</h3>
            <?php
            $sql = "SELECT * FROM articles WHERE author_id = " . $_SESSION['id'] . " ORDER BY created_at DESC";
            $result = mysqli_query($conn, $sql);
            while($row = mysqli_fetch_assoc($result)):
            ?>
            <article>
                <h4><?php echo $row['title']; ?></h4>
                <p>تاريخ النشر: <?php echo $row['created_at']; ?></p>
            </article>
            <?php endwhile; ?>
        </section>

        <!-- صفحة إدارة المستخدمين -->
        <section id="manage_users" class="section <?php echo $current_page == 'manage_users' ? 'active' : ''; ?>">
            <h2>إدارة المستخدمين</h2>
            <?php
            $sql = "SELECT * FROM users ORDER BY created_at DESC";
            $result = mysqli_query($conn, $sql);
            ?>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>اسم المستخدم</th>
                        <th>تاريخ التسجيل</th>
                        <th>نوع الحساب</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo $user['created_at']; ?></td>
                        <td><?php echo $user['is_admin'] ? 'مدير' : 'مستخدم عادي'; ?></td>
                        <td>
                            <a href="?page=user_details&id=<?php echo $user['id']; ?>" class="btn">تفاصيل</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- صفحة تفاصيل المستخدم -->
        <section id="user_details" class="section <?php echo $current_page == 'user_details' ? 'active' : ''; ?>">
            <h2>تفاصيل المستخدم</h2>
            <?php
            if(isset($_GET['id'])) {
                $user_id = $_GET['id'];
                
                // معلومات المستخدم
                $sql = "SELECT * FROM users WHERE id = $user_id";
                $result = mysqli_query($conn, $sql);
                $user = mysqli_fetch_assoc($result);
                
                if($user):
            ?>
            <div class="user-info">
                <h3>معلومات المستخدم</h3>
                <p>اسم المستخدم: <?php echo htmlspecialchars($user['username']); ?></p>
                <p>تاريخ التسجيل: <?php echo $user['created_at']; ?></p>
                <p>نوع الحساب: <?php echo $user['is_admin'] ? 'مدير' : 'مستخدم عادي'; ?></p>
            </div>

            <div class="user-articles">
                <h3>المقالات</h3>
                <?php
                    $sql = "SELECT * FROM articles WHERE author_id = $user_id ORDER BY created_at DESC";
                    $articles = mysqli_query($conn, $sql);
                    while($article = mysqli_fetch_assoc($articles)):
                    ?>
                <div class="article">
                    <h4><?php echo htmlspecialchars($article['title']); ?></h4>
                    <p>تاريخ النشر: <?php echo $article['created_at']; ?></p>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="user-files">
                <h3>الملفات المرفوعة</h3>
                <?php
                    $sql = "SELECT * FROM uploaded_files WHERE user_id = $user_id ORDER BY upload_date DESC";
                    $files = mysqli_query($conn, $sql);
                    while($file = mysqli_fetch_assoc($files)):
                    ?>
                <div class="file">
                    <p>اسم الملف: <?php echo htmlspecialchars($file['original_name']); ?></p>
                    <p>الحجم: <?php echo number_format($file['file_size'] / 1024, 2) . ' KB'; ?></p>
                    <p>تاريخ الرفع: <?php echo $file['upload_date']; ?></p>
                    <a href="uploads/<?php echo $file['filename']; ?>" class="btn" download>تحميل</a>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="user-comments">
                <h3>التعليقات</h3>
                <?php
                    $sql = "SELECT comments.*, articles.title as article_title 
                            FROM comments 
                            LEFT JOIN articles ON comments.article_id = articles.id 
                            WHERE comments.user_id = $user_id 
                            ORDER BY comments.created_at DESC";
                    $comments = mysqli_query($conn, $sql);
                    while($comment = mysqli_fetch_assoc($comments)):
                    ?>
                <div class="comment">
                    <p>على مقال: <?php echo htmlspecialchars($comment['article_title']); ?></p>
                    <p>التعليق: <?php echo htmlspecialchars($comment['comment']); ?></p>
                    <p>التاريخ: <?php echo $comment['created_at']; ?></p>
                </div>
                <?php endwhile; ?>
            </div>
            <?php 
                endif;
            }
            ?>
        </section>
    </main>

    <footer>
        <p>جميع الحقوق محفوظة &copy; 2024</p>
    </footer>

    <script>
    // إظهار القسم المطلوب عند تحميل الصفحة
    document.addEventListener('DOMContentLoaded', function() {
        const sections = document.querySelectorAll('.section');
        sections.forEach(section => {
            if (section.classList.contains('active')) {
                section.style.display = 'block';
            }
        });
    });
    </script>
</body>

</html>