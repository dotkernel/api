JOB=$3
COMMAND=$(echo "${JOB}" | jq -r '.command')

echo "Running $COMMAND"

#pecl install sqlite
#pecl install php8-sqlite3

apt-get install php8.2-sqlite3
apt-get install php8.3-sqlite3

echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"

apt-get install php8.2-sqlite
apt-get install php8.3-sqlite

#php -i | grep sqlite

if [[ ${COMMAND} =~ phpunit ]];then
  mv config/autoload/local.php.dist config/autoload/local.php
  mv config/autoload/mail.local.php.dist config/autoload/mail.local.php
  mv config/autoload/local.test.php.dist config/autoload/local.test.php
fi
