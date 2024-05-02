JOB=$3
PHP_VERSION=$5

COMMAND=$(echo "${JOB}" | jq -r '.command')

echo "Running $COMMAND"


apt-get install "php${PHP_VERSION}-sqlite3"

if [[ ${COMMAND} =~ phpunit ]];then
  mv config/autoload/local.php.dist config/autoload/local.php
  mv config/autoload/mail.local.php.dist config/autoload/mail.local.php
  mv config/autoload/local.test.php.dist config/autoload/local.test.php
fi
