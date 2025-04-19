# LetsGo - Travel Website

LetsGo is a Laravel-based travel website that provides travel route planning, travel diary sharing, user social networking, and other features. The project uses Docker for development and deployment, allowing team members to collaborate in a consistent environment.

## Project Structure

```
LetsGo/
├── app/                  # Laravel application core code
├── bootstrap/            # Laravel bootstrap files
├── config/               # Configuration files
├── database/             # Database migrations and seeds
├── docker-compose.yml    # Docker Compose configuration
├── Dockerfile            # Docker image build configuration
├── public/               # Public resources directory
├── resources/            # Frontend resources (views, JS, CSS, etc.)
├── routes/               # Route definitions
├── scripts/              # Utility scripts
├── storage/              # Storage directory
└── tests/                # Test files
```

## Environment Requirements

- Docker
- Docker Compose
- Git

## Local Development Setup

### Clone Repository

```bash
git clone https://github.com/your-username/LetsGo.git
cd LetsGo
```

### Start Docker Environment

```bash
# Build and start containers
docker-compose up -d

# Check container status
docker-compose ps
```

### Project Initialization

```bash
# Enter container
docker exec -it xampp-apache bash

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database migration
php artisan migrate --seed

# Frontend resource compilation
npm run dev
```

### Access Website

- Website: http://localhost
- phpMyAdmin: http://localhost:8080

## Docker Environment Description

The project uses the following Docker containers:

- **apache**: Web server container with PHP
- **mariadb**: MariaDB 10.6 database server
- **phpmyadmin**: phpMyAdmin interface for database management

## Development Workflow

1. Create feature branches for development
```bash
git checkout -b feature/your-feature-name
```

2. Commit changes
```bash
git add .
git commit -m "Describe your changes"
```

3. Push to GitHub for collaboration
```bash
git push origin feature/your-feature-name
```

4. Create Pull Request for code review

## Project Maintenance

- **Stop containers**: `docker-compose down`
- **Rebuild**: `docker-compose build`
- **View logs**: `docker-compose logs -f [service-name]`

## Notes

- Ensure the database configuration in the `.env` file matches the configuration in docker-compose.yml
- When running database commands, make sure to execute them inside the container

## Contribution Guidelines

1. Follow Laravel coding standards
2. Write tests for all new features 
3. Run tests before submitting: `php artisan test`