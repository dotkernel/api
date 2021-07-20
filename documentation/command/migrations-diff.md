# Generate a migration by comparing your current database with your mapping information.

## Usage

Run the following command in your application’s root directory:

`vendor/bin/doctrine-migrations diff`

If you have mapping modifications, this will create a new migration file under `data/doctrine/migrations/` directory.
Opening the migration file, you will notice that it contains some queries that will drop your `oauth_*` tables.
In order to avoid dropping these tables, you need to add a parameter called `filter-expression`.
At this step, you should also delete your latest migration with the DROP queries in it.

The command to be executed without dropping these tables looks like this:

`vendor/bin/doctrine-migrations diff --filter-expression="/^(?!oauth_)/"`


## Filtering multiple unmapped table patterns

If your database contains multiple unmapped table groups, then the pattern in `filter-expression` should hold all table prefixes concatenated by pipe character (`|`).
For example, if you need to filter tables prefixed with `foo_` and `bar_`,  then the command should look like this:

`vendor/bin/doctrine-migrations diff --filter-expression="/^(?!foo_|bar_)/"`


## Troubleshooting

On Windows, running the command in PowerShell might still add the `DROP TABLE oauth_*` queries to the migration file.
This happens because for PowerShell the caret (`^`) is a special character, so it gets dropped (`"/^(?!oauth_)/"` becomes `"/(?!oauth_)/"` when it reaches your command).
Escaping it will not help either.
In this case, we recommend running the command:
* directly from your IDE
* using `Linux shell`
* from the `Command Prompt`


## Help

You can get more help with this command by running:

`vendor/bin/doctrine-migrations help diff`
