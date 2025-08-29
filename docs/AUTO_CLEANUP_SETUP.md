# 自动清理过期Token设置指南

## 概述
OSBuzz现在支持多种方式自动清理过期的密码重置token，确保数据库保持清洁和安全。

## 自动清理机制

### 1. 实时清理 (已实现)
系统在以下时机自动清理过期token：
- ✅ 用户请求忘记密码时
- ✅ 用户访问重置密码页面时  
- ✅ 管理员访问密码重置管理页面时

### 2. 定时清理 (需要设置)

#### Windows 任务计划器设置

1. **打开任务计划器**
   - 按 `Win + R`，输入 `taskschd.msc`

2. **创建基本任务**
   - 右键点击"任务计划程序库" → "创建基本任务"
   - 名称：`OSBuzz Token Cleanup`
   - 描述：`自动清理过期的密码重置token`

3. **设置触发器**
   - 选择"每天"
   - 开始时间：`00:00:00`
   - 重复任务间隔：`5分钟`
   - 持续时间：`1天`

4. **设置操作**
   - 选择"启动程序"
   - 程序/脚本：`C:\Osbuzzz\cleanup_tokens.bat`
   - 起始于：`C:\Osbuzzz`

#### Linux Cron 设置

```bash
# 编辑crontab
crontab -e

# 添加以下行 (每5分钟运行一次)
*/5 * * * * /usr/bin/php /path/to/Osbuzzz/cleanup_tokens.php

# 或者每10分钟运行一次
*/10 * * * * /usr/bin/php /path/to/Osbuzzz/cleanup_tokens.php
```

#### 手动运行测试

**Windows:**
```cmd
cd C:\Osbuzzz
php cleanup_tokens.php
```

**Linux:**
```bash
cd /path/to/Osbuzzz
php cleanup_tokens.php
```

## 文件说明

### 新增文件

1. **`cleanup_tokens.php`** - 主要清理脚本
   - 自动删除过期token
   - 记录清理日志
   - 提供详细统计信息

2. **`cleanup_tokens.bat`** - Windows批处理脚本
   - 用于Windows任务计划器
   - 包含错误处理和日志记录

3. **`logs/token_cleanup.log`** - 清理日志文件
   - 记录每次清理的详细信息
   - 包含时间戳和统计数据

### 修改的文件

1. **`app/base.php`**
   - 新增 `cleanup_expired_tokens()` 函数

2. **`app/page/user/forgot_password.php`**
   - 添加请求时自动清理

3. **`app/page/user/reset_password.php`**
   - 添加访问时自动清理

4. **`app/page/admin/password_reset_management.php`**
   - 添加页面加载时自动清理
   - 改进手动清理功能

## 监控和维护

### 查看清理日志
```bash
# 查看最近的清理记录
tail -f logs/token_cleanup.log

# 查看今天的清理记录
grep "$(date +%Y-%m-%d)" logs/token_cleanup.log
```

### 清理频率建议

| Token过期时间 | 建议清理频率 | 原因 |
|--------------|-------------|------|
| 5分钟 | 每5-10分钟 | 及时清理，避免积累 |
| 1小时 | 每30分钟 | 平衡性能和清洁度 |
| 1天 | 每2小时 | 减少系统负载 |

### 性能考虑

- ✅ **轻量级操作**：清理过程非常快速
- ✅ **索引优化**：`expires_at` 字段已建立索引
- ✅ **批量删除**：一次删除所有过期记录
- ✅ **错误处理**：包含完整的异常处理

### 故障排除

#### 清理脚本不运行
1. 检查PHP路径是否正确
2. 确认数据库连接正常
3. 检查文件权限
4. 查看错误日志

#### 日志文件过大
```bash
# 轮换日志文件 (保留最近7天)
find logs/ -name "*.log" -mtime +7 -delete
```

#### 性能影响
- 清理操作很轻量，通常在几毫秒内完成
- 如果担心性能，可以降低清理频率

## 安全考虑

1. **及时清理**：过期token被快速删除，减少安全风险
2. **日志记录**：所有清理操作都有审计日志
3. **权限控制**：清理脚本只删除过期数据，不影响有效token
4. **故障安全**：清理失败不会影响系统正常运行

## 测试验证

### 测试清理功能
1. 创建一个测试用户
2. 请求密码重置
3. 等待5分钟让token过期
4. 运行清理脚本
5. 检查数据库确认token已删除

### 监控清理效果
```sql
-- 查看当前token状态
SELECT 
    COUNT(*) as total,
    COUNT(CASE WHEN expires_at < NOW() THEN 1 END) as expired,
    COUNT(CASE WHEN expires_at > NOW() THEN 1 END) as active
FROM password_resets;
```

这样的自动清理系统确保了数据库的清洁，提高了安全性，并减少了手动维护的需要！
