#!/bin/bash
# 项目管理脚本
# 用法: ./project-manager.sh [命令] [子命令] [参数...]

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # 无颜色

# 检查Docker容器是否运行
check_container() {
  if ! docker exec xampp-apache echo >/dev/null 2>&1; then
    echo -e "${RED}错误: Apache容器未运行${NC}"
    echo "请先启动容器: docker-compose up -d"
    exit 1
  fi
}

# 主帮助信息
show_main_help() {
  echo -e "${YELLOW}项目管理工具${NC}"
  echo "用法: $0 [命令] [子命令] [参数...]"
  echo ""
  echo "主要命令:"
  echo "  create      - 创建新的Laravel项目"
  echo "  init        - 初始化已有项目"
  echo "  config      - 管理Apache虚拟主机配置"
  echo "  dev         - 管理开发环境"
  echo "  help        - 显示帮助信息"
  echo ""
  echo "使用 '$0 [命令] help' 查看特定命令的帮助"
}

# 创建命令的帮助信息
show_create_help() {
  echo -e "${YELLOW}创建Laravel项目${NC}"
  echo "用法: $0 create [项目名] [选项]"
  echo ""
  echo "此命令会在www目录下创建一个新的Laravel项目,并配置好Apache虚拟主机。"
  echo "选项:"
  echo "  --vue             - 添加Vue支持并配置Vite"
  echo "  --db-name=DBNAME  - 指定数据库名(默认与项目名相同)"
  echo ""
  echo "示例:"
  echo "  $0 create lv-bookstore                     - 创建名为'lv-bookstore'的Laravel项目"
  echo "  $0 create lv-bookstore --vue               - 创建包含Vue支持的Laravel项目"
  echo "  $0 create lv-bookstore --db-name=bookstore - 创建项目并指定数据库名"
}

# 初始化命令的帮助信息
show_init_help() {
  echo -e "${YELLOW}初始化已有项目${NC}"
  echo "用法: $0 init [项目名] [选项]"
  echo ""
  echo "此命令会初始化www目录下已有的项目。"
  echo "选项:"
  echo "  --db-name=DBNAME  - 指定数据库名(默认与项目名相同)"
  echo "  --db-init         - 初始化数据库(运行迁移和填充)"
  echo "  --npm-install     - 安装前端依赖"
  echo ""
  echo "示例:"
  echo "  $0 init lv-bookstore                          - 初始化已有项目"
  echo "  $0 init lv-bookstore --db-name=bookstore      - 指定数据库名"
  echo "  $0 init lv-bookstore --db-init --npm-install  - 完整初始化"
}

# 配置命令的帮助信息
show_config_help() {
  echo -e "${YELLOW}管理项目配置${NC}"
  echo "用法: $0 config [子命令] [参数...]"
  echo ""
  echo "子命令:"
  echo "  list              - 列出所有可用项目配置"
  echo "  active            - 显示当前活动的项目配置"
  echo "  enable [项目名]    - 启用指定项目配置"
  echo "  disable [项目名]   - 禁用指定项目配置"
  echo "  switch [项目名]    - 切换到指定项目(禁用其他，启用指定项目)"
  echo "  basic             - 切换到基础模式(启用000-default.conf)"
  echo "  create [项目名]    - 从模板创建新的项目配置"
  echo ""
  echo "示例:"
  echo "  $0 config list                   - 显示所有项目"
  echo "  $0 config switch lv-bookstore    - 切换到lv-bookstore项目"
}

# 开发环境命令的帮助信息
show_dev_help() {
  echo -e "${YELLOW}管理开发环境${NC}"
  echo "用法: $0 dev [子命令] [项目名]"
  echo ""
  echo "子命令:"
  echo "  start [项目名]    - 启动项目的开发环境(Artisan serve和npm)"
  echo "  stop [项目名]     - 停止项目的开发环境"
  echo "  status           - 显示所有正在运行的开发环境"
  echo "  fix-vite [项目名] - 修复项目的Vite配置(添加Docker环境所需的server配置)"
  echo ""
  echo "示例:"
  echo "  $0 dev start lv-bookstore      - 启动lv-bookstore项目的开发环境"
  echo "  $0 dev stop lv-bookstore       - 停止lv-bookstore项目的开发环境"
  echo "  $0 dev fix-vite lv-bookstore   - 修复lv-bookstore的Vite配置"
}

