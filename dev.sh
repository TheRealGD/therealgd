#!/bin/bash

docker-compose stop;
docker-compose rm -f;
docker-compose build --force-rm;
docker-compose up;
