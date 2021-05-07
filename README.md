# DotKernel API
DotKernel's PSR-15 API built around the Mezzio API skeleton.

Based on Enrico Zimuel's Zend Expressive API skeleton proposal.https://github.com/ezimuel/zend-expressive-api

![OSS Lifecycle](https://img.shields.io/osslifecycle/dotkernel/api)

[![GitHub issues](https://img.shields.io/github/issues/dotkernel/api)](https://github.com/dotkernel/api/issues)
[![GitHub forks](https://img.shields.io/github/forks/dotkernel/api)](https://github.com/dotkernel/api/network)
[![GitHub stars](https://img.shields.io/github/stars/dotkernel/api)](https://github.com/dotkernel/api/stargazers)
[![GitHub license](https://img.shields.io/github/license/dotkernel/api)](https://github.com/dotkernel/api/blob/3.0/LICENSE.md)

![PHP from Packagist (specify version)](https://img.shields.io/packagist/php-v/dotkernel/api/3.0.x-dev)


## Getting Started
### Step 1: Install project dependencies:
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


## Testing the installation:
```bash
$ php -S 0.0.0.0:8080 -t public
```
Visit your application's [home page](http://localhost:8080/). You should get the following message:
```json
{
  "message": "Welcome to DotKernel API!"
}
```


## Accessing API resources:
In order to consume protected endpoints of your API, your API calls must contain an `Authorization: Bearer <ACCESS_TOKEN>` header (replace `<ACCESS_TOKEN>` with an actual access token you just generated).

### Generating an access token:
`POST` one of the below raw JSON objects to your application's `/oauth2/generate` endpoint.

Use this JSON to generate an access token with admin privileges:
```json
{
  "grant_type": "password",
  "client_id": "admin",
  "client_secret": "admin",
  "scope": "api",
  "username": "admin",
  "password": "dotadmin"
}
```
or this one for an access token with default user privileges:
```json
{
  "grant_type": "password",
  "client_id": "frontend",
  "client_secret": "frontend",
  "scope": "api",
  "username": "test@dotkernel.com",
  "password": "dotkernel"
}
```

The response should have the following structure:
```json
{
    "token_type": "Bearer",
    "expires_in": 86400,
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJmcm9udGVuZCIsImp0aSI6ImIxOWFhNDAyYjNiZWRhMzQ3ZGVhZGE0MzIzNjUwNmNhYWFiYmNkYzk3NWEyYTE0YjNkYjRkY2MxY2UwMzgxZDI3Y2RlNjhhMWUyMzc5MDViIiwiaWF0IjoxNjE4OTE1MDg4Ljg0MzE5NSwibmJmIjoxNjE4OTE1MDg4Ljg0MzIsImV4cCI6MTYxOTAwMTQ4OC44MzI4OCwic3ViIjoidGVzdEBkb3RrZXJuZWwuY29tIiwic2NvcGVzIjpbImFwaSJdfQ.EBgH1IDZOuQLG6ujlZk2etgLFn9zwuRVdSRQpQvGUIMpEaheKTbISlGtJiWwinpZnFM3El-L70WHjd6BbP5qnkzSD-irR6SALvI0q8HdhqfdYwAQnOLoUCPQ7cGK-Gd7fNaSd4W2w7ULJqQ3IuodRTw5o-Cvnxa-qMrAtkmnonMN8XcpDAtxG2Y2fanHoROm_-lOnI_pB57OyD4lpKM1arkmPgDO9JSFUsMswsEUzpP5Ne6Wfpst4E4Hk4Rlxmc29f1Rj6nfijWDJCskY29z3ItNGJlnV4WJJclm7gpp_ssLxBgKV5iVERoojzFcypYSVK4bCVkBK2OBmik81me4Ng",
    "refresh_token": "def502006ed9681a5db2041762ab40b304dc8e9a3bc8a7e7b9db9851fd93ae16686c25eec4952373801cc278b1c5557c9f5e2c8e293e7cfe8a14328f228acd11eeb00b1fb348685bd381c2d41814a1b346095389f1f72ed45a20809dbd2306ba7fe0ac3a69f4e333d7bd39d1757044604e637a46aee7308ae83b9298b70fd91d81a45782df2327131648e9f30666b974f14dfe702671c71098622c85c25d969f7902c301fe76337188fe60008d75ed813332ddbea69daae26dc831b2efba5d8c52b848b378b542528beef7b65a863d9eb59ab42eb552b3983d5b0d7fc105f6c5533b1e91d22e46b8f556b7b551a0aaa0457f4ec00fae4d5409164be9465336d75a5c1394b1fcafdd936cfd6fd91aa56c87e130b0226535aac9af5e07f56bc8bb65011139f2109fcdcaa7f02960942fd099ddff51f2ae9e954998c8d8646fb1c9a6cba8c7d78fa117099f1fb3ee76a89495178b86ad667f9a23836ca324b315065935b66b8b5be81109ada0bba6f05add047fa7214bf0a733aec174e0f4079e7f"
}
```
where:
* `token_type` indicates the token type (in this case, it's a Bearer token)
* `expires_in` indicates the amount of **seconds** the access token will expire in
* `access_token` contains the access token (send this in the `Authorization` header of your API calls)
* `refresh_token` contains the refresh token (used to regenerate an access token when it expires)

### Refreshing an access token:
Replace:
* `<CLIENT_ID>` with the `client_id` your access token was generated for
* `<CLIENT_SECRET>` with the `client_secret` your access token was generated for
* `<REFRESH_TOKEN>` with the `refresh_token` your access token was generated for

in the below raw JSON and `POST` it to `/oauth2/refresh`:
```json
{
  "grant_type": "refresh_token",
  "client_id": "<CLIENT_ID>",
  "client_secret": "<CLIENT_SECRET>",
  "scope": "api",
  "refresh_token": "<REFRESH_TOKEN>"
}
```

The response should have the same structure as the one returned by the `/oauth2/generate` endpoint.

**IMPORTANT: Don't forget to invalidate the above credentials on your application's production servers!**
