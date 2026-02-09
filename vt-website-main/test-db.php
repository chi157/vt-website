<?php
// 測試資料庫連線和管理員帳號
require_once 'backend/config.php';

echo "<h2>資料庫連線測試</h2>";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS
    );
    echo "✅ 資料庫連線成功！<br><br>";
    
    // 檢查資料表是否存在
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<h3>現有資料表：</h3>";
    echo "<pre>";
    print_r($tables);
    echo "</pre>";
    
    // 檢查 admins 資料表
    if (in_array('admins', $tables)) {
        echo "<h3>管理員資料：</h3>";
        $admins = $pdo->query("SELECT id, username, created_at FROM admins")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($admins);
        echo "</pre>";
        
        // 測試密碼驗證
        echo "<h3>密碼驗證測試：</h3>";
        $stmt = $pdo->prepare("SELECT password FROM admins WHERE username = 'admin'");
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            echo "找到管理員帳號<br>";
            echo "密碼雜湊：" . $admin['password'] . "<br>";
            
            // 測試密碼 'password'
            if (password_verify('password', $admin['password'])) {
                echo "✅ 密碼 'password' 驗證成功！<br>";
            } else {
                echo "❌ 密碼 'password' 驗證失敗<br>";
                echo "正在更新為正確的密碼...<br>";
                
                // 更新密碼
                $newHash = password_hash('password', PASSWORD_DEFAULT);
                $updateStmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
                $updateStmt->execute([$newHash]);
                
                echo "✅ 密碼已更新！請重新登入<br>";
            }
        } else {
            echo "❌ 找不到管理員帳號，正在建立...<br>";
            
            $hash = password_hash('password', PASSWORD_DEFAULT);
            $insertStmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES ('admin', ?)");
            $insertStmt->execute([$hash]);
            
            echo "✅ 管理員帳號已建立！<br>";
        }
    } else {
        echo "❌ admins 資料表不存在！請匯入 database.sql<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ 資料庫連線失敗：" . $e->getMessage();
}

echo "<br><br><a href='admin.php'>前往管理後台</a>";
?>
