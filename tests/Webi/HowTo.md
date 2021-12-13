# Sancti testing
Before tests install package see package README.md and create testing database.

## Create .env.testing from .env and change
nano .env.testing
```sh
# environment
APP_ENV=testing
APP_DEBUG=true

# Mysql settings
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_testing
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

## Create database user with mysql
mysql -u root -p
```sh
GRANT ALL PRIVILEGES ON *.* TO root@localhost IDENTIFIED BY 'toor' WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON *.* TO root@127.0.0.1 IDENTIFIED BY 'toor' WITH GRANT OPTION;
```

### Create database mysql command line
```sh
mysql -uroot -ptoor -e "CREATE DATABASE IF NOT EXISTS laravel_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

## Create laravel_testing databse tables
```sh
php artisan --env=testing migrate
```

## Add to app phpunit.xml config file
nano phpunit.xml
```xml
<env name="APP_ENV" value="testing" force="true"/>
<env name="APP_DEBUG" value="true" force="true"/>
```

## Publish
```sh
# go to laravel app
cd app

# copy test files
php artisan vendor:publish --tag=sancti-tests --force

# test with artisan
php artisan test tests/Sancti --stop-on-failure

# or with phpunit
vendor/bin/phpunit tests/Sancti --stop-on-failure
```

## Manually
Copy files from package **tests/Sancti** directory to laravel app **tests/Sancti** directory

### Add to laravel app phpunit.xml
```xml
<testsuite name="Sancti">
	<directory suffix="Test.php">./tests/Sancti</directory>
</testsuite>

<env name="APP_ENV" value="testing" force="true"/>
<env name="APP_DEBUG" value="true" force="true"/>
```

### Run tests
```sh
php artisan test tests/Sancti --stop-on-failure
```