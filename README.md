# DotKernel API
DotKernel's PSR-15 API built around the Zend Expressive API skeleton.


## Getting Started
Step 1: Install project dependencies by running:
```bash
$ composer install
```

Step 2: Prepare config files:
* duplicate `config/autoload/local.php.dist` as `config/autoload/local.php`
* duplicate `config/autoload/mail.local.php.dist` as `config/autoload/mail.local.php`

Step 3: Setup database:
* create a new MySQL database - set collation to `utf8mb4_general_ci`
* fill out the database connection params in `config/autoload/local.php`
* run the database migrations by using the following command:
```bash
$ vendor/bin/doctrine-migrations migrate
```

Step 4: Optional steps:
* Enable development mode using this command:
```bash
$ composer development-enable
```
* Configure SMTP by adding setting your account params in `config/autoload/mail.local.php` under `dot_mail -> default -> smtp_options`


## Using the CLI interface:
You can access Zend Expressive's CLI by using the following command:
```bash
$ composer expressive
```
You can access Doctrine's CLI by using the following command:
```bash
$ php vendor/doctrine/orm/bin/doctrine
```
You can access Doctrine's migration tools by using the following command:
```bash
$ vendor/bin/doctrine-migrations
```


## Running the application
```bash
$ php -S 0.0.0.0:8080 -t public
```
To test the application, visit the [home page](http://localhost:8080/). You should get the following message:
```json
{
  "message": "Welcome to DotKernel API!"
}
```


## Documentation
Visit [this link](http://localhost:8080/documentation) to access the application's documentation.
Here, you can request an access token using the /oauth2/generate endpoint using the following credentials:
```
username: test@dotkernel.com
password: dotkernel
```
