<?php
// 資料庫連線設定
define('DB_HOST', 'localhost');        // 資料庫主機
define('DB_NAME', 'vt_website');       // 資料庫名稱（請修改為你的資料庫名稱）
define('DB_USER', 'root');             // 資料庫使用者名稱（請修改）
define('DB_PASS', '123456789');                 // 資料庫密碼（請修改）
define('DB_CHARSET', 'utf8mb4');

// 網站設定
define('SITE_URL', 'https://vtwebsite.chi157.com'); // 網站網址
define('UPLOAD_DIR', __DIR__ . '/../uploads/'); // 上傳檔案目錄

// Session 設定
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // HTTPS 使用

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
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'] ?? ''
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
    finfo_close($finfo);
    
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
