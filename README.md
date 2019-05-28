# DotKernel API
DotKernel's PSR-15 API built around the Zend Expressive API skeleton.


## Getting Started
Step 1: Install project dependencies by running:
```bash
$ composer install
```

Step 2: Prepare config files:
* duplicate `config/autoload/db.local.php.dist` as `config/autoload/db.local.php`
* duplicate `config/autoload/local.php.dist` as `config/autoload/local.php`
* duplicate `config/autoload/authorization.local.php.dist` as `config/autoload/authorization.local.php`

Step 3: Setup database:
* create a new MySQL database - set collation to `utf8mb4_general_ci`
* fill out the database connection params in `config/autoload/db.local.php`
* run the database migrations by using the following command:
```bash
$ vendor/bin/doctrine-migrations migrate
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


## Creating a module
```bash
$ composer expressive module:create Module
```
This will create the directory structure under: `src/Module` with the module's ConfigProvider and add the it to `config/config.php`


## Creating a request handler
```bash
$ composer expressive handler:create Module\Example\Handler\ExampleHandler
```
This will create:
* the handler: `src/Module/Example/Handler/ExampleHandler.php`
* the factory: `src/Module/Example/Handler/ExampleHandlerFactory.php`
and register it in: `config/autoload/zend-expressive-tooling-factories.global.php`
For a cleaner structure, it is recommended refactoring handler factories by moving them to their own directory: `Factory`. In this case, make sure the factory's namespace is changed accordingly and that the change is reflected in `config/autoload/zend-expressive-tooling-factories.global.php` as well.


## Creating an endpoint
Routes can be created in one of the following locations:
* `config/routes.php`
* `src/{module-name}/RoutesDelegator.php` (First, make sure you registered it in `src/App/ConfigProvider.php`'s `getDependencies` method, under the `delegators` key). By using this method, if you want to move a module between projects, you will automatically move the related endpoints as well.


## Working with entities
A good practice is storing all related entities in the same directory `Entity`, next to the module's `Handler` directory. For example:
* `src/Module/Example/Entity/ExampleEntity.php`
* `src/Module/Example/Entity/ExampleDetailEntity.php`
* `src/Module/Example/Entity/ExampleCategoryEntity.php`


## Running the application
```bash
$ php -S 0.0.0.0:8080 -t public
```
To test the application, visit the [test page](http://localhost:8080/test). You should get the following message:
```json
{
  "message": "Welcome to DotKernel API!",
  "debug": {
    "database": "connected",
    "php": "7.x.x"
  }
}
```
If the request is successful, you can delete this endpoint from `src/App/RoutesDelegator.php`


## Documentation
Visit [this link](http://localhost:8080/documentation) to access the application's documentation.
