JOB=$3
PHP_VERSION=$4
COMMAND=$(echo "${JOB}" | jq -r '.command')

echo "Running pre-run $COMMAND"

if [[ ${COMMAND} =~ phpunit ]];then

  apt-get install php"${PHP_VERSION}"-sqlite3

  cp config/autoload/local.php.dist config/autoload/local.php
  cp config/autoload/local.test.php.dist config/autoload/local.test.php

fi
