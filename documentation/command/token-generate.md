# Generating tokens in DotKernel API

This is a multipurpose command that allows creating tokens required by different parts of the API.


## Usage

Go to your application's root directory.

Run the token generator command by executing the following command:

    php ./bin/cli.php token:generate <type>

Where `<type>` is one of the following:
* [error-reporting](#generate-error-reporting-token)

If you need help using the command, execute the following command:

    php ./bin/cli.php token:generate --help


### Generate error reporting token

You can generate an error reporting token by executing the following command:

    php ./bin/cli.php token:generate error-reporting

The output should look similar to this:

    Error reporting token:
    
        0123456789abcdef0123456789abcdef01234567

Copy the generated token.

Open `config/autoload/error-handling.global.php` and paste the copied token as shown below:

    return [
        ...
        ErrorReportServiceInterface::class => [
            ...
            'tokens' => [
                '0123456789abcdef0123456789abcdef01234567',
            ],
            ...
        ]
    ]

Save and close `config/autoload/error-handling.global.php`.

**Note**:

If your application is NOT in development mode, make sure you clear your config cache by executing:

    php ./bin/clear-config-cache.php
