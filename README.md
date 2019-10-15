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

Step 4: (Optional) Enable development mode:
```bash
$ composer development-enable
```

After the project has been successfully installed, you should modify the default OAuth2 client and it's secret phrase.


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


## Creating an endpoint
Routes can be created in one of the following locations:
* `config/routes.php`
* `src/{module-name}/RoutesDelegator.php` (First, make sure you registered it in `src/App/ConfigProvider.php`'s `getDependencies` method, under the `delegators` key). By using this method, if you want to move a module between projects, you will automatically move the related endpoints as well.


## Working with entities
A good practice is storing all related entities in the same directory `Entity`, next to the module's `Handler` directory. For example:
* `src/Module/src/Example/Entity/ExampleEntity.php`
* `src/Module/src/Example/Entity/ExampleDetailEntity.php`
* `src/Module/src/Example/Entity/ExampleCategoryEntity.php`


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
