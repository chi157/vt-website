<?php
session_start();
require_once 'backend/config.php';

$isLoggedIn = isLoggedIn();
$username = '';
if ($isLoggedIn) {
    $user = getCurrentUser();
    $username = $user['username'] ?? '';
}
?>
<!-- 頂部導航欄 -->
<nav class="navbar">
  <div class="navbar-container">
    <a href="index.html" class="navbar-logo">
      <img src="images/頭貼%20-%20圓形.png" alt="柒柒 chi" class="navbar-logo-img">
      <span>柒柒 chi</span>
    </a>
    <button class="navbar-toggle" id="navbarToggle" aria-label="切換選單">
      <span class="navbar-toggle-icon"></span>
      <span class="navbar-toggle-icon"></span>
      <span class="navbar-toggle-icon"></span>
    </button>
    <ul class="navbar-menu" id="navbarMenu">
      <li class="navbar-item"><a href="about.html" class="navbar-link">自我介紹</a></li>
      <li class="navbar-item"><a href="course.html" class="navbar-link">程式設計課程</a></li>
      <li class="navbar-item"><a href="keychain.html" class="navbar-link">鑰匙圈預購</a></li>
      <li class="navbar-item"><a href="donate.html" class="navbar-link">加班台規則與說明</a></li>
      <li class="navbar-item"><a href="url.html" class="navbar-link">各平台連結</a></li>
      
      <?php if ($isLoggedIn): ?>
      <!-- 已登入：顯示個人資料和登出 -->
      <li class="navbar-item"><a href="profile.php" class="navbar-link">👤 個人資料</a></li>
      <li class="navbar-item"><a href="logout.php" class="navbar-link">登出 (<?php echo htmlspecialchars($username); ?>)</a></li>
      <?php else: ?>
      <!-- 未登入：顯示登入和註冊 -->
      <li class="navbar-item"><a href="login.php" class="navbar-link">登入</a></li>
      <li class="navbar-item"><a href="register.php" class="navbar-link">註冊</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>