#==================== 创建项目相关函数 ====================

# 创建Laravel项目
create_laravel_project() {
  local project_name=$1
  local with_vue=$2
  local db_name=${3:-$project_name}  # 如果未指定数据库名，则使用项目名
  local step=1
  
  # 检查项目名是否为空
  if [ -z "$project_name" ]; then
    echo -e "${RED}错误: 请提供项目名称${NC}"
    show_create_help
    exit 1
  fi
  
  # 检查项目是否已存在
  if docker exec xampp-apache bash -c "[ -d /var/www/html/$project_name ]"; then
    echo -e "${RED}错误: 项目 '$project_name' 已存在${NC}"
    exit 1
  fi
  
  echo -e "${YELLOW}开始创建Laravel项目: $project_name${NC}"
  
  # 创建Laravel项目
  echo "$((step++)). 使用Composer创建Laravel项目..."
  docker exec -it xampp-apache bash -c "cd /var/www/html && composer create-project laravel/laravel $project_name"
  
  # 如果选择了Vue，安装相关依赖
  if [ "$with_vue" = "true" ]; then
    echo "$((step++)). 配置Vue支持..."
    docker exec -it xampp-apache bash -c "cd /var/www/html/$project_name && composer require laravel/ui"
    docker exec -it xampp-apache bash -c "cd /var/www/html/$project_name && php artisan ui vue"
    docker exec -it xampp-apache bash -c "cd /var/www/html/$project_name && npm uninstall vite"
    docker exec -it xampp-apache bash -c "cd /var/www/html/$project_name && npm install vite@5.2.0 --save-dev"
    docker exec -it xampp-apache bash -c "cd /var/www/html/$project_name && npm install"
    
    # 配置Vite在Docker中正确工作
    echo "$((step++)). 配置Vite在Docker环境中工作..."
    docker exec -it xampp-apache bash -c "cd /var/www/html/$project_name && \
    if grep -q 'server:' vite.config.js; then
      echo 'Vite server配置已存在，跳过修改'
    else
      # 在配置对象末尾(最后的括号前)添加server配置
      sed -i 's/});/  server: {\n    host: \"0.0.0.0\",\n    hmr: {\n      host: \"localhost\"\n    }\n  }\n});/' vite.config.js
      echo 'Vite配置已更新'
    fi"
    
    echo -e "${YELLOW}提示: 前端资源需要构建${NC}"
  fi
  
  # 设置权限
  echo "$((step++)). 设置项目权限..."
  set_project_permissions "$project_name"
  
  # 创建Apache配置
  echo "$((step++)). 创建Apache配置..."
  create_apache_config "$project_name"

  # 配置数据库连接
  echo "$((step++)). 配置数据库连接..."
  configure_database "$project_name" "$db_name"
  
  # 显示成功信息和后续步骤
  echo -e "\n${GREEN}==============================${NC}"
  echo -e "${GREEN}Laravel项目 '$project_name' 创建成功!${NC}"
  echo -e "${GREEN}==============================${NC}"
  
  echo -e "\n${YELLOW}▶ 后续步骤${NC}"
  echo "1. 启用项目: $0 config switch $project_name.conf"
  echo "2. 访问项目: http://localhost 或 http://$project_name.local (需要配置hosts)"
  echo "3. 启动开发环境: $0 dev start $project_name"
}

