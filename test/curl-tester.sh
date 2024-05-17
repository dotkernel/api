#!/bin/bash

# Bootstrap

OK='✅'
KO='❌'

if ! command -v curl > /dev/null 2>&1; then
    echo "$KO curl is not installed"
    exit 1
fi

. test/curl/endpoints.sh
. test/curl/functions.sh

body=""
status_code=0

get $endpoint_home
if echo "$body" | grep -q 'Welcome'; then
  echo "$OK $endpoint_home: $status_code"
else
  echo "$KO $endpoint_home: $status_code"
  exit 1
fi
