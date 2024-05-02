JOB=$3
PHP_VERSION=$4
COMMAND=$(echo "${JOB}" | jq -r '.command')

echo "Running $COMMAND"

if [[ ${COMMAND} =~ phpunit ]];then

#  apt-get update
  apt-get install php"${PHP_VERSION}"-sqlite3=8.2.18
#  apt-get install php8.2-sqlite3
#  apt-get install php8.3-sqlite3

  cp config/autoload/local.php.dist config/autoload/local.php
  cp config/autoload/mail.local.php.dist config/autoload/mail.local.php
  cp config/autoload/local.test.php.dist config/autoload/local.test.php

fi
