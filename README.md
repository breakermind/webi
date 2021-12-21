# Webi web api authentication for laravel
Web Rest Api register/login authentication with user account email verification.

### Default routes for rest api
- Register user
- Login user
- Activate email
- Reset password
- Change password
- Logout user

## Create database user with mysql
mysql -u root -p
```sh
GRANT ALL PRIVILEGES ON *.* TO root@localhost IDENTIFIED BY 'toor' WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON *.* TO root@127.0.0.1 IDENTIFIED BY 'toor' WITH GRANT OPTION;
```

### Create database mysql command line
```sh
# for app and tests
mysql -uroot -ptoor -e "CREATE DATABASE IF NOT EXISTS laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -uroot -ptoor -e "CREATE DATABASE IF NOT EXISTS laravel_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

## Create laravel project
```sh
composer create-project laravel/laravel webi
cd webi
```

### Install webi with composer (v2.0 or dev-main)
```sh
composer require breakermind/webi
```

### Or add in composer.json
```json
{
	"require": {
		"breakermind/webi": "^2.0"
	}
}
```

### Create database and configure mysql, smtp in .env file
nano webi/.env
```sh
# Mysql settings
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=toor

# Smpt (etc. gmail, mailgun or localhost)
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=25
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=hi@localhost
MAIL_FROM_NAME="${APP_NAME}"
```

### Change app User class
app/Models/User.php
```php
<?php
namespace App\Models;

use Webi\Models\WebiUser;
// use Laravel\Sanctum\HasApiTokens;

class User extends WebiUser
{
	// use HasApiTokens;

	protected $casts = [
		// 'email_verified_at' => 'datetime',
	];

	protected $dispatchesEvents = [
		// 'saved' => UserSaved::class,
		// 'deleted' => UserDeleted::class,
	];
}
```

## Webi setup (optional)
```sh
php artisan vendor:publish --provider="Webi\WebiServiceProvider.php"
php artisan vendor:publish --tag=webi-config --force
```

### Update composer autoload
```sh
composer update
composer dump-autoload -o
```

### Migrations
```sh
php artisan migrate
```

### Run local server
```sh
php artisan serv
```

## Default routes
Allowed request content types: application/json or x-www-form-urlencoded
```sh
# login local
curl -X POST http://app.xx/web/api/login -d '{"email": "bo@woo.xx", "password": "password123"}'
curl -X POST http://app.xx/web/api/login -F "password=password123" -F "email=bo@woo.xx"
curl -X POST http://127.0.0.1:8000/api/login -d '{"email": "bo@woo.xx", "password": "password123"}' -H 'Content-Type: application/json'

# register
curl -X POST http://app.xx/web/api/register -F "name=Jony" -F "password=password123" -F "password_confirmation=password123" -F "email=bo@woo.xx"
curl -X POST http://127.0.0.1:8000/api/register -F "name=Jony" -F "password=password123" -F "password_confirmation=password123" -F "email=bo@woo.xx"

# activate
curl http://app.xx/web/api/activate/30/61928cad3f2d0
curl http://127.0.0.1:8000/api/activate/30/61928cad3f2d0

# reset
curl -X POST http://app.xx/web/api/reset -F "email=bo@woo.xx"
curl -X POST http://127.0.0.1:8000/api/reset -F "email=bo@woo.xx"

# get logged user data
curl http://app.xx/web/api/test/admin
curl http://127.0.0.1:8000/web/api/test/admin

# csrf token
curl http://app.xx/web/api/csrf
curl http://127.0.0.1:8000/web/api/csrf

# check is logged via remember me
curl http://app.xx/web/api/logged
curl http://127.0.0.1:8000/web/api/logged

# change pass logged users
curl -X POST http://app.xx/web/api/change-password -F "password_current=password123" -F "password=password1231" -F "password_confirmation=password1231"
curl http://127.0.0.1:8000/api/change-password -d '{"password_current": "password123", "password": "password1234", "password_confirmation": "password1234"}'

# logout logged users
curl http://app.xx/web/api/logout
curl http://127.0.0.1:8000/api/logout
```

## Get data with axios
```html
<!-- Csrf token -->
<meta name="csrf" content="{{ csrf_token() }}">

