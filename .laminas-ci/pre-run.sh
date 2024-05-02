JOB=$3
COMMAND=$(echo "${JOB}" | jq -r '.command')

echo "Running $COMMAND"

pecl install sqlite3

if [[ ${COMMAND} =~ phpunit ]];then
  mv config/autoload/local.php.dist config/autoload/local.php
  mv config/autoload/mail.local.php.dist config/autoload/mail.local.php
  mv config/autoload/local.test.php.dist config/autoload/local.test.php
fi