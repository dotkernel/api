echo "$PWD"

echo "$2"

JOB=$3
COMMAND=$(echo "${JOB}" | jq -r '.command')

ls -la

echo "$COMMAND"
#if [[ ${COMMAND} =~ phpunit ]];then
    #mv config/autoload/local.php.dist config/autoload/local.php
    #mv config/autoload/mail.local.php.dist config/autoload/mail.local.php
    #mv config/autoload/local.test.php.dist config/autoload/local.test.php

#fi