# Implementing a book module in DotKernel API

## File structure

The below file structure is just an example, you can have multiple components such as event listeners, wrappers, etc.

```markdown
.
└── src/
    └── Book/
        └── src/
            ├── Collection/
            │   └── BookCollection.php
            ├── Entity/
            │   └── Book.php
            ├── Handler/
            │   └── BookHandler.php
            ├── InputFilter/
            │   ├── Input/
            │   │   ├── AuthorInput.php
            │   │   ├── NameInput.php
            │   │   └── ReleaseDateInput.php
            │   └── BookInputFilter.php
            ├── Repository/
            │   └── BookRepository.php
            ├── Service/
            │   ├── BookService.php
            │   └── BookServiceInterface.php
            ├── ConfigProvider.php
            └── RoutesDelegator.php
```

* `src/Book/src/Collection/BookCollection.php` - a collection refers to a container for a group of related objects, typically used to manage sets of related entities fetched from a database
* `src/Book/src/Entity/Book.php` - an entity refers to a PHP class that represents a persistent object or data structure
* `src/Book/src/Handler/BookHandler.php` - handlers are middleware that can handle requests based on an action
* `src/Book/src/Repository/BookRepository.php` - a repository is a class responsible for querying and retrieving entities from the database
* `src/Book/src/Service/BookService.php` - is a class or component responsible for performing a specific task or providing functionality to other parts of the application
* `src/Book/src/ConfigProvider.php` -  is a class that provides configuration for various aspects of the framework or application
* `src/Book/src/RoutesDelegator.php` - a routes delegator is a delegator factory responsible for configuring routing middleware based on routing configuration provided by the application
* `src/Book/src/InputFilter/BookInputFilter.php` - input filters and validators
* `src/Book/src/InputFilter/Input/*` - input filters and validator configurations

## File creation and contents

* `src/Book/src/Collection/BookCollection.php`

```php
<?php
      
declare(strict_types=1);

namespace Api\Book\Collection;

use Api\App\Collection\ResourceCollection;

class BookCollection extends ResourceCollection
{
}
```

* `src/Book/src/Entity/Book.php`

To keep things simple in this tutorial our book will have 3 properties: `name`, `author` and `release date`.

```php
<?php

declare(strict_types=1);

namespace Api\Book\Entity;

use Api\App\Entity\AbstractEntity;
use Api\Book\Repository\BookRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ORM\Table("book")]
class Book extends AbstractEntity
{
    #[ORM\Column(name: "name", type: "string", length: 100)]
    protected string $name;

    #[ORM\Column(name: "author", type: "string", length: 100)]
    protected string $author;

    #[ORM\Column(name: "releaseDate", type: "datetime_immutable")]
    protected DateTimeImmutable $releaseDate;

    public function __construct(string $name, string $author, DateTimeImmutable $releaseDate)
    {
        parent::__construct();

        $this->setName($name);
        $this->setAuthor($author);
        $this->setReleaseDate($releaseDate);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getReleaseDate(): DateTimeImmutable
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(DateTimeImmutable $releaseDate): self
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getArrayCopy(): array
    {
        return [
            'uuid' => $this->getUuid()->toString(),
            'name' => $this->getName(),
            'author' => $this->getAuthor(),
            'releaseDate' => $this->getReleaseDate(),
        ];
    }
}
```

* `src/Book/src/Repository/BookRepository.php`

```php
<?php
      
declare(strict_types=1);

namespace Api\Book\Repository;

use Api\App\Helper\PaginationHelper;
use Api\Book\Collection\BookCollection;
use Api\Book\Entity\Book;
use Doctrine\ORM\EntityRepository;
use Dot\AnnotatedServices\Annotation\Entity;

/**
 * @Entity(name="Api\Book\Entity\Book")
 * @extends EntityRepository<object>
 */
class BookRepository extends EntityRepository
{
    public function saveBook(Book $book): Book
    {
        $this->getEntityManager()->persist($book);
        $this->getEntityManager()->flush();

        return $book;
    }

    public function getBooks(array $filters = []): BookCollection
    {
        $page = PaginationHelper::getOffsetAndLimit($filters);

        $qb = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('book')
            ->from(Book::class, 'book')
            ->orderBy($filters['order'] ?? 'book.created', $filters['dir'] ?? 'desc')
            ->setFirstResult($page['offset'])
            ->setMaxResults($page['limit']);

        $qb->getQuery()->useQueryCache(true);

        return new BookCollection($qb, false);
    }
}
```

* `src/Book/src/Service/BookService.php`

