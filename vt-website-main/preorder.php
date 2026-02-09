<?php
require_once 'backend/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_name = cleanInput($_POST['recipient_name'] ?? '');
    $phone = cleanInput($_POST['phone'] ?? '');
    $store_name = cleanInput($_POST['store_name'] ?? '');
    $store_address = cleanInput($_POST['store_address'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 1);
    $notes = cleanInput($_POST['notes'] ?? '');
    
    // 驗證
    if (empty($recipient_name) || empty($phone) || empty($store_name) || empty($store_address)) {
        $error = '所有欄位都必須填寫';
    } elseif ($quantity < 1) {
        $error = '數量至少為 1';
    } elseif (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
        $error = '請上傳付款證明';
    } else {
        // 上傳檔案
        $uploadResult = uploadFile($_FILES['payment_proof']);
        
        if (!$uploadResult['success']) {
            $error = $uploadResult['message'];
        } else {
            try {
                $total_price = 160 * $quantity; // 100 + 60 運費
                
                $stmt = $pdo->prepare("
                    INSERT INTO preorders (user_id, username, email, phone, recipient_name, store_name, store_address, quantity, total_price, payment_proof, notes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $user['id'],
                    $user['username'],
                    $user['email'],
                    $phone,
                    $recipient_name,
                    $store_name,
                    $store_address,
                    $quantity,
                    $total_price,
                    $uploadResult['filename'],
                    $notes
                ]);
                
                $success = '預購成功！訂單編號：' . $pdo->lastInsertId();
            } catch (PDOException $e) {
                $error = '預購失敗，請稍後再試';
            }
        }
    }
}
?>
<!doctype html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>鑰匙圈預購表單 - 柒柒 chi</title>
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
            <h1 class="form-title">🔑 鑰匙圈預購表單</h1>
            <div class="user-info">
                歡迎，<?php echo htmlspecialchars($user['username']); ?>！
            </div>
            
            <?php if ($error): ?>
                <div class="form-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="form-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="price-info">
                <p>鑰匙圈價格：NT$ 100 / 個</p>
                <p>運費（7-11 賣貨便）：NT$ 60</p>
            </div>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label" for="recipient_name">收件人姓名 *</label>
                    <input type="text" id="recipient_name" name="recipient_name" class="form-input" value="<?php echo htmlspecialchars($user['username']); ?>" readonly required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="phone">聯絡電話 *</label>
                    <input type="tel" id="phone" name="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" readonly required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="store_name">7-11 門市名稱 *</label>
                    <input type="text" id="store_name" name="store_name" class="form-input" placeholder="例如：台北中山門市" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="store_address">7-11 門市地址 *</label>
                    <input type="text" id="store_address" name="store_address" class="form-input" placeholder="例如：台北市中山區..." required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="quantity">數量 *</label>
                    <input type="number" id="quantity" name="quantity" class="form-input" min="1" value="1" required>
                </div>
                
                <div class="price-info" style="margin-bottom: 24px;">
                    <p style="font-size: 16px; margin: 4px 0;">計算：<span id="quantity-display">1</span> × NT$ 100 + 運費 NT$ 60</p>
                    <p class="total-price">總金額：NT$ <span id="total-price">160</span></p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">LINE Pay 付款證明 * (請上傳截圖)</label>
                    <div class="file-upload" onclick="document.getElementById('payment_proof').click()">
                        <p style="color: #7dd3fc; margin-bottom: 8px;">📷 點擊上傳圖片</p>
                        <p style="color: rgba(255,255,255,0.6); font-size: 13px;">支援 JPG、PNG 格式，最大 5MB</p>
                        <input type="file" id="payment_proof" name="payment_proof" accept="image/jpeg,image/png,image/jpg" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="notes">備註（選填）</label>
                    <textarea id="notes" name="notes" class="form-textarea" placeholder="有任何特殊需求可以在這裡備註..."></textarea>
                </div>
                
                <button type="submit" class="form-button">送出預購訂單</button>
            </form>
            
            <div class="logout-link">
                <a href="logout.php">登出</a> | <a href="keychain.html">返回商品頁</a>
            </div>
        </div>
    </div>
    </main>
    
    <script src="script.js"></script>
    <script>
        // 更新总金额计算
        function updateTotalPrice() {
            const quantity = parseInt(document.getElementById('quantity').value) || 1;
            const unitPrice = 100;
            const shippingFee = 60;
            const total = (quantity * unitPrice) + shippingFee;
            
            document.getElementById('quantity-display').textContent = quantity;
            document.getElementById('total-price').textContent = total;
        }
        
        // 监听数量变化
        document.getElementById('quantity').addEventListener('input', updateTotalPrice);
        document.getElementById('quantity').addEventListener('change', updateTotalPrice);
        
        // 文件上传提示
        document.getElementById('payment_proof').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            if (fileName) {
                document.querySelector('.file-upload p').textContent = '✅ ' + fileName;
            }
        });
        
        // 表单验证
        document.querySelector('form').addEventListener('submit', function(e) {
            const storeName = document.getElementById('store_name').value.trim();
            const storeAddress = document.getElementById('store_address').value.trim();
            const quantity = document.getElementById('quantity').value;
            const paymentProof = document.getElementById('payment_proof').files.length;
            
            let errorMsg = '';
            
            if (!storeName) {
                errorMsg = '請填寫 7-11 門市名稱';
            } else if (!storeAddress) {
                errorMsg = '請填寫 7-11 門市地址';
            } else if (!quantity || quantity < 1) {
                errorMsg = '請填寫數量（至少為 1）';
            } else if (paymentProof === 0) {
                errorMsg = '請上傳付款證明截圖';
            }
            
            if (errorMsg) {
                e.preventDefault();
                alert('❌ ' + errorMsg);
                return false;
            }
        });
        
        // 設置 HTML5 驗證訊息為繁體中文
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input[required], textarea[required]');
            inputs.forEach(function(input) {
                input.addEventListener('invalid', function(e) {
                    e.preventDefault();
                    if (this.validity.valueMissing) {
                        this.setCustomValidity('請填寫此欄位');
                    }
                });
                input.addEventListener('input', function() {
                    this.setCustomValidity('');
                });
            });
            
            // 數量欄位特殊處理
            const quantityInput = document.getElementById('quantity');
            quantityInput.addEventListener('invalid', function(e) {
                e.preventDefault();
                if (this.validity.valueMissing) {
                    this.setCustomValidity('請填寫數量');
                } else if (this.validity.rangeUnderflow) {
                    this.setCustomValidity('數量至少為 1');
                }
            });
        });
    </script>
</body>
</html>
