#!/bin/sh

# HTTPS Cert Update
crontab /certcrontab
cron

# Launch Nginx
nginx -g 'daemon off;'
