JOB=$3
PHP_VERSION=$4
COMMAND=$(echo "${JOB}" | jq -r '.command')

echo "Running $COMMAND @@@@@@@@@@@@@@@"
echo "PHP VERSION : ${PHP_VERSION}"

apt-get install php8.2-sqlite3
apt-get install php8.3-sqlite3

if [[ ${COMMAND} =~ phpunit ]];then
  mv config/autoload/local.php.dist config/autoload/local.php
  mv config/autoload/mail.local.php.dist config/autoload/mail.local.php
  mv config/autoload/local.test.php.dist config/autoload/local.test.php
fi
