# LetsGo Docker XAMPP 开发环境

## 🚀 技术栈

### 核心组件
- **PHP 8.2**：最新稳定版，内置关键扩展
- **Apache 2.4**：高性能 Web 服务器
- **MariaDB 10.6**：高兼容性数据库
- **phpMyAdmin**：可视化数据库管理工具
- **Node.js 22.x**：前端资源编译
- **Composer**：PHP 依赖管理器
- **Docker**：环境一致性保证

## 📂 项目结构

```bash
xampp-docker/
├── config/                # 配置文件目录
│   ├── apache2/           # Apache配置
│   ├── mariadb/           # 数据库配置
│   └── php/               # PHP配置
├── data/                  # 数据持久化
│   └── mariadb/           # 数据库文件
├── www/                   # Web根目录
│   └── lv-bookstore/      # 项目示例
├── .env                   # 环境变量
├── scripts/               # 管理脚本
│   ├── xampp.sh           # 环境管理
│   └── project-manager.sh # 项目管理
├── docker-compose.yml     # Docker服务配置
└── Dockerfile             # 镜像构建
```

## 🛠 快速开始

### 前置条件

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (V27.5.0+)
- Bash 终端（推荐 Git Bash 或 WSL）

### 安装步骤

1. 设置脚本权限
```bash
chmod +x scripts/xampp.sh scripts/project-manager.sh
```

2. 启动Docker环境
```bash
./scripts/xampp.sh start
```

### 访问服务

| 服务       | 地址                  | 凭据      |
| ---------- | --------------------- | --------- |
| 网站       | http://localhost      | -         |
| 数据库管理 | http://localhost:8080 | root/root |

## 🔧 命令手册

### 环境管理 (`xampp.sh`)

```bash
./scripts/xampp.sh [命令]
```

#### 命令概览

| 命令      | 操作             | 用途                               |
| :-------- | :--------------- | :--------------------------------- |
| `start`   | 启动环境         | 初始化所有 Docker 容器和服务       |
| `stop`    | 停止环境         | 安全关闭所有正在运行的容器         |
| `restart` | 重启环境         | 完全重置并重新加载所有服务         |
| `status`  | 检查环境状态     | 显示容器运行情况和基本信息         |
| `shell`   | 进入容器终端     | 直接访问 Apache 容器的交互式 Shell |
| `mysql`   | 进入数据库客户端 | 快速连接到 MariaDB 数据库          |
| `logs`    | 查看容器日志     | 实时或历史日志追踪                 |
| `info`    | 显示环境信息     | 输出系统组件版本和访问地址         |

#### 使用示例

**基本环境管理**

```
# 启动开发环境  
./scripts/xampp.sh start  

# 停止开发环境  
./scripts/xampp.sh stop  

# 重启环境  
./scripts/xampp.sh restart  
```

**诊断和调试**

```
# 查看环境状态  
./scripts/xampp.sh status  

# 查看容器日志  
./scripts/xampp.sh logs  

# 显示环境详细信息  
./scripts/xampp.sh info  
```

**高级交互**

```
# 进入容器终端  
./scripts/xampp.sh shell  

# 进入 MySQL 客户端  
./scripts/xampp.sh mysql  
```



### 项目管理 (`project-manager.sh`)

#### 命令分类

| 主命令   | 功能           | 主要操作            |
| -------- | -------------- | ------------------- |
| `create` | 创建新项目     | 初始化 Laravel 项目 |
| `init`   | 初始化已有项目 | 配置依赖和数据库    |
| `config` | 虚拟主机配置   | Apache 配置管理     |
| `dev`    | 开发环境管理   | 服务启动与监控      |

#### 1. 创建项目 `create`

```bash
./scripts/project-manager.sh create [项目名] [选项]
```

**创建选项**

| 选项               | 描述          | 示例                           |
| ------------------ | ------------- | ------------------------------ |
| `--vue`            | 添加 Vue 支持 | `create blog --vue`            |
| `--db-name=DBNAME` | 指定数据库名  | `create blog --db-name=blogdb` |

**使用示例**

```bash
# 创建基础 Laravel 项目
./scripts/project-manager.sh create blog

# 创建 Vue 集成项目
./scripts/project-manager.sh create blog --vue
```

