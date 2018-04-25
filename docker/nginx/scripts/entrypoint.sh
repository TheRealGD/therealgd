#!/bin/sh

# HTTPS Cert Update
crontab /certcrontab
cron

# Launch nginx, if it fails - try to obtain HTTPS & retry once
nginx -g 'daemon off;'                                     \
|| (                                                       \
    echo "Obtaining initial HTTPS Cert"                                                    && \
    echo "docker-run -ti therealgd_nginx --entrypoint=\"certbot --nginx\""                 && \
    mv /etc/nginx/nginx.conf /etc/nginx/nginx.conf.real                                    && \
    cp /etc/nginx/nginx.conf.fake /etc/nginx/nginx.conf                                    && \
    ./certbot-auto --nginx -n --agree-tos --email $ADMIN_EMAIL --domains $REDIRECT_DOMAINS && \
    killall nginx                                                                          && \
    cp /etc/nginx/nginx.conf.real /etc/nginx/nginx.conf                                    && \
    nginx -g 'daemon off;')
