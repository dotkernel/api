# Creating admin accounts in Dotkernel API

## Usage

Run the following command in your application’s root directory:

```shell
php ./bin/cli.php admin:create -i {IDENTITY} -p {PASSWORD}
```

OR

```shell
php ./bin/cli.php admin:create --identity {IDENTITY} --password {PASSWORD}
```

after replacing:

* _{IDENTITY}_ with a valid username OR email address
* _{PASSWORD}_ with a valid password

> If the specified identity or password contain special characters, make sure you surround them with double quote signs this method does not allow specifying an admin role – newly created accounts will have role of admin.

If the submitted data is valid, the outputted response is:

>
> [INFO] Admin account has been created.
>

The new admin account is ready to use.

You can get more help with this command by running:

```shell
php ./bin/cli.php help admin:create
```
