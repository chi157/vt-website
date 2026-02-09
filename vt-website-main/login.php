<?php
require_once 'backend/config.php';

if (isLoggedIn()) {
    header('Location: preorder.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = cleanInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = '請輸入使用者名稱和密碼';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, email, password FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                header('Location: preorder.php');
                exit;
            } else {
                $error = '使用者名稱或密碼錯誤';
            }
        } catch (PDOException $e) {
            $error = '登入失敗，請稍後再試';
        }
    }
}
?>
<!doctype html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>登入 - 柒柒 chi</title>
    <link rel="icon" type="image/png" href="images/頭貼%20-%20圓形.png">
    <link rel="stylesheet" href="style.css">
    <script src="navbar.js" defer></script>
</head>
<body>
    <div class="cloud cloud--1" aria-hidden="true"></div>
    <div class="cloud cloud--2" aria-hidden="true"></div>
    <div class="cloud cloud--3" aria-hidden="true"></div>
    <div class="cloud cloud--4" aria-hidden="true"></div>
    
    <main class="page">
        <div class="form-container">
            <div class="form-card">
                <h1 class="form-title">登入</h1>
                
                <?php if ($error): ?>
                    <div class="form-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label" for="username">使用者名稱或電子郵件</label>
                        <input type="text" id="username" name="username" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password">密碼</label>
                        <input type="password" id="password" name="password" class="form-input" required>
                    </div>
                    
                    <button type="submit" class="form-button">登入</button>
                </form>
                
                <div class="form-link">
                    還沒有帳號？<a href="register.php">註冊</a>
                </div>
            </div>
        </div>
    </main>
    
    <script src="script.js"></script>
</body>
</html>
