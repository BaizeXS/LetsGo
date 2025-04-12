#!/bin/bash

# XAMPP Docker 环境管理脚本
# 用法: ./scripts/xampp.sh [命令]

# 颜色定义
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# 帮助信息
show_help() {
    echo -e "${YELLOW}XAMPP Docker环境管理工具${NC}"
    echo "用法: $0 [命令]"
    echo ""
    echo "可用命令:"
    echo "  start       - 启动Docker环境"
    echo "  stop        - 停止Docker环境"
    echo "  restart     - 重启Docker环境"
    echo "  status      - 查看Docker容器状态"
    echo "  logs        - 查看Docker容器日志"
    echo "  help        - 显示帮助信息"
}

# 启动Docker环境
start_env() {
    echo -e "${YELLOW}启动Docker环境...${NC}"
    docker-compose up -d
    echo -e "${GREEN}Docker环境已启动${NC}"
    
    # 显示容器状态
    docker-compose ps
    
    echo -e "\n${GREEN}环境访问地址:${NC}"
    echo "- 网站: http://localhost (或配置的WEB_PORT端口)"
    echo "- phpMyAdmin: http://localhost:8080 (或配置的PMA_PORT端口)"
}

# 停止Docker环境
stop_env() {
    echo -e "${YELLOW}停止Docker环境...${NC}"
    docker-compose down
    echo -e "${GREEN}Docker环境已停止${NC}"
}

# 重启Docker环境
restart_env() {
    echo -e "${YELLOW}重启Docker环境...${NC}"
    docker-compose restart
    echo -e "${GREEN}Docker环境已重启${NC}"
    
    # 显示容器状态
    docker-compose ps
}

# 查看Docker容器状态
status_env() {
    echo -e "${YELLOW}Docker容器状态:${NC}"
    docker-compose ps
}

# 查看Docker容器日志
logs_env() {
    echo -e "${YELLOW}Docker容器日志:${NC}"
    docker-compose logs
}

# 主函数
main() {
    if [ $# -eq 0 ]; then
        show_help
        return 0
    fi
    
    case $1 in
        start)
            start_env
            ;;
        stop)
            stop_env
            ;;
        restart)
            restart_env
            ;;
        status)
            status_env
            ;;
        logs)
            logs_env
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