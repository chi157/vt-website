<?php
require_once 'backend/config.php';

if (isLoggedIn()) {
    header('Location: preorder.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = cleanInput($_POST['username'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = cleanInput($_POST['phone'] ?? '');
    
    // 驗證
    if (empty($username) || empty($email) || empty($password) || empty($phone)) {
        $error = '所有欄位都必須填寫';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '電子郵件格式不正確';
    } elseif (strlen($password) < 6) {
        $error = '密碼至少需要 6 個字元';
    } elseif ($password !== $confirm_password) {
        $error = '密碼確認不一致';
    } else {
        try {
            // 檢查使用者名稱是否已存在
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = '使用者名稱或電子郵件已被使用';
            } else {
                // 新增使用者
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, phone) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password, $phone]);
                
                // 自動登入
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                
                header('Location: preorder.php');
                exit;
            }
        } catch (PDOException $e) {
            $error = '註冊失敗，請稍後再試';
        }
    }
}
?>
<!doctype html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>註冊 - 柒柒 chi</title>
    <link rel="icon" type="image/png" href="images/頭貼%20-%20圓形.png">
    <link rel="stylesheet" href="style.css">
    <script src="navbar.js" defer></script>
    <style>
        .form-container {
            max-width: 500px;
            margin: 80px auto 40px;
            padding: 24px;
        }
        .form-card {
            background: rgba(26, 41, 80, 0.6);
            border-radius: 16px;
            padding: 32px;
            backdrop-filter: blur(10px);
        }
        .form-title {
            color: #7dd3fc;
            font-size: 24px;
            margin-bottom: 24px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            color: #7dd3fc;
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .form-input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(125, 211, 252, 0.3);
            border-radius: 8px;
            color: white;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-input:focus {
            outline: none;
            border-color: #7dd3fc;
            box-shadow: 0 0 0 3px rgba(125, 211, 252, 0.1);
        }
        .form-error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid #ef4444;
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .form-button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .form-button:hover {
            transform: translateY(-2px);
        }
        .form-link {
            text-align: center;
            margin-top: 16px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
        }
        .form-link a {
            color: #7dd3fc;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="cloud cloud--1" aria-hidden="true"></div>
    <div class="cloud cloud--2" aria-hidden="true"></div>
    <div class="cloud cloud--3" aria-hidden="true"></div>
    <div class="cloud cloud--4" aria-hidden="true"></div>
    
    <div class="form-container">
        <div class="form-card">
            <h1 class="form-title">註冊帳號</h1>
            
            <?php if ($error): ?>
                <div class="form-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="username">使用者名稱</label>
                    <input type="text" id="username" name="username" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">電子郵件</label>
                    <input type="email" id="email" name="email" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="phone">手機號碼</label>
                    <input type="tel" id="phone" name="phone" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">密碼（至少 6 個字元）</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="confirm_password">確認密碼</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                </div>
                
                <button type="submit" class="form-button">註冊</button>
            </form>
            
            <div class="form-link">
                已經有帳號了？<a href="login.php">登入</a>
            </div>
        </div>
    </div>
    
    <script src="script.js"></script>
</body>
</html>
