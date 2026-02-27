# Kislay PHP Production Skeleton

A production-ready boilerplate for the Kislay ecosystem, inspired by Express.js and Laravel.

## Features

- **Modular Architecture**: Controllers, Middleware, and Routes separated.
- **Database Ready**: Built-in SQLite support via PDO Service.
- **Composer Support**: PSR-4 autoloading included.
- **Non-blocking I/O**: Powered by Kislay Core extensions.

## Directory Structure

- `bin/`: Executables (e.g., server starter)
- `config/`: Application configuration
- `storage/`: Database and logs
- `src/`: Application logic
  - `Controllers/`: Request handlers
  - `Middleware/`: Request filtering
  - `Routes/`: API definitions
  - `Services/`: Database and other business services

## Getting Started

1. Install Kislay extensions.
2. `composer install`
3. Run the server:

```bash
./bin/server
```

## API Endpoints

- `GET /`: Welcome message
- `GET /health`: Liveness check
- `GET /api/v1/users`: List users from SQLite
- `POST /api/v1/users`: Create a random user in SQLite