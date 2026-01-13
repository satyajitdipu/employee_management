# Cranberry Cookie

## Installation

To install Cranberry Cookie, follow these steps:

1. Navigate to the directory where you want to install Cranberry Cookie:

    ```
    cd /path/to/cranberry-cookie
    ```

2. Run the following command to install the required packages:

    ```
    npm clean-install
    composer install
    ```

## First Time Installation Only

If you are installing Cranberry Cookie for the first time, you will need to run the following additional steps:

1. Copy the example environment file and create a new `.env` file:

    ```
    cp .env.example .env
    ```

2. Generate an application key:

    ```
    php artisan key:generate
    ```

3. Run the database migrations and seed the database with initial data:

    ```
    php artisan migrate:fresh --seed
    ```

    **NOTE:** The `migrate:fresh` command destroys all data in the database. Do not run this command in a production environment.
## Testing

This project includes comprehensive test suites using PHPUnit.

### Running Tests

To run the test suite:

```bash
# Run all tests
php artisan test

# Run tests with coverage report
php artisan test --coverage

# Run specific test file
php artisan test tests/Unit/Models/UserTest.php

# Run tests in verbose mode
php artisan test --verbose
```

### Test Structure

- **Unit Tests** (`tests/Unit/`): Test individual units of code in isolation
- **Feature Tests** (`tests/Feature/`): Test user-facing features and HTTP endpoints

### CI/CD

This project uses GitHub Actions for continuous integration. The CI pipeline:
- Runs on every push and pull request
- Executes the full test suite
- Generates code coverage reports
- Enforces minimum 80% code coverage

## To install PM2 globally and start your application using a configuration file:

2. Start your application using the provided ecosystem configuration file (`ecosystem.config.cjs`):
    ```bash
    pm2 start ecosystem.config.cjs
    ```

This will install PM2 globally on your system, allowing you to manage your Node.js applications effectively. The second command starts your application using the specified configuration file, ensuring it runs smoothly under PM2's process management.


## For Production

If you are installing Cranberry Cookie in a production environment, you should use the following commands instead:

```
php artisan migrate
php artisan db:seed --class=InitialSetupSeeder
```

## Export Translations

To export translations for the `en` locale, run the following command:

```
php artisan translatable:export en
```

## Run Development Environment

To start the development server, run the following command:

```
php artisan serve
```

Then, run the following command to compile the front-end assets:

```
npm run dev
```

## Upgrade Filament

To upgrade Filament, run the following commands:

```
php artisan config:clear
php artisan livewire:discover
php artisan route:clear
php artisan view:clear
 php artisan storage:link
```

```
composer update
php artisan filament:upgrade
```
