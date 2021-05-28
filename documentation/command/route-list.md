# Displaying DotKernel API endpoints using dot-cli

## Usage

Run the following command in your application’s root directory:

`php ./bin/cli.php route:list`

The command runs through all routes and extracts endpoint information in realtime.
The output should be similar to the following:
```text
+--------+---------------------------------+--------------------------------+
| Method | Name                            | Path                           |
+--------+---------------------------------+--------------------------------+
| PATCH  | account.activate                | /account/activate/{hash}       |
| POST   | account.activate.request        | /account/activate              |
| PATCH  | account.modify-password         | /account/reset-password/{hash} |
| POST   | account.recover-identity        | /account/recover-identity      |
| POST   | account.register                | /account/register              |
| POST   | account.reset-password.request  | /account/reset-password        |
| GET    | account.reset-password.validate | /account/reset-password/{hash} |
| POST   | admin.create                    | /admin                         |
| DELETE | admin.delete                    | /admin/{uuid}                  |
| GET    | admin.list                      | /admin                         |
| PATCH  | admin.my-account.update         | /admin/my-account              |
| GET    | admin.my-account.view           | /admin/my-account              |
| GET    | admin.role.list                 | /admin/role                    |
| GET    | admin.role.view                 | /admin/role/{uuid}             |
| PATCH  | admin.update                    | /admin/{uuid}                  |
| GET    | admin.view                      | /admin/{uuid}                  |
| POST   | error.report                    | /error-report                  |
| GET    | home                            | /                              |
| POST   | security.generate-token         | /security/generate-token       |
| POST   | security.refresh-token          | /security/refresh-token        |
| POST   | user.activate                   | /user/{uuid}/activate          |
| POST   | user.avatar.create              | /user/{uuid}/avatar            |
| DELETE | user.avatar.delete              | /user/{uuid}/avatar            |
| GET    | user.avatar.view                | /user/{uuid}/avatar            |
| POST   | user.create                     | /user                          |
| DELETE | user.delete                     | /user/{uuid}                   |
| GET    | user.list                       | /user                          |
| DELETE | user.my-account.delete          | /user/my-account               |
| PATCH  | user.my-account.update          | /user/my-account               |
| GET    | user.my-account.view            | /user/my-account               |
| POST   | user.my-avatar.create           | /user/my-avatar                |
| DELETE | user.my-avatar.delete           | /user/my-avatar                |
| GET    | user.my-avatar.view             | /user/my-avatar                |
| GET    | user.role.list                  | /user/role                     |
| GET    | user.role.view                  | /user/role/{uuid}              |
| PATCH  | user.update                     | /user/{uuid}                   |
| GET    | user.view                       | /user/{uuid}                   |
+--------+---------------------------------+--------------------------------+
```

You can get more help with this command by running:

`php ./bin/cli.php help route:list`
