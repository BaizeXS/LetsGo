#!/bin/bash

# Project management script
# Usage: ./scripts/project-manager.sh [command] [options]

# Ensure scripts directory exists
mkdir -p $(dirname "$0")

# Configuration file storage directory
CONFIG_DIR="./config/environments"
mkdir -p $CONFIG_DIR

# Color definitions
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Help information
show_help() {
    echo -e "${YELLOW}Project Management Tool${NC}"
    echo "Usage: $0 [command] [options]"
    echo ""
    echo "Available commands:"
    echo "  init [project_name] --db-name=name --db-init --npm-install  - Initialize project"
    echo "  config list              - List all available configurations"
    echo "  config switch [name]     - Switch to specified configuration"
    echo "  config save [name]       - Save current configuration"
    echo "  dev start [project_name] - Start development server"
    echo "  dev stop [project_name]  - Stop development server"
    echo "  docker up                - Start Docker containers"
    echo "  docker down              - Stop Docker containers"
    echo "  docker restart           - Restart Docker containers"
    echo "  help                     - Show help information"
}

# List all configurations
list_configs() {
    echo -e "${YELLOW}Available configurations:${NC}"
    if [ -d "$CONFIG_DIR" ]; then
        ls -1 "$CONFIG_DIR" | grep -v "README.md" | sed 's/\.env\.//'
    else
        echo "Configuration directory not found"
    fi
    
    echo -e "\n${YELLOW}Current configuration:${NC}"
    if [ -f ".env" ]; then
        # Try to extract APP_NAME or custom identifier from env file
        APP_NAME=$(grep "APP_NAME" .env | cut -d'=' -f2)
        if [ -n "$APP_NAME" ]; then
            echo "$APP_NAME"
        else
            echo "Default configuration (unnamed)"
        fi
    else
        echo ".env file not found"
    fi
}

# Switch configuration
switch_config() {
    CONFIG_NAME=$1
    CONFIG_FILE="$CONFIG_DIR/.env.$CONFIG_NAME"
    
    if [ -z "$CONFIG_NAME" ]; then
        echo -e "${RED}Error: Please specify a configuration name${NC}"
        list_configs
        return 1
    fi
    
    if [ -f "$CONFIG_FILE" ]; then
        cp "$CONFIG_FILE" .env
        echo -e "${GREEN}Switched to configuration: $CONFIG_NAME${NC}"
        
        # Ask whether to restart Docker
        read -p "Restart Docker containers to apply new configuration? (y/n): " restart
        if [[ $restart == "y" || $restart == "Y" ]]; then
            docker_restart
        fi
    else
        echo -e "${RED}Error: Configuration '$CONFIG_NAME' does not exist${NC}"
        list_configs
        return 1
    fi
}

# Save configuration
save_config() {
    CONFIG_NAME=$1
    
    if [ -z "$CONFIG_NAME" ]; then
        echo -e "${RED}Error: Please specify a configuration name${NC}"
        return 1
    fi
    
    if [ ! -f ".env" ]; then
        echo -e "${RED}Error: .env file not found${NC}"
        return 1
    fi
    
    mkdir -p "$CONFIG_DIR"
    cp .env "$CONFIG_DIR/.env.$CONFIG_NAME"
    echo -e "${GREEN}Current configuration saved as: $CONFIG_NAME${NC}"
}

# Initialize project
init_project() {
    PROJECT_NAME=$1
    shift
    
    if [ -z "$PROJECT_NAME" ]; then
        echo -e "${RED}Error: Please specify a project name${NC}"
        return 1
    fi
    
    echo -e "${YELLOW}Initializing project: $PROJECT_NAME${NC}"
    
    # Parse additional parameters
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
    
    # Create or update .env file
    if [ ! -f ".env.example" ]; then
        echo -e "${RED}Error: .env.example file not found${NC}"
        return 1
    fi
    
    cp .env.example .env
    
    # Update APP_NAME
    sed -i "s/APP_NAME=.*/APP_NAME=$PROJECT_NAME/" .env
    
    # If using Docker, update database configuration
    sed -i "s/DB_HOST=.*/DB_HOST=mariadb/" .env
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=root/" .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=root/" .env
    
    # Save configuration
    save_config $PROJECT_NAME
    
    # Generate application key
    echo -e "${YELLOW}Generating application key...${NC}"
    php artisan key:generate
    
    # Initialize database
    if [ "$DB_INIT" = true ]; then
        echo -e "${YELLOW}Executing database migration...${NC}"
        php artisan migrate:fresh --seed
    fi
    
    # Install npm dependencies
    if [ "$NPM_INSTALL" = true ]; then
        echo -e "${YELLOW}Installing frontend dependencies...${NC}"
        npm install
    fi
    
    echo -e "${GREEN}Project $PROJECT_NAME initialization completed!${NC}"
}

# Development server management
dev_start() {
    PROJECT_NAME=$1
    
    if [ -z "$PROJECT_NAME" ]; then
        echo -e "${RED}Error: Please specify a project name${NC}"
        return 1
    fi
    
    # Switch to corresponding configuration
    switch_config "$PROJECT_NAME"
    
    echo -e "${YELLOW}Starting development server: $PROJECT_NAME${NC}"
    
    # Use Docker to run project
    docker exec -it xampp-apache bash -c "cd /var/www/html && php artisan serve --host=0.0.0.0 --port=8000" &
    DEV_SERVER_PID=$!
    
    # Start npm
    echo -e "${YELLOW}Starting frontend build...${NC}"
    npm run dev &
    NPM_PID=$!
    
    echo -e "${GREEN}Development server started${NC}"
    echo "Access: http://localhost:8000"
    echo "Frontend development server running on: http://localhost:5173"
    
    # Save PID to temporary file
    echo "$DEV_SERVER_PID" > .dev_server_pid
    echo "$NPM_PID" > .npm_pid
}

dev_stop() {
    PROJECT_NAME=$1
    
    echo -e "${YELLOW}Stopping development server...${NC}"
    
    # If PID file exists, kill processes
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
    
    echo -e "${GREEN}Development server stopped${NC}"
}

# Docker management functions
docker_up() {
    echo -e "${YELLOW}Starting Docker containers...${NC}"
    docker-compose up -d
    echo -e "${GREEN}Docker containers started${NC}"
}

docker_down() {
    echo -e "${YELLOW}Stopping Docker containers...${NC}"
    docker-compose down
    echo -e "${GREEN}Docker containers stopped${NC}"
}

docker_restart() {
    echo -e "${YELLOW}Restarting Docker containers...${NC}"
    docker-compose restart
    echo -e "${GREEN}Docker containers restarted${NC}"
}

# Main function
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
                    echo -e "${RED}Error: Unknown configuration command${NC}"
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
                    echo -e "${RED}Error: Unknown development server command${NC}"
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
                    echo -e "${RED}Error: Unknown Docker command${NC}"
                    show_help
                    return 1
                    ;;
            esac
            ;;
        help)
            show_help
            ;;
        *)
            echo -e "${RED}Error: Unknown command $1${NC}"
            show_help
            return 1
            ;;
    esac
}

# Execute main function
main "$@" 