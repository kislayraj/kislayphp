# Kislay PHP Production Skeleton

A production-ready boilerplate for the Kislay ecosystem.

## Features

- **Modular Architecture**: Controllers, Middleware, and Routes separated.
- **Database Ready**: Laravel-style driver/connection config (`database.default`, `database.connections`).
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

### Install Extensions with PIE (No git clone/phpize flow)

```bash
pie install kislayphp/core
pie install kislayphp/gateway
pie install kislayphp/discovery
pie install kislayphp/queue
pie install kislayphp/metrics
pie install kislayphp/config
pie install kislayphp/eventbus
pie install kislayphp/persistence
```

If you need strict reproducibility, pin explicit versions in your deployment manifests.

### C++ Persistence Facades

When `kislayphp/persistence` is installed, the skeleton bootstraps it automatically:

- `Kislay\\Persistence\\DB`
- `Kislay\\Persistence\\Eloquent`

Core behavior handled in extension:

- Boots DB connections from config (`DB::boot($config['database'])`)
- Attaches request lifecycle (`DB::attach($app)`)
- Tracks PDO connections and auto-rolls back leaked transactions
- Keeps cache entries bounded with TTL controls

Example:

```php
$app = new Kislay\Core\App();
$config = App\Services\Configuration::all();

Kislay\Persistence\DB::boot($config['database']);
Kislay\Persistence\DB::attach($app);

$count = Kislay\Persistence\DB::transaction(function (PDO $db) {
    return (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
});
```

### Unified config class (developer API)

Developers should read configuration from one class:

```php
use App\Services\Configuration;

$config = Configuration::all();
$dbDefault = Configuration::get('database.default');
```

Merge behavior:
- Remote config (from `Kislay\\Config\\ConfigClient`) is loaded first.
- Local `config/app.php` is applied after remote and overrides matching keys.
- If remote is unavailable, local config is used as-is.

### Laravel-style model usage

Your app can keep model-style APIs while DB lifecycle is still handled by extension internals:

```php
use App\Models\User;
use App\ORM\DB;

$users = User::latest('id')->paginate(20, 1); // per page, page

$user = User::create([
    'name' => 'Jane',
    'email' => 'jane@example.com',
]);

$existingOrNew = User::firstOrCreate(
    ['email' => 'ops@example.com'],
    ['name' => 'Ops User']
);

$count = DB::table('users')->count();
```

### Model boot and events (Laravel-style)

```php
use App\ORM\Model;

final class User extends Model
{
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            // return false to cancel insert
        });

        static::saved(function (User $user) {
            // audit log, event dispatch, cache invalidation, etc.
        });
    }
}
```

## Run with Docker

From `/Users/dhruvraj/Documents/phpExtension/kislayphp`:

```bash
docker compose up --build -d
```

Check container:

```bash
docker compose ps
docker compose logs -f app
```

Stop:

```bash
docker compose down
```

### Docker Environment Variables

- `HTTP_PORT` (default `9000`)
- `APP_ENV` (default `production`)
- `DB_CONNECTION` (default `sqlite`)
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `SQLITE_DB_PATH` (default `/app/storage/database.sqlite`)
- `MYSQL_*`, `MARIADB_*`, `PGSQL_*` connection env vars
- `PIE_VERSION` build arg (default `1.3.8`)
- `CORE_VERSION` build arg (default `0.0.3`)
- `GATEWAY_VERSION` build arg (default `0.0.2`)
- `DISCOVERY_VERSION` build arg (default `0.0.3`)
- `QUEUE_VERSION` build arg (default `0.0.1`)
- `METRICS_VERSION` build arg (default `0.0.1`)
- `CONFIG_VERSION` build arg (default `0.0.1`)
- `EVENTBUS_VERSION` build arg (default `0.0.1`)
- `PERSISTENCE_VERSION` build arg (default `0.0.1`)
- `REQUIRED_EXTENSIONS` build arg (default empty; comma-separated package names to enforce)

### PIE Install Behavior

- `kislayphp/core` is required and build will fail if it cannot be installed.
- Other extensions are installed via PIE in best-effort mode; if an extension package is broken upstream, the image build continues and logs a warning.
- The image only enables extensions that were installed successfully.
- To enforce strict production builds, set `REQUIRED_EXTENSIONS`.

Example strict mode:

```bash
REQUIRED_EXTENSIONS=kislayphp/discovery,kislayphp/gateway,kislayphp/persistence \
docker compose up --build -d
```

### Quick API Check

```bash
curl -i http://127.0.0.1:9000/health
curl -i http://127.0.0.1:9000/api/v1/users
curl -i "http://127.0.0.1:9000/api/v1/users?page=1&limit=20"
curl -i -X POST http://127.0.0.1:9000/api/v1/users \
  -H "Content-Type: application/json" \
  -d '{"name":"Perf User","email":"perf-user@example.com"}'
```

## API Endpoints

- `GET /`: Welcome message
- `GET /health`: Liveness check
- `GET /api/v1/users`: List users from SQLite with pagination
: Query params: `page` (default `1`), `limit` (default `20`, max `100`)
- `POST /api/v1/users`: Create user in SQLite
: Request body: optional `name`, optional `email`
: Response codes: `201` created, `409` email conflict, `422` invalid email

## Performance Test

Run local benchmarks with ApacheBench:

```bash
printf '{"name":"Perf User"}' > tmp/post-user.json
ab -k -n 10000 -c 100 http://127.0.0.1:9008/health
ab -k -n 5000 -c 60 "http://127.0.0.1:9008/api/v1/users?page=1&limit=20"
ab -l -n 2000 -c 30 -p tmp/post-user.json -T application/json http://127.0.0.1:9008/api/v1/users
```

Latest report: `docs/PERFORMANCE_REPORT_2026-02-28.md`
