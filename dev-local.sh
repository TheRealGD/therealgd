#!/bin/bash

export GIT_SHA=`git rev-parse --short HEAD`;
export GIT_BRANCH=`git rev-parse --abbrev-ref HEAD`;
docker-compose stop;
docker-compose rm -f -v;
docker-compose build --force-rm;
docker-compose up;

