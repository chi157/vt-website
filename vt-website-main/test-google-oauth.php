<?php
// Google OAuth 配置測試工具
// 此檔案用於檢查 Google OAuth 配置是否正確

require_once 'backend/config.php';

echo "<h1>Google OAuth 配置檢查</h1>";
echo "<style>body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; } .success { color: green; } .error { color: red; } .warning { color: orange; } code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }</style>";

// 檢查常數是否已定義
echo "<h2>1. 配置常數檢查</h2>";

if (defined('GOOGLE_CLIENT_ID')) {
    $clientId = GOOGLE_CLIENT_ID;
    if ($clientId === 'YOUR_GOOGLE_CLIENT_ID') {
        echo "<p class='error'>❌ Google Client ID 尚未設定（仍為預設值）</p>";
    } else {
        echo "<p class='success'>✅ Google Client ID 已設定: <code>" . substr($clientId, 0, 20) . "...</code></p>";
    }
} else {
    echo "<p class='error'>❌ GOOGLE_CLIENT_ID 常數未定義</p>";
}

if (defined('GOOGLE_CLIENT_SECRET')) {
    $clientSecret = GOOGLE_CLIENT_SECRET;
    if ($clientSecret === 'YOUR_GOOGLE_CLIENT_SECRET') {
        echo "<p class='error'>❌ Google Client Secret 尚未設定（仍為預設值）</p>";
    } else {
        echo "<p class='success'>✅ Google Client Secret 已設定: <code>" . str_repeat('*', 20) . "</code></p>";
    }
} else {
    echo "<p class='error'>❌ GOOGLE_CLIENT_SECRET 常數未定義</p>";
}

if (defined('GOOGLE_REDIRECT_URI')) {
    echo "<p class='success'>✅ Redirect URI: <code>" . GOOGLE_REDIRECT_URI . "</code></p>";
} else {
    echo "<p class='error'>❌ GOOGLE_REDIRECT_URI 常數未定義</p>";
}

// 檢查 HTTPS
echo "<h2>2. HTTPS 檢查</h2>";
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
if ($isHttps) {
    echo "<p class='success'>✅ 網站使用 HTTPS</p>";
} else {
    echo "<p class='warning'>⚠️ 網站未使用 HTTPS（Google OAuth 需要 HTTPS）</p>";
}

// 檢查 cURL 擴充
echo "<h2>3. PHP 擴充檢查</h2>";
if (function_exists('curl_version')) {
    $curlVersion = curl_version();
    echo "<p class='success'>✅ cURL 已安裝（版本 " . $curlVersion['version'] . "）</p>";
} else {
    echo "<p class='error'>❌ cURL 未安裝（OAuth 需要 cURL）</p>";
}

// 檢查資料庫結構
echo "<h2>4. 資料庫結構檢查</h2>";
try {
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredColumns = ['google_id', 'avatar', 'auth_provider'];
    $missingColumns = array_diff($requiredColumns, $columns);
    
    if (empty($missingColumns)) {
        echo "<p class='success'>✅ 資料庫結構已更新（包含 Google OAuth 欄位）</p>";
    } else {
        echo "<p class='error'>❌ 資料庫缺少以下欄位: " . implode(', ', $missingColumns) . "</p>";
        echo "<p>請執行 <code>backend/add_google_auth.sql</code> 來更新資料庫</p>";
    }
    
    // 檢查 password 欄位是否可為 NULL
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'password'");
    $passwordColumn = $stmt->fetch();
    if ($passwordColumn && $passwordColumn['Null'] === 'YES') {
        echo "<p class='success'>✅ password 欄位允許 NULL（支援 Google 登入無密碼）</p>";
    } else {
        echo "<p class='warning'>⚠️ password 欄位不允許 NULL，建議執行資料庫更新</p>";
    }
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ 無法檢查資料庫: " . $e->getMessage() . "</p>";
}

// 檢查檔案是否存在
echo "<h2>5. 檔案檢查</h2>";
$files = [
    'google-callback.php' => 'Google OAuth 回調處理',
    'login.php' => '登入頁面',
    'register.php' => '註冊頁面',
    'backend/config.php' => '配置檔案'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "<p class='success'>✅ {$description}: <code>{$file}</code></p>";
    } else {
        echo "<p class='error'>❌ 檔案不存在: <code>{$file}</code></p>";
    }
}

// 總結
echo "<h2>6. 下一步</h2>";
if (!defined('GOOGLE_CLIENT_ID') || GOOGLE_CLIENT_ID === 'YOUR_GOOGLE_CLIENT_ID') {
    echo "<ol>";
    echo "<li>前往 <a href='https://console.cloud.google.com/' target='_blank'>Google Cloud Console</a></li>";
    echo "<li>建立 OAuth 2.0 憑證（詳見 GOOGLE_OAUTH_SETUP.md）</li>";
    echo "<li>更新 backend/config.php 中的憑證</li>";
    echo "<li>執行 backend/add_google_auth.sql 更新資料庫</li>";
    echo "<li>重新載入此頁面檢查配置</li>";
    echo "</ol>";
} else {
    echo "<p class='success'>✅ 配置看起來正常！您可以嘗試使用 Google 登入功能。</p>";
    echo "<p>測試步驟：</p>";
    echo "<ol>";
    echo "<li>前往 <a href='login.php'>登入頁面</a></li>";
    echo "<li>點擊「使用 Google 登入」按鈕</li>";
    echo "<li>選擇 Google 帳號並授權</li>";
    echo "<li>應該會自動登入並重定向</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<p><small>測試完成後，建議刪除此檔案以確保安全。</small></p>";
?>
