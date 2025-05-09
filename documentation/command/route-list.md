# Displaying Dotkernel API endpoints using dot-cli

## Usage

Run the following command in your application’s root directory:

```shell
php ./bin/cli.php route:list
```

The command runs through all routes and extracts endpoint information in realtime.
The output should be similar to the following:

```text
+------+----------------+-------------------- 38 Routes ------+-------------------------------------+
|    # | Request method | Route name                          | Route path                          |
+------+----------------+-------------------------------------+-------------------------------------+
|    1 | GET            | app::view-index                     | /                                   |
|    2 | GET            | admin::list-admin                   | /admin                              |
|    3 | POST           | admin::create-admin                 | /admin                              |
|    4 | GET            | admin::view-account                 | /admin/account                      |
|    5 | PATCH          | admin::update-account               | /admin/account                      |
|    6 | GET            | admin::list-role                    | /admin/role                         |
|    7 | GET            | admin::view-role                    | /admin/role/{uuid}                  |
|    8 | DELETE         | admin::delete-admin                 | /admin/{uuid}                       |
|    9 | GET            | admin::view-admin                   | /admin/{uuid}                       |
|   10 | PATCH          | admin::update-admin                 | /admin/{uuid}                       |
|   11 | POST           | app::create-error-report            | /error-report                       |
|   12 | POST           | security::generate-token            | /security/generate-token            |
|   13 | POST           | security::refresh-token             | /security/refresh-token             |
|   14 | GET            | user::list-user                     | /user                               |
|   15 | POST           | user::create-user                   | /user                               |
|   16 | DELETE         | user::delete-account                | /user/account                       |
|   17 | GET            | user::view-account                  | /user/account                       |
|   18 | PATCH          | user::update-account                | /user/account                       |
|   19 | POST           | user::create-account                | /user/account                       |
|   20 | POST           | user::request-activate-account      | /user/account/activate              |
|   21 | PATCH          | user::activate-account              | /user/account/activate/{hash}       |
|   22 | DELETE         | user::delete-account-avatar         | /user/account/avatar                |
|   23 | GET            | user::view-account-avatar           | /user/account/avatar                |
|   24 | POST           | user::create-account-avatar         | /user/account/avatar                |
|   25 | POST           | user::recover-account               | /user/account/recover               |
|   26 | POST           | user::create-account-reset-password | /user/account/reset-password        |
|   27 | GET            | user::check-account-reset-password  | /user/account/reset-password/{hash} |
|   28 | PATCH          | user::update-account-reset-password | /user/account/reset-password/{hash} |
|   29 | GET            | user::list-role                     | /user/role                          |
|   30 | GET            | user::view-role                     | /user/role/{uuid}                   |
|   31 | DELETE         | user::delete-user                   | /user/{uuid}                        |
|   32 | GET            | user::view-user                     | /user/{uuid}                        |
|   33 | PATCH          | user::update-user                   | /user/{uuid}                        |
|   34 | PATCH          | user::activate-user                 | /user/{uuid}/activate               |
|   35 | DELETE         | user::delete-user-avatar            | /user/{uuid}/avatar                 |
|   36 | GET            | user::view-user-avatar              | /user/{uuid}/avatar                 |
|   37 | POST           | user::create-user-avatar            | /user/{uuid}/avatar                 |
|   38 | PATCH          | user::deactivate-user               | /user/{uuid}/deactivate             |
+------+----------------+-------------------------------------+-------------------------------------+
```

## Filtering results

The following filters can be applied when displaying the routes list:

* Filter routes by name, using: `-i|--name[=NAME]`
* Filter routes by path, using: `-p|--path[=PATH]`
* Filter routes by method, using: `-m|--method[=METHOD]`

The filters are case-insensitive and can be combined.

Get more help by running this command:

```shell
php ./bin/cli.php route:list --help
```
