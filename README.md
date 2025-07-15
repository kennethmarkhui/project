## Local Development Docker Setup

### Prerequisites

Ensure the following are installed and that Docker is currently running:

- [Docker](https://www.docker.com/get-started)
- [Docker Compose](https://docs.docker.com/compose/install)

### Unix systems (Linux, WSL, macOS)

1.  Initial Setup (Run Once)

    ```
    sh ./dev/setup.sh
    # OR
    bash ./dev/setup.sh
    ```

2.  Starting the Project

    ```
    sh ./dev/start.sh
    # OR
    bash ./dev/start.sh
    ```

3.  Stopping the Project

    ```
    docker-compose down
    ```

### Windows

1.  Install WSL (If not already installed)

    ```
    # powershell
    wsl --install
    ```

2.  Open VS Code in WSL

    - Install [Remote - WSL extension](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-wsl).

3.  In the project terminal

    - Initial Setup (Run Once)

        ```
        ./dev/setup.sh
        ```

    - Starting the project

        ```
        ./dev/start.sh
        ```

    - Stopping the Project

        ```
        docker-compose down
        ```

    - Or make it executable first

        ```
        chmod +x ./dev/setup.sh ./dev/start.sh
        ```

## Local Development Non-Docker Setup

### Prerequisites

Before you begin, ensure you have the following installed:

- [PHP](https://php.net)
- [Composer](https://getcomposer.org)
- [Node and NPM](https://nodejs.org)

1.  Install Dependencies and build frontend

    ```
    npm install && npm run build
    composer install
    ```

2.  Make .env and copy .env.example

3.  Setup Database [docs](https://laravel.com/docs/12.x/installation#databases-and-migrations)

4.  Migrate and Seed Database

    ```
    php artisan migrate:fresh --seed
    ```

5.  Start dev

    ```
    composer run dev
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

If you're using Docker setup, prepend docker-compose exec app to each command below.

```bash
# Start dev server
composer run dev
# → docker-compose exec app composer run dev

# List all laravel commands
php artisan
# → docker-compose exec app php artisan

# Run migrations
php artisan migrate
# → docker-compose exec app php artisan migrate

# Clear application cache
php artisan cache:clear
# → docker-compose exec app php artisan cache:clear

# Run tests
php artisan test
# → docker-compose exec app php artisan test
```