# 配置数据库连接
configure_database() {
  local project_name=$1
  local db_name=${2:-$project_name}  # 如果未指定数据库名，则使用项目名
  local env_file="/var/www/html/$project_name/.env"
  
  # 获取主机环境变量
  source .env 2>/dev/null || true
  
  # 使用Docker容器中的sed命令更新数据库配置
  docker exec xampp-apache bash -c "sed -i 's/DB_HOST=127.0.0.1/DB_HOST=mariadb/g' $env_file"
  docker exec xampp-apache bash -c "sed -i 's/DB_DATABASE=laravel/DB_DATABASE=$db_name/g' $env_file"
  docker exec xampp-apache bash -c "sed -i 's/DB_USERNAME=root/DB_USERNAME=root/g' $env_file"
  docker exec xampp-apache bash -c "sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=${MARIADB_ROOT_PASSWORD:-root}/g' $env_file"
  
  echo -e "${GREEN}数据库连接已配置:${NC}"
  echo " - 数据库: $db_name"
  echo " - 主机: mariadb"
  echo " - 用户: root"
  echo " - 密码: ${MARIADB_ROOT_PASSWORD:-root}"
}

#==================== 初始化项目相关函数 ====================

# 初始化已有项目
init_existing_project() {
  local project_name=$1
  local db_name=$2
  local db_init=$3
  local npm_install=$4
  local step=1
  
  # 检查项目名是否为空
  if [ -z "$project_name" ]; then
    echo -e "${RED}错误: 请提供项目名称${NC}"
    show_init_help
    exit 1
  fi
  
  echo -e "${YELLOW}开始初始化项目: $project_name${NC}"
  
  # 检查项目是否存在
  if ! docker exec xampp-apache bash -c "[ -d /var/www/html/$project_name ]"; then
    echo -e "${RED}错误: 项目 '$project_name' 不存在${NC}"
    exit 1
  fi
  
  # 安装Composer依赖
  echo "$((step++)). 安装Composer依赖..."
  docker exec -it xampp-apache bash -c "cd /var/www/html/$project_name && composer install"
  
  # 检查并创建.env文件
  echo "$((step++)). 配置环境文件..."
  docker exec xampp-apache bash -c "cd /var/www/html/$project_name && \
  if [ ! -f .env ]; then
    if [ -f .env.example ]; then
      cp .env.example .env
      php artisan key:generate
    else
      echo '${RED}错误: 未找到.env.example文件${NC}'
    fi
  else
    echo '.env文件已存在,跳过创建'
  fi"
  
  # 配置数据库连接
  echo "$((step++)). 配置数据库连接..."
  configure_database "$project_name" "$db_name"
  
  # 如果指定了，运行数据库迁移
  if [ "$db_init" = "true" ]; then
    echo "$((step++)). 运行数据库迁移和填充..."
    # docker exec -it xampp-apache bash -c "cd /var/www/html/$project_name && php artisan migrate --seed"
    docker exec -it xampp-apache bash -c "cd /var/www/html/$project_name && php artisan migrate"
  fi
  
  # 如果指定了，安装npm依赖
  if [ "$npm_install" = "true" ]; then
    echo "$((step++)). 安装前端依赖..."
    docker exec -it xampp-apache bash -c "cd /var/www/html/$project_name && npm install"
    
    # 配置Vite在Docker中正确工作
    echo "$((step++)). 配置Vite在Docker环境中工作..."
    docker exec -it xampp-apache bash -c "cd /var/www/html/$project_name && \
    if [ -f vite.config.js ]; then
      if grep -q 'server:' vite.config.js; then
        echo 'Vite server配置已存在，跳过修改'
      else
        # 在配置对象末尾(最后的括号前)添加server配置
        sed -i 's/});/  server: {\n    host: \"0.0.0.0\",\n    hmr: {\n      host: \"localhost\"\n    }\n  }\n});/' vite.config.js
        echo 'Vite配置已更新'
      fi
    else
      echo 'vite.config.js文件不存在，跳过Vite配置'
    fi"
  fi
  
  # 设置权限
  echo "$((step++)). 设置项目权限..."
  set_project_permissions "$project_name"
  
  # 创建Apache配置
  echo "$((step++)). 创建Apache配置..."
  create_apache_config "$project_name"
  
  # 显示成功信息和后续步骤
  echo -e "\n${GREEN}==============================${NC}"
  echo -e "${GREEN}项目 '$project_name' 初始化成功!${NC}"
  echo -e "${GREEN}==============================${NC}"
  
  echo -e "\n${YELLOW}▶ 后续步骤${NC}"
  echo "1. 启用项目: $0 config switch $project_name.conf"
  echo "2. 访问项目: http://localhost 或 http://$project_name.local (需要配置hosts)"
  echo "3. 启动开发环境: $0 dev start $project_name"
}

