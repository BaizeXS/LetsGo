#!/bin/bash
# XAMPP Docker Environment Management Script
# Usage: ./xampp.sh [command]

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Check docker-compose command format
if command -v docker-compose &>/dev/null; then
  COMPOSE_CMD="docker-compose"
elif command -v docker &>/dev/null && docker compose version &>/dev/null; then
  COMPOSE_CMD="docker compose"
else
  echo "Error: docker-compose or docker compose command not found"
  exit 1
fi

# Show help information
show_help() {
  echo -e "${YELLOW}XAMPP Docker Environment Management Tool${NC}"
  echo "Usage: $0 [command]"
  echo ""
  echo "Commands:"
  echo "  start       - Start XAMPP environment"
  echo "  stop        - Stop XAMPP environment"
  echo "  restart     - Restart XAMPP environment"
  echo "  status      - Show environment status"
  echo "  shell       - Enter Apache container shell"
  echo "  mysql       - Enter MariaDB client"
  echo "  logs        - View Apache logs"
  echo "  info        - Show environment information"
  echo ""
  echo "Examples:"
  echo "  $0 start    - Start environment"
  echo "  $0 shell    - Enter Apache container"
}

# Start environment
start_env() {
  echo -e "${BLUE}Starting XAMPP Docker environment...${NC}"
  $COMPOSE_CMD up -d
  echo -e "${GREEN}Environment started${NC}"
  echo ""
  show_urls
}

# Stop environment
stop_env() {
  echo -e "${BLUE}Stopping XAMPP Docker environment...${NC}"
  $COMPOSE_CMD down
  echo -e "${GREEN}Environment stopped${NC}"
}

# Restart environment
restart_env() {
  echo -e "${BLUE}Restarting XAMPP Docker environment...${NC}"
  $COMPOSE_CMD restart
  echo -e "${GREEN}Environment restarted${NC}"
  echo ""
  show_urls
}

# Show environment status
show_status() {
  echo -e "${YELLOW}XAMPP Docker environment status:${NC}"
  $COMPOSE_CMD ps
}

# Enter Apache container shell
enter_shell() {
  echo -e "${BLUE}Enter Apache container shell...${NC}"
  docker exec -it xampp-apache bash
}

# Enter MariaDB client
enter_mysql() {
  echo -e "${BLUE}Connecting to MariaDB...${NC}"
  source .env
  docker exec -it xampp-mariadb mysql -uroot -p${MARIADB_ROOT_PASSWORD:-root}
}

# View Apache logs
view_logs() {
  echo -e "${BLUE}View Apache logs...${NC}"
  docker logs -f xampp-apache
}

# Show environment information
show_info() {
  echo -e "${YELLOW}XAMPP Docker environment information:${NC}"
  
  # Check if containers are running
  if ! docker ps | grep -q xampp-apache; then
    echo -e "${RED}Environment not started${NC}"
    return
  fi
  
  # PHP version
  echo -e "${BLUE}PHP version:${NC}"
  docker exec xampp-apache php -v | head -n 1
  
  # Apache version
  echo -e "\n${BLUE}Apache version:${NC}"
  docker exec xampp-apache apache2 -v | head -n 1
  
  # MariaDB version
  echo -e "\n${BLUE}MariaDB version:${NC}"
  docker exec xampp-mariadb mysql -V
  
  # Node.js version
  echo -e "\n${BLUE}Node.js version:${NC}"
  docker exec xampp-apache node -v
  
  # npm version
  echo -e "\n${BLUE}npm version:${NC}"
  docker exec xampp-apache npm -v
  
  # Composer version
  echo -e "\n${BLUE}Composer version:${NC}"
  docker exec xampp-apache composer -V | head -n 1
  
  echo ""
  show_urls
}

# Show access URLs
show_urls() {
  source .env
  echo -e "${YELLOW}Access URLs:${NC}"
  echo -e "网站: ${GREEN}http://localhost:${WEB_PORT:-80}${NC}"
  echo -e "phpMyAdmin: ${GREEN}http://localhost:${PMA_PORT:-8080}${NC}"
  echo -e "SSL: ${GREEN}https://localhost:${SSL_PORT:-443}${NC}"
}

# Main function
main() {
  # Process commands
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
      echo -e "${RED}Error: Unknown command '$1'${NC}"
      show_help
      exit 1
      ;;
  esac
}

# If no parameters, show help
if [ $# -eq 0 ]; then
  show_help
  exit 0
fi

# Execute main function
main "$@"
