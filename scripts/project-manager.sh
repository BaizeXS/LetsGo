#!/bin/bash

# 项目管理脚本
# 用法: ./scripts/project-manager.sh [命令] [选项]

# 确保scripts目录存在
mkdir -p $(dirname "$0")

# 配置文件存储目录
CONFIG_DIR="./config/environments"
mkdir -p $CONFIG_DIR

# 颜色定义
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# 帮助信息
show_help() {
    echo -e "${YELLOW}项目管理工具${NC}"
    echo "用法: $0 [命令] [选项]"
    echo ""
    echo "可用命令:"
    echo "  init [项目名称] --db-name=名称 --db-init --npm-install  - 初始化项目"
    echo "  config list              - 列出所有可用配置"
    echo "  config switch [名称]     - 切换到指定配置"
    echo "  config save [名称]       - 保存当前配置"
    echo "  dev start [项目名称]     - 启动开发服务器"
    echo "  dev stop [项目名称]      - 停止开发服务器"
    echo "  docker up                - 启动Docker容器"
    echo "  docker down              - 停止Docker容器"
    echo "  docker restart           - 重启Docker容器"
    echo "  help                     - 显示帮助信息"
}

# 列出所有配置
list_configs() {
    echo -e "${YELLOW}可用配置:${NC}"
    if [ -d "$CONFIG_DIR" ]; then
        ls -1 "$CONFIG_DIR" | grep -v "README.md" | sed 's/\.env\.//'
    else
        echo "没有找到配置目录"
    fi
    
    echo -e "\n${YELLOW}当前配置:${NC}"
    if [ -f ".env" ]; then
        # 尝试从env文件中提取APP_NAME或自定义标识
        APP_NAME=$(grep "APP_NAME" .env | cut -d'=' -f2)
        if [ -n "$APP_NAME" ]; then
            echo "$APP_NAME"
        else
            echo "默认配置（未命名）"
        fi
    else
        echo "未找到.env文件"
    fi
}

# 切换配置
switch_config() {
    CONFIG_NAME=$1
    CONFIG_FILE="$CONFIG_DIR/.env.$CONFIG_NAME"
    
    if [ -z "$CONFIG_NAME" ]; then
        echo -e "${RED}错误: 请指定配置名称${NC}"
        list_configs
        return 1
    fi
    
    if [ -f "$CONFIG_FILE" ]; then
        cp "$CONFIG_FILE" .env
        echo -e "${GREEN}已切换到配置: $CONFIG_NAME${NC}"
        
        # 询问是否重启Docker
        read -p "是否重启Docker容器以应用新配置? (y/n): " restart
        if [[ $restart == "y" || $restart == "Y" ]]; then
            docker_restart
        fi
    else
        echo -e "${RED}错误: 配置 '$CONFIG_NAME' 不存在${NC}"
        list_configs
        return 1
    fi
}

# 保存配置
save_config() {
    CONFIG_NAME=$1
    
    if [ -z "$CONFIG_NAME" ]; then
        echo -e "${RED}错误: 请指定配置名称${NC}"
        return 1
    fi
    
    if [ ! -f ".env" ]; then
        echo -e "${RED}错误: 没有找到.env文件${NC}"
        return 1
    fi
    
    mkdir -p "$CONFIG_DIR"
    cp .env "$CONFIG_DIR/.env.$CONFIG_NAME"
    echo -e "${GREEN}当前配置已保存为: $CONFIG_NAME${NC}"
}

# 初始化项目
init_project() {
    PROJECT_NAME=$1
    shift
    
    if [ -z "$PROJECT_NAME" ]; then
        echo -e "${RED}错误: 请指定项目名称${NC}"
        return 1
    fi
    
    echo -e "${YELLOW}初始化项目: $PROJECT_NAME${NC}"
    
    # 解析额外参数
    DB_NAME="laravel"
    DB_INIT=false
    NPM_INSTALL=false
    
    for arg in "$@"; do
        case $arg in
            --db-name=*)
                DB_NAME="${arg#*=}"
                ;;
            --db-init)
                DB_INIT=true
                ;;
            --npm-install)
                NPM_INSTALL=true
                ;;
        esac
    done
    
    # 创建或更新.env文件
    if [ ! -f ".env.example" ]; then
        echo -e "${RED}错误: 未找到.env.example文件${NC}"
        return 1
    fi
    
    cp .env.example .env
    
    # 更新APP_NAME
    sed -i "s/APP_NAME=.*/APP_NAME=$PROJECT_NAME/" .env
    
    # 如果使用Docker，更新数据库配置
    sed -i "s/DB_HOST=.*/DB_HOST=mariadb/" .env
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=root/" .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=root/" .env
    
    # 保存配置
    save_config $PROJECT_NAME
    
    # 生成应用密钥
    echo -e "${YELLOW}生成应用密钥...${NC}"
    php artisan key:generate
    
    # 初始化数据库
    if [ "$DB_INIT" = true ]; then
        echo -e "${YELLOW}执行数据库迁移...${NC}"
        php artisan migrate:fresh --seed
    fi
    
    # 安装npm依赖
    if [ "$NPM_INSTALL" = true ]; then
        echo -e "${YELLOW}安装前端依赖...${NC}"
        npm install
    fi
    
    echo -e "${GREEN}项目 $PROJECT_NAME 初始化完成!${NC}"
}

