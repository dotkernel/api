echo "$PWD"

cd ../

echo "$PWD"

echo "$2"

JOB=$3
COMMAND=$(echo "${JOB}" | jq -r '.command')

echo "$COMMAND"
#if [[ ! ${COMMAND} =~ phpunit ]];then
#    exit 0
#fi

#mv config/autoload/local.php.dist config/autoload/local.php
#mv config/autoload/mail.local.php.dist config/autoload/mail.local.php
#mv config/autoload/local.test.php.dist config/autoload/local.test.php