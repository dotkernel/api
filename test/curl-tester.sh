#!/bin/bash

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
access_token=""
refresh_token=""
halt_on_error=1

get $endpoint_home
if [[ $status_code -eq 200 ]] && echo "$body" | grep -q 'Welcome'; then
  echo "$OK [$status_code] Homepage can be accessed."
else
  echo "$KO [$status_code] Homepage can NOT be accessed."
  exit $halt_on_error
fi

# should not generate token for invalid credentials
post $endpoint_security_generate_token '{"grant_type":"password","client_id":"admin","client_secret":"admin","scope":"api","username":"incorrect","password":"incorrect"}'
if [[ $status_code -eq 400 ]] && echo "$body" | grep -q 'Invalid credentials.'; then
  echo "$OK [$status_code] Generating access token using invalid credentials returns 'Invalid credentials' and a 400 status code."
else
  echo "$KO [$status_code] Generating access token using invalid credentials returns unexpected response:"
  echo "$body"
  exit $halt_on_error
fi

# should generate valid token for valid credentials
post $endpoint_security_generate_token '{"grant_type":"password","client_id":"admin","client_secret":"admin","scope":"api","username":"admin","password":"dotkernel"}'
if [[ $status_code -eq 200 ]] && echo "$body" | grep -q '{"token_type":"Bearer","expires_in":86400,"access_token":'; then
  echo "$OK [$status_code] Generating access token using valid credentials returns tokens and a 200 status code."

  access_token=$(sed -n 's/.*"access_token":"\([^"]*\)".*/\1/p' <<< "$body")
  if [ -z "$access_token" ]; then
    echo "$KO Invalid access token detected: $access_token"
    exit $halt_on_error
  fi
  refresh_token=$(sed -n 's/.*"refresh_token":"\([^"]*\)".*/\1/p' <<< "$body")
  if [ -z "$refresh_token" ]; then
    echo "$KO Invalid refresh token detected: $refresh_token"
    exit $halt_on_error
  fi
else
  echo "$KO [$status_code] Generating access token using valid credentials returns unexpected response:"
  echo "$body"
  exit $halt_on_error
fi
