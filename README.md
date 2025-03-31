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
## To install PM2 globally and start your application using a configuration file:
1. Install PM2 globally:
    ```bash
    npm install -g pm2
    ```

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
