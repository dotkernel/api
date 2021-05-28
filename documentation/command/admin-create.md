# Creating admin accounts in DotKernel API

## Usage

Run the following command in your application’s root directory:

`php ./bin/cli.php admin:create -i {IDENTITY} -p {PASSWORD}`

OR

`php ./bin/cli.php admin:create --identity {IDENTITY} --password {PASSWORD}`

after replacing:
* {IDENTITY} with a valid username OR email address
* {PASSWORD} with a valid password

**NOTE:**
* if the specified identity or password contain special characters, make sure you surround them with double quote signs
* this method does not allow specifying an admin role – newly created accounts will have role of admin 

If the submitted data is valid, the outputted response is:
`Admin account has been created.`

The new admin account is ready to use.

You can get more help with this command by running:

`php ./bin/cli.php help admin:create`
