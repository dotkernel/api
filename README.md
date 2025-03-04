# DotKernel API

DotKernel API is a PHP skeleton app for building REST APIs using [Laminas](https://github.com/laminas) and [Mezzio](https://github.com/mezzio) components and implements standards like PSR-3, PSR-4, PSR-7, PSR-11 and PSR-15.

Dotkernel API is an alternative for legacy Laminas API Tools (formerly Apigility) applications, and is based on Enrico Zimuel's [Zend Expressive API - Skeleton example](https://github.com/ezimuel/zend-expressive-api).

> Live [demo](https://api.dotkernel.net/).

## Documentation

Documentation is available at: https://docs.dotkernel.org/api-documentation/

## Badges

![OSS Lifecycle](https://img.shields.io/osslifecycle/dotkernel/api)
![PHP from Packagist (specify version)](https://img.shields.io/packagist/php-v/dotkernel/api/5.2.0)

[![GitHub issues](https://img.shields.io/github/issues/dotkernel/api)](https://github.com/dotkernel/api/issues)
[![GitHub forks](https://img.shields.io/github/forks/dotkernel/api)](https://github.com/dotkernel/api/network)
[![GitHub stars](https://img.shields.io/github/stars/dotkernel/api)](https://github.com/dotkernel/api/stargazers)
[![GitHub license](https://img.shields.io/github/license/dotkernel/api)](https://github.com/dotkernel/api/blob/5.0/LICENSE.md)

[![Build Static](https://github.com/dotkernel/api/actions/workflows/continuous-integration.yml/badge.svg?branch=5.0)](https://github.com/dotkernel/api/actions/workflows/continuous-integration.yml)
[![codecov](https://codecov.io/gh/dotkernel/api/graph/badge.svg?token=53FN78G5CK)](https://codecov.io/gh/dotkernel/api)
[![Qodana](https://github.com/dotkernel/api/actions/workflows/qodana_code_quality.yml/badge.svg?branch=5.0)](https://github.com/dotkernel/api/actions/workflows/qodana_code_quality.yml)
[![PHPStan](https://github.com/dotkernel/api/actions/workflows/static-analysis.yml/badge.svg?branch=5.0)](https://github.com/dotkernel/api/actions/workflows/static-analysis.yml)

## Getting Started

## Step 1: Clone the project

Using your terminal, navigate inside the directory you want to download the project files into. Make sure that the directory is empty before proceeding to the download process. Once there, run the following command:

    git clone https://github.com/dotkernel/api.git .

## Step 2: Install project's dependencies

    composer install

## Step 3: Development mode

If you're installing the project for development, make sure you have development mode enabled, by running:

    composer development-enable

You can disable development mode by running:

    composer development-disable

You can check if you have development mode enabled by running:

    composer development-status

## Step 4: Prepare config files

* duplicate `config/autoload/cors.local.php.dist` as `config/autoload/cors.local.php` <- if your API will be consumed by another application, make sure configure the `allowed_origins`
* duplicate `config/autoload/local.php.dist` as `config/autoload/local.php`
* duplicate `config/autoload/mail.local.php.dist` as `config/autoload/mail.local.php` <- if your API will send emails, make sure you fill in SMTP connection params
* **optional**: in order to run/create tests, duplicate `config/autoload/local.test.php.dist` as `config/autoload/local.test.php` <- this creates a new in-memory database that your tests will run on

## Step 5: Setup database

## Running migrations

* create a new MySQL database - set collation to `utf8mb4_general_ci`
* fill out the database connection params in `config/autoload/local.php` under `$databases['default']`
* run the database migrations by using the following command:

    php vendor/bin/doctrine-migrations migrate

This command will prompt you to confirm that you want to run it:

    WARNING! You are about to execute a migration in database "..." that could result in schema changes and data loss. Are you sure you wish to continue? (yes/no) [yes]:

Hit `Enter` to confirm the operation.

## Executing fixtures

**Fixtures are used to seed the database with initial values and should be executed after migrating the database.**

To list all the fixtures, run:

    php bin/doctrine fixtures:list

This will output all the fixtures in the order of execution.

To execute all fixtures, run:

    php bin/doctrine fixtures:execute

To execute a specific fixture, run:

    php bin/doctrine fixtures:execute --class=FixtureClassName

More details on how fixtures work can be found here: https://github.com/dotkernel/dot-data-fixtures#creating-fixtures

## Step 6: Test the installation

    php -S 0.0.0.0:8080 -t public

Sending a GET request to the [home page](http://0.0.0.0:8080/) should output the following message:

    {
    "message": "DotKernel API version 5"
    }

## Documentation

In order to access DotKernel API documentation, check the provided [readme file](documentation/README.md).

Additionally, each CLI command available has it's own documentation:

* [Create admin account](documentation/command/admin-create.md)
* [Generate database migrations](documentation/command/migrations-diff.md)
* [Display available endpoints](documentation/command/route-list.md)
* [Generate tokens](documentation/command/token-generate.md)
