<?php
session_start();

// بيانات المستخدمين المخزنة في مصفوفة
$users = [
    100 => ['name' => 'H', 'email' => 'H@example.com', 'password' => '321'],
    102 => ['name' => 'MO', 'email' => 'mo@example.com', 'password' => '213'],
    103 => ['name' => 'Hema', 'email' => 'hema@example.com', 'password' => '231'],
    104 => ['name' => 'Jou', 'email' => 'jou@example.com', 'password' => '123'],
    105 => ['name' => 'freelancer', 'email' => 'freelancer@example.com', 'password' => '000'],
    106 => ['name' => 'gust', 'email' => 'gust@example.com', 'password' => '111'] // إضافة المستخدم الجديد
];

// تحديد الصفحة المطلوبة
$page = $_GET['page'] ?? 'home';

// معالجة تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $page == 'login') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    foreach ($users as $id => $user) {
        if ($user['email'] == $email && $user['password'] == $password) {
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $user['name'];
            header("Location: zero_team.php?page=home");
            exit;
        }
    }
    $login_error = "❌ البريد الإلكتروني أو كلمة المرور غير صحيحة!";
}

// معالجة تسجيل الخروج
if ($page == 'logout') {
    session_destroy();
    header("Location: zero_team.php?page=login");
    exit;
}

// معالجة رفع الملفات
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $page == 'upload') {
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $upload_file = $upload_dir . basename($_FILES['file']['name']);

    if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_file)) {
        $message = "<div class='alert alert-success'>✅ تم رفع الملف بنجاح: <a href='$upload_file' target='_blank' class='link-success'>$upload_file</a></div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ فشل في رفع الملف! حاول مرة أخرى.</div>";
    }
}

// التحقق من تسجيل الدخول
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// التحقق من إذا كان المستخدم هو المدير
function is_admin() {
    return is_logged_in() && $_SESSION['user_name'] == 'H';
}

// التحقق من إذا كان المستخدم هو gust
function is_gust() {
    return is_logged_in() && $_SESSION['user_name'] == 'gust';
}
?>

