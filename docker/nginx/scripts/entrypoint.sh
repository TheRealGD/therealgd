#!/bin/sh

# HTTPS Cert Update
crontab /certcrontab
cron

nginx -g 'daemon off;'