```php
<?php
      
declare(strict_types=1);

namespace Api\Book\Service;

use Api\Book\Entity\Book;
use Api\Book\Repository\BookRepository;
use Dot\AnnotatedServices\Annotation\Inject;
use DateTimeImmutable;

class BookService implements BookServiceInterface
{
    /**
     * @Inject({
     *     BookRepository::class,
     * })
     */
    public function __construct(protected BookRepository $bookRepository)
    {
    }

    public function createBook(array $data): Book
    {
        $book = new Book(
            $data['name'],
            $data['author'],
            new DateTimeImmutable($data['releaseDate'])
        );

        return $this->bookRepository->saveBook($book);
    }

    public function getBooks(array $filters = [])
    {
        return $this->bookRepository->getBooks($filters);
    }
}
```

* `src/Book/src/Service/BookServiceInterface.php`

```php
<?php
      
declare(strict_types=1);

namespace Api\Book\Service;

interface BookServiceInterface
{
}
```

* `src/Book/src/ConfigProvider.php`

```php
<?php
      
declare(strict_types=1);

namespace Api\Book;

use Api\Book\Collection\BookCollection;
use Api\Book\Entity\Book;
use Api\Book\Handler\BookHandler;
use Api\Book\Repository\BookRepository;
use Api\Book\Service\BookService;
use Api\Book\Service\BookServiceInterface;
use Dot\AnnotatedServices\Factory\AnnotatedRepositoryFactory;
use Dot\AnnotatedServices\Factory\AnnotatedServiceFactory;
use Mezzio\Hal\Metadata\MetadataMap;
use Api\App\ConfigProvider as AppConfigProvider;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'     => $this->getDependencies(),
            MetadataMap::class => $this->getHalConfig(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                BookHandler::class        => AnnotatedServiceFactory::class,
                BookService::class => AnnotatedServiceFactory::class,
                BookRepository::class => AnnotatedRepositoryFactory::class,
            ],
            'aliases'   => [
                BookServiceInterface::class     => BookService::class,
            ],
        ];
    }

    public function getHalConfig(): array
    {
        return [
            AppConfigProvider::getCollection(BookCollection::class, 'books.list', 'books'),
            AppConfigProvider::getResource(Book::class, 'book.create'),
        ];
    }
}
```

* `src/Book/src/RoutesDelegator.php`

```php
<?php
      
namespace Api\Book;

use Api\Book\Handler\BookHandler;
use Mezzio\Application;
use Psr\Container\ContainerInterface;

class RoutesDelegator
{
    public function __invoke(ContainerInterface $container, string $serviceName, callable $callback): Application
    {
        /** @var Application $app */
        $app = $callback();

        $app->get(
            '/books',
            BookHandler::class,
            'books.list'
        );

        $app->post(
            '/book',
            BookHandler::class,
            'book.create'
        );

        return $app;
    }
}
```

* `src/Book/src/InputFilter/BookInputFilter.php`

```php
<?php
      
declare(strict_types=1);

namespace Api\Book\InputFilter;

use Api\Book\InputFilter\Input\AuthorInput;
use Api\Book\InputFilter\Input\NameInput;
use Api\Book\InputFilter\Input\ReleaseDateInput;
use Laminas\InputFilter\InputFilter;

class BookInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(new NameInput('name'));
        $this->add(new AuthorInput('author'));
        $this->add(new ReleaseDateInput('releaseDate'));
    }
}
```

* `src/Book/src/InputFilter/Input/AuthorInput.php`

```php
<?php
      
declare(strict_types=1);

namespace Api\Book\InputFilter\Input;

use Api\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\Input;
use Laminas\Validator\NotEmpty;

class AuthorInput extends Input
{
    public function __construct(?string $name = null, bool $isRequired = true)
    {
        parent::__construct($name);

        $this->setRequired($isRequired);

        $this->getFilterChain()
            ->attachByName(StringTrim::class)
            ->attachByName(StripTags::class);

        $this->getValidatorChain()
            ->attachByName(NotEmpty::class, [
                'message' => sprintf(Message::VALIDATOR_REQUIRED_FIELD_BY_NAME, 'author'),
            ], true);
    }
}
```

* `src/Book/src/InputFilter/Input/NameInput.php`

```php
<?php
      
declare(strict_types=1);

namespace Api\Book\InputFilter\Input;

use Api\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\Input;
use Laminas\Validator\NotEmpty;

class NameInput extends Input
{
    public function __construct(?string $name = null, bool $isRequired = true)
    {
        parent::__construct($name);

        $this->setRequired($isRequired);

        $this->getFilterChain()
            ->attachByName(StringTrim::class)
            ->attachByName(StripTags::class);

        $this->getValidatorChain()
            ->attachByName(NotEmpty::class, [
                'message' => sprintf(Message::VALIDATOR_REQUIRED_FIELD_BY_NAME, 'name'),
            ], true);
    }
}
```

* `src/Book/src/InputFilter/Input/ReleaseDateInput.php`