<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <title>Zero Team</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
    body {
        background-color: #111;
        font-family: 'Roboto', sans-serif;
        color: #f0f0f0;
        background-image: url('https://www.transparenttextures.com/patterns/black-linen.png');
        background-size: cover;
    }

    .container {
        max-width: 950px;
        margin: 50px auto;
        padding: 20px;
        background: rgba(44, 44, 44, 0.9);
        border-radius: 12px;
        box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.5);
    }

    h2 {
        text-align: center;
        color: #00ff00;
    }

    .info,
    .upload-section,
    .login-section {
        font-size: 18px;
        padding: 15px;
        background: #333;
        border-radius: 8px;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.3);
        margin-bottom: 20px;
    }

    .error {
        color: #ff4d4d;
        font-weight: bold;
        text-align: center;
    }

    .btn-upload,
    .btn-login {
        width: 100%;
        background: linear-gradient(90deg, #00ff00 0%, #009900 100%);
        border: none;
        color: white;
        padding: 10px;
        border-radius: 5px;
        transition: 0.3s;
    }

    .btn-upload:hover,
    .btn-login:hover {
        background: linear-gradient(90deg, #009900 0%, #006600 100%);
    }

    .navbar {
        background-color: #000;
    }

    .navbar-brand {
        font-weight: bold;
        font-size: 24px;
        color: #00ff00 !important;
        text-align: center;
        width: 100%;
    }

    .footer {
        text-align: center;
        padding: 10px;
        background-color: #000;
        color: #00ff00;
        position: fixed;
        bottom: 0;
        width: 100%;
    }

    .home-description {
        font-size: 16px;
        line-height: 1.6;
        color: #e0e0e0;
        text-align: left;
    }

    .home-description h3 {
        color: #00ff00;
        margin-top: 20px;
    }

    .home-description ul {
        list-style-type: none;
        padding: 0;
    }

    .home-description ul li {
        margin-bottom: 10px;
    }
    </style>
</head>

<body>
    <?php if ($page != 'login'): ?>
    <nav class="navbar navbar-dark">
        <div class="container d-flex justify-content-center">
            <a class="navbar-brand" href="zero_team.php?page=home">Zero Team</a>
            <?php if (is_logged_in()): ?>
            <div class="d-flex">
                <?php if (!is_gust()): ?>
                <a class="navbar-brand" href="zero_team.php?page=upload">📂 رفع الملفات</a>
                <a class="navbar-brand" href="zero_team.php?page=gallery">📂 معرض الملفات</a>
                <?php endif; ?>
                <a class="navbar-brand" href="zero_team.php?page=comments">💬 التعليقات</a>
                <?php if (is_admin()): ?>
                <a class="navbar-brand" href="zero_team.php?page=admin">👥 بيانات الموظفين</a>
                <?php endif; ?>
                <a class="navbar-brand" href="zero_team.php?page=logout">🔓 تسجيل الخروج</a>
            </div>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>
    <div class="container">
        <?php if ($page == 'home'): ?>
        <div class="info home-description">
            <h2>مرحبًا بكم في Zero Team</h2>
            <p><strong>Zero Team - Cybersecurity Experts</strong></p>
            <h3>🔹 Who We Are</h3>
            <p><strong>Zero Team</strong> is a specialized cybersecurity company dedicated to protecting individuals and
                businesses from the ever-growing digital threats. We believe that cybersecurity is not an option but a
                necessity. Our mission is to provide advanced security solutions and strategies to safeguard against
                cyber attacks.</p>
            <h3>🔹 Our Team</h3>
            <ul>
                <li><strong>H (Leader & Ethical Hacker)</strong> – Founder and CEO, an expert in penetration testing and
                    social engineering, leading the team in executing advanced cybersecurity operations.</li>
                <li><strong>Hema (Cybersecurity Specialist)</strong> – Responsible for identifying vulnerabilities and
                    developing protective measures against sophisticated cyber threats.</li>
                <li><strong>Mo (Security Data Analyst)</strong> – Analyzes security data to detect unusual patterns that
                    may indicate cyber attacks.</li>
                <li><strong>Jou (Cyber Defense Expert)</strong> – Develops defense systems and implements rapid response
                    plans to mitigate cyber threats.</li>
            </ul>
            <h3>🔹 Our Services</h3>
            <ul>
                <li>✅ <strong>Penetration Testing</strong> - We simulate real-world cyber attacks to identify security
                    weaknesses in networks and applications before hackers exploit them.</li>
                <li>✅ <strong>Threat Intelligence & Risk Assessment</strong> - We help businesses assess potential risks
                    and develop defensive strategies to protect their data and infrastructure.</li>
                <li>✅ <strong>Incident Response & Digital Forensics</strong> - We provide advanced solutions to counter
                    cyber attacks, with in-depth digital forensics to track down attackers.</li>
                <li>✅ <strong>Security Monitoring & SIEM Solutions</strong> - Our AI-powered security monitoring helps
                    detect suspicious activities in systems and networks, preventing breaches before they occur.</li>
                <li>✅ <strong>Data Encryption & Secure Communication</strong> - We develop high-level encryption
                    solutions to ensure data security during storage and transmission.</li>
                <li>✅ <strong>Cybersecurity Awareness & Training</strong> - We offer specialized training programs to
                    enhance security awareness among individuals and organizations, helping them defend against cyber
                    threats.</li>
            </ul>
            <h3>🔹 Why Choose Zero Team?</h3>
            <ul>
                <li>- A team of cybersecurity experts with extensive experience.</li>
                <li>- Cutting-edge technologies to secure systems and data.</li>
                <li>- Customized solutions tailored to businesses and individuals.</li>
                <li>- Rapid response to cyber threats to ensure maximum protection.</li>
            </ul>
            <p>🚀 <strong>With Zero Team, Your Digital World is Always Secure!</strong> 🚀</p>
        </div>
        <?php elseif ($page == 'login'): ?>
        <div class="login-section">
            <h2>Welcome to Zero Team Company</h2>
            <h3>🔐 تسجيل الدخول</h3>
            <?php if(isset($login_error)) echo "<p class='error'>$login_error</p>"; ?>
            <form action="zero_team.php?page=login" method="post">
                <div class="mb-3">
                    <input class="form-control" type="email" name="email" placeholder="البريد الإلكتروني" required>
                </div>
                <div class="mb-3">
                    <input class="form-control" type="password" name="password" placeholder="كلمة المرور" required>
                </div>
                <button class="btn btn-login" type="submit">🔓 تسجيل الدخول</button>
            </form>
        </div>
        <?php elseif ($page == 'upload' && is_logged_in() && !is_gust()): ?>
        <div class="upload-section">
            <h2>📤 رفع ملف</h2>
            <?php if(isset($message)) echo $message; ?>
            <form action="zero_team.php?page=upload" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <input class="form-control" type="file" name="file" required>
                </div>
                <button class="btn btn-upload" type="submit">🚀 رفع الملف</button>
            </form>
        </div>
        <?php elseif ($page == 'gallery' && is_logged_in() && !is_gust()): ?>
        <div class="info">
            <h2>📂 معرض الملفات</h2>
            <?php
            $files = scandir('uploads/');
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    echo "<p><a href='uploads/$file' target='_blank'>$file</a></p>";
                }
            }
            ?>
        </div>
        <?php elseif ($page == 'comments' && is_logged_in()): ?>
        <div class="info">
            <h2>💬 التعليقات</h2>
            <form method='post' action='zero_team.php?page=comments'>
                <textarea name='comment' class='form-control' placeholder='اكتب تعليقك هنا...' required></textarea>
                <button type='submit' class='btn btn-upload'>إرسال</button>
            </form>
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $comment = $_POST['comment'];
                echo "<p>تعليقك: $comment</p>";
            }
            ?>
        </div>
        <?php elseif ($page == 'admin' && is_admin()): ?>
        <div class="info">
            <h2>👥 قائمة الموظفين</h2>
            <?php foreach ($users as $id => $user): ?>
            <p><a href="zero_team.php?page=profile&id=<?php echo $id; ?>"><?php echo $user['name']; ?></a></p>
            <?php endforeach; ?>
        </div>
        <?php elseif ($page == 'profile' && isset($_GET['id']) && isset($users[$_GET['id']])): ?>
        <div class="info">
            <h2>👤 بيانات المستخدم</h2>
            <p><strong>📛 الاسم:</strong> <?php echo $users[$_GET['id']]['name']; ?></p>
            <p><strong>📧 البريد الإلكتروني:</strong> <?php echo $users[$_GET['id']]['email']; ?></p>
            <p><strong>🔑 كلمة المرور:</strong> <?php echo $users[$_GET['id']]['password']; ?></p>
            <p><strong>👤 النوع:</strong>
                <?php 
                if ($users[$_GET['id']]['name'] == 'H') {
                    echo 'Admin';
                } elseif ($users[$_GET['id']]['name'] == 'freelancer') {
                    echo 'User';
                } elseif ($users[$_GET['id']]['name'] == 'gust') {
                    echo 'Guest';
                } else {
                    echo 'Member';
                }
                ?>
            </p>
        </div>
        <?php else: ?>
        <p class="error">❌ الصفحة غير متاحة!</p>
        <?php endif; ?>
    </div>

    <div class="footer">
        © 2023 Zero Team. All rights reserved.
    </div>
</body>

</html>