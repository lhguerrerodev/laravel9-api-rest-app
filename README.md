
## Api rest - JWT Auth - Deploy Hosting

1.- Create the project:
> composer create-project laravel/laravel api-rest-app
> cd api-rest-app, php artisan serve

2.- Create the DB and DB user, make sure that remote MySQL can be accessed from our IP (Give access to the IP from the web hosting)

3.- Configure DB connection in .env file (optional: .env.example without adding production passwords since it will be uploaded to git):

DB_CONNECTION=mysql
DB_HOST=191.101.13.223 // IP de tu hosting web
DB_PORT=3306
DB_DATABASE=u172740170_lhguerrero_db
DB_USERNAME=u172740170_admin
DB_PASSWORD=*******

4.- Make the migrations
> php artisan migrate

Update changes added in migrations:
> php artisan migrate:fresh

Run the seeds
> php artisan migrate:fresh --seed

Run only the seeds: 
> php artisan db:seed

In production: 
> php artisan db:seed --force


5.- Install and setup JWT

Repo: https://github.com/PHP-Open-Source-Saver/jwt-auth

> composer require php-open-source-saver/jwt-auth
> php artisan vendor:publish --provider="PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider"
> php artisan jwt:secret

6.- Configure AuthGuard config/auth.php

'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
],

'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],

    'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
    ],
],

7.- Modify the User model app/Models/User.php

8.- Modify Authentication middleware app/Http/Middleware/Authenticate.php for token validations

Note: For change JWT expire time, modify 'ttl' => env('JWT_TTL', 10) in config/jwt.php, time in minutes

9.- Create the AuthController and add auth methods
> php artisan make:controller AuthController

## Roles and permissions

> php artisan make:migration create_roles_permissions_tables
> php artisan make:model Role
> php artisan make:model Permission
> php artisan make:middleware Authorization

Add Authorization middleware in routeMiddleware app/Http/Kernel.php 
'authorization' => \App\Http\Middleware\Authorization::class

> php artisan make:controller RoleController
> php artisan make:controller PermissionController
> php artisan make:controller UserController


## Despues de clonar 
copy - paste .env.example and rename with .env
configure db conection in .env
> composer install
> php artisan key:generate
> php artisan jwt:secret

Note: make sure bd password contains valid characters for .env file