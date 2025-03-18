#!/bin/bash
# XAMPP Docker环境管理脚本
# 用法: ./xampp.sh [命令]

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # 无颜色

# 检测docker-compose命令格式
if command -v docker-compose &>/dev/null; then
  COMPOSE_CMD="docker-compose"
elif command -v docker &>/dev/null && docker compose version &>/dev/null; then
  COMPOSE_CMD="docker compose"
else
  echo "错误: 未找到docker-compose或docker compose命令"
  exit 1
fi

# 显示帮助信息
show_help() {
  echo -e "${YELLOW}XAMPP Docker环境管理工具${NC}"
  echo "用法: $0 [命令]"
  echo ""
  echo "命令:"
  echo "  start       - 启动XAMPP环境"
  echo "  stop        - 停止XAMPP环境"
  echo "  restart     - 重启XAMPP环境"
  echo "  status      - 显示环境状态"
  echo "  shell       - 进入Apache容器的shell"
  echo "  mysql       - 进入MariaDB客户端"
  echo "  logs        - 查看Apache日志"
  echo "  info        - 显示环境信息"
  echo ""
  echo "示例:"
  echo "  $0 start    - 启动环境"
  echo "  $0 shell    - 进入Apache容器"
}

# 启动环境
start_env() {
  echo -e "${BLUE}启动XAMPP Docker环境...${NC}"
  $COMPOSE_CMD up -d
  echo -e "${GREEN}环境已启动${NC}"
  echo ""
  show_urls
}

# 停止环境
stop_env() {
  echo -e "${BLUE}停止XAMPP Docker环境...${NC}"
  $COMPOSE_CMD down
  echo -e "${GREEN}环境已停止${NC}"
}

# 重启环境
restart_env() {
  echo -e "${BLUE}重启XAMPP Docker环境...${NC}"
  $COMPOSE_CMD restart
  echo -e "${GREEN}环境已重启${NC}"
  echo ""
  show_urls
}

# 显示环境状态
show_status() {
  echo -e "${YELLOW}XAMPP Docker环境状态:${NC}"
  $COMPOSE_CMD ps
}

# 进入Apache容器的shell
enter_shell() {
  echo -e "${BLUE}进入Apache容器shell...${NC}"
  docker exec -it xampp-apache bash
}

# 进入MariaDB客户端
enter_mysql() {
  echo -e "${BLUE}连接到MariaDB...${NC}"
  source .env
  docker exec -it xampp-mariadb mysql -uroot -p${MARIADB_ROOT_PASSWORD:-root}
}

# 查看Apache日志
view_logs() {
  echo -e "${BLUE}查看Apache日志...${NC}"
  docker logs -f xampp-apache
}

# 显示环境信息
show_info() {
  echo -e "${YELLOW}XAMPP Docker环境信息:${NC}"
  
  # 检查容器是否运行
  if ! docker ps | grep -q xampp-apache; then
    echo -e "${RED}环境未启动${NC}"
    return
  fi
  
  # PHP版本
  echo -e "${BLUE}PHP版本:${NC}"
  docker exec xampp-apache php -v | head -n 1
  
  # Apache版本
  echo -e "\n${BLUE}Apache版本:${NC}"
  docker exec xampp-apache apache2 -v | head -n 1
  
  # MariaDB版本
  echo -e "\n${BLUE}MariaDB版本:${NC}"
  docker exec xampp-mariadb mysql -V
  
  # Node.js版本
  echo -e "\n${BLUE}Node.js版本:${NC}"
  docker exec xampp-apache node -v
  
  # npm版本
  echo -e "\n${BLUE}npm版本:${NC}"
  docker exec xampp-apache npm -v
  
  # Composer版本
  echo -e "\n${BLUE}Composer版本:${NC}"
  docker exec xampp-apache composer -V | head -n 1
  
  echo ""
  show_urls
}

# 显示访问URL
show_urls() {
  source .env
  echo -e "${YELLOW}访问地址:${NC}"
  echo -e "网站: ${GREEN}http://localhost:${WEB_PORT:-80}${NC}"
  echo -e "phpMyAdmin: ${GREEN}http://localhost:${PMA_PORT:-8080}${NC}"
  echo -e "SSL: ${GREEN}https://localhost:${SSL_PORT:-443}${NC}"
}

# 主函数
main() {
  # 处理命令
  case "$1" in
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
      show_status
      ;;
    shell)
      enter_shell
      ;;
    mysql)
      enter_mysql
      ;;
    logs)
      view_logs
      ;;
    info)
      show_info
      ;;
    help|--help|-h)
      show_help
      ;;
    *)
      echo -e "${RED}错误: 未知命令 '$1'${NC}"
      show_help
      exit 1
      ;;
  esac
}

# 如果没有参数，显示帮助
if [ $# -eq 0 ]; then
  show_help
  exit 0
fi

# 执行主函数
main "$@" 