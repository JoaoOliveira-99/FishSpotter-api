# FishSpotter API

Backend API for **FishSpotter**, built with [Laravel](https://laravel.com).

This repository is part of the FishSpotter project:

```
FishSpotter/
├── api/     # Laravel backend (this folder)
└── admin/   # Admin panel
```

> **Status:** early development. The project is currently based on the Laravel skeleton and features are being added.

## Tech Stack

| Layer           | Technology                         |
| --------------- | ---------------------------------- |
| Language        | PHP 8.3+                           |
| Framework       | Laravel 13                         |
| Database        | SQLite (default), MySQL supported  |
| Frontend assets | Vite 8 + Tailwind CSS 4            |
| Testing         | PHPUnit 12                         |
| Code style      | Laravel Pint                       |

## Requirements

- PHP **8.3** or higher
- [Composer](https://getcomposer.org/)
- Node.js and npm
- SQLite (or MySQL, if you change the database connection)

## Getting Started

### 1. Install and configure

The quickest way is the bundled setup script:

```bash
composer run setup
```

This will:

1. Install PHP dependencies
2. Create `.env` from `.env.example` (if it doesn't exist)
3. Generate the application key
4. Run the database migrations
5. Install npm dependencies and build the frontend assets

<details>
<summary>Manual setup</summary>

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
npm run build
```

</details>

### 2. Run the development server

```bash
composer run dev
```

The API will be available at [http://localhost:8000](http://localhost:8000).

## Configuration

All configuration lives in the `.env` file. The most relevant variables:

| Variable         | Default                 | Description                            |
| ---------------- | ----------------------- | -------------------------------------- |
| `APP_NAME`       | `Laravel`               | Application name                       |
| `APP_ENV`        | `local`                 | Environment (`local`, `production`)    |
| `APP_DEBUG`      | `true`                  | Show detailed errors (disable in prod) |
| `APP_URL`        | `http://localhost:8000` | Base URL of the API                    |
| `DB_CONNECTION`  | `sqlite`                | Database driver                        |
| `QUEUE_CONNECTION` | `database`            | Queue driver                           |
| `CACHE_STORE`    | `database`              | Cache driver                           |

### Using MySQL

To use MySQL instead of SQLite, update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fishspotter
DB_USERNAME=root
DB_PASSWORD=
```

Then run the migrations again:

```bash
php artisan migrate
```

## Available Commands

| Command                    | Description                                   |
| -------------------------- | --------------------------------------------- |
| `composer run setup`       | Install everything and prepare the project    |
| `composer run dev`         | Start the development environment             |
| `composer run test`        | Run the test suite                            |
| `php artisan migrate`      | Run database migrations                       |
| `php artisan migrate:fresh --seed` | Reset the database and seed it        |
| `php artisan route:list`   | List all registered routes                    |
| `./vendor/bin/pint`        | Format the code using Laravel Pint            |
| `npm run dev`              | Start Vite in development mode                |
| `npm run build`            | Build frontend assets for production          |

## Testing

```bash
composer run test
```

Or directly:

```bash
php artisan test
```

## Project Structure

```
api/
├── app/
│   ├── Http/Controllers/   # Request handlers
│   ├── Models/             # Eloquent models
│   └── Providers/          # Service providers
├── bootstrap/              # Framework bootstrap files
├── config/                 # Configuration files
├── database/
│   ├── factories/          # Model factories
│   ├── migrations/         # Database schema
│   └── seeders/            # Seed data
├── public/                 # Web server entry point
├── resources/              # Views, CSS and JS
├── routes/                 # Route definitions
├── storage/                # Logs, cache and uploaded files
└── tests/                  # Feature and unit tests
```

## License

This project is licensed under the MIT License.
