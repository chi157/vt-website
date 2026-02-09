<?php
require_once 'backend/config.php';

echo "開始執行資料庫遷移...\n\n";

try {
    // 讀取 SQL 文件
    $sql = file_get_contents('backend/add_google_auth.sql');
    
    // 移除註釋和空行，分割 SQL 語句
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && strpos($stmt, '--') !== 0;
        }
    );
    
    $executedCount = 0;
    foreach ($statements as $statement) {
        if (empty(trim($statement))) continue;
        
        try {
            $pdo->exec($statement);
            $executedCount++;
            echo "✅ 執行成功: " . substr(trim($statement), 0, 60) . "...\n";
        } catch (PDOException $e) {
            // 忽略 "Duplicate column" 錯誤（欄位已存在）
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "⚠️  欄位已存在（跳過）: " . substr(trim($statement), 0, 60) . "...\n";
            } else {
                echo "❌ 執行失敗: " . $e->getMessage() . "\n";
                echo "   SQL: " . substr(trim($statement), 0, 100) . "...\n";
            }
        }
    }
    
    echo "\n✅ 遷移完成！共執行 {$executedCount} 個語句。\n\n";
    
    // 驗證欄位
    echo "驗證資料庫結構...\n";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredColumns = ['google_id', 'avatar', 'auth_provider'];
    foreach ($requiredColumns as $column) {
        if (in_array($column, $columns)) {
            echo "✅ {$column} 欄位存在\n";
        } else {
            echo "❌ {$column} 欄位不存在\n";
        }
    }
    
    echo "\n🎉 資料庫已準備好使用 Google OAuth！\n";
    echo "\n下一步：\n";
    echo "1. 前往 Google Cloud Console 取得 OAuth 憑證\n";
    echo "2. 更新 backend/config.php 中的 GOOGLE_CLIENT_ID 和 GOOGLE_CLIENT_SECRET\n";
    echo "3. 訪問 test-google-oauth.php 檢查配置\n";
    echo "4. 刪除此文件 (run-migration.php)\n";
    
} catch (Exception $e) {
    echo "❌ 錯誤: " . $e->getMessage() . "\n";
    exit(1);
}
