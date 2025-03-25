# Generating tokens in Dotkernel API

This is a multipurpose command that allows creating tokens required by different parts of the API.

## Usage

Go to your application's root directory.

Run the token generator command by executing the following command:

```shell
php ./bin/cli.php token:generate <type>
```

Where `<type>` is one of the following:

* [error-reporting](#generate-error-reporting-token)

If you need help using the command, execute the following command:

```shell
php ./bin/cli.php token:generate --help
```

### Generate error reporting token

You can generate an error reporting token by executing the following command:

```shell
php ./bin/cli.php token:generate error-reporting
```

The output should look similar to this:

```text
Error reporting token:

    0123456789abcdef0123456789abcdef01234567

* copy the generated token
* open config/autoload/error-handling.global.php
* paste the copied string inside the tokens array found under the ErrorReportServiceInterface::class key.
```

Copy the generated token.

Open `config/autoload/error-handling.global.php` and paste the copied token under the `tokens` key, as shown below:

```php
return [
    // ...
    ErrorReportServiceInterface::class => [
        // ...
        'tokens' => [
            '0123456789abcdef0123456789abcdef01234567',
        ],
        // ...
    ]
]
```

Save and close `config/autoload/error-handling.global.php`.

> If your application is NOT in development mode, make sure you clear your config cache by executing:

```shell
php ./bin/clear-config-cache.php
```
