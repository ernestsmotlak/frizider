# Frizider API

A Laravel + Vue.js application for managing refrigerator inventory, pantry items, recipes, and grocery lists.

## Prerequisites

Before you begin, ensure you have the following installed on your system:

- **PHP** >= 8.2
- **Composer** (PHP dependency manager)
- **Node.js** >= 18.x and **npm** (or **yarn**)
- **SQLite** (or MySQL/PostgreSQL if preferred)
- **Git**

## Getting Started

### 1. Fork the Repository

1. Navigate to the repository on GitHub
2. Click the "Fork" button in the top-right corner
3. Clone your forked repository:

```bash
git clone https://github.com/YOUR_USERNAME/frizider-api.git
cd frizider-api
```

### 2. Install Laravel Dependencies

Install PHP dependencies using Composer:

```bash
composer install
```

### 3. Install Frontend Dependencies

Install Node.js dependencies for the Vue.js frontend:

```bash
cd frontend
npm install
cd ..
```

### 4. Environment Configuration

Copy the environment example file and generate the application key:

```bash
cp .env.example .env
php artisan key:generate
```

**Important**: Edit the `.env` file and configure the following variables:

- `APP_NAME` - Your application name
- `APP_URL` - Your application URL (default: `http://localhost:8000`)
- `DB_CONNECTION` - Database connection type (default: `sqlite`)
- `DB_DATABASE` - Database path (for SQLite: `database/database.sqlite`)
- `JWT_SECRET` - JWT authentication secret (generate with: `php artisan jwt:secret`)

Generate the JWT secret:

```bash
php artisan jwt:secret
```

### 5. Database Setup

If using SQLite, ensure the database file exists:

```bash
touch database/database.sqlite
```

Run the database migrations:

```bash
php artisan migrate
```

### 5.1. Seed Database (Optional)

A database seeder is available that creates test data including an admin user, ingredients, recipes, and shopping lists. To seed the database:

```bash
php artisan db:seed
```

Or to reset and seed the database:

```bash
php artisan migrate:fresh --seed
```

**Test User Credentials:**
- **Email:** `admin@example.com`
- **Password:** `admin`

The seeder creates:
- 1 admin user
- 1 storage space (Main Fridge)
- 5 pantry items (ingredients)
- 2 recipes with ingredients
- 2 shopping lists with items

### 6. Build Frontend Assets (Optional for Production)

If you want to build the frontend for production:

```bash
cd frontend
npm run build
cd ..
```

## Running the Application

### Development Mode

The project includes a convenient dev script that runs both Laravel and Vue development servers:

```bash
composer run dev
```

This will start:
- Laravel server on `http://localhost:8000`
- Vue development server on `http://localhost:5173`
- Queue worker
- Log viewer

### Manual Setup (Alternative)

If you prefer to run servers manually:

**Terminal 1 - Laravel API:**
```bash
php artisan serve
```

**Terminal 2 - Vue Frontend:**
```bash
cd frontend
npm run dev
```

The Laravel API will be available at `http://localhost:8000` and the Vue frontend at `http://localhost:5173`.

### Quick Setup Script

Alternatively, you can use the built-in setup script:

```bash
composer run setup
```

This will:
- Install Composer dependencies
- Create `.env` file if it doesn't exist
- Generate application key
- Run database migrations
- Install npm dependencies
- Build frontend assets

## Project Structure

```
frizider-api/
├── app/                    # Laravel application code
│   ├── Http/
│   │   ├── Controllers/    # API controllers
│   │   └── Middleware/     # Custom middleware
│   └── Models/             # Eloquent models
├── frontend/               # Vue.js frontend application
│   ├── src/
│   │   ├── api/           # API client configuration
│   │   ├── components/    # Vue components
│   │   ├── layouts/       # Layout components
│   │   ├── pages/         # Page components
│   │   ├── router/        # Vue Router configuration
│   │   └── stores/        # Pinia stores
│   └── package.json       # Frontend dependencies
├── config/                 # Laravel configuration files
├── database/
│   ├── migrations/        # Database migrations
│   └── database.sqlite    # SQLite database (created after migration)
└── routes/
    └── api.php            # API routes
```

## Technology Stack

### Backend
- **Laravel 12** - PHP framework
- **JWT Auth** (tymon/jwt-auth) - Authentication
- **SQLite** - Database (can be configured for MySQL/PostgreSQL)

### Frontend
- **Vue 3** - Progressive JavaScript framework
- **TypeScript** - Type-safe JavaScript
- **Vue Router** - Client-side routing
- **Pinia** - State management
- **Axios** - HTTP client
- **Tailwind CSS** - Utility-first CSS framework
- **Vite** - Build tool and development server

## Testing

Run the test suite:

```bash
composer run test
```

Or using PHPUnit directly:

```bash
php artisan test
```

## Additional Resources

### About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