```php
<?php
      
declare(strict_types=1);

namespace Api\Book\InputFilter\Input;

use Api\App\Message;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\Input;
use Laminas\Validator\Date;
use Laminas\Validator\NotEmpty;

class ReleaseDateInput extends Input
{
    public function __construct(?string $name = null, bool $isRequired = true)
    {
        parent::__construct($name);

        $this->setRequired($isRequired);

        $this->getFilterChain()
            ->attachByName(StringTrim::class)
            ->attachByName(StripTags::class);

        $this->getValidatorChain()
            ->attachByName(Date::class, [
                'message' => sprintf(Message::INVALID_VALUE, 'releaseDate'),
            ], true);
    }
}
```

* `src/Book/src/Handler/BookHandler.php`

```php
<?php
      
declare(strict_types=1);

namespace Api\Book\Handler;

use Api\App\Handler\ResponseTrait;
use Api\Book\InputFilter\BookInputFilter;
use Api\Book\Service\BookServiceInterface;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Dot\AnnotatedServices\Annotation\Inject;

class BookHandler implements RequestHandlerInterface
{
    use ResponseTrait;

    /**
     * @Inject({
     *     HalResponseFactory::class,
     *     ResourceGenerator::class,
     *     BookServiceInterface::class
     * })
     */
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected BookServiceInterface $bookService
    ) {
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $books = $this->bookService->getBooks($request->getQueryParams());

        return $this->createResponse($request, $books);
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new BookInputFilter())->setData($request->getParsedBody());
        if (! $inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        $book = $this->bookService->createBook($inputFilter->getValues());

        return $this->createResponse($request, $book);
    }
}
```

## Configuring and registering the new module

Once you set up all the files as in the example above, you will need to do a few additional configurations:

* register the namespace by adding this line `"Api\\Book\\": "src/Book/src/",` in `composer.json` under the `autoload.psr-4` key
* register the module by adding `Api\Book\ConfigProvider::class,` under `Api\User\ConfigProvider::class,`
* register the module's routes by adding `\Api\Book\RoutesDelegator::class,` under `\Api\User\RoutesDelegator::class,` in `src/App/src/ConfigProvider.php`
* update Composer autoloader by running the command:

```shell
composer dump-autoload
```

It should look like this:

```php
public function getDependencies(): array
{
    return [
        'delegators' => [
            Application::class => [
                RoutesDelegator::class,
                \Api\Admin\RoutesDelegator::class,
                \Api\User\RoutesDelegator::class,
                \Api\Book\RoutesDelegator::class,
            ],
        ],
        'factories'  => [
            ...
        ]
        ...
```

* In `src/config/autoload/doctrine.global.php` add this under the `doctrine.driver` key:

```php
'BookEntities'  => [
    'class' => AttributeDriver::class,
    'cache' => 'array',
    'paths' => __DIR__ . '/../../src/Book/src/Entity',
],
```

* `Api\\Book\Entity'    => 'BookEntities',` add this under the `doctrine.driver.drivers` key

Example:

```php
<?php
...
return [
    'doctrine'            => [
        ...
        'driver'        => [
            'orm_default'   => [
                'class'   => MappingDriverChain::class,
                'drivers' => [
                    'Api\\App\Entity'    => 'AppEntities',
                    'Api\\Admin\\Entity' => 'AdminEntities',
                    'Api\\User\\Entity'  => 'UserEntities',
                    'Api\\Book\Entity'    => 'BookEntities',
                ],
            ],
            'AdminEntities' => [
                'class' => AttributeDriver::class,
                'cache' => 'array',
                'paths' => __DIR__ . '/../../src/Admin/src/Entity',
            ],
            'UserEntities'  => [
                'class' => AttributeDriver::class,
                'cache' => 'array',
                'paths' => __DIR__ . '/../../src/User/src/Entity',
            ],
            'AppEntities'   => [
                'class' => AttributeDriver::class,
                'cache' => 'array',
                'paths' => __DIR__ . '/../../src/App/src/Entity',
            ],
            'BookEntities'  => [
                'class' => AttributeDriver::class,
                'cache' => 'array',
                'paths' => __DIR__ . '/../../src/Book/src/Entity',
            ],
        ],
        ...
```

Next we need to configure access to the newly created endpoints, add `books.list` and `book.create` to the authorization rbac array, under the `UserRole::ROLE_GUEST` key.
> Make sure you read and understand the rbac documentation.

## Migrations

We created the `Book` entity, but we didn't create the associated table for it.

Doctrine can handle the table creation, run the following command:

```shell
vendor/bin/doctrine-migrations diff --filter-expression='/^(?!oauth_)/'
```

This will check for differences between your entities and database structure and create migration files if necessary, in `data/doctrine/migrations`.

To execute the migrations run:

```shell
vendor/bin/doctrine-migrations migrate
```

## Checking endpoints

If we did everything as planned we can call the `http://0.0.0.0:8080/book` endpoint and create a new book:

```shell
curl -X POST http://0.0.0.0:8080/book
  -H "Content-Type: application/json"
  -d '{"name": "test", "author": "author name", "releaseDate": "2023-03-03"}'
```

To list the books use:

```shell
curl http://0.0.0.0:8080/books
```