<!-- Axios -->
<script defer src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script type="text/javascript">
	window.onload = () => {
		let csrf = document.querySelector('meta[name=csrf]').content;
		// console.log(csrf)

		let data = {
			"name": "Webi",
			"email": "yo3@app.xx",
			"password": "password12345",
			"password_confirmation": "password12345",
		}

		axios.defaults.withCredentials = true

		axios.get('/web/api/csrf').then(async () => {
			try {
				let r = await axios.post('/web/api/register', data)
				console.log(data)
			} catch(e) {
				console.log(e.response.data)
				console.log(e.response.status);
				console.log(e.response.headers);
			}
		})
	}
</script>
```

## Route examples
routes/api.php
```php
<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/post/{id}', [PostController::class, 'details'])->name('post.details');

// Private routes
Route::middleware(['auth'])->group(function () {
	// Only authorized users
	Route::get('/user/{id}', [UserController::class, 'details'])->name('user.details');
});

// Api routes here
Route::prefix('web/api')->name('web.api.')->middleware(['web'])->group(function() {
	// Public routes goes here

	Route::middleware(['auth'])->group(function () {
		// Private routes goes here (logged users)
	});

	Route::prefix('panel')->middleware(['auth', 'webi-role:worker|admin'])->group(function () {
		// Private routes for user role worker and admin
		// Route::resource('orders', OrdersController::class);
	});

	Route::prefix('panel')->middleware(['auth', 'webi-role:admin'])->group(function () {
		// Private routes for administrator
		// Route::resource('settings', SettingsController::class);
	});
});

// Last route
Route::fallback(function (){
	return response()->json(['message' => 'Unauthorized'], 401);
})->name('fallback');
```

# Package settings (dev)

### Add service provider to config/app.php (if errors)
Add if installed not from composer or if local package or if errors
```php
'providers' => [
	Webi\WebiServiceProvider::class,
],
'aliases' => [
	'Webi' => Webi\Http\Facades\WebiFacade::class,
]
```

### Get package without packagist
```json
{
	"repositories": [{
		"type": "vcs",
		"url": "https://github.com/breakermind/webi"
	}],
	"require": {
		"breakermind/webi": "^2.0"
	}
}
```

### Local package development
Add import repo path to **dev-main** directory
```json
{
	"repositories": [{
		"type": "path",
		"url": "packages/breakermind/webi"
	}],
	"require": {
		"breakermind/webi": "dev-main"
	}
}
```

### Webi testing
```sh
# go to laravel app
cd app

# create testing config in
nano .env.testing

# database tables
php artisan --env=testing migrate

# copy test files
php artisan vendor:publish --tag=webi-tests --force

# test with artisan
php artisan test tests/Webi --stop-on-failure

# or with phpunit
vendor/bin/phpunit tests/Webi --stop-on-failure
```

### Install vps/linux
Install required server packages.
```sh
# firewall, stuff
sudo apt install git composer ufw net-tools dnsutils mailutils

# servers
sudo apt install mariadb-server postfix nginx redis memcached

# php
sudo apt install -y php7.3-fpm
sudo apt install -y php7.3-{mysql,json,xml,curl,mbstring,opcache,gd,imagick,imap,bcmath,bz2,zip,intl,redis,memcache,memcached}

sudo apt install -y php7.4-fpm
sudo apt install -y php7.4-{mysql,json,xml,curl,mbstring,opcache,gd,imagick,imap,bcmath,bz2,zip,intl,redis,memcache,memcached}

sudo apt install -y php8.0-fpm
sudo apt install -y php8.0-{mysql,xml,curl,mbstring,opcache,gd,imagick,imap,bcmath,bz2,zip,intl,redis,memcache,memcached}
```

### Localhost website
nano /etc/hosts
```sh
127.0.0.1 app.xx www.app.xx
```

### Permissions
```sh
mkdir -p /www/webi/public

chown -R username:www-data /www
chmod -R 2775 /www
```

### Nginx virtualhost
```conf
server {
	listen 80;
	listen [::]:80;
	server_name app.xx www.app.xx;
	root /www/webi/public;
	index index.php index.html;
	location / {
		# try_files $uri $uri/ =404;
		try_files $uri $uri/ /index.php$is_args$args;
	}
	location ~ \.php$ {
		include snippets/fastcgi-php.conf;
		fastcgi_pass unix:/run/php/php8.0-fpm.sock;
		# fastcgi_pass 127.0.0.1:9000;
	}
	location ~* \.(js|css|png|jpg|jpeg|gif|webp|svg|ico)$ {
		expires -1;
		access_log off;
	}
	disable_symlinks off;
	client_max_body_size 100M;
	charset utf-8;
	source_charset utf-8;
}
```