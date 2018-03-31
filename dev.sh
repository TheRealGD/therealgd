#!/bin/bash
#source <(curl -s http://169.254.169.254/latest/user-data)
#source tempdevenv.sh

docker-compose stop;
docker-compose rm -f;
docker-compose build --force-rm;
docker-compose up nginx-dev;
