## Prerequisites

Before you begin, ensure you have the following installed:

- [Docker](https://www.docker.com/get-started)
- [Docker Compose](https://docs.docker.com/compose/install/)

## Setting Up

```sh
# Run only for the first time to setup docker
bash ./dev/setup.sh
```

```sh
# Starting the project
bash ./dev/start.sh
```

## Available Services

| Service     | Port                    |
| ----------- | ----------------------- |
| Application | `http://localhost:8000` |
| phpMyAdmin  | `http://localhost:8080` |

## Useful Commands

### Docker Commands

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# Access PHP container shell
docker-compose exec app bash

# List running containers
docker-compose ps
```

### Laravel Commands

```bash
# Start dev server
docker-compose exec app composer run dev

# Clear application cache
docker-compose exec app php artisan cache:clear

# Run migrations
docker-compose exec app php artisan migrate

# Create a new controller
docker-compose exec app php artisan make:controller

# Run tests
docker-compose exec app php artisan test
```
