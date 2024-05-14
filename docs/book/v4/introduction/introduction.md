# Introduction

Based on Enrico Zimuel’s Zend Expressive API – Skeleton example, DotKernel API runs on Laminas and Mezzio components and implements standards like PSR-3, PSR-4, PSR-7, PSR-11 and PSR-15.

Here is a list of the core components:

* Middleware Microframework (mezzio/mezzio)
* Error Handler (dotkernel/dot-errorhandler)
* Problem Details (mezzio/mezzio-problem-details)
* CORS (mezzio/mezzio-cors)
* Routing (mezzio/mezzio-fastroute)
* Authentication (mezzio/mezzio-authentication)
* Authorization (mezzio/mezzio-authorization)
* Config Aggregator (laminas/laminas-config-aggregator)
* Container (roave/psr-container-doctrine)
* Annotations (dotkernel/dot-annotated-services)
* Input Filter (laminas/laminas-inputfilter)
* Doctrine 2 ORM (doctrine/orm)
* Serializer/Deserializer (laminas/laminas-hydrator)
* Paginator (laminas/laminas-paginator)
* HAL (mezzio/mezzio-hal)
* CLI (dotkernel/dot-cli)
* TwigRenderer (mezzio/mezzio-twigrenderer)
* Fixtures (dotkernel/dot-data-fixtures)
* UUID (ramsey/uuid-doctrine)

## Doctrine 2 ORM

For the persistence in a relational database management system we chose Doctrine ORM (object-relational mapper).

The benefit of Doctrine for the programmer is the ability to focus on the object-oriented business logic and worry about persistence only as a secondary priority.

## Documentation

Our documentation is Postman based. We use the following files in which we store information about every available endpoint ready to be tested:

* documentation/DotKernel_API.postman_collection.json
* documentation/DotKernel_API.postman_environment.json

## Hypertext Application Language

For our API payloads (a value object for describing the API resource, its relational links and any embedded/child resources related to it) we chose mezzio-hal.

## CORS

By using `MezzioCorsMiddlewareCorsMiddleware`, the CORS preflight will be recognized and the middleware will start to detect the proper CORS configuration. The Router is used to detect every allowed request method by executing a route match with all possible request methods. Therefore, for every preflight request, there is at least one Router request.

## OAuth 2.0

OAuth 2.0 is an authorization framework that enables applications to obtain limited access to user accounts on your DotKernel API. We are using mezzio/mezzio-authentication-oauth2 which provides OAuth 2.0 authentication for Mezzio and PSR-7/PSR-15 applications by using league/oauth2-server package.

## Email

It is not unlikely for an API to send emails depending on the use case. Here is another area where DotKernel API shines. Using `DotMailServiceMailService` provided by dotkernel/dot-mail you can easily send custom email templates.

## Configuration

From authorization at request route level to API keys for your application, you can find every configuration variable in the config directory.

Registering a new module can be done by including its ConfigProvider.php in config.php.

Brand new middlewares should go into pipeline.php. Here you can edit the order in which they run and find more info about the currently included ones.

You can further customize your api within the autoload directory where each configuration category has its own file.

## Routing

Each module has a `RoutesDelegator.php` file for managing existing routes inside that specific module. It also allows a quick way of adding new routes by providing the route path, Middlewares that the route will use and the route name.

You can allocate permissions per route name in order to restrict access for a user role to a specific route in `config/autoload/authorization.global.php`.

## Commands

For registering new commands first make sure your command class extends `SymfonyComponentConsoleCommandCommand`. Then you can enable it by registering it in `config/autoload/cli.global.php`.

## File locker

Here you will also find our brand-new file locker configuration, so you can easily turn it on or off (by default: `'enabled' => true`).

Note: The File Locker System will create a `command-{command-default-name}.lock` file which will not let another instance of the same command to run until the previous one has finished.

## PSR Standards

* [PSR-3](https://www.php-fig.org/psr/psr-3/): Logger Interface – the application uses `LoggerInterface` for error logging
* [PSR-4](https://www.php-fig.org/psr/psr-4): Autoloader – the application locates classes using an autoloader
* [PSR-7](https://www.php-fig.org/psr/psr-7): HTTP message interfaces – the handlers return `ResponseInterface`
* [PSR-11](https://www.php-fig.org/psr/psr-11): Container interface – the application is container-based
* [PSR-15](https://www.php-fig.org/psr/psr-15): HTTP Server Request Handlers – the handlers implement `RequestHandlerInterface`

## Tests

One of the best ways to ensure the quality of your product is to create and run functional and unit tests. You can find factory-made tests in the tests/AppTest/ folder, and you can also register your own.

We have 2 types of tests: functional and unit tests, you can run both types at the same type by executing this command:

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