#### 2. 初始化项目 `init`

```bash
./scripts/project-manager.sh init [项目名] [选项]
```

**初始化选项**

| 选项               | 描述         | 示例                         |
| ------------------ | ------------ | ---------------------------- |
| `--db-name=DBNAME` | 指定数据库名 | `init blog --db-name=blogdb` |
| `--db-init`        | 初始化数据库 | `init blog --db-init`        |
| `--npm-install`    | 安装前端依赖 | `init blog --npm-install`    |

**使用示例**

```bash
# 初始化已有项目
./scripts/project-manager.sh init blog

# 初始化并设置数据库
./scripts/project-manager.sh init blog --db-name=custom_db --db-init

# 完整初始化
./scripts/project-manager.sh init blog --db-name=custom_db --db-init --npm-install
```

#### 3. 虚拟主机配置 `config`

```bash
./scripts/project-manager.sh config [子命令] [参数]
```

**虚拟主机配置子命令**

| 子命令    | 功能             | 典型用法                |
| --------- | ---------------- | ----------------------- |
| `list`    | 列出所有可用配置 | `config list`           |
| `active`  | 显示当前活动配置 | `config active`         |
| `enable`  | 启用项目配置     | `config enable myblog`  |
| `disable` | 禁用项目配置     | `config disable myblog` |
| `switch`  | 切换项目配置     | `config switch myblog`  |
| `basic`   | 恢复默认配置     | `config basic`          |
| `create`  | 创建新配置       | `config create newsite` |

**使用示例**

```bash
# 列出所有项目
./scripts/project-manager.sh config list

# 切换到指定项目
./scripts/project-manager.sh config switch mysite
```

#### 4. 开发环境管理 `dev`

```bash
./scripts/project-manager.sh dev [子命令] [项目名]
```

**开发环境子命令**

| 子命令     | 功能             | 典型用法              |
| ---------- | ---------------- | --------------------- |
| `start`    | 启动项目开发环境 | `dev start myblog`    |
| `stop`     | 停止项目开发环境 | `dev stop myblog`     |
| `status`   | 显示开发环境状态 | `dev status`          |
| `fix-vite` | 修复 Vite 配置   | `dev fix-vite myblog` |

**使用示例**

```shell
# 启动指定项目的开发环境
./scripts/project-manager.sh dev start myblog

# 停止指定项目的开发环境
./scripts/project-manager.sh dev stop myblog
```

**启动开发环境将同时启动：**

- **PHP Artisan Serve**
  - 访问地址：http://localhost:8000
  - 提供 Laravel 后端服务

- **npm 开发服务器**
  - 访问地址：http://localhost:5173
  - 提供前端资源实时编译

## 📝 环境变量配置

在 `.env` 文件中配置：

```bash
# 数据库配置
MARIADB_ROOT_PASSWORD=root
MARIADB_DATABASE=laravel
MARIADB_USER=user
MARIADB_PASSWORD=password

# 端口映射
WEB_PORT=80
SSL_PORT=443
VITE_PORT=5173
LARAVEL_PORT=8000
PMA_PORT=8080
```

## 💡 开发工作流

1. 启动环境
```bash
./scripts/xampp.sh start
```

2. 创建项目
```bash
./scripts/project-manager.sh create myblog --vue
```

3. 启动开发服务
```bash
./scripts/project-manager.sh dev start myblog
```

## 🔗 常用 Laravel 命令

```bash
# 数据库迁移
php artisan migrate

# 创建控制器
php artisan make:controller BlogController

# 创建模型
php artisan make:model Blog -m
```

## 🌐 端口使用

| 端口 | 服务               |
| ---- | ------------------ |
| 80   | Apache HTTP        |
| 443  | Apache HTTPS       |
| 8080 | phpMyAdmin         |
| 3306 | MariaDB            |
| 5173 | Vite 开发服务器    |
| 8000 | PHP Artisan 服务器 |

## 📌 注意事项

- ARM 架构用户注意 Docker 镜像兼容性
- 修改 `.env` 后需重启环境
- 首次使用请详细阅读文档
