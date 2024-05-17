get() {
  response=$(curl -s -w "\n%{http_code}" $1)

  body=$(echo "$response" | sed '$d')
  status_code=$(echo "$response" | tail -n 1)
}

post() {
  response=$(curl -s -w "\n%{http_code}" -X POST -H "Content-Type: application/json" -d $2 $1)

  body=$(echo "$response" | sed '$d')
  status_code=$(echo "$response" | tail -n 1)
}
