#!/bin/bash
# XAMPP Docker Environment Management Script
# Usage: ./scripts/xampp.sh [command]

# Color definitions
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Help information
show_help() {
    echo -e "${YELLOW}XAMPP Docker Environment Management Tool${NC}"
    echo "Usage: $0 [command]"
    echo ""
    echo "Available commands:"
    echo "  start       - Start Docker environment"
    echo "  stop        - Stop Docker environment"
    echo "  restart     - Restart Docker environment"
    echo "  status      - Check Docker container status"
    echo "  logs        - View Docker container logs"
    echo "  help        - Show help information"
}

# Start Docker environment
start_env() {
    echo -e "${YELLOW}Starting Docker environment...${NC}"
    docker-compose up -d
    echo -e "${GREEN}Docker environment started${NC}"
    
    # Show container status
    docker-compose ps
    
    echo -e "\n${GREEN}Environment access addresses:${NC}"
    echo "- Website: http://localhost (or configured WEB_PORT port)"
    echo "- phpMyAdmin: http://localhost:8080 (or configured PMA_PORT port)"
}

# Stop Docker environment
stop_env() {
    echo -e "${YELLOW}Stopping Docker environment...${NC}"
    docker-compose down
    echo -e "${GREEN}Docker environment stopped${NC}"
}

# Restart Docker environment
restart_env() {
    echo -e "${YELLOW}Restarting Docker environment...${NC}"
    docker-compose restart
    echo -e "${GREEN}Docker environment restarted${NC}"
    
    # Show container status
    docker-compose ps
}

# Check Docker container status
status_env() {
    echo -e "${YELLOW}Docker container status:${NC}"
    docker-compose ps
}

# View Docker container logs
logs_env() {
    echo -e "${YELLOW}Docker container logs:${NC}"
    docker-compose logs
}

# Main function
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
            echo -e "${RED}Error: Unknown command $1${NC}"
            show_help
            return 1
            ;;
    esac
}

# Execute main function
main "$@" 