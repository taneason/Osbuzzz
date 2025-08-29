# 忘记密码功能说明

## 功能概述
OSBuzz现在支持完整的忘记密码功能，用户可以通过邮件重置密码。

## 新增文件

### 1. 用户页面
- `app/page/user/forgot_password.php` - 忘记密码请求页面
- `app/page/user/reset_password.php` - 重置密码页面

### 2. 管理员页面
- `app/page/admin/test_email.php` - 邮件系统测试页面（仅管理员）
- `app/page/admin/password_reset_management.php` - 密码重置管理页面（仅管理员）

### 3. 数据库
- `database/password_reset_migration.sql` - 数据库迁移脚本

## 数据库设置

### 步骤1：运行数据库迁移
执行以下SQL命令来创建`password_resets`表（带外键约束）：

```sql
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `email` (`email`),
  KEY `token` (`token`),
  KEY `expires_at` (`expires_at`),
  UNIQUE KEY `token_unique` (`token`),
  CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### 数据库关系设计
新的设计包含以下改进：
- **外键约束**：`user_id` 直接关联到 `user.id`
- **级联删除**：用户被删除时，相关的重置令牌也会自动删除
- **唯一令牌**：防止令牌重复
- **更好的索引**：提升查询性能

## 邮件配置

邮件配置已经在`base.php`中设置：
- SMTP服务器：Gmail (smtp.gmail.com)
- 端口：587
- 发送邮箱：taneason0912@gmail.com
- 应用密码：已配置

## 功能流程

### 1. 用户请求重置密码
1. 用户访问登录页面，点击"Forgot password?"
2. 进入`forgot_password.php`页面
3. 输入邮箱地址
4. 系统验证邮箱是否存在
5. 生成安全令牌并发送邮件

### 2. 邮件发送
- 生成64字符的随机令牌
- 令牌有效期：5分钟
- 发送HTML格式的专业邮件
- 包含重置链接和纯文本备用内容

### 3. 密码重置
1. 用户点击邮件中的链接
2. 进入`reset_password.php`页面
3. 验证令牌有效性和过期时间
4. 输入新密码（需确认）
5. 更新密码并标记令牌为已使用

## 安全特性

### 1. 令牌安全
- 使用`random_bytes(32)`生成64字符令牌
- 令牌只能使用一次（使用后立即删除）
- 5分钟自动过期（更安全的短期有效性）
- 每次新请求会删除该用户的旧令牌
- 外键约束确保数据完整性

### 2. 用户关联安全
- 使用 `user_id` 而不是仅仅依赖邮箱
- 外键约束防止孤立的重置记录
- 级联删除保持数据一致性

### 2. 密码安全
- 最少6字符要求
- 使用SHA1加密（与现有系统一致）
- 需要确认密码

### 3. 邮箱验证
- 验证邮箱格式
- 检查邮箱是否在系统中注册
- 防止信息泄露

## 使用方法

### 用户端
1. 访问：`/page/user/login.php`
2. 点击"Forgot password?"
3. 输入注册邮箱
4. 查收邮件并点击重置链接
5. 设置新密码

### 管理员功能
1. 以管理员身份登录
2. 访问：`/page/admin/password_reset_management.php`
3. 查看重置请求统计和历史
4. 清理过期令牌
5. 测试邮件系统（链接到测试页面）

## 维护建议

### 定期清理
建议定期运行以下SQL来清理过期令牌：
```sql
DELETE FROM password_resets WHERE expires_at < NOW();
```

注意：使用后的令牌会自动删除，无需额外清理。

### 监控
- 监控邮件发送成功率
- 检查过期令牌数量
- 关注重置密码使用频率

## 故障排除

### 邮件发送失败
1. 检查Gmail应用密码是否正确
2. 确认网络连接
3. 查看服务器错误日志

### 令牌无效
1. 检查链接是否完整
2. 确认令牌未过期（1小时内）
3. 验证令牌未被使用

### 数据库错误
1. 确认`password_resets`表已创建
2. 检查表结构是否正确
3. 验证数据库连接

## 更新历史
- **v1.0** (2025-08-30): 初始版本，包含完整的忘记密码功能
