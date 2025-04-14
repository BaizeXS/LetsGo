# LetsGo - 旅游网站

LetsGo是一个基于Laravel的旅游网站，提供旅游路线规划、游记分享、用户社交等功能。该项目使用Docker进行开发和部署，以便团队成员可以在一致的环境中进行协同开发。

## 项目结构

```
LetsGo/
├── app/                  # Laravel应用核心代码
├── bootstrap/            # Laravel引导文件
├── config/               # 配置文件
├── database/             # 数据库迁移和种子
├── docker-compose.yml    # Docker Compose配置
├── Dockerfile            # Docker镜像构建配置
├── public/               # 公共资源目录
├── resources/            # 前端资源（视图、JS、CSS等）
├── routes/               # 路由定义
├── scripts/              # 实用脚本
├── storage/              # 存储目录
└── tests/                # 测试文件
```

## 环境要求

- Docker
- Docker Compose
- Git

## 本地开发设置

### 克隆仓库

```bash
git clone https://github.com/你的用户名/LetsGo.git
cd LetsGo
```

### 启动Docker环境

```bash
# 构建并启动容器
docker-compose up -d

# 查看容器状态
docker-compose ps
```

### 项目初始化

```bash
# 进入容器
docker exec -it xampp-apache bash

# 安装依赖
composer install
npm install

# 环境设置
cp .env.example .env
php artisan key:generate

# 数据库迁移
php artisan migrate --seed

# 前端资源编译
npm run dev
```

### 访问网站

- 网站: http://localhost
- phpMyAdmin: http://localhost:8080

## Docker环境说明

项目使用以下Docker容器:

- **apache**: 包含PHP 8.2、Apache和Node.js的Web服务器容器
- **mariadb**: MariaDB 10.6数据库服务器
- **phpmyadmin**: 用于数据库管理的phpMyAdmin界面

## 开发工作流程

1. 创建功能分支进行开发
```bash
git checkout -b feature/your-feature-name
```

2. 提交更改
```bash
git add .
git commit -m "描述你的更改"
```

3. 推送到GitHub进行协作
```bash
git push origin feature/your-feature-name
```

4. 创建Pull Request进行代码审查

## 项目维护

- **停止容器**: `docker-compose down`
- **重新构建**: `docker-compose build`
- **查看日志**: `docker-compose logs -f [服务名]`

## 注意事项

- 确保`.env`文件中的数据库配置与docker-compose.yml中的配置匹配
- 运行数据库命令时，确保是在容器内执行

## 贡献指南

1. 遵循Laravel编码规范
2. 为所有新功能编写测试
3. 提交前运行测试: `php artisan test`