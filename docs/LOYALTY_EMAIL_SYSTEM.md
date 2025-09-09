# Loyalty Points & Email Verification System

## 設置說明

### 1. 數據庫設置
首先運行數據庫更新腳本：
```sql
-- 在 phpMyAdmin 或 MySQL 命令行中運行：
-- 文件位置：database/loyalty_and_email_verification.sql
```

### 2. 郵件設置
你的郵件系統已經設置好了！在 `app/base.php` 的 `get_mail()` 函數中：

```php
function get_mail() {
    require_once 'lib/PHPMailer.php';
    require_once 'lib/SMTP.php';

    $m = new PHPMailer(true);
    $m->isSMTP();
    $m->SMTPAuth = true;
    $m->Host = 'smtp.gmail.com';
    $m->Port = 587;
    $m->Username = 'taneason0912@gmail.com';
    $m->Password = 'tmdz rbwz tvyt yqbh';
    $m->CharSet = 'utf-8';
    $m->setFrom($m->Username, 'Osbuzzz');

    return $m;
}
```

✅ **郵件系統已就緒** - 無需額外設置！

### 3. 系統功能

#### Loyalty Points System:
- **賺取積分**: 每消費 RM1 = 1 積分（可設置）
- **兌換折扣**: 100 積分 = RM1 折扣（可設置）
- **最小兌換**: 100 積分起兌（可設置）
- **最大折扣**: 訂單總額的 50%（可設置）
- **註冊獎勵**: 新用戶獲得 100 積分
- **積分有效期**: 12 個月（可設置為永不過期）

#### Email Verification:
- **註冊驗證**: 新用戶註冊需要驗證郵箱
- **郵箱修改驗證**: 修改郵箱地址需要驗證
- **重發驗證**: 支持重新發送驗證郵件
- **24小時有效期**: 驗證連結 24 小時內有效

### 4. 管理員功能

#### 系統設置頁面：
- URL: `/page/admin/loyalty_settings.php`
- 可以配置所有 loyalty points 相關設置
- 可以開啟/關閉郵箱驗證要求

#### 用戶管理：
- 查看用戶積分餘額
- 查看郵箱驗證狀態
- 可手動調整用戶積分

### 5. 新增頁面

#### 前台用戶頁面：
- `/page/user/verify_email.php` - 郵箱驗證頁面
- `/page/user/signup_success.php` - 註冊成功頁面
- `/page/user/resend_verification.php` - 重發驗證郵件
- `/page/user/loyalty_history.php` - 積分歷史記錄

#### 後台管理頁面：
- `/page/admin/loyalty_settings.php` - 積分系統設置

### 6. 數據庫新增表格

- `loyalty_transactions` - 積分交易記錄
- `loyalty_settings` - 系統設置
- `email_verification_logs` - 郵箱驗證記錄

用戶表新增欄位：
- `loyalty_points` - 積分餘額
- `email_verified` - 郵箱驗證狀態
- `email_verification_token` - 驗證令牌
- `email_verification_expires` - 令牌過期時間
- `pending_email` - 待驗證的新郵箱
- `pending_email_token` - 新郵箱驗證令牌
- `pending_email_expires` - 新郵箱驗證過期時間

### 7. 使用流程

#### 新用戶註冊：
1. 填寫郵箱 → 填寫密碼和用戶名
2. 收到驗證郵件 → 點擊驗證連結
3. 郵箱驗證成功 → 獲得 100 積分獎勵
4. 可以正常登入和購物

#### 積分系統：
1. 購買商品獲得積分
2. 結帳時可選擇使用積分抵扣
3. 查看積分歷史記錄
4. 積分自動計算和更新

#### 郵箱修改：
1. 在個人資料頁面修改郵箱
2. 新郵箱收到驗證郵件
3. 點擊驗證連結確認修改
4. 郵箱更新成功

### 8. 安全性

- 驗證令牌使用 `random_bytes(32)` 生成
- 令牌有 24 小時有效期
- 積分交易有完整記錄
- 支持事務處理確保數據一致性
- 防止重複驗證和惡意操作

### 9. 自定義設置

管理員可以在後台調整：
- 積分獲得比例
- 積分兌換比例  
- 最小兌換積分
- 最大折扣百分比
- 積分有效期
- 註冊獎勵積分
- 是否要求郵箱驗證

### 10. 測試建議

1. 先在測試環境設置郵件服務
2. 測試完整的註冊和驗證流程
3. 測試積分獲得和兌換功能
4. 測試郵箱修改驗證
5. 檢查所有錯誤處理情況

---

## 後續可以擴展的功能

- 積分過期自動處理
- 積分轉贈功能
- VIP 等級系統
- 生日積分獎勵
- 推薦好友獎勵
- 積分商城
- 郵件模板自定義
- 多語言支持
