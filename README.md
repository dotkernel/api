# DotKernel API

DotKernel's PSR-15 API built around the Mezzio API skeleton based on [Enrico Zimuel's Zend Expressive API skeleton example](https://github.com/ezimuel/zend-expressive-api).

![OSS Lifecycle](https://img.shields.io/osslifecycle/dotkernel/api)

[![GitHub issues](https://img.shields.io/github/issues/dotkernel/api)](https://github.com/dotkernel/api/issues)
[![GitHub forks](https://img.shields.io/github/forks/dotkernel/api)](https://github.com/dotkernel/api/network)
[![GitHub stars](https://img.shields.io/github/stars/dotkernel/api)](https://github.com/dotkernel/api/stargazers)
[![GitHub license](https://img.shields.io/github/license/dotkernel/api)](https://github.com/dotkernel/api/blob/3.0/LICENSE.md)

![PHP from Packagist (specify version)](https://img.shields.io/packagist/php-v/dotkernel/api/3.0.x-dev)


## Getting Started

### Step 1: Clone the project
Using your terminal, navigate inside the directory you want to download the project files into. Make sure that the directory is empty before proceeding to the download process. Once there, run the following command:
```shell
git clone https://github.com/dotkernel/api.git .
```


### Step 2: Install project dependencies
```shell
composer install
```
During the installation process you will be prompted:
```shell
Please select which config file you wish to inject 'Laminas\*\ConfigProvider' into:
  [0] Do not inject
  [1] config/config.php
Make your selection (default is 1):
```
Please enter `0` because the application has an injected ConfigProvider which already contains the prompted configurations.

Next, you will be prompted: `Remember this option for other packages of the same type? (Y/n)`

Please hit `Enter` to accept the default option, which will also leave other packages' ConfigProviders not injected.


### Step 3: Development mode
If you're installing the project for development, make sure you have development mode enabled, by running:
```shell
composer development-enable
```

You can disable development mode by running:
```shell
composer development-disable
```

You can check if you have development mode enabled by running:
```shell
composer development-status
```


### Step 4: Prepare config files
* duplicate `config/autoload/cors.local.php.dist` as `config/autoload/cors.local.php` <- if your API will be consumed by another application, make sure configure the `allowed_origins`
* duplicate `config/autoload/local.php.dist` as `config/autoload/local.php`
* duplicate `config/autoload/mail.local.php.dist` as `config/autoload/mail.local.php` <- if your API will send emails, make sure you fill in SMTP connection params

Optional:
* duplicate `phpcs.xml.dist` as `phpcs.xml`
* duplicate `phpunit.xml.dist` as `phpunit.xml`


### Step 5: Setup database
* create a new MySQL database - set collation to `utf8mb4_general_ci`
* fill out the database connection params in `config/autoload/local.php` under `$databases['default']`
* run the database migrations by using the following command:
```shell
php vendor/bin/doctrine-migrations migrate
```
This command will prompt you to confirm that you want to run it:
```shell
WARNING! You are about to execute a migration in database "..." that could result in schema changes and data loss. Are you sure you wish to continue? (yes/no) [yes]:
```
Hit `Enter` to confirm the operation.


### Step 6: Test the installation
```shell
php -S 0.0.0.0:8080 -t public
```
Sending a GET request to the [home page](http://localhost:8080/) should output the following message:
```json
{
  "message": "Welcome to DotKernel API!"
}
```


## Using the CLI:
You can access the Mezzio's CLI by using the following command:
```shell
php vendor/bin/mezzio
```
You can access the Laminas' CLI by using the following command:
```shell
php vendor/bin/laminas
```
You can access Doctrine's CLI by using the following command:
```shell
php vendor/bin/doctrine
```
You can access DBAL CLI by using the following command:
```shell
php vendor/bin/doctrine-dbal
```
You can access Doctrine's migration tools by using the following command:
```shell
php vendor/bin/doctrine-migrations
```
You can access app-specific commands by using the following command:
```shell
php bin/cli.php
```
