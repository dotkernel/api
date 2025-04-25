JOB=$3
PHP_VERSION=$4
COMMAND=$(echo "${JOB}" | jq -r '.command')

echo "Running pre-run $COMMAND"

if [[ ${COMMAND} =~ phpunit ]];then
  apt-get install php"${PHP_VERSION}"-sqlite3
fi
