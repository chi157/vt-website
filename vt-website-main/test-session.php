<?php
// 顯示 PHP 錯誤（測試用）
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'backend/config.php';

echo "<h2>Session 測試</h2>";
echo "Session 狀態：" . session_status() . "<br>";
echo "Session ID：" . session_id() . "<br><br>";

if (isset($_SESSION['admin_id'])) {
    echo "✅ 已登入<br>";
    echo "管理員 ID：" . $_SESSION['admin_id'] . "<br>";
    echo "管理員名稱：" . $_SESSION['admin_username'] . "<br>";
} else {
    echo "❌ 未登入<br>";
}

echo "<br><br>";
echo "POST 資料：<br><pre>";
print_r($_POST);
echo "</pre>";

echo "<br>SESSION 資料：<br><pre>";
print_r($_SESSION);
echo "</pre>";

// 測試手動登入
if (isset($_GET['test_login'])) {
    $_SESSION['admin_id'] = 1;
    $_SESSION['admin_username'] = 'admin';
    echo "<br>✅ 手動設定 Session 完成！<br>";
    echo "<a href='admin.php'>前往管理後台</a>";
}

echo "<br><br><a href='test-session.php?test_login=1'>測試手動登入</a>";
echo "<br><a href='admin.php'>前往管理後台</a>";
?>