# 设置项目权限
set_project_permissions() {
  local project_name=$1
  docker exec xampp-apache bash -c "chown -R www-data:www-data /var/www/html/$project_name"
  docker exec xampp-apache bash -c "chmod -R 755 /var/www/html/$project_name"
  docker exec xampp-apache bash -c "chmod -R 775 /var/www/html/$project_name/storage /var/www/html/$project_name/bootstrap/cache"
}

# 创建Apache配置
create_apache_config() {
  local project_name=$1
  docker exec xampp-apache bash -c "cp /etc/apache2/sites-available/laravel-template.conf /etc/apache2/sites-available/$project_name.conf"
  docker exec xampp-apache bash -c "sed -i 's/PROJECT_NAME/$project_name/g' /etc/apache2/sites-available/$project_name.conf"
}

#==================== 项目配置相关函数 ====================

# 列出所有可用的项目配置
config_list_projects() {
  echo -e "${YELLOW}可用项目配置:${NC}"
  docker exec xampp-apache ls -l /etc/apache2/sites-available/ | grep -v "^total" | awk '{print $9}'
  echo ""
  echo -e "${YELLOW}已启用的项目配置:${NC}"
  docker exec xampp-apache ls -l /etc/apache2/sites-enabled/ | grep -v "^total" | awk '{print $9}'
}

# 显示当前活动的项目
config_show_active() {
  echo -e "${YELLOW}当前启用的项目配置:${NC}"
  docker exec xampp-apache ls -l /etc/apache2/sites-enabled/ | grep -v "^total" | awk '{print $9}'
}

# 启用指定项目
config_enable_project() {
  if [ -z "$1" ]; then
    echo -e "${RED}错误: 未指定项目名${NC}"
    return 1
  fi
  
  local config_name="$1"
  # 检查是否已包含.conf后缀
  if [[ "$config_name" != *.conf ]]; then
    config_name="${config_name}.conf"
  fi
  
  echo "启用项目: $config_name"
  docker exec xampp-apache a2ensite "$config_name"
  docker exec xampp-apache service apache2 reload
  echo -e "${GREEN}项目 $config_name 已启用${NC}"
}

# 禁用指定项目
config_disable_project() {
  if [ -z "$1" ]; then
    echo -e "${RED}错误: 未指定项目名${NC}"
    return 1
  fi
  
  local config_name="$1"
  # 检查是否已包含.conf后缀
  if [[ "$config_name" != *.conf ]]; then
    config_name="${config_name}.conf"
  fi
  
  echo "禁用项目: $config_name"
  docker exec xampp-apache a2dissite "$config_name"
  docker exec xampp-apache service apache2 reload
  echo -e "${GREEN}项目 $config_name 已禁用${NC}"
}

# 切换到指定项目
config_switch_project() {
  if [ -z "$1" ]; then
    echo -e "${RED}错误: 未指定项目名${NC}"
    return 1
  fi
  
  local config_name="$1"
  # 检查是否已包含.conf后缀
  if [[ "$config_name" != *.conf ]]; then
    config_name="${config_name}.conf"
  fi
  
  echo "禁用所有已启用的站点..."
  docker exec xampp-apache bash -c 'a2dissite $(ls -A /etc/apache2/sites-enabled/)'
  echo "启用项目: $config_name"
  docker exec xampp-apache a2ensite "$config_name"
  docker exec xampp-apache service apache2 reload
  echo -e "${GREEN}已切换到项目: $config_name${NC}"
}

# 切换到基础模式
config_switch_to_basic() {
  echo "切换到基础模式..."
  docker exec xampp-apache bash -c 'a2dissite $(ls -A /etc/apache2/sites-enabled/)'
  docker exec xampp-apache a2ensite 000-default.conf
  docker exec xampp-apache service apache2 reload
  echo -e "${GREEN}已切换到基础模式${NC}"
}

