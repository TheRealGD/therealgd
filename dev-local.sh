#!/bin/bash

docker-compose stop;
docker-compose rm -f -v;
docker-compose build --force-rm;
docker-compose up;
