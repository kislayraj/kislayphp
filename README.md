# Kislay PHP Production Skeleton

A production-ready boilerplate for the Kislay ecosystem, inspired by Express.js and Laravel.

## Directory Structure

- `bin/`: Executables (e.g., server starter)
- `config/`: Application configuration
- `public/`: Static assets
- `src/`: Application logic
  - `Controllers/`: Request handlers
  - `Middleware/`: Request filtering
  - `Routes/`: API definitions
- `index.php`: Main entry point

## Getting Started

1. Install Kislay extensions (`https`, `gateway`, `discovery`).
2. Run the server:

```bash
./bin/server
```

## API Endpoints

- `GET /`: Welcome message
- `GET /health`: Liveness check
- `GET /api/v1/status`: Scoped health check