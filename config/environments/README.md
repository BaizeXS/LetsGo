# 环境配置目录

此目录包含项目的不同环境配置文件。

## 命名规则

配置文件应当遵循以下命名规则：`.env.{环境名称}`

例如：
- `.env.letsgo` - 基础生产环境配置
- `.env.dev` - 开发环境配置
- `.env.test` - 测试环境配置

## 使用方法

使用项目管理脚本切换配置：

```bash
# 列出所有可用配置
./scripts/project-manager.sh config list

# 切换到指定配置
./scripts/project-manager.sh config switch letsgo

# 保存当前配置
./scripts/project-manager.sh config save dev
```

## Docker环境管理

```bash
# 启动Docker容器
./scripts/project-manager.sh docker up

# 停止Docker容器
./scripts/project-manager.sh docker down

# 重启Docker容器
./scripts/project-manager.sh docker restart
```

## 注意事项

1. 不要在配置文件中存储敏感信息（如密钥、API密钥等）
2. 团队成员可以根据自己的本地开发环境创建个人配置
3. 配置文件变更后需要重启Docker容器以应用更改 