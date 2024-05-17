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
