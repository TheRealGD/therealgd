#!/bin/sh

adddate() {
  while IFS= read -r line; do
    echo "$(date) $line"
  done
}

if certbot renew --nginx --agree-tos | adddate >>/var/log/nginx/certbot.log; then
  echo "CertBot run OK" | adddate >>/var/log/nginx/certbot.log
else
  echo "CertBot run FAILED" | adddate >>/var/log/nginx/certbot.log
fi

sleep 2
nginx -s reload

echo "nginx restarted" | adddate >>/var/log/nginx/certbot.log
