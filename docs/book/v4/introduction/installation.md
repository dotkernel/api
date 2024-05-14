# Installation

## Install dependencies

```shell
composer install
```

## Development mode

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

## Prepare config files

* duplicate `config/autoload/cors.local.php.dist` as `config/autoload/cors.local.php` <- if your API will be consumed by another application, make sure configure the `allowed_origins`
* duplicate `config/autoload/local.php.dist` as `config/autoload/local.php`
* duplicate `config/autoload/mail.local.php.dist` as `config/autoload/mail.local.php` <- if your API will send emails, make sure you fill in SMTP connection params
* **optional**: in order to run/create tests, duplicate `config/autoload/local.test.php.dist` as `config/autoload/local.test.php` <- this creates a new in-memory database that your tests will run on.

## Setup database

Make sure you fill out the database credentials in `config/autoload/local.php` under `$databases['default']`.

## Running migrations

* create a new MySQL database - set collation to `utf8mb4_general_ci`
* run the database migrations by using the following command:

```shell
php vendor/bin/doctrine-migrations migrate
```

This command will prompt you to confirm that you want to run it.

> WARNING! You are about to execute a migration in database "..." that could result in schema changes and data loss. Are you sure you wish to continue? (yes/no) [yes]:

Hit `Enter` to confirm the operation.

## Executing fixtures

**Fixtures are used to seed the database with initial values and should be executed after migrating the database.**

To list all the fixtures, run:

```shell
php bin/doctrine fixtures:list
```

This will output all the fixtures in the order of execution.

To execute all fixtures, run:

```shell
php bin/doctrine fixtures:execute
```

To execute a specific fixture, run:

```shell
php bin/doctrine fixtures:execute --class=FixtureClassName
```

More details on how fixtures work can be found here: https://github.com/dotkernel/dot-data-fixtures#creating-fixtures

## Test the installation

```shell
php -S 0.0.0.0:8080 -t public
```

Sending a GET request to the [home page](http://0.0.0.0:8080/) should output the following message:

> {"message": "Welcome to DotKernel API!"}

## Running tests

The project has 2 types of tests: functional and unit tests, you can run both types at the same type by executing this command:

```shell
php vendor/bin/phpunit
```

## Running unit tests

```shell
vendor/bin/phpunit --testsuite=UnitTests --testdox --colors=always
```

## Running functional tests

```shell
vendor/bin/phpunit --testsuite=FunctionalTests --testdox --colors=always
```
