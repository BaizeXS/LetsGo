#!/bin/bash
# LetsGo Project Manager Script
# Usage: ./project-manager.sh [command] [parameters...]

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Check if Docker containers are running
check_container() {
  if ! docker exec xampp-apache echo >/dev/null 2>&1; then
    echo -e "${RED}Error: Apache container not running${NC}"
    echo -e "Please start the container first: ${BLUE}./xampp.sh start${NC}"
    exit 1
  fi
}

# Show help information
show_help() {
  echo -e "${YELLOW}LetsGo Project Manager${NC}"
  echo "Usage: $0 [command] [parameters...]"
  echo ""
  echo "Commands:"
  echo "  init        - Initialize project (install dependencies, run migrations, etc.)"
  echo "  dev start   - Start development environment (Artisan serve and npm)"
  echo "  dev stop    - Stop development environment"
  echo "  dev status  - Show development environment status"
  echo "  fix-vite    - Fix Vite configuration to work in Docker"
  echo ""
  echo "Examples:"
  echo "  $0 init     - Initialize project"
  echo "  $0 dev start - Start development environment"
}

# Initialize project
init_project() {
  echo -e "${YELLOW}Initializing LetsGo project...${NC}"
  check_container
  local step=1
  
  # Install Composer dependencies
  echo -e "${BLUE}$((step++)). Install Composer dependencies...${NC}"
  docker exec -it xampp-apache bash -c "cd /var/www/html && composer install"
  
  # Ensure .env file exists
  echo -e "${BLUE}$((step++)). Check environment file...${NC}"
  if [ ! -f ".env" ] && [ -f ".env.example" ]; then
    cp .env.example .env
    echo -e "${GREEN}Environment file created${NC}"
  fi

  # Auto configure database connection
  echo -e "${BLUE}$((step++)). Auto configure database connection...${NC}"
  docker exec xampp-apache bash -c "cd /var/www/html && \
  sed -i 's/DB_HOST=.*/DB_HOST=mariadb/g' .env && \
  sed -i 's/DB_DATABASE=.*/DB_DATABASE=letsgo/g' .env && \
  sed -i 's/DB_USERNAME=.*/DB_USERNAME=user/g' .env && \
  sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=password/g' .env"
  
  # Generate application key
  echo -e "${BLUE}$((step++)). Generate application key...${NC}"
  docker exec -it xampp-apache bash -c "cd /var/www/html && php artisan key:generate"

  # Add Vue support
  echo -e "${BLUE}$((step++)). Add Vue support...${NC}"
  docker exec -it xampp-apache bash -c "cd /var/www/html && composer require laravel/ui"
  docker exec -it xampp-apache bash -c "cd /var/www/html && php artisan ui vue"

  # Install npm dependencies
  echo -e "${BLUE}$((step++)). Install frontend dependencies...${NC}"
  docker exec -it xampp-apache bash -c "cd /var/www/html && npm uninstall vite"
  docker exec -it xampp-apache bash -c "cd /var/www/html && npm install vite@5.2.0 --save-dev"
  docker exec -it xampp-apache bash -c "cd /var/www/html && npm install"

  # Fix Vite configuration
  echo -e "${BLUE}$((step++)). Fix Vite configuration...${NC}"
  docker exec -it xampp-apache bash -c "cd /var/www/html && \
  if [ -f vite.config.js ]; then
    if grep -q 'server:' vite.config.js; then
      echo 'Vite server configuration already exists, skipping modification'
    else
      sed -i 's/});/  server: {\n    host: \"0.0.0.0\",\n    hmr: {\n      host: \"localhost\"\n    }\n  }\n});/' vite.config.js
      echo 'Vite configuration updated'
    fi
  else
    echo 'Vite configuration file not found, skipping'
  fi"
  
  # Run database migrations
  echo -e "${BLUE}$((step++)). Run database migrations...${NC}"
  read -p "Run database migrations? (y/n): " run_migration
  if [[ $run_migration == "y" || $run_migration == "Y" ]]; then
    docker exec -it xampp-apache bash -c "cd /var/www/html && php artisan migrate"
  fi

  # Set project permissions
  echo -e "${BLUE}$((step++)). Set project permissions...${NC}"
  docker exec xampp-apache bash -c "chown -R www-data:www-data /var/www/html"
  docker exec xampp-apache bash -c "chmod -R 755 /var/www/html"
  docker exec xampp-apache bash -c "chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache"
  
  echo -e "\n${GREEN}Project initialized!${NC}"
  echo -e "${YELLOW}▶ Use the following command to start the development environment:${NC}"
  echo "  $0 dev start"
}

# Check if screen is installed
check_screen() {
  if ! docker exec xampp-apache which screen >/dev/null 2>&1; then
    echo -e "${YELLOW}Installing screen...${NC}"
    docker exec xampp-apache apt-get update
    docker exec xampp-apache apt-get install -y screen
  fi
}

# Development environment related functions
dev_start() {
  echo -e "${YELLOW}Starting LetsGo development environment...${NC}"
  check_container
  check_screen
  
  # Stop any running services
  dev_stop >/dev/null 2>&1
  
  # Start services
  echo "1. Start PHP Artisan service..."
  docker exec -d xampp-apache bash -c "cd /var/www/html && screen -dmS artisan-letsgo php artisan serve --host=0.0.0.0 --port=8000"
  
  echo "2. Start npm development server..."
  docker exec -d xampp-apache bash -c "cd /var/www/html && screen -dmS npm-letsgo npm run dev"
  
  echo -e "${GREEN}Services started in the background!${NC}"
  
  # Show access addresses
  source .env 2>/dev/null || true
  echo -e "\n${GREEN}Access addresses:${NC}"
  echo -e "Laravel application: http://localhost:${LARAVEL_PORT:-8000}"
  echo -e "Vite development server: http://localhost:${VITE_PORT:-5173}"
}

dev_stop() {
  echo -e "${YELLOW}Stopping LetsGo development environment...${NC}"
  check_container
  
  docker exec xampp-apache bash -c "screen -X -S artisan-letsgo quit" 2>/dev/null
  docker exec xampp-apache bash -c "screen -X -S npm-letsgo quit" 2>/dev/null
  
  echo -e "${GREEN}Services stopped!${NC}"
}

dev_status() {
  echo -e "${YELLOW}LetsGo development environment status:${NC}"
  check_container
  docker exec xampp-apache screen -ls
}

# Fix Vite configuration
fix_vite_config() {
  echo -e "${YELLOW}Fixing Vite configuration to work in Docker...${NC}"
  check_container
  
  docker exec -it xampp-apache bash -c "cd /var/www/html && \
  if [ -f vite.config.js ]; then
    if grep -q 'server:' vite.config.js; then
      echo '${GREEN}Vite server configuration already exists, skipping modification${NC}'
    else
      # Add server configuration to the end of the configuration object (before the closing bracket)
      sed -i 's/});/  server: {\n    host: \"0.0.0.0\",\n    hmr: {\n      host: \"localhost\"\n    }\n  }\n});/' vite.config.js
      echo '${GREEN}Vite configuration updated${NC}'
    fi
  else
    echo '${RED}Error: vite.config.js file does not exist${NC}'
  fi"
}

# Main function
main() {
  case "$1" in
    init)
      init_project
      ;;
    dev)
      case "$2" in
        start)
          dev_start
          ;;
        stop)
          dev_stop
          ;;
        status)
          dev_status
          ;;
        *)
          echo -e "${RED}Error: Unknown dev subcommand '$2'${NC}"
          show_help
          exit 1
          ;;
      esac
      ;;
    fix-vite)
      fix_vite_config
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