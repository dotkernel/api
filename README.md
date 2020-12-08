# DotKernel API
DotKernel's PSR-15 API built around the Mezzio API skeleton.

Based on Enrico Zimuel's Zend Expressive API skeleton proposal.https://github.com/ezimuel/zend-expressive-api


## Getting Started
### Step 1: Install project dependencies by running:
```bash
$ composer install
```
During the installation process you will be prompted:
```bash
Please select which config file you wish to inject 'Laminas\*\ConfigProvider' into:
  [0] Do not inject
  [1] config/config.php
Make your selection (default is 1):
```
Please enter `0` because the application has an injected ConfigProvider which already contains the prompted configurations.

Next, you will be prompted: `Remember this option for other packages of the same type? (Y/n)`

Please hit `Enter` to accept the default option, which will also leave other packages' ConfigProviders uninjected.


### Step 2: Prepare config files:
* duplicate `config/autoload/local.php.dist` as `config/autoload/local.php`
* duplicate `config/autoload/mail.local.php.dist` as `config/autoload/mail.local.php`

### Step 3: Setup database:
* create a new MySQL database - set collation to `utf8mb4_general_ci`
* fill out the database connection params in `config/autoload/local.php`
* run the database migrations by using the following command:
```bash
$ vendor/bin/doctrine-migrations migrate
```

**NOTE:**  on Windows, using XAMPP, use the below command:
```bash
php vendor/doctrine/migrations/bin/doctrine-migrations migrate
```


### Step 4: Optional steps:
* Enable development mode using this command:
```bash
$ composer development-enable
```
* Configure SMTP by adding setting your account params in `config/autoload/mail.local.php` under `dot_mail -> default -> smtp_options`


## Using the CLI interface:
You can access the Mezzio's CLI by using the following command:
```bash
$ composer mezzio
```
You can access Doctrine's CLI by using the following command:
```bash
$ php vendor/doctrine/orm/bin/doctrine
```
You can access Doctrine's migration tools by using the following command:
```bash
$ vendor/bin/doctrine-migrations
```


## Running the application on your local machine:
```bash
$ php -S 0.0.0.0:8080 -t public
```
To test the application, visit the [home page](http://localhost:8080/). You should get the following message:
```json
{
  "message": "Welcome to DotKernel API!"
}
```


## Your application's documentation:
Visit [this link](http://localhost:8080/documentation) to access the application's documentation.
Here, you can request an access token using the `/oauth2/generate` endpoint using the following credentials:
```
username: test@dotkernel.com
password: dotkernel
```
**IMPORTANT: Don't forget to invalidate the above credentials on your application's production servers!**