# 开发服务器管理
dev_start() {
    PROJECT_NAME=$1
    
    if [ -z "$PROJECT_NAME" ]; then
        echo -e "${RED}错误: 请指定项目名称${NC}"
        return 1
    fi
    
    # 切换到对应配置
    switch_config "$PROJECT_NAME"
    
    echo -e "${YELLOW}启动开发服务器: $PROJECT_NAME${NC}"
    
    # 使用Docker运行项目
    docker exec -it xampp-apache bash -c "cd /var/www/html && php artisan serve --host=0.0.0.0 --port=8000" &
    DEV_SERVER_PID=$!
    
    # 启动npm
    echo -e "${YELLOW}启动前端构建...${NC}"
    npm run dev &
    NPM_PID=$!
    
    echo -e "${GREEN}开发服务器已启动${NC}"
    echo "访问: http://localhost:8000"
    echo "前端开发服务器运行在: http://localhost:5173"
    
    # 保存PID到临时文件
    echo "$DEV_SERVER_PID" > .dev_server_pid
    echo "$NPM_PID" > .npm_pid
}

dev_stop() {
    PROJECT_NAME=$1
    
    echo -e "${YELLOW}停止开发服务器...${NC}"
    
    # 如果PID文件存在，杀掉进程
    if [ -f ".dev_server_pid" ]; then
        DEV_SERVER_PID=$(cat .dev_server_pid)
        kill -9 $DEV_SERVER_PID 2>/dev/null
        rm .dev_server_pid
    fi
    
    if [ -f ".npm_pid" ]; then
        NPM_PID=$(cat .npm_pid)
        kill -9 $NPM_PID 2>/dev/null
        rm .npm_pid
    fi
    
    echo -e "${GREEN}开发服务器已停止${NC}"
}

# Docker管理函数
docker_up() {
    echo -e "${YELLOW}启动Docker容器...${NC}"
    docker-compose up -d
    echo -e "${GREEN}Docker容器已启动${NC}"
}

docker_down() {
    echo -e "${YELLOW}停止Docker容器...${NC}"
    docker-compose down
    echo -e "${GREEN}Docker容器已停止${NC}"
}

docker_restart() {
    echo -e "${YELLOW}重启Docker容器...${NC}"
    docker-compose restart
    echo -e "${GREEN}Docker容器已重启${NC}"
}

# 主函数
main() {
    if [ $# -eq 0 ]; then
        show_help
        return 0
    fi
    
    case $1 in
        init)
            shift
            init_project "$@"
            ;;
        config)
            case $2 in
                list)
                    list_configs
                    ;;
                switch)
                    switch_config $3
                    ;;
                save)
                    save_config $3
                    ;;
                *)
                    echo -e "${RED}错误: 未知的配置命令${NC}"
                    show_help
                    return 1
                    ;;
            esac
            ;;
        dev)
            case $2 in
                start)
                    dev_start $3
                    ;;
                stop)
                    dev_stop $3
                    ;;
                *)
                    echo -e "${RED}错误: 未知的开发服务器命令${NC}"
                    show_help
                    return 1
                    ;;
            esac
            ;;
        docker)
            case $2 in
                up)
                    docker_up
                    ;;
                down)
                    docker_down
                    ;;
                restart)
                    docker_restart
                    ;;
                *)
                    echo -e "${RED}错误: 未知的Docker命令${NC}"
                    show_help
                    return 1
                    ;;
            esac
            ;;
        help)
            show_help
            ;;
        *)
            echo -e "${RED}错误: 未知命令 $1${NC}"
            show_help
            return 1
            ;;
    esac
}

# 执行主函数
main "$@" 