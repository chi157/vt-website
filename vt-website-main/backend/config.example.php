<?php
// !!! 重要：請複製此文件為 config.local.php 並填入您的實際憑證 !!!
// config.local.php 已在 .gitignore 中，不會被提交到 Git

// 資料庫連線設定
define('DB_HOST', 'localhost');
define('DB_NAME', 'vt_website');
define('DB_USER', 'root');
define('DB_PASS', '');  // 請填入您的資料庫密碼
define('DB_CHARSET', 'utf8mb4');

// 網站設定
define('SITE_URL', 'https://vtwebsite.chi157.com');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// Google OAuth 設定
// 請到 https://console.cloud.google.com/ 建立 OAuth 2.0 憑證
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', SITE_URL . '/google-callback.php');

// Session 設定
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);

// 建立資料庫連線
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("資料庫連線失敗: " . $e->getMessage());
}

// 啟動 Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 檢查是否登入
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// 檢查是否為管理員
function isAdmin() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

// 取得當前使用者資訊
function getCurrentUser() {
    global $pdo;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, phone FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            return $user;
        }
    } catch (PDOException $e) {
        // 如果查詢失敗，返回 Session 中的基本資訊
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'] ?? '',
        'phone' => ''
    ];
}

// 清理輸入資料
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// 檔案上傳處理
function uploadFile($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'], $maxSize = 5242880) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => '檔案上傳失敗'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => '檔案大小超過限制（最大 5MB）'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'message' => '不支援的檔案格式'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = UPLOAD_DIR . $filename;
    
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => '檔案儲存失敗'];
}
?>