# 从模板创建新项目配置
config_create_project() {
  if [ -z "$1" ]; then
    echo -e "${RED}错误: 未指定项目名${NC}"
    return 1
  fi
  
  echo "从模板创建项目配置: $1"
  docker exec xampp-apache bash -c "cp /etc/apache2/sites-available/laravel-template.conf /etc/apache2/sites-available/$1.conf"
  docker exec xampp-apache bash -c "sed -i 's/PROJECT_NAME/$1/g' /etc/apache2/sites-available/$1.conf"
  echo -e "${GREEN}已创建项目配置: $1.conf${NC}"
  
  echo -e "${YELLOW}提示: 使用以下命令启用此项目:${NC}"
  echo "  $0 config enable $1.conf"
  echo "或切换到此项目:"
  echo "  $0 config switch $1.conf"
}

#==================== 开发环境相关函数 ====================

# 检查screen是否安装
check_screen() {
  if ! docker exec xampp-apache which screen >/dev/null 2>&1; then
    echo -e "${YELLOW}安装screen...${NC}"
    docker exec xampp-apache apt-get update
    docker exec xampp-apache apt-get install -y screen
  fi
}

# 启动Laravel开发环境
dev_start_env() {
  local project_name=$1
  
  # 检查项目是否存在
  if ! docker exec xampp-apache bash -c "[ -d /var/www/html/$project_name ]"; then
    echo -e "${RED}错误: 项目 '$project_name' 不存在${NC}"
    exit 1
  fi
  
  echo -e "${YELLOW}在后台启动Laravel项目 '$project_name' 的开发环境${NC}"
  
  # 确保screen已安装
  check_screen
  
  # 停止可能正在运行的旧实例
  docker exec xampp-apache bash -c "screen -X -S artisan-$project_name quit" 2>/dev/null
  docker exec xampp-apache bash -c "screen -X -S npm-$project_name quit" 2>/dev/null
  
  echo "1. 启动PHP Artisan Serve..."
  docker exec -d xampp-apache bash -c "cd /var/www/html/$project_name && screen -dmS artisan-$project_name php artisan serve --host=0.0.0.0 --port=8000"
  
  echo "2. 启动npm开发服务器..."
  docker exec -d xampp-apache bash -c "cd /var/www/html/$project_name && screen -dmS npm-$project_name npm run dev"
  
  echo -e "${GREEN}服务已在后台启动!${NC}"
  echo -e "\n${GREEN}访问地址:${NC}"
  echo "Laravel应用: http://localhost:8000"
  echo "Vite开发服务器: http://localhost:5173"
  echo -e "\n${YELLOW}管理命令:${NC}"
  echo "查看运行中的服务: $0 dev status"
  echo "停止服务: $0 dev stop $project_name"
}

# 停止开发环境
dev_stop_env() {
  local project_name=$1
  
  echo -e "${YELLOW}停止Laravel项目 '$project_name' 的开发环境${NC}"
  
  docker exec xampp-apache bash -c "screen -X -S artisan-$project_name quit" 2>/dev/null
  docker exec xampp-apache bash -c "screen -X -S npm-$project_name quit" 2>/dev/null
  
  echo -e "${GREEN}服务已停止!${NC}"
}

# 显示开发环境状态
dev_show_status() {
  echo -e "${YELLOW}正在运行的开发环境:${NC}"
  docker exec xampp-apache screen -ls
}

# 修复Vite配置
fix_vite_config() {
  local project_name=$1
  
  if [ -z "$project_name" ]; then
    echo -e "${RED}错误: 请提供项目名称${NC}"
    return 1
  fi
  
  echo -e "${YELLOW}修复 $project_name 的Vite配置...${NC}"
  
  docker exec -it xampp-apache bash -c "cd /var/www/html/$project_name && \
  if [ -f vite.config.js ]; then
    if grep -q 'server:' vite.config.js; then
      echo 'Vite server配置已存在，跳过修改'
    else
      # 在配置对象末尾(最后的括号前)添加server配置
      sed -i 's/});/  server: {\n    host: \"0.0.0.0\",\n    hmr: {\n      host: \"localhost\"\n    }\n  }\n});/' vite.config.js
      echo 'Vite配置已更新'
    fi
  else
    echo '${RED}错误: vite.config.js文件不存在${NC}'
  fi"
}

