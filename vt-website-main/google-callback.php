<?php
require_once 'backend/config.php';

// 如果已登入，重定向到預購頁面
if (isLoggedIn()) {
    header('Location: preorder.php');
    exit;
}

// 處理 Google OAuth 回調
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // 交換授權碼以獲取訪問令牌
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $tokenData = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $tokenInfo = json_decode($response, true);
        $accessToken = $tokenInfo['access_token'];
        
        // 使用訪問令牌獲取用戶資訊
        $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $userInfoResponse = curl_exec($ch);
        curl_close($ch);
        
        $userInfo = json_decode($userInfoResponse, true);
        
        if (isset($userInfo['id'])) {
            $googleId = $userInfo['id'];
            $email = $userInfo['email'];
            $name = $userInfo['name'] ?? '';
            $avatar = $userInfo['picture'] ?? '';
            
            try {
                // 檢查是否已有此 Google 帳號
                $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE google_id = ?");
                $stmt->execute([$googleId]);
                $user = $stmt->fetch();
                
                if ($user) {
                    // 已存在的 Google 帳號，直接登入
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    
                    header('Location: preorder.php');
                    exit;
                } else {
                    // 檢查電子郵件是否已被使用
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    $existingUser = $stmt->fetch();
                    
                    if ($existingUser) {
                        // 電子郵件已存在，將 Google ID 綁定到現有帳號
                        $stmt = $pdo->prepare("UPDATE users SET google_id = ?, avatar = ?, auth_provider = 'google' WHERE email = ?");
                        $stmt->execute([$googleId, $avatar, $email]);
                        
                        $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE email = ?");
                        $stmt->execute([$email]);
                        $user = $stmt->fetch();
                        
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['email'] = $user['email'];
                        
                        header('Location: preorder.php');
                        exit;
                    } else {
                        // 新用戶，創建帳號
                        // 從電子郵件生成用戶名
                        $username = explode('@', $email)[0];
                        $originalUsername = $username;
                        $counter = 1;
                        
                        // 確保用戶名唯一
                        while (true) {
                            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                            $stmt->execute([$username]);
                            if (!$stmt->fetch()) {
                                break;
                            }
                            $username = $originalUsername . $counter;
                            $counter++;
                        }
                        
                        $stmt = $pdo->prepare("INSERT INTO users (username, email, google_id, avatar, auth_provider) VALUES (?, ?, ?, ?, 'google')");
                        $stmt->execute([$username, $email, $googleId, $avatar]);
                        
                        $_SESSION['user_id'] = $pdo->lastInsertId();
                        $_SESSION['username'] = $username;
                        $_SESSION['email'] = $email;
                        
                        header('Location: preorder.php');
                        exit;
                    }
                }
            } catch (PDOException $e) {
                error_log("Google OAuth Error: " . $e->getMessage());
                header('Location: login.php?error=oauth_failed');
                exit;
            }
        }
    }
}

// 如果沒有授權碼或出錯，重定向回登入頁面
header('Location: login.php?error=oauth_failed');
exit;
