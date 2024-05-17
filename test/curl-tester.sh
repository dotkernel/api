#!/bin/bash

# Bootstrap

OK='✅'
KO='❌'

if ! command -v curl > /dev/null 2>&1; then
    echo "$KO curl is not installed"
    exit 1
fi

# Define globals

body=""
status_code=0

# Functions

send_request() {
  response=$(curl -s -w "\n%{http_code}" $1)

  body=$(echo "$response" | sed '$d')
  status_code=$(echo "$response" | tail -n 1)
}

get() {
  send_request $1
}

post() {
  send_request $1
}

# Endpoints

endpoint_home="https://api.dotkernel.net"

# Tests

get $endpoint_home

if echo "$body" | grep -q '{"message":"Welcome to DotKernel API!"}'; then
  echo "$OK $endpoint_home: SUCCESS ($status_code)"
else
  echo "$KO $endpoint_home: FAILED ($status_code)"
  exit 1
fi