#==================== 命令处理 ====================

# 处理创建命令
handle_create_command() {
  if [ "$1" = "help" ] || [ "$1" = "--help" ] || [ "$1" = "-h" ]; then
    show_create_help
    return
  fi
  
  local project_name=""
  local with_vue="false"
  local db_name=""
  
  # 解析参数
  for arg in "$@"; do
    if [ "$arg" = "--vue" ]; then
      with_vue="true"
    elif [[ "$arg" == "--db-name="* ]]; then
      db_name="${arg#--db-name=}"
    elif [ -z "$project_name" ]; then
      project_name="$arg"
    fi
  done
  
  create_laravel_project "$project_name" "$with_vue" "$db_name"
}

# 处理初始化命令
handle_init_command() {
  if [ "$1" = "help" ] || [ "$1" = "--help" ] || [ "$1" = "-h" ]; then
    show_init_help
    return
  fi
  
  local project_name=""
  local db_name=""
  local db_init="false"
  local npm_install="false"
  
  # 解析第一个参数为项目名
  if [ -n "$1" ]; then
    project_name="$1"
    shift
  fi
  
  # 解析其余参数
  for arg in "$@"; do
    if [[ "$arg" == "--db-name="* ]]; then
      db_name="${arg#--db-name=}"
    elif [ "$arg" = "--db-init" ]; then
      db_init="true"
    elif [ "$arg" = "--npm-install" ]; then
      npm_install="true"
    fi
  done
  
  init_existing_project "$project_name" "$db_name" "$db_init" "$npm_install"
}

# 处理配置命令
handle_config_command() {
  local subcommand="$1"
  
  if [ -z "$subcommand" ] || [ "$subcommand" = "help" ] || [ "$subcommand" = "--help" ] || [ "$subcommand" = "-h" ]; then
    show_config_help
    return
  fi
  
  shift
  
  case "$subcommand" in
    list)
      config_list_projects
      ;;
    active)
      config_show_active
      ;;
    enable)
      config_enable_project "$1"
      ;;
    disable)
      config_disable_project "$1"
      ;;
    switch)
      config_switch_project "$1"
      ;;
    basic)
      config_switch_to_basic
      ;;
    create)
      config_create_project "$1"
      ;;
    *)
      echo -e "${RED}错误: 未知子命令 '$subcommand'${NC}"
      show_config_help
      exit 1
      ;;
  esac
}

# 处理开发环境命令
handle_dev_command() {
  local subcommand="$1"
  
  if [ -z "$subcommand" ] || [ "$subcommand" = "help" ] || [ "$subcommand" = "--help" ] || [ "$subcommand" = "-h" ]; then
    show_dev_help
    return
  fi
  
  shift
  
  case "$subcommand" in
    start)
      dev_start_env "$1"
      ;;
    stop)
      dev_stop_env "$1"
      ;;
    status)
      dev_show_status
      ;;
    fix-vite)
      fix_vite_config "$1"
      ;;
    *)
      echo -e "${RED}错误: 未知子命令 '$subcommand'${NC}"
      show_dev_help
      exit 1
      ;;
  esac
}

# 主函数
main() {
  # 检查容器状态
  check_container
  
  # 处理主命令
  local command="$1"
  
  if [ -z "$command" ] || [ "$command" = "help" ] || [ "$command" = "--help" ] || [ "$command" = "-h" ]; then
    show_main_help
    return
  fi
  
  shift
  
  case "$command" in
    create)
      handle_create_command "$@"
      ;;
    init)
      handle_init_command "$@"
      ;;
    config)
      handle_config_command "$@"
      ;;
    dev)
      handle_dev_command "$@"
      ;;
    *)
      echo -e "${RED}错误: 未知命令 '$command'${NC}"
      show_main_help
      exit 1
      ;;
  esac
}

# 如果没有参数，显示帮助
if [ $# -eq 0 ]; then
  show_main_help
  exit 0
fi

# 执行主函数
main "$@" 