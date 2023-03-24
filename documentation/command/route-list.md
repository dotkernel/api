# Displaying DotKernel API endpoints using dot-cli

## Usage

Run the following command in your application’s root directory:

    php ./bin/cli.php route:list

The command runs through all routes and extracts endpoint information in realtime.
The output should be similar to the following:
```text
+--------+---------------------------------+--------------------------------+
| Method | Name                            | Path                           |
+--------+---------------------------------+--------------------------------+
| DELETE | admin.delete                    | /admin/{uuid}                  |
| DELETE | user.my-account.delete          | /user/my-account               |
| DELETE | user.my-avatar.delete           | /user/my-avatar                |
| DELETE | user.delete                     | /user/{uuid}                   |
| DELETE | user.avatar.delete              | /user/{uuid}/avatar            |
| GET    | home                            | /                              |
| GET    | account.reset-password.validate | /account/reset-password/{hash} |
| GET    | admin.list                      | /admin                         |
| GET    | admin.my-account.view           | /admin/my-account              |
| GET    | admin.role.list                 | /admin/role                    |
| GET    | admin.role.view                 | /admin/role/{uuid}             |
| GET    | admin.view                      | /admin/{uuid}                  |
| GET    | user.list                       | /user                          |
| GET    | user.my-account.view            | /user/my-account               |
| GET    | user.my-avatar.view             | /user/my-avatar                |
| GET    | user.role.list                  | /user/role                     |
| GET    | user.role.view                  | /user/role/{uuid}              |
| GET    | user.view                       | /user/{uuid}                   |
| GET    | user.avatar.view                | /user/{uuid}/avatar            |
| PATCH  | account.activate                | /account/activate/{hash}       |
| PATCH  | account.modify-password         | /account/reset-password/{hash} |
| PATCH  | admin.my-account.update         | /admin/my-account              |
| PATCH  | admin.update                    | /admin/{uuid}                  |
| PATCH  | user.my-account.update          | /user/my-account               |
| PATCH  | user.update                     | /user/{uuid}                   |
| POST   | account.activate.request        | /account/activate              |
| POST   | account.recover-identity        | /account/recover-identity      |
| POST   | account.register                | /account/register              |
| POST   | account.reset-password.request  | /account/reset-password        |
| POST   | admin.create                    | /admin                         |
| POST   | error.report                    | /error-report                  |
| POST   | security.generate-token         | /security/generate-token       |
| POST   | security.refresh-token          | /security/refresh-token        |
| POST   | user.create                     | /user                          |
| POST   | user.my-avatar.create           | /user/my-avatar                |
| POST   | user.activate                   | /user/{uuid}/activate          |
| POST   | user.avatar.create              | /user/{uuid}/avatar            |
+--------+---------------------------------+--------------------------------+
```

## Filtering results

The following filters can be applied when displaying the routes list:
* Filter routes by name, using: `-i|--name[=NAME]`
* Filter routes by path, using: `-p|--path[=PATH]`
* Filter routes by method, using: `-m|--method[=METHOD]`

The filters are case-insensitive and can be combined.

Get more help by running this command:

    php ./bin/cli.php route:list --help
